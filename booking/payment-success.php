<?php
ob_start();
header('Content-Type: application/json');

require '../vendor/autoload.php';
use Razorpay\Api\Api;

include '../db.php';
session_start();

// Enable MySQLi error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit();
}

// Extract payment details
$paymentId = $data['razorpay_payment_id'] ?? '';
$orderId = $data['razorpay_order_id'] ?? '';
$signature = $data['razorpay_signature'] ?? '';
$userId = isset($_SESSION['id']) ? (int)$_SESSION['id'] : null;

// Extract booking details (sent from frontend JS)
$phone = $data['phone'] ?? null;
$hotelId = isset($data['hotel_id']) ? (int)$data['hotel_id'] : null;
$roomType = $data['room_type'] ?? '';
$checkinDate = $data['checkin_date'] ?? '';
$checkoutDate = $data['checkout_date'] ?? '';
$totalNights = isset($data['total_nights']) ? (int)$data['total_nights'] : 1;
$totalPrice = isset($data['total_price']) ? (float)$data['total_price'] : 0;

$status = "confirmed";
$method = "Razorpay";
$paymentTime = date('Y-m-d H:i:s');

// Razorpay secret key
$key_secret = 'vtBXANJQ79EqQ8AaVs1PmEdA';

// Verify signature
$generated_signature = hash_hmac('sha256', $orderId . "|" . $paymentId, $key_secret);

if (!hash_equals($generated_signature, $signature)) {
    echo json_encode(['success' => false, 'message' => 'Invalid payment signature']);
    exit();
}

try {
    // Update phone if provided and userId exists
if ($userId && !empty($phone)) {
    $updatePhoneStmt = $conn->prepare("UPDATE users SET phone = ? WHERE id = ? AND (phone IS NULL OR phone = '')");
    $updatePhoneStmt->bind_param("si", $phone, $userId);
    $updatePhoneStmt->execute();
}

    // Step 1: Insert booking
    $bookingStmt = $conn->prepare("INSERT INTO bookings 
        (user_id, hotel_id, room_type, checkin_date, checkout_date, total_nights, total_price, status, payment_id, booking_time)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    $bookingStmt->bind_param(
        "iisssddss",
        $userId,
        $hotelId,
        $roomType,
        $checkinDate,
        $checkoutDate,
        $totalNights,
        $totalPrice,
        $status,
        $paymentId
    );
    $bookingStmt->execute();
    $bookingId = $conn->insert_id;

    $paystatus = "success";
    // Step 2: Insert payment
    $paymentStmt = $conn->prepare("INSERT INTO payments 
        (payment_id, order_id, booking_id, amount_paid, status, method, payment_time, razorpay_signature)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    $paymentStmt->bind_param(
        "ssidssss",
        $paymentId,
        $orderId,
        $bookingId,
        $totalPrice,
        $paystatus,
        $method,
        $paymentTime,
        $signature
    );
    $paymentStmt->execute();

    // Return JSON response
    echo json_encode([
        'success' => true,
        'message' => 'Booking confirmed!',
        'booking_id' => $bookingId
    ]);
    exit();

} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database insertion failed']);
    exit();
}

?>
