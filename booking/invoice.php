<?php
use Dompdf\Dompdf;
use Dompdf\Options;
require_once '../vendor/autoload.php'; // Path to Dompdf autoload

include '../db.php';
session_start();

if (!isset($_GET['booking_id'])) {
    die("Booking ID not provided.");
}
$booking_id = intval($_GET['booking_id']);
$download = isset($_GET['download']) && $_GET['download'] == 1;

$query = "
SELECT 
    b.booking_id, b.total_price, b.checkin_date, b.checkout_date,
    b.total_nights, b.booking_time,
    CONCAT(u.first_name, ' ', COALESCE(u.last_name, '')) AS full_name, 
    u.email, u.phone,
    h.name AS hotel_name, h.location, h.image_main,
    r.room_type
FROM bookings b
JOIN users u ON b.user_id = u.id
JOIN hotels h ON b.hotel_id = h.hotel_id
JOIN rooms r ON b.room_type = r.room_type AND r.hotel_id = b.hotel_id
WHERE b.booking_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Invalid booking ID");
}
$data = $result->fetch_assoc();

ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Booking Invoice</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #f8f9fa;
    margin: 0;
    padding: 30px;
}

.invoice-container {
    max-width: 600px;
    background: #fff;
    margin: auto;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    padding: 30px 40px;
    box-sizing: border-box;
}

.header {
    text-align: center;
    border-bottom: 2px solid #0079c2;
    padding-bottom: 15px;
    margin-bottom: 20px;
}

.header h1 {
    color: #0079c2;
    margin: 0;
    font-size: 28px;
}

.hotel-info {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 25px;
}

.hotel-info img {
    width: 120px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    margin-right: 20px;
}

.hotel-details h2 {
    margin: 0;
    color: #333;
}

.hotel-details p {
    margin: 3px 0;
    color: #555;
    text-align: center;
}

.section {
    margin-bottom: 20px;
}

.section h3 {
    border-left: 4px solid #0079c2;
    padding-left: 10px;
    color: #333;
    margin-bottom: 10px;
}

.details p {
    margin: 6px 0;
    font-size: 15px;
}

.label {
    display: inline-block;
    width: 140px;
    font-weight: 600;
    color: #444;
}

.value {
    color: #222;
}

.total {
    margin-top: 40px;
    font-size: 18px;
    font-weight: bold;
    color: #0079c2;
}

.footer {
    text-align: center;
    margin-top: 30px;
    font-size: 14px;
    color: #666;
}

.download-btn {
    display: block;
    width: fit-content;
    margin: 25px auto 0;
    padding: 10px 20px;
    background-color: #0079c2;
    color: #fff;
    text-decoration: none;
    border-radius: 5px;
    font-weight: 600;
    transition: 0.3s;
}

.download-btn:hover {
    background-color: #005ea0;
}

.note {
    margin: 20px 0;
    padding: 10px;
    background: #e7f3fa;
    border-left: 5px solid #0079c2;
    font-size: 14px;
    color: #004b75;
}

.go-back {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #0079c2;
            font-weight: bold;
            font-size: 14px;
            border: 1px solid #005fa3;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background 0.3s ease;
            margin-left:10%;
            /* margin-bottom: 5%; */
        }
        .go-back:hover {
            background-color: #005fa3;
            color: white;
        }
/* =========================
   Responsive Media Queries
========================= */

/* Tablets (≤1024px) */
@media (max-width: 1024px) {
    .invoice-container {
        padding: 25px 30px;
    }

    .header h1 {
        font-size: 26px;
    }

    .hotel-info img {
        width: 100px;
        height: 70px;
    }

    .hotel-details h2 {
        font-size: 18px;
    }

    .details p {
        font-size: 14px;
    }

    .label {
        width: 120px;
        display: block;
        margin-top:20px;
    }

    .total {
        font-size: 16px;
    }

    .download-btn {
        padding: 8px 16px;
    }
}

/* Mobile (≤768px) */
@media (max-width: 768px) {
    .invoice-container {
        padding: 20px;
    }

    .header h1 {
        font-size: 24px;
    }

    .hotel-info {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .hotel-info img {
        margin: 0 0 10px 0;
        width: 90px;
        height: 60px;
    }

    .hotel-details h2 {
        font-size: 16px;
    }

    .hotel-details p {
        font-size: 14px;
    }

    .details p {
        font-size: 13px;
    }

    .total {
        font-size: 16px;
    }

    .download-btn {
        padding: 8px 14px;
        font-size: 14px;
    }

    .note {
        font-size: 13px;
        padding: 8px;
    }
}

/* Small Mobile (≤480px) */
@media (max-width: 480px) {
    .invoice-container {
        padding: 15px;
    }

    .header h1 {
        font-size: 22px;
    }

    .hotel-info img {
        width: 80px;
        height: 55px;
    }

    .hotel-details h2 {
        font-size: 15px;
    }

    .details p {
        font-size: 12px;
    }

    .total {
        font-size: 15px;
    }

    .download-btn {
        padding: 6px 12px;
        font-size: 13px;
    }

    .note {
        font-size: 12px;
        padding: 6px;
    }
}

/* ---------- Go Back Button Responsive ---------- */

/* Tablets (≤1024px) */
@media (max-width: 1024px) {
    .go-back {
        font-size: 13px;
        padding: 7px 14px;
        margin-left: 8%;
    }
}

/* Mobile (≤768px) */
@media (max-width: 768px) {
    .go-back {
        font-size: 12px;
        padding: 6px 12px;
        margin-left: 5%;
    }
}

/* Small Mobile (≤480px) */
@media (max-width: 480px) {
    .go-back {
        font-size: 11px;
        padding: 5px 10px;
        margin-left: 2%;
    }
}

</style>
</head>
<body>
<?php if (!$download): ?>
<a class="go-back" href="../homepage/home.php"><i class="fas fa-arrow-left"></i> Back to Home</a>
<?php endif; ?>
<div class="invoice-container">

    <div class="header">
        <h1>Booking Invoice</h1>
    </div>

    <div class="hotel-info">
        <!-- <img src="../admin/hotel/<?php echo htmlspecialchars($data['image_main']); ?>" alt="Hotel Image"> -->
        <div class="hotel-details">
            <h2><?php echo htmlspecialchars($data['hotel_name']);?></h2>
            <p><?php echo htmlspecialchars($data['location']);?></p>
        </div>
    </div>

    <div class="section">
        <h3>Guest Details</h3>
        <div class="details">
            <p><span class="label">Guest Name:</span> <span class="value"><?php echo htmlspecialchars($data['full_name']); ?></span></p>
            <p><span class="label">Email:</span> <span class="value"><?php echo htmlspecialchars($data['email']); ?></span></p>
            <p><span class="label">Phone:</span> <span class="value"><?php echo htmlspecialchars($data['phone']); ?></span></p>
        </div>
    </div>

    <div class="section">
        <h3>Booking Details</h3>
        <div class="details">
            <p><span class="label">Room Type:</span> <span class="value"><?php echo htmlspecialchars($data['room_type']); ?></span></p>
            <p><span class="label">Check-in:</span> 
               <span class="value"><?php echo date('d-m-Y', strtotime($data['checkin_date'])); ?></span></p>
            <p><span class="label">Check-out:</span> 
               <span class="value"><?php echo date('d-m-Y', strtotime($data['checkout_date'])); ?></span></p>
            <p><span class="label">Total Nights:</span> <span class="value"><?php echo htmlspecialchars($data['total_nights']); ?></span></p>
            <p><span class="label">Booking Date:</span> <span class="value"><?php echo date('d-m-Y', strtotime($data['booking_time'])); ?></span></p>
        </div>
    </div>

    <div class="total">
        Total Amount: Rs.<?php echo number_format($data['total_price'], 2); ?>
    </div>

    <div class="note">
        <p><strong>Note:</strong> You can cancel your booking up to <b>24 hours before check-in.</b></p>
    </div>

    <?php if (!$download): ?>
        <a href="?booking_id=<?php echo $booking_id; ?>&download=1" class="download-btn">⬇ Download Invoice</a>
    <?php endif; ?>

    <div class="footer">
        Thank you for booking with <b>HotelHub</b>!<br>
        We look forward to hosting you again.
    </div>
</div>
</body>
</html>
<?php
$html = ob_get_clean();

if ($download) {
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("invoice_{$booking_id}.pdf", ["Attachment" => true]);
    exit;
} else {
    echo $html;
}
?>
