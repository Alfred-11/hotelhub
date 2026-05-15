<?php
include '../../db.php';

// Fetch all users from the database
$query = "SELECT * FROM users";
$result = mysqli_query($conn, $query);

$users = array();

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    echo json_encode($users);
} else {
    echo json_encode(array('error' => 'Failed to fetch users: ' . mysqli_error($conn)));
}

mysqli_close($conn);
?>