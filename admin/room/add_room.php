<?php
include '../../db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $hotel_id = $_POST['hotel_id'];
    $room_type = $_POST['room_type'];
    $price_per_night = $_POST['price_per_night'];
    $capacity = $_POST['capacity'];
    $beds = $_POST['beds'];

    $stmt = $conn->prepare("INSERT INTO rooms (hotel_id, room_type, price_per_night, capacity, beds) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isdss", $hotel_id, $room_type, $price_per_night, $capacity, $beds);

    if($stmt->execute()){
        // Return JSON response like hotel add
        echo json_encode([
            "status" => "success",
            "message" => "Room added successfully!"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => $stmt->error
        ]);
    }

    $stmt->close();
    $conn->close();
}
?>
