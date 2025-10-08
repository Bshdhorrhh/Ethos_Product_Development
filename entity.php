<?php
session_start();
require_once 'config/database.php';
require_once 'models/Entity.php';

$database = new Database();
$db = $database->getConnection();
$entity = new Entity($db);

$entity_id = $_GET['id'] ?? '';

if (!$entity->getById($entity_id)) {
    header("Location: index.php");
    exit;
}

$timeline = $entity->getTimeline(50);
$lastSeen = $entity->getLastSeen();
$isInactive = $entity->isInactive(12);

// Check if user image exists
$userImage = null;
if ($entity->face_id) {
    $imagePath = "data/face_images/{$entity->face_id}.jpg";
    if (file_exists($imagePath)) {
        $userImage = $imagePath;
    }
}

// Fallback to default image if no user image found
if (!$userImage) {
    $userImage = "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTI4IiBoZWlnaHQ9IjEyOCIgdmlld0JveD0iMCAwIDEyOCAxMjgiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMjgiIGhlaWdodD0iMTI4IiByeD0iMTYiIGZpbGw9IiNFRUVFRUUiLz4KPHBhdGggZD0iTTY0IDY0QzcwLjYyNzQgNjQgNzYgNTguNjI3NCA3NiA1MkM3NiA0NS4zNzI2IDcwLjYyNzQgNDAgNjQgNDBDNTcuMzcyNiA0MCA1MiA0NS4zNzI2IDUyIDUyQzUyIDU4LjYyNzQgNTcuMzcyNiA2NCA2NCA2NFoiIGZpbGw9IiM5QTlBOUEiLz4KPHBhdGggZD0iTTY0IDcwQzcyLjgzNjYgNzAgODAgNzcuMTYzNCA4MCA4NlY4OEM4MCA5MC4yMDkyIDc4LjIwOTIgOTIgNzYgOTJINTJDNDkuNzkwOSA5MiA0OCA5MC4yMDkyIDQ4IDg4Vjg2QzQ4IDc3LjE2MzQgNTUuMTYzNCA3MCA2NCA3MFoiIGZpbGw9IiM5QTlBOUEiLz4KPC9zdmc+Cg==";
}

// PREDICTION INTEGRATION
$prediction = null;
$predictionError = null;

if ($lastSeen) {
    // Use last seen time as start time and current time as end time
    $start_time = $lastSeen['timestamp'];
    $end_time = date('Y-m-d H:i:s');
    $entity_id_for_prediction = $entity->entity_id;
    
    // Call the prediction function
    function run_prediction($start_time, $end_time, $entity_id) {
        $python_script = __DIR__ . '/data_with_input.py';
        $command = 'python "' . $python_script . '" predict "' . $start_time . '" "' . $end_time . '" "' . $entity_id . '"';
        $output = shell_exec($command . ' 2>&1');
        return $output;
    }
    
    $python_output = run_prediction($start_time, $end_time, $entity_id_for_prediction);
    
    // Parse the JSON output from Python
    $json_pattern = '/\{"most_likely_location".*?"status".*?\}$/';
    preg_match($json_pattern, $python_output, $matches);
    
    if (!empty($matches)) {
        $json_output = $matches[0];
        $prediction = json_decode($json_output, true);
        
        if (!$prediction || !isset($prediction['status'])) {
            $predictionError = "Failed to parse prediction result";
        }
    } else {
        $predictionError = "No prediction data received";
    }
}

$breakdown = $prediction['breakdown'];
arsort($breakdown); // Sort by percentage in descending order
$top_locations = array_slice($breakdown, 0, 3, true); // Get top 3 locations

$counter = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $entity->name ?> - Entity Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <img class="h-16 w-16 rounded-full object-cover border-2 border-gray-300" 
                         src="<?= $userImage ?>" 
                         alt="<?= htmlspecialchars($entity->name) ?>"
                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHZpZXdCb3g9IjAgMCA2NCA2NCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjY0IiBoZWlnaHQ9IjY0IiByeD0iOCIgZmlsbD0iI0VFRUVFRSIvPgo8cGF0aCBkPSJNMzIgMzJDMzUuMzEzOCAzMiAzOCAyOS4zMTM4IDM4IDI2QzM4IDIyLjY4NjIgMzUuMzEzOCAyMCAzMiAyMEMyOC42ODYyIDIwIDI2IDIyLjY4NjIgMjYgMjZDMjYgMjkuMzEzOCAyOC42ODYyIDMyIDMyIDMyWiIgZmlsbD0iIzlBOUE5QSIvPgo8cGF0aCBkPSJNMzIgMzVDMzYuNDE4MyAzNSA0MCAzOC41ODE3IDQwIDQzVjQ0QzQwIDQ1LjEwNDYgMzkuMTA0NiA0NiAzOCA0NkgxNkMxNC44OTU0IDQ2IDE0IDQ1LjEwNDYgMTQgNDRWNDNDMTQgMzguNTgxNyAxNy41ODE3IDM1IDIyIDM1SDMyWiIgZmlsbD0iIzlBOUE5QSIvPgo8L3N2Zz4K'">
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800"><?= htmlspecialchars($entity->name) ?></h1>
                    <p class="text-gray-600">Entity ID: <?= $entity->entity_id ?></p>
                </div>
            </div>
            <a href="index.php" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
        </div>

        <!-- Alert Banner -->
        <?php if($isInactive): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span class="font-bold">Security Alert: </span>
                <span class="ml-2">This entity has been inactive for more than 12 hours</span>
            </div>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Entity Info -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <!-- Large User Image -->
                    <div class="flex justify-center mb-6">
                        <div class="relative">
                            <img class="h-32 w-32 rounded-full object-cover border-4 
                                <?= $isInactive ? 'border-red-300' : 'border-green-300' ?>" 
                                 src="<?= $userImage ?>" 
                                 alt="<?= htmlspecialchars($entity->name) ?>"
                                 onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTI4IiBoZWlnaHQ9IjEyOCIgdmlld0JveD0iMCAwIDEyOCAxMjgiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMjgiIGhlaWdodD0iMTI4IiByeD0iMTYiIGZpbGw9IiNFRUVFRUUiLz4KPHBhdGggZD0iTTY0IDY0QzcwLjYyNzQgNjQgNzYgNTguNjI3NCA3NiA1MkM3NiA0NS4zNzI2IDcwLjYyNzQgNDAgNjQgNDBDNTcuMzcyNiA0MCA1MiA0NS4zNzI2IDUyIDUyQzUyIDU4LjYyNzQgNTcuMzcyNiA2NCA2NCA2NFoiIGZpbGw9IiM5QTlBOUEiLz4KPHBhdGggZD0iTTY0IDcwQzcyLjgzNjYgNzAgODAgNzcuMTYzNCA4MCA4NlY4OEM4MCA5MC4yMDkyIDc4LjIwOTIgOTIgNzYgOTJINTJDNDkuNzkwOSA5MiA0OCA5MC4yMDkyIDQ4IDg4Vjg2QzQ4IDc3LjE2MzQgNTUuMTYzNCA3MCA2NCA3MFoiIGZpbGw9IiM5QTlBOUEiLz4KPC9zdmc+Cg=='">
                            <?php if($isInactive): ?>
                            <div class="absolute -bottom-2 -right-2 bg-red-500 text-white rounded-full p-1">
                                <i class="fas fa-exclamation-triangle text-xs"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h2 class="text-xl font-bold text-gray-800 mb-4">Entity Information</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Role</label>
                            <p class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    <?= $entity->role == 'student' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' ?>">
                                    <?= ucfirst($entity->role) ?>
                                </span>
                            </p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500">Email</label>
                            <p class="mt-1 text-gray-900"><?= $entity->email ?></p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500">Department</label>
                            <p class="mt-1 text-gray-900"><?= $entity->department ?></p>
                        </div>

                        <?php if($entity->student_id): ?>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Student ID</label>
                            <p class="mt-1 text-gray-900"><?= $entity->student_id ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if($entity->staff_id): ?>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Staff ID</label>
                            <p class="mt-1 text-gray-900"><?= $entity->staff_id ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if($entity->card_id): ?>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Card ID</label>
                            <p class="mt-1 text-gray-900"><?= $entity->card_id ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if($entity->device_hash): ?>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Device Hash</label>
                            <p class="mt-1 text-gray-900 font-mono text-sm"><?= $entity->device_hash ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if($entity->face_id): ?>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Face ID</label>
                            <p class="mt-1 text-gray-900"><?= $entity->face_id ?></p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Image Status -->
                    <div class="mt-6 p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600 flex items-center">
                            <i class="fas fa-image mr-2"></i>
                            <?php if($entity->face_id && file_exists("data/face_images/{$entity->face_id}.jpg")): ?>
                                <span class="text-green-600">Face image verified</span>
                            <?php elseif($entity->face_id): ?>
                                <span class="text-yellow-600">Face ID exists but image not found</span>
                            <?php else: ?>
                                <span class="text-gray-500">No face ID assigned</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <!-- Last Seen -->
                <?php if($lastSeen): ?>
                <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Last Seen</h2>
                    <div class="space-y-2">
                        <p class="text-gray-900"><?= $lastSeen['description'] ?></p>
                        <p class="text-sm text-gray-500">
                            <?= date('M j, Y g:i A', strtotime($lastSeen['timestamp'])) ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Column - Timeline and Prediction -->
            <div class="lg:col-span-2">
                <!-- PREDICTION SECTION -->
                <?php if($lastSeen): ?>
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-map-marker-alt text-blue-500 mr-2"></i>
                        Current Location Prediction
                    </h2>
                    
                    <?php if($prediction && $prediction['status'] === 'success'): ?>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-lg font-semibold text-green-800">Prediction Successful</h3>
                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                    <?= number_format($prediction['confidence'] * 100, 1) ?>% Confidence
                                </span>
                            </div>
                            
                            <div class="mb-4">
                                <p class="text-gray-700 mb-1">Most Likely Location:</p>
                                <p class="text-2xl font-bold text-green-700"><?= $prediction['most_likely_location'] ?></p>
                            </div>
                            
                            <div class="mt-4">
                                <h4 class="font-medium text-gray-700 mb-2">Location Breakdown:</h4>
                                <div class="space-y-2">
                                    <?php foreach($top_locations as $location => $percentage): ?>
                                    <div>
                                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                                            <span>Location <?= $location ?></span>
                                            <span><?= number_format($percentage * 100, 1) ?>%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full" 
                                                 style="width: <?= $percentage * 100 ?>%"></div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="mt-4 text-xs text-gray-500">
                                <p>Prediction period: <?= date('M j, Y g:i A', strtotime($start_time)) ?> to <?= date('M j, Y g:i A', strtotime($end_time)) ?></p>
                            </div>
                        </div>
                    <?php elseif($predictionError): ?>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                                <h3 class="text-lg font-semibold text-red-800">Prediction Failed</h3>
                            </div>
                            <p class="text-red-700 mt-2"><?= $predictionError ?></p>
                        </div>
                    <?php else: ?>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <i class="fas fa-clock text-yellow-500 mr-2"></i>
                                <h3 class="text-lg font-semibold text-yellow-800">Prediction Processing</h3>
                            </div>
                            <p class="text-yellow-700 mt-2">Location prediction is being calculated...</p>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Timeline -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-800">Activity Timeline</h2>
                        <div class="text-sm text-gray-600">
                            <?= count($timeline) ?> activities found (showing last 50)
                        </div>
                    </div>

                    <div class="space-y-4">
                        <?php if(count($timeline) > 0): ?>
                            <?php foreach($timeline as $event): ?>
                            <div class="flex items-start space-x-4 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="flex-shrink-0 w-3 h-3 mt-2 rounded-full 
                                    <?= $event['type'] == 'card_swipe' ? 'bg-green-500' :
                                       ($event['type'] == 'wifi_association' ? 'bg-blue-500' :
                                       ($event['type'] == 'library_checkout' ? 'bg-purple-500' :
                                       ($event['type'] == 'lab_booking' ? 'bg-orange-500' :
                                       ($event['type'] == 'text_note' ? 'bg-red-500' : 'bg-gray-500')))) ?>">
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900"><?= $event['description'] ?></p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <?= date('M j, Y g:i A', strtotime($event['timestamp'])) ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <?= str_replace('_', ' ', ucfirst($event['type'])) ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <i class="fas fa-history text-4xl text-gray-400 mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No activity recorded</h3>
                                <p class="text-gray-600">This entity has no recorded activities across all data sources.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Function to handle image loading errors
        document.addEventListener('DOMContentLoaded', function() {
            const profileImage = document.querySelector('img[alt="<?= htmlspecialchars($entity->name) ?>"]');
            if (profileImage) {
                profileImage.addEventListener('click', function() {
                    const modal = document.createElement('div');
                    modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50';
                    modal.innerHTML = `
                        <div class="relative">
                            <img src="${this.src}" class="max-w-4xl max-h-4xl rounded-lg" alt="Large view of <?= htmlspecialchars($entity->name) ?>">
                            <button class="absolute top-4 right-4 text-white text-2xl bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center">
                                &times;
                            </button>
                        </div>
                    `;
                    document.body.appendChild(modal);
                    
                    modal.querySelector('button').addEventListener('click', function() {
                        document.body.removeChild(modal);
                    });
                    
                    modal.addEventListener('click', function(e) {
                        if (e.target === modal) {
                            document.body.removeChild(modal);
                        }
                    });
                });
                
                profileImage.style.cursor = 'pointer';
                profileImage.title = 'Click to view larger image';
            }
        });
    </script>
</body>
</html>