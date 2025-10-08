<?php
session_start();
require_once 'config/database.php';
require_once 'models/Entity.php';

$database = new Database();
$db = $database->getConnection();
$entity = new Entity($db);

$query = $_GET['q'] ?? '';
$type = $_GET['type'] ?? 'all';

$results = [];
if (!empty($query)) {
    $results = $entity->search($query, $type);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Campus Entity System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Entity Search</h1>
                <p class="text-gray-600">Cross-source entity resolution</p>
            </div>
            <a href="index.php" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
        </div>

        <!-- Search Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form action="search.php" method="GET" class="flex gap-4">
                <div class="flex-1">
                    <input type="text" name="q" value="<?= htmlspecialchars($query) ?>" 
                           placeholder="Search by ID, name, email, card, device..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <select name="type" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="all" <?= $type == 'all' ? 'selected' : '' ?>>All Types</option>
                        <option value="student" <?= $type == 'student' ? 'selected' : '' ?>>Students</option>
                        <option value="staff" <?= $type == 'staff' ? 'selected' : '' ?>>Staff</option>
                    </select>
                </div>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
            </form>
        </div>

        <!-- Results -->
        <?php if (!empty($query)): ?>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">
                Search Results for "<?= htmlspecialchars($query) ?>"
                <span class="text-sm font-normal text-gray-600">(<?= count($results) ?> results)</span>
            </h2>

            <?php if (count($results) > 0): ?>
            <div class="space-y-4">
                <?php foreach($results as $result): ?>
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-800">
                                <?= htmlspecialchars($result['name']) ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    <?= $result['role'] == 'student' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' ?>">
                                    <?= ucfirst($result['role']) ?>
                                </span>
                            </h3>
                            <p class="text-gray-600"><?= $result['email'] ?> • <?= $result['department'] ?></p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <?php if($result['student_id']): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-gray-100 text-gray-800">
                                    <i class="fas fa-id-card mr-1"></i>Student: <?= $result['student_id'] ?>
                                </span>
                                <?php endif; ?>
                                <?php if($result['staff_id']): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-gray-100 text-gray-800">
                                    <i class="fas fa-id-card mr-1"></i>Staff: <?= $result['staff_id'] ?>
                                </span>
                                <?php endif; ?>
                                <?php if($result['card_id']): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-blue-100 text-blue-800">
                                    <i class="fas fa-credit-card mr-1"></i>Card: <?= $result['card_id'] ?>
                                </span>
                                <?php endif; ?>
                                <?php if($result['device_hash']): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-purple-100 text-purple-800">
                                    <i class="fas fa-wifi mr-1"></i>Device: <?= substr($result['device_hash'], 0, 8) ?>...
                                </span>
                                <?php endif; ?>
                                <?php if($result['face_id']): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-orange-100 text-orange-800">
                                    <i class="fas fa-camera mr-1"></i>Face: <?= $result['face_id'] ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="ml-4">
                            <a href="entity.php?id=<?= $result['entity_id'] ?>" 
                               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                View Profile
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-8">
                <i class="fas fa-search text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No entities found</h3>
                <p class="text-gray-600">Try searching with different terms or check the spelling.</p>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>