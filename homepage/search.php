<?php
include '../db.php'; // Use your own DB connection file

$search = $_GET['q'] ?? '';

if ($search !== '') {
    $results = [];

    // 🔍 Search hotels
    $stmt = $conn->prepare("SELECT hotel_id, name, location FROM hotels WHERE name LIKE ? OR location LIKE ? LIMIT 10");
    $like = "%$search%";
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $results[] = [
            'hotel_id' => $row['hotel_id'],
            'name' => $row['name'],
            'location' => $row['location']
        ];
    }

    echo json_encode($results);
}
?>