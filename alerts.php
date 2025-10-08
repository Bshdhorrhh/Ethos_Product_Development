<?php
session_start();
require_once 'config/database.php';
require_once 'models/Entity.php';

$database = new Database();
$db = $database->getConnection();
$entity = new Entity($db);

// Get all entities and check for inactivity (limited for performance)
$allEntities = $entity->getAll(200, 0); // Limit to 200 for performance

$alerts = [];
foreach ($allEntities as $e) {
    $entityObj = new Entity($db);
    if ($entityObj->getById($e['entity_id'])) {
        if ($entityObj->isInactive(12)) {
            $lastSeen = $entityObj->getLastSeen();
            $alerts[] = [
                'entity' => $entityObj,
                'last_seen' => $lastSeen ? [
                    'time' => $lastSeen['timestamp'],
                    'description' => $lastSeen['description']
                ] : null
            ];
        }
    }
}

// Helper function for time ago
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } else {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Alerts - Campus Entity System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Security Alerts</h1>
                <p class="text-gray-600">Entities inactive for 12+ hours</p>
            </div>
            <a href="index.php" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
        </div>

        <!-- Alerts Summary -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Inactivity Alerts</h2>
                    <p class="text-gray-600">Showing first 200 entities for performance</p>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-bold <?= count($alerts) > 0 ? 'text-red-600' : 'text-green-600' ?>">
                        <?= count($alerts) ?>
                    </span>
                    <p class="text-sm text-gray-600">Active Alerts</p>
                </div>
            </div>
        </div>

        <!-- Alerts List -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <?php if(count($alerts) > 0): ?>
            <div class="space-y-4">
                <?php foreach($alerts as $alert): ?>
                <div class="border border-red-200 rounded-lg p-4 bg-red-50">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                                <h3 class="text-lg font-semibold text-gray-800">
                                    <?= htmlspecialchars($alert['entity']->name) ?>
                                </h3>
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    <?= $alert['entity']->role == 'student' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' ?>">
                                    <?= ucfirst($alert['entity']->role) ?>
                                </span>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-2">
                                <?= $alert['entity']->email ?> • <?= $alert['entity']->department ?>
                            </p>

                            <?php if($alert['last_seen']): ?>
                            <div class="text-sm">
                                <p class="text-gray-700">
                                    <span class="font-medium">Last Activity:</span> 
                                    <?= $alert['last_seen']['description'] ?>
                                </p>
                                <p class="text-gray-500">
                                    <?= date('M j, Y g:i A', strtotime($alert['last_seen']['time'])) ?>
                                    (<?= timeAgo($alert['last_seen']['time']) ?>)
                                </p>
                            </div>
                            <?php else: ?>
                            <p class="text-red-700 text-sm font-medium">No activity ever recorded</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="ml-4">
                            <a href="entity.php?id=<?= $alert['entity']->entity_id ?>" 
                               class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                Investigate
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-8">
                <i class="fas fa-check-circle text-4xl text-green-500 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Security Alerts</h3>
                <p class="text-gray-600">All checked entities have been active within the last 12 hours.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>