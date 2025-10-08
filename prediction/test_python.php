<?php
// test_python.php - Test connection to Python
echo "<h2>Testing Python Connection</h2>";

// Test command
$command = 'python --version 2>&1';
$output = shell_exec($command);

echo "<p>Python Version: " . $output . "</p>";

// Test if we can run the Python script
$test_command = 'python data_with_input.py predict "2025-09-16 23:56:00" "2025-09-17 02:59:00" "E100011" 2>&1';
$test_output = shell_exec($test_command);

echo "<h3>Python Script Test Output:</h3>";
echo "<pre>" . htmlspecialchars($test_output) . "</pre>";

// Check if model files exist
$model_files = [
    'trained_location_model.joblib',
    'model_feature_columns.json',
    'data_with_input.py'
];

echo "<h3>Required Files:</h3>";
foreach ($model_files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ $file exists</p>";
    } else {
        echo "<p style='color: red;'>✗ $file missing</p>";
    }
}
?>