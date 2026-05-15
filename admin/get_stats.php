<?php
include '../db.php';
$response = [];

// Total Hotels
$hotelQuery = $conn->query("SELECT COUNT(*) AS total_hotels FROM hotels");
$response['total_hotels'] = $hotelQuery->fetch_assoc()['total_hotels'] ?? 0;

// Total Rooms
$roomQuery = $conn->query("SELECT COUNT(*) AS total_rooms FROM rooms");
$response['total_rooms'] = $roomQuery->fetch_assoc()['total_rooms'] ?? 0;

// Total Users
$userQuery = $conn->query("SELECT COUNT(*) AS total_users FROM users");
$response['total_users'] = $userQuery->fetch_assoc()['total_users'] ?? 0;

// Total Bookings
$bookingQuery = $conn->query("SELECT COUNT(*) AS total_bookings FROM bookings");
$response['total_bookings'] = $bookingQuery->fetch_assoc()['total_bookings'] ?? 0;

// Total Revenue (sum of successful payments)
$revenueQuery = $conn->query("SELECT SUM(amount_paid) AS total_revenue FROM payments WHERE status='success'");
$response['total_revenue'] = $revenueQuery->fetch_assoc()['total_revenue'] ?? 0;

// Most Booked Hotel
$mostBookedHotelQuery = $conn->query("
  SELECT h.name AS hotel_name, COUNT(b.booking_id) AS total_bookings
  FROM bookings b
  JOIN hotels h ON b.hotel_id = h.hotel_id
  GROUP BY h.hotel_id
  ORDER BY total_bookings DESC
  LIMIT 1
");
if ($mostBookedHotelQuery->num_rows > 0) {
  $response['most_booked_hotel'] = $mostBookedHotelQuery->fetch_assoc()['hotel_name'];
} else {
  $response['most_booked_hotel'] = "No bookings yet";
}

// Return JSON
header('Content-Type: application/json');
echo json_encode($response);
