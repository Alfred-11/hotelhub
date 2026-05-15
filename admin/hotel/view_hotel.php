<?php
include '../../db.php';
session_start();

if (!isset($_GET['id'])) {
    die("Hotel ID not provided.");
}

$hotel_id = intval($_GET['id']);

// Fetch hotel details
$sql = "SELECT * FROM hotels WHERE hotel_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$result = $stmt->get_result();
$hotel = $result->fetch_assoc();

if (!$hotel) {
    die("Hotel not found!");
}

// Fetch hotel facilities
$facilities = [];
$sql2 = "SELECT facility_name FROM hotel_facilities WHERE hotel_id = ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $hotel_id);
$stmt2->execute();
$res2 = $stmt2->get_result();
while ($row = $res2->fetch_assoc()) {
    $facilities[] = $row['facility_name'];
}

$stmt->close();
$stmt2->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($hotel['name']); ?> - Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f6fa;
            font-family: 'Poppins', sans-serif;
        }
        .hotel-container {
            max-width: 950px;
            margin: 40px auto;
            background: #fff;
            border-radius: 12px;
            padding: 35px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.08);
        }
        .hotel-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #0079c2;
            margin-bottom: 5px;
        }
        .hotel-sub {
            color: #666;
            font-size: 0.95rem;
        }
        .main-image {
            width: 100%;
            border-radius: 10px;
            height: 380px;
            object-fit: cover;
            margin-top: 15px;
            margin-bottom: 20px;
        }
        .gallery img {
            width: 32%;
            border-radius: 8px;
            height: 140px;
            object-fit: cover;
        }
        .section-title {
            font-weight: 600;
            margin-top: 25px;
            color: #0079c2;
            font-size: 1.1rem;
            
        }
        .facility-badge {
            background: #eef7ff;
            color: #0079c2;
            margin: 4px 6px 4px 0;
            padding: 6px 12px;
            border-radius: 20px;
            display: inline-block;
            font-size: 0.9rem;
        }
        .price-text {
            text-align: right;
            font-size: 20px;
            color: #0079c2;
            font-weight: 600;
            margin-top: 5px;
        }
        .divider {
            border-bottom: 1px solid #eee;
            margin: 15px 0;
        }
    </style>
</head>
<body>

<div class="hotel-container">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="hotel-title"><?php echo htmlspecialchars($hotel['name']); ?></h1>
            <p class="hotel-sub mb-0"><?php echo htmlspecialchars($hotel['location']); ?> • ⭐ <?php echo htmlspecialchars($hotel['rating']); ?>/5</p>
        </div>
        <div class="price-text">
            ₹ <?php echo number_format($hotel['price'], 2); ?> / night
        </div>
    </div>

    <img src="<?php echo htmlspecialchars($hotel['image_main']); ?>" class="main-image" alt="Hotel Image">

    <!-- Gallery -->
    <div class="gallery mb-4">
        <?php if ($hotel['image_gallery1']) echo "<img src='{$hotel['image_gallery1']}' alt='Gallery 1'>"; ?>
        <?php if ($hotel['image_gallery2']) echo "<img src='{$hotel['image_gallery2']}' alt='Gallery 2'>"; ?>
        <?php if ($hotel['image_gallery3']) echo "<img src='{$hotel['image_gallery3']}' alt='Gallery 3'>"; ?>
    </div>

    <!-- Description -->
    <h4 class="section-title">About the Hotel</h4>
    <p><?php echo nl2br(htmlspecialchars($hotel['description'])); ?></p>

    <div class="divider"></div>

    <!-- Hotel Info -->
    <h4 class="section-title">Hotel Details</h4>
    <p><strong>Address:</strong> <?php echo htmlspecialchars($hotel['address']); ?></p>
    <p><strong>Check-in:</strong> <?php echo htmlspecialchars($hotel['checkin_time']); ?> 
       &nbsp; | &nbsp; <strong>Check-out:</strong> <?php echo htmlspecialchars($hotel['checkout_time']); ?></p>

    <?php if (!empty($facilities)) { ?>
        <h4 class="section-title">Facilities</h4>
        <div>
            <?php foreach ($facilities as $facility): ?>
                <span class="facility-badge"><?php echo htmlspecialchars($facility); ?></span>
            <?php endforeach; ?>
        </div>
    <?php } ?>

    <div class="divider"></div>

    <!-- Policies -->
    <h4 class="section-title">Cancellation Policy</h4>
    <p><?php echo nl2br(htmlspecialchars($hotel['cancellation_policy'])); ?></p>

    <h4 class="section-title">Payment Policy</h4>
    <p><?php echo nl2br(htmlspecialchars($hotel['payment_policy'])); ?></p>
</div>

</body>
</html>
