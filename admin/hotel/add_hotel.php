<?php
include '../../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect hotel info
    $name = $_POST['name'];
    $location = $_POST['location'];
    $address = $_POST['address'];
    $description = $_POST['description'];
    $checkin_time = $_POST['checkin_time'];
    $checkout_time = $_POST['checkout_time'];
    $cancellation_policy = $_POST['cancellation_policy'];
    $payment_policy = $_POST['payment_policy'];
    $rating = $_POST['rating']; 
    $price = $_POST['price'];



    // ===== Handle images =====
    $uploadsDir = __DIR__ . "/uploads/"; // absolute path
    if (!file_exists($uploadsDir)) mkdir($uploadsDir, 0755, true);

    // Helper function
    function uploadImage($file, $uploadsDir) {
        if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $uniqueName = time() . '_' . uniqid() . '.' . $ext;
            $targetPath = $uploadsDir . $uniqueName;
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                return "uploads/" . $uniqueName; // relative path to DB
            }
        }
        return ""; // if not uploaded
    }

    $image_main = uploadImage($_FILES['image_main'], $uploadsDir);
    $image_gallery1 = uploadImage($_FILES['image_gallery1'], $uploadsDir);
    $image_gallery2 = uploadImage($_FILES['image_gallery2'], $uploadsDir);
    $image_gallery3 = uploadImage($_FILES['image_gallery3'], $uploadsDir);

    // ===== Insert hotel =====
$stmt = $conn->prepare("INSERT INTO hotels 
(name, location, address, description, image_main, image_gallery1, image_gallery2, image_gallery3, checkin_time, checkout_time, cancellation_policy, payment_policy, rating, price) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "sssssssssssssd",
    $name,
    $location,
    $address,
    $description,
    $image_main,
    $image_gallery1,
    $image_gallery2,
    $image_gallery3,
    $checkin_time,
    $checkout_time,
    $cancellation_policy,
    $payment_policy,
    $rating,
    $price
);

    if ($stmt->execute()) {
        $hotel_id = $stmt->insert_id;

        // Insert facilities
        if (!empty($_POST['facilities'])) {
            foreach ($_POST['facilities'] as $facility) {
                $stmt2 = $conn->prepare("INSERT INTO hotel_facilities (hotel_id, facility_name) VALUES (?, ?)");
                $stmt2->bind_param("is", $hotel_id, $facility);
                $stmt2->execute();
                $stmt2->close();
            }
        }

        echo json_encode(["status" => "success", "message" => "Hotel added successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
