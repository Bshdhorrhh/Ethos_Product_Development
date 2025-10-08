<?php
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

try {
    // Add performance indexes
    $indexes = [
        "ALTER TABLE entities ADD INDEX idx_entity_role (role)",
        "ALTER TABLE entities ADD INDEX idx_entity_department (department)",
        "ALTER TABLE card_swipes ADD INDEX idx_swipe_card_timestamp (card_id, timestamp)",
        "ALTER TABLE wifi_logs ADD INDEX idx_wifi_device_timestamp (device_hash, timestamp)",
        "ALTER TABLE library_checkouts ADD INDEX idx_checkout_entity_timestamp (entity_id, timestamp)",
        "ALTER TABLE lab_bookings ADD INDEX idx_booking_entity_start (entity_id, start_time)",
        "ALTER TABLE free_text_notes ADD INDEX idx_note_entity_timestamp (entity_id, timestamp)",
        "ALTER TABLE cctv_frames ADD INDEX idx_cctv_face_timestamp (face_id, timestamp)",
        "ALTER TABLE card_swipes ADD INDEX idx_swipe_timestamp (timestamp)",
        "ALTER TABLE wifi_logs ADD INDEX idx_wifi_timestamp (timestamp)"
    ];

    foreach ($indexes as $index) {
        $conn->exec($index);
        echo "Created index: " . $index . "<br>";
    }

    echo "Database optimization completed!";

} catch(PDOException $e) {
    echo "Error optimizing database: " . $e->getMessage();
}
?>