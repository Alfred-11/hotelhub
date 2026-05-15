<?php
include '../../db.php';
session_start();

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo "Hotel ID not provided.";
    exit;
}

$hotel_id = intval($_GET['id']);

// Delete facilities first
$stmt1 = $conn->prepare("DELETE FROM hotel_facilities WHERE hotel_id = ?");
$stmt1->bind_param("i", $hotel_id);
$stmt1->execute();
$stmt1->close();

// Delete hotel
$stmt2 = $conn->prepare("DELETE FROM hotels WHERE hotel_id = ?");
$stmt2->bind_param("i", $hotel_id);

if ($stmt2->execute()) {
    echo "success";
} else {
    http_response_code(500);
    echo "Error deleting hotel.";
}

$stmt2->close();
$conn->close();
?>
