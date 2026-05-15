<?php
include '../../db.php';

$hotels = [];

// Fetch hotels
$result = $conn->query("SELECT * FROM hotels ORDER BY hotel_id DESC");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Fetch facilities for this hotel
        $hotel_id = $row['hotel_id'];
        $facilities = [];
        $fac_result = $conn->query("SELECT facility_name FROM hotel_facilities WHERE hotel_id = $hotel_id");
        if ($fac_result->num_rows > 0) {
            while ($fac = $fac_result->fetch_assoc()) {
                $facilities[] = $fac['facility_name'];
            }
        }
        $row['facilities'] = $facilities;
        $hotels[] = $row;
    }
}

echo json_encode($hotels);

$conn->close();
?>
