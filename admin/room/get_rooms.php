<?php
include '../../db.php';

$rooms = [];

// Fetch rooms with hotel name
$result = $conn->query("
    SELECT r.*, h.name AS hotel_name 
    FROM rooms r
    LEFT JOIN hotels h ON r.hotel_id = h.hotel_id
    ORDER BY r.room_id DESC
");

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rooms[] = $row;
    }
}

echo json_encode($rooms);

$conn->close();
?>
