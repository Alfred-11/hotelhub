<?php
include '../../db.php';
session_start();

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo "Room ID not provided.";
    exit;
}

$room_id = intval($_GET['id']);

// If your rooms have related data (like bookings), delete those first if needed
// Example: $stmt1 = $conn->prepare("DELETE FROM room_bookings WHERE room_id = ?");
// $stmt1->bind_param("i", $room_id);
// $stmt1->execute();
// $stmt1->close();

// Delete room
$stmt2 = $conn->prepare("DELETE FROM rooms WHERE room_id = ?");
$stmt2->bind_param("i", $room_id);

if ($stmt2->execute()) {
    echo "success";
} else {
    http_response_code(500);
    echo "Error deleting room.";
}

$stmt2->close();
$conn->close();
?>
