<?php
session_start();
include '../db.php';

// User info
$user_id = $_SESSION['id'] ?? 0;
$first_name = $last_name = $email = $phone = '';

if ($user_id > 0) {
    $stmt = $conn->prepare("SELECT first_name, last_name, email, phone FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $first_name = $user['first_name'];
        $last_name  = $user['last_name'];
        $email      = $user['email'];
        $phone      = $user['phone'];
    }
    $stmt->close();
}

// POST data
$hotel_id    = $_POST['hotel_id'] ?? 1;
$room_index  = $_POST['room_index'] ?? 0;  // <-- use room index now
$check_in    = $_POST['check_in_date'] ?? date('Y-m-d');
$check_out   = $_POST['check_out_date'] ?? date('Y-m-d');
$guests      = $_POST['guests'] ?? 2;

// Fetch hotel info
$stmt = $conn->prepare("SELECT * FROM hotels WHERE hotel_id = ?");
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$hotel_result = $stmt->get_result();
$hotel = $hotel_result->fetch_assoc();
$stmt->close();


$room_index = $_POST['room_index'] ?? 0;
$rooms = $_SESSION['rooms'] ?? [];
$selectedRoom = $rooms[$room_index] ?? $rooms[0];


// Calculate nights & total amount
$checkIn = new DateTime($check_in);
$checkOut = new DateTime($check_out);
$nights = $checkOut->diff($checkIn)->days;
if ($nights < 1) $nights = 1;

$totalAmount = $selectedRoom['price_per_night'] * $nights;

// Helper function for hotel image
function hotelImage($path){
    return '../admin/hotel/'.$path;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Booking Summary - HotelHub</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
/* General Body Styles */
body {
    font-family: 'Poppins', sans-serif;
    background: #f5f5f5;
    margin: 0;
    padding: 0;
}
/* Container */
.container {
    max-width: 1200px;
    margin: 30px auto;
    display: flex;
    gap: 20px;
    flex-wrap: wrap; /* Allows stacking on smaller screens */
    justify-content: center; /* Optional: center the forms */
}

/* Contact Form */
.contact-form {
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    min-width: 300px;
    width: 100%;
    max-width: 500px; /* Both forms same width */
    box-sizing: border-box;
    height: 370px;
}

/* Booking Details */
.booking-details {
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    min-width: 300px;
    width: 100%;
    max-width: 500px; /* Same as contact form */
    box-sizing: border-box;
}


 

/* Section Headings */
.contact-form h2, .booking-details h2 {
    color: #0079c2;
    margin-bottom: 20px;
    text-align: center;
}

/* Contact Form */
.contact-form label {
    display: block;
    margin: 15px 0 5px;
    font-weight: 500;
}

.contact-form input {
    width: 100%;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
    box-sizing: border-box;
}

/* Booking Details Header: Image + Hotel Info */
.booking-header {
    display: flex;
    gap: 30px;
    align-items: flex-start;
    margin-bottom: 20px;
}

.booking-header img {
    width: 150px;
    border-radius: 8px;
    object-fit: cover;
}

/* Hotel Info Next to Image */
.hotel-info {
    display: flex;
    flex-direction: column;
    justify-content: center;
    margin: auto 0;
}

.hotel-info div {
    margin: 5px 0;
    font-weight: 500;
}

/* Booking Details: Room Info */
.booking-details .details {
    margin-top: 10px;
}

.details div {
    display: flex;
    justify-content: space-between;
    margin: 8px 0;
}

/* Total Amount */
.total {
    font-weight: 600;
    font-size: 1.2rem;
    color: #0079c2;
    border-top: 1px solid #ddd;
    padding-top: 10px;
    margin-top: 10px;
}

/* Confirm Button */
.confirm-btn {
    background: #0079c2;
    color: #fff;
    border: none;
    padding: 12px 20px;
    width: 100%;
    border-radius: 6px;
    font-size: 1rem;
    cursor: pointer;
    margin-top: 15px;
    transition: 0.3s;
}

.confirm-btn:hover {
    background: #005fa3;
}

/* Room Card Styles */
.room-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 15px;
    background: #f9f9f9;
    transition: 0.3s;
}

.room-details {
    display: flex;
    flex-direction: column;
}

.room-details h4 {
    margin: 0 0 5px 0;
    color: #0079c2;
}

.room-details p {
    margin: 8px 0;
    font-size: 0.9rem;
    color: #555;
}

.price {
    font-weight: 600;
    font-size: 1.1rem;
    color: #0079c2;
    margin: 5px 0 0 0;
}

.per-night {
    font-size: 0.9rem;
    color: #555;
    margin-left: 5px;
}


.room-amenities {
    margin-top: 5px;
    font-size: 0.85rem;
    color: #666;
}

.room-amenities i {
    margin-right: 5px;
    color: #ff4500;
}

/* Booking Info Card */
.booking-info {
    display: flex;
    justify-content: space-between;
    margin: 15px 0;
    text-align: center;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
}

.booking-info .info-item {
    flex: 1;
}

.booking-info .label {
    font-weight: 500;
    color: #0079c2;
}

.booking-info .value {
    margin-top: 5px;
    font-weight: 600;
    color: #333;
}

.total {
    display: flex;
    justify-content: space-between; /* pushes items to extreme ends */
    align-items: center; /* vertically center if needed */
    font-weight: 600;
    font-size: 1.1rem;
    color: #0079c2;
    border-top: 1px solid #ddd;
    padding-top: 10px;
    margin-top: 10px;
}

input:-webkit-autofill,
input:-webkit-autofill:focus,
input:-webkit-autofill:hover,
input:-webkit-autofill:active {
  -webkit-box-shadow: 0 0 0 1000px #fff inset !important;
  box-shadow: 0 0 0 1000px #fff inset !important;
  -webkit-text-fill-color: #000 !important;
}

/* Header Styles */
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 15px 50px;
  box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 100;
}

.logo {
  font-size: 1.8rem;
  font-weight: bold;
  color: #0079c2;
  flex: 1; /* left side */
}

/* Center Navigation Links */
.nav-links {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 30px;
  flex: 2; /* take middle space */
}

.nav-links a {
  text-decoration: none;
  color: black;
  font-size: 1rem;
  font-weight: 500;
  transition: color 0.3s;
}

.nav-links a:hover {
  color: #0079c2;
}

/* Right Side Profile / Sign In */
.nav-right {
  flex: 1;
  display: flex;
  justify-content: flex-end;
  align-items: center;
}

/* Sign-In Button */
.sign-in-btn {
  background: #0079c2;
  color: white !important;
  padding: 8px 18px;
  border-radius: 5px;
  text-decoration: none;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.3s;
}

.sign-in-btn:hover {
  background: #005fa3;
}

/* Profile Section */
.profile-toggle {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  position: relative;
  max-width: 220px;
  white-space: nowrap;
}

.profile-info {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 5px 10px;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.3s ease;
}

.profile-info:hover {
  background-color: #e7e7e7;
}

.profile-name {
  max-width: 150px; /* Restrict name width */
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.profile-icon {
  height: 30px;
  width: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  border: 1px solid black;
  flex-shrink: 0;
}

.profile-menu {
  display: none;
  position: absolute;
  top: 100%;
  right: 0;
  width: 230px;
  min-width: 200px;
  max-width: 250px;
  background-color: #fff;
  border-radius: 10px;
  box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.15);
  padding: 8px 0;
  z-index: 999;
}

.profile-menu.show {
  display: block;
}

.profile-menu a {
  display: block;
  padding: 12px 16px;
  color: #333;
  text-decoration: none;
  font-size: 15px;
  font-weight: 500;
  transition: background 0.2s ease;
}

.profile-menu a:hover {
  background-color: #f1f1f1;
}

/* =========================
   📱 Responsive CSS
   ========================= */

/* ---------- Tablet Screens (≤1024px) ---------- */
@media (max-width: 1024px) {
    .container {
        gap: 15px;
    }

    .booking-header {
        gap: 20px;
    }

    .booking-header img {
        width: 130px;
    }

    .hotel-info div {
        font-size: 0.95rem;
    }

    .room-card {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .room-details h4 {
        font-size: 1.1rem;
    }

    .price {
        font-size: 1rem;
    }

    .booking-info .label, .booking-info .value {
        font-size: 0.95rem;
    }

    .confirm-btn {
        padding: 10px 16px;
        font-size: 0.95rem;
    }
}

/* ---------- Mobile Screens (≤768px) ---------- */
@media (max-width: 768px) {
    .container {
        flex-direction: column;
        margin: 20px;
    }

    .contact-form, .booking-details {
        max-width: 100%;
    }

    .booking-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .booking-header img {
        width: 120px;
        margin-bottom: 10px;
    }

    .hotel-info {
        margin: 0;
    }

    .room-card {
        padding: 12px;
    }

    .room-details h4 {
        font-size: 1rem;
    }

    .price {
        font-size: 0.95rem;
    }

    .booking-info {
        gap: 10px;
        text-align: center;
    }

    .total {
        gap: 5px;
        text-align: center;
    }

    /* Navbar adjustments for mobile */
    .navbar {
        padding: 10px 20px;
        gap: 10px;
    }

    .nav-links {
        gap: 20px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .nav-right {
        justify-content: center;
    }

    .profile-name {
        max-width: 120px;
    }

    .profile-icon {
        height: 28px;
        width: 28px;
    }
}

/* ---------- Small Mobile Screens (≤480px) ---------- */
@media (max-width: 480px) {
    .booking-header img {
        width: 100px;
    }

    .hotel-info div {
        font-size: 0.9rem;
    }

    .room-details h4 {
        font-size: 0.95rem;
    }

    .price {
        font-size: 0.9rem;
    }

    .booking-info{
       flex-direction: column;
    }

    .booking-info .label, .booking-info .value {
        font-size: 0.9rem;
    }

    .confirm-btn {
        font-size: 0.9rem;
        padding: 10px 14px;
    }

    .navbar {
        padding: 10px 15px;
    }

    .logo {
        font-size: 1.5rem;
    }

    .nav-links {
        gap: 15px;
        font-size: 0.9rem;
    }

    .profile-name {
        max-width: 100px;
        font-size: 0.85rem;
    }

    .profile-icon {
        height: 26px;
        width: 26px;
    }
}


</style>
</head>
<body>

<nav class="navbar">
  <div class="logo">HotelHub</div>

  <div class="nav-right">
    <?php if (isset($_SESSION['user']) && isset($_SESSION['role']) && $_SESSION['role'] !== 'admin'): ?>
      <!-- single wrapper: profile-toggle contains both the clickable area and the menu -->
      <div class="profile-toggle" onclick="toggleProfileMenu()">
        <div class="profile-info" role="button" aria-haspopup="true" aria-expanded="false">
          <div class="profile-icon"><i class="fas fa-user" aria-hidden="true"></i></div>
          <span class="profile-name">
            <?= htmlspecialchars($_SESSION['user']); ?>
            <i class="fas fa-angle-down" style="margin-left:5px;color:#777;" aria-hidden="true"></i>
          </span>
        </div>

        <!-- profile menu must be a child of .profile-toggle for your JS to treat clicks inside as "inside" -->
        <div id="profile-menu" class="profile-menu" aria-label="Profile menu">
          <a href="../homepage/view_profile.php">View Profile</a>
          <a href="booking-history.php">My Bookings</a>
        </div>
      </div>
    <?php else: ?>
      <a href="../login/index.html" class="sign-in-btn">Sign In</a>
    <?php endif; ?>
  </div>
</nav>

<div class="container">

    <!-- Contact Info -->
    <div class="contact-form">
    <h2>Contact Information</h2>
    <form id="contactForm">
        <label for="fullName">Full Name</label>
        <input type="text" id="fullName" value="<?= htmlspecialchars($first_name . ' ' . $last_name) ?>" required>

        <label for="email">Email Address</label>
        <input type="email" id="email" value="<?= htmlspecialchars($email) ?>" required>

        <label for="phone">Phone Number</label>
        <input type="tel" 
       id="phone" 
       name="phone" 
       maxlength="10" 
       value="<?= isset($phone) ? htmlspecialchars($phone) : '' ?>" 
       required>

    </form>
</div>

<!-- Booking Details -->
<div class="booking-details">
    <h2>Booking Summary</h2>

    <div class="booking-header">
        <img src="<?= hotelImage($hotel['image_main']) ?>" alt="Hotel Image">
        <div class="hotel-info">
            <div><strong><?= htmlspecialchars($hotel['name']) ?></strong></div>
            <div><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($hotel['location']) ?></div>
        </div>
    </div>

    <div class="booking-info">
        <div class="info-item">
            <div class="label">Check-in</div>
            <div class="value"><?= date('D, M d', strtotime($check_in)) ?></div>
        </div>
        <div class="info-item">
            <div class="label">Check-out</div>
            <div class="value"><?= date('D, M d', strtotime($check_out)) ?></div>
        </div>
        <div class="info-item">
            <div class="label">Nights</div>
            <div class="value"><?= $nights ?></div>
        </div>
    </div>

    <div class="room-option">
        <label class="room-card" style="cursor: default;">
            <div class="room-details">
                <h4><?= htmlspecialchars($selectedRoom['room_type']) ?></h4>
                <p><i class="fas fa-user"></i> Capacity: <?= htmlspecialchars($selectedRoom['capacity']) ?></p>
                <p><i class="fas fa-bed"></i> Beds: <?= htmlspecialchars($selectedRoom['beds']) ?></p>
                <p class="price">Rs. <?= number_format($selectedRoom['price_per_night'],2) ?> 
                    <span class="per-night">per night</span>
                </p>
                <?php if (!empty($selectedRoom['amenities'])): ?>
                    <p class="room-amenities"><i class="fas fa-concierge-bell"></i> 
                        <?= htmlspecialchars($selectedRoom['amenities']) ?>
                    </p>
                <?php endif; ?>
            </div>
        </label>
    </div>

    <div class="total">
        <span>Total Amount</span>
        <span id="totalAmount">₹<?= number_format($totalAmount, 2) ?></span>
    </div>

    <button type="button" id="confirmBooking" class="confirm-btn">Confirm Booking</button>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
/* ---------- Utility functions ---------- */
function parseAmountFromSpan(spanId) {
  // Get innerText like "₹1,234.00" and return numeric 1234
  const el = document.getElementById(spanId);
  if (!el) return 0;
  const text = el.innerText || el.textContent || '';
  // Remove currency symbols, commas, whitespace
  const num = text.replace(/[^0-9.]/g, '');
  return parseFloat(num) || 0;
}

/* Toggle profile dropdown (unchanged) */
function toggleProfileMenu() {
  const menu = document.getElementById("profile-menu");
  menu.classList.toggle("show");
}
document.addEventListener("click", function (e) {
  const profileToggle = document.querySelector(".profile-toggle");
  const profileMenu = document.getElementById("profile-menu");
  if (profileToggle && !profileToggle.contains(e.target)) {
    profileMenu.classList.remove("show");
  }
});

/* ---------- Main booking / payment flow ---------- */
document.getElementById('confirmBooking').addEventListener('click', function (e) {
  e.preventDefault();

  // Validate contact fields
  const fullName = document.getElementById('fullName').value.trim();
  const email = document.getElementById('email').value.trim();
  const phone = document.getElementById('phone').value.trim();

  if (!fullName || !email || !phone) {
    alert('Please fill in all contact details before confirming your booking.');
    return;
  }
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const phonePattern = /^[0-9]{10}$/;
  if (!emailPattern.test(email)) { alert('Please enter a valid email address.'); return; }
  if (!phonePattern.test(phone)) { alert('Please enter a valid 10-digit phone number.'); return; }

  // Parse final amount from span (not an input)
  const finalAmount = parseAmountFromSpan('totalAmount'); // in rupees
  if (!finalAmount || finalAmount <= 0) { alert('Invalid amount.'); return; }

  // Create order on server (send as form data)
  const form = new URLSearchParams();
  form.append('amount', finalAmount);

  fetch('create_order.php', {
    method: 'POST',
    body: new URLSearchParams({ amount: finalAmount })
})
.then(res => res.json())
.then(data => {
    if(!data.success || !data.order_id) {
        console.error('Razorpay order error:', data.error);
        alert('Error creating order: ' + data.error);
        return;
    }

    var options = {
        key: "rzp_test_YXSmAaz8w7PTl9",
        amount: Math.round(finalAmount * 100),
        currency: "INR",
        name: "HotelHub",
        description: "Hotel Booking",
        order_id: data.order_id,
        prefill: {
            name: fullName,
            email: email,
            contact: phone
        },
        theme: { color: "#0079c2" },
        handler: function(response) {
            fetch('payment-success.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        phone: document.getElementById("phone").value,
        razorpay_payment_id: response.razorpay_payment_id,
        razorpay_order_id: response.razorpay_order_id,
        razorpay_signature: response.razorpay_signature,
        hotel_id: <?= $hotel_id ?>,
        room_type: '<?= htmlspecialchars($selectedRoom['room_type']) ?>',
        checkin_date: '<?= $check_in ?>',
        checkout_date: '<?= $check_out ?>',
        total_nights: <?= $nights ?>,
        total_price: <?= $totalAmount ?>
    })
})
.then(res => res.json())
.then(resp => {
    if(resp.success){
        alert(resp.message + " Booking ID: " + resp.booking_id);
        window.location.href = `invoice.php?booking_id=${resp.booking_id}`;
    } else {
        alert("Error: " + resp.message);
    }
});
        }
    };

    var rzp = new Razorpay(options);
    rzp.open();
})
.catch(err => {
    console.error('Fetch error:', err);
    alert('Error creating payment order.');
});
});

</script>


</body>
</html>
