<?php
include '../../db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

$hotel_id = intval($_POST['hotel_id']);
$name = trim($_POST['name']);
$location = trim($_POST['location']);
$address = trim($_POST['address']);
$description = trim($_POST['description']);
$checkin_time = $_POST['checkin_time'] ?? null;
$checkout_time = $_POST['checkout_time'] ?? null;
$cancellation_policy = trim($_POST['cancellation_policy']);
$payment_policy = trim($_POST['payment_policy']);
$rating = floatval($_POST['rating']);
$price = floatval($_POST['price']);

// Basic validation
if (empty($name) || empty($location) || empty($price)) {
    die("Please fill all required fields.");
}

// ===== Handle Image Uploads =====
$uploadsDir = __DIR__ . "/uploads/";
if (!file_exists($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

function uploadImage($file, $uploadsDir) {
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed) || $file['size'] > $maxSize) {
            return "";
        }
        $uniqueName = time() . '_' . uniqid() . '.' . $ext;
        $targetPath = $uploadsDir . $uniqueName;
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return "uploads/" . $uniqueName;
        }
    }
    return "";
}

// Fetch existing images to keep them if not replaced
$existing = $conn->query("SELECT image_main, image_gallery1, image_gallery2, image_gallery3 FROM hotels WHERE hotel_id = $hotel_id")->fetch_assoc();

$image_main = uploadImage($_FILES['image_main'], $uploadsDir) ?: $existing['image_main'];
$image_gallery1 = uploadImage($_FILES['image_gallery1'], $uploadsDir) ?: $existing['image_gallery1'];
$image_gallery2 = uploadImage($_FILES['image_gallery2'], $uploadsDir) ?: $existing['image_gallery2'];
$image_gallery3 = uploadImage($_FILES['image_gallery3'], $uploadsDir) ?: $existing['image_gallery3'];

// ===== Update Query =====
$sql = "UPDATE hotels SET 
    name = ?, 
    location = ?, 
    address = ?, 
    description = ?, 
    image_main = ?, 
    image_gallery1 = ?, 
    image_gallery2 = ?, 
    image_gallery3 = ?, 
    checkin_time = ?, 
    checkout_time = ?, 
    cancellation_policy = ?, 
    payment_policy = ?, 
    rating = ?, 
    price = ?
    WHERE hotel_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssssssssssssdii",
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
    $price,
    $hotel_id
);

// ===== Execute & Redirect =====
if ($stmt->execute()) {
    echo "<script>
        alert('Hotel details updated successfully!');
        window.location.href = '../admin-dashboard.php';
    </script>";
} else {
    echo "<script>
        alert('Error updating hotel: " . addslashes($stmt->error) . "');
        window.history.back();
    </script>";
}

$stmt->close();
$conn->close();
?>
