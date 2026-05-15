<?php
include '../../db.php';
header('Content-Type: application/json');

$room_id = intval($_POST['room_id'] ?? 0);
$room_type = $_POST['room_type'] ?? '';
$price = $_POST['price_per_night'] ?? 0;
$capacity = $_POST['capacity'] ?? '';
$beds = $_POST['beds'] ?? '';

if (!$room_id) {
    echo json_encode(['status' => 'error', 'message' => 'Room ID missing']);
    exit;
}

$stmt = $conn->prepare("UPDATE rooms SET room_type=?, price_per_night=?, capacity=?, beds=? WHERE room_id=?");
$stmt->bind_param("sdssi", $room_type, $price, $capacity, $beds, $room_id);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'room' => [
            'room_id' => $room_id,
            'room_type' => $room_type,
            'price_per_night' => $price,
            'capacity' => $capacity,
            'beds' => $beds
        ]
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
$stmt->close();
$conn->close();
