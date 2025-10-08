<?php
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

try {
    // Drop existing tables if they exist
    $conn->exec("DROP TABLE IF EXISTS cctv_frames, free_text_notes, lab_bookings, library_checkouts, wifi_logs, card_swipes, entities");
    
    // Create entities table
    $conn->exec("CREATE TABLE entities (
        entity_id VARCHAR(20) PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        role ENUM('student', 'staff') NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        department VARCHAR(50),
        student_id VARCHAR(20),
        staff_id VARCHAR(20),
        card_id VARCHAR(20),
        device_hash VARCHAR(50),
        face_id VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Create card_swipes table
    $conn->exec("CREATE TABLE card_swipes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        card_id VARCHAR(20),
        location_id VARCHAR(50),
        timestamp DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_card_id (card_id),
        INDEX idx_timestamp (timestamp)
    )");

    // Create wifi_logs table
    $conn->exec("CREATE TABLE wifi_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        device_hash VARCHAR(50),
        ap_id VARCHAR(50),
        timestamp DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_device_hash (device_hash),
        INDEX idx_timestamp (timestamp)
    )");

    // Create library_checkouts table
    $conn->exec("CREATE TABLE library_checkouts (
        checkout_id VARCHAR(20) PRIMARY KEY,
        entity_id VARCHAR(20),
        book_id VARCHAR(20),
        timestamp DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_entity_id (entity_id),
        INDEX idx_timestamp (timestamp)
    )");

    // Create lab_bookings table
    $conn->exec("CREATE TABLE lab_bookings (
        booking_id VARCHAR(20) PRIMARY KEY,
        entity_id VARCHAR(20),
        room_id VARCHAR(50),
        start_time DATETIME,
        end_time DATETIME,
        attended ENUM('YES', 'NO'),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_entity_id (entity_id),
        INDEX idx_start_time (start_time)
    )");

    // Create free_text_notes table
    $conn->exec("CREATE TABLE free_text_notes (
        note_id VARCHAR(20) PRIMARY KEY,
        entity_id VARCHAR(20),
        category ENUM('rsvp', 'helpdesk', 'maintenance', 'feedback', 'incident'),
        text TEXT,
        timestamp DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_entity_id (entity_id),
        INDEX idx_timestamp (timestamp)
    )");

    // Create cctv_frames table
    $conn->exec("CREATE TABLE cctv_frames (
        frame_id VARCHAR(20) PRIMARY KEY,
        location_id VARCHAR(50),
        timestamp DATETIME,
        face_id VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_face_id (face_id),
        INDEX idx_timestamp (timestamp)
    )");

    echo "Database tables created successfully!<br>";

} catch(PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
}
?>