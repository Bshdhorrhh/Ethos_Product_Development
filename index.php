<?php
session_start();
require_once 'config/database.php';
require_once 'models/Entity.php';

$database = new Database();
$db = $database->getConnection();
$entity = new Entity($db);

// Get dashboard stats (simplified for performance)
$stats = $entity->getDashboardStats();

// Get recent entities (limited for performance)
$recentEntities = $entity->getAll(10, 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Entity Resolution System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Campus Entity Resolution System</h1>
            <p class="text-gray-600">Cross-source entity tracking and security monitoring</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Total Entities</h3>
                        <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['total_entities']) ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <i class="fas fa-user-check text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Active (7 days)</h3>
                        <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['active_today']) ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-red-100 rounded-lg">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Potentially Inactive</h3>
                        <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['inactive_alerts']) ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <i class="fas fa-database text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Data Sources</h3>
                        <p class="text-2xl font-bold text-gray-900">7</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Entity Search</h2>
            <form action="search.php" method="GET" class="flex gap-4">
                <div class="flex-1">
                    <input type="text" name="q" placeholder="Search by ID, name, email, card, device..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <select name="type" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="all">All Types</option>
                        <option value="student">Students</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
            </form>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <a href="alerts.php" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">View All Entities</h3>
                        <p class="text-gray-600 mt-1">Browse all <?= number_format($stats['total_entities']) ?> entities</p>
                    </div>
                    <div class="text-blue-600">
                        <i class="fas fa-list text-2xl"></i>
                    </div>
                </div>
            </a>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Entities</h3>
                <div class="space-y-3">
                    <?php foreach($recentEntities as $entity_item): ?>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <div>
                            <p class="font-medium text-gray-800"><?= htmlspecialchars($entity_item['name']) ?></p>
                            <p class="text-sm text-gray-500"><?= $entity_item['role'] ?> • <?= $entity_item['department'] ?></p>
                        </div>
                        <a href="entity.php?id=<?= $entity_item['entity_id'] ?>" 
                           class="px-3 py-1 bg-blue-100 text-blue-600 rounded-full text-sm hover:bg-blue-200 transition-colors">
                            View
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4 text-center">
                    <a href="browse.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        View All Entities →
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>