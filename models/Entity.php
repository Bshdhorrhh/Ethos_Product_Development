<?php
class Entity {
    private $conn;
    private $table_name = "entities";

    public $entity_id;
    public $name;
    public $role;
    public $email;
    public $department;
    public $student_id;
    public $staff_id;
    public $card_id;
    public $device_hash;
    public $face_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($limit = null, $offset = 0) {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name";
        if ($limit !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($query);
        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch()['total'];
    }

    public function getById($entity_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE entity_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $entity_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->entity_id = $row['entity_id'];
            $this->name = $row['name'];
            $this->role = $row['role'];
            $this->email = $row['email'];
            $this->department = $row['department'];
            $this->student_id = $row['student_id'];
            $this->staff_id = $row['staff_id'];
            $this->card_id = $row['card_id'];
            $this->device_hash = $row['device_hash'];
            $this->face_id = $row['face_id'];
            return true;
        }
        return false;
    }

    public function search($query, $type = 'all', $limit = 100) {
        $sql = "SELECT * FROM " . $this->table_name . " WHERE 
                entity_id LIKE ? OR name LIKE ? OR email LIKE ? OR 
                student_id LIKE ? OR staff_id LIKE ? OR card_id LIKE ? OR 
                device_hash LIKE ? OR face_id LIKE ?";
        
        if ($type !== 'all') {
            $sql .= " AND role = ?";
        }
        
        $sql .= " ORDER BY name LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        
        $searchTerm = "%$query%";
        if ($type !== 'all') {
            $stmt->bindValue(1, $searchTerm);
            $stmt->bindValue(2, $searchTerm);
            $stmt->bindValue(3, $searchTerm);
            $stmt->bindValue(4, $searchTerm);
            $stmt->bindValue(5, $searchTerm);
            $stmt->bindValue(6, $searchTerm);
            $stmt->bindValue(7, $searchTerm);
            $stmt->bindValue(8, $searchTerm);
            $stmt->bindValue(9, $type);
            $stmt->bindValue(10, (int)$limit, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(1, $searchTerm);
            $stmt->bindValue(2, $searchTerm);
            $stmt->bindValue(3, $searchTerm);
            $stmt->bindValue(4, $searchTerm);
            $stmt->bindValue(5, $searchTerm);
            $stmt->bindValue(6, $searchTerm);
            $stmt->bindValue(7, $searchTerm);
            $stmt->bindValue(8, $searchTerm);
            $stmt->bindValue(9, (int)$limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Optimized activity methods with limits
    public function getCardSwipes($limit = 50) {
        if (!$this->card_id) return [];
        
        $query = "SELECT * FROM card_swipes WHERE card_id = ? 
                 ORDER BY timestamp DESC LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->card_id);
        $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getWifiLogs($limit = 50) {
        if (!$this->device_hash) return [];
        
        $query = "SELECT * FROM wifi_logs WHERE device_hash = ? 
                 ORDER BY timestamp DESC LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->device_hash);
        $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLibraryCheckouts($limit = 50) {
        $query = "SELECT * FROM library_checkouts WHERE entity_id = ? 
                 ORDER BY timestamp DESC LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->entity_id);
        $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLabBookings($limit = 50) {
        $query = "SELECT * FROM lab_bookings WHERE entity_id = ? 
                 ORDER BY start_time DESC LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->entity_id);
        $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFreeTextNotes($limit = 50) {
        $query = "SELECT * FROM free_text_notes WHERE entity_id = ? 
                 ORDER BY timestamp DESC LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->entity_id);
        $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCctvFrames($limit = 50) {
        if (!$this->face_id) return [];
        
        $query = "SELECT * FROM cctv_frames WHERE face_id = ? 
                 ORDER BY timestamp DESC LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->face_id);
        $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Optimized timeline with limits
    public function getTimeline($limit = 100) {
        $timeline = [];

        // Get activities with limits
        $cardSwipes = $this->getCardSwipes(20);
        foreach ($cardSwipes as $row) {
            $timeline[] = [
                'timestamp' => $row['timestamp'],
                'type' => 'card_swipe',
                'location' => $row['location_id'],
                'description' => "Card swipe at {$row['location_id']}",
                'confidence' => 1.0,
                'raw_data' => $row
            ];
        }

        $wifiLogs = $this->getWifiLogs(20);
        foreach ($wifiLogs as $row) {
            $timeline[] = [
                'timestamp' => $row['timestamp'],
                'type' => 'wifi_association',
                'location' => $row['ap_id'],
                'description' => "WiFi connection at {$row['ap_id']}",
                'confidence' => 0.9,
                'raw_data' => $row
            ];
        }

        $libraryCheckouts = $this->getLibraryCheckouts(20);
        foreach ($libraryCheckouts as $row) {
            $timeline[] = [
                'timestamp' => $row['timestamp'],
                'type' => 'library_checkout',
                'location' => 'Library',
                'description' => "Checked out book {$row['book_id']}",
                'confidence' => 1.0,
                'raw_data' => $row
            ];
        }

        $labBookings = $this->getLabBookings(20);
        foreach ($labBookings as $row) {
            $attended = $row['attended'] == 'YES';
            $timeline[] = [
                'timestamp' => $row['start_time'],
                'type' => 'lab_booking',
                'location' => $row['room_id'],
                'description' => $attended ? 
                    "Attended lab booking at {$row['room_id']}" : 
                    "Scheduled lab booking at {$row['room_id']} (not attended)",
                'confidence' => $attended ? 1.0 : 0.6,
                'raw_data' => $row
            ];
        }

        $freeTextNotes = $this->getFreeTextNotes(20);
        foreach ($freeTextNotes as $row) {
            $timeline[] = [
                'timestamp' => $row['timestamp'],
                'type' => 'text_note',
                'location' => 'System',
                'description' => "[{$row['category']}] {$row['text']}",
                'confidence' => 0.8,
                'raw_data' => $row
            ];
        }

        $cctvFrames = $this->getCctvFrames(20);
        foreach ($cctvFrames as $row) {
            $timeline[] = [
                'timestamp' => $row['timestamp'],
                'type' => 'cctv_sighting',
                'location' => $row['location_id'],
                'description' => "CCTV sighting at {$row['location_id']}",
                'confidence' => 0.85,
                'raw_data' => $row
            ];
        }

        // Sort by timestamp and limit
        usort($timeline, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        return array_slice($timeline, 0, $limit);
    }

    public function getLastSeen() {
        $timeline = $this->getTimeline(1);
        return count($timeline) > 0 ? $timeline[0] : null;
    }

    public function isInactive($hours = 12) {
        $lastSeen = $this->getLastSeen();
        if (!$lastSeen) return true;

        $lastSeenTime = strtotime($lastSeen['timestamp']);
        $currentTime = time();
        return ($currentTime - $lastSeenTime) > ($hours * 3600);
    }

    // Simple dashboard stats without complex joins
    public function getDashboardStats() {
        $stats = [];

        // Total entities
        $stats['total_entities'] = $this->getCount();

        // Simple active count (entities with any activity in last 7 days)
        // This is a simplified version for performance
        $query = "SELECT COUNT(DISTINCT entity_id) as count FROM (
            SELECT entity_id FROM library_checkouts WHERE timestamp >= NOW() - INTERVAL 7 DAY
            UNION 
            SELECT entity_id FROM lab_bookings WHERE start_time >= NOW() - INTERVAL 7 DAY
            UNION 
            SELECT entity_id FROM free_text_notes WHERE timestamp >= NOW() - INTERVAL 7 DAY
        ) as recent_activity";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['active_today'] = $stmt->fetch()['count'];

        // Simple inactive count (estimate)
        $stats['inactive_alerts'] = max(0, $stats['total_entities'] - $stats['active_today']);

        return $stats;
    }
}
?>