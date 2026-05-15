<?php
// Include database connection
require_once '../../db.php';

// Set headers for JSON response
header('Content-Type: application/json');

try {
    // Fetch all bookings from the database
    $query = "SELECT * FROM bookings";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        throw new Exception("Database query failed: " . mysqli_error($conn));
    }

    $bookings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $bookings[] = [
            'booking_id' => $row['booking_id'] ?? $row['id'] ?? '',
            'user_id' => $row['user_id'] ?? '',
            'hotel_id' => $row['hotel_id'] ?? '',
            'booking_date' => $row['booking_time'] ?? '',
            'check_in_date' => $row['checkin_date'] ?? '',
            'check_out_date' => $row['checkout_date'] ?? '',
            'status' => $row['status'] ?? 'confirmed'
        ];
    }

    // Return bookings as JSON
    echo json_encode($bookings);
} catch (Exception $e) {
    // Return error message as JSON
    echo json_encode(['error' => $e->getMessage()]);
}
?>