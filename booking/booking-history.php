<?php
session_start();
include '../db.php';

// Check if user is logged in
if (!isset($_SESSION['id']) && !isset($_SESSION['google_id'])) {
    header('Location: ../login/index.html');
    exit;
}

// Get user ID from session
$user_id = isset($_SESSION['id']) ? $_SESSION['id'] : $_SESSION['google_id'];

// Fetch all bookings for the user
$stmt = $conn->prepare("
    SELECT b.*, h.name as hotel_name, h.location
    FROM bookings b
    JOIN hotels h ON b.hotel_id = h.hotel_id
    WHERE b.user_id = ?
    ORDER BY b.booking_time DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings_result = $stmt->get_result();
$bookings = [];
while ($row = $bookings_result->fetch_assoc()) {
    $bookings[] = $row;
}
$stmt->close();

// Process booking cancellation
if (isset($_POST['cancel_booking']) && isset($_POST['booking_id'])) {
    $booking_id = intval($_POST['booking_id']);
    
    // Check if booking exists and belongs to the user
    $stmt = $conn->prepare("SELECT booking_time, status FROM bookings WHERE booking_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
        
        // Check if booking is within 24 hours
        $booking_time = new DateTime($booking['booking_time']);
        $current_time = new DateTime();
        $time_diff = $current_time->diff($booking_time);
        $hours_diff = $time_diff->h + ($time_diff->days * 24);
        
        if ($hours_diff <= 24 && $booking['status'] != 'cancelled') {
            // Update booking status to cancelled
            $update_stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE booking_id = ?");
            $update_stmt->bind_param("i", $booking_id);
            $update_stmt->execute();
            $update_stmt->close();
            
            // Redirect to refresh the page
            header('Location: booking-history.php?cancel_success=1');
            exit;
        } else {
            // Cancellation not allowed after 24 hours
            header('Location: booking-history.php?cancel_error=time_exceeded');
            exit;
        }
    } else {
        // Booking not found or doesn't belong to user
        header('Location: booking-history.php?cancel_error=not_found');
        exit;
    }
}

// Handle success/error messages
$cancel_success = isset($_GET['cancel_success']) && $_GET['cancel_success'] == 1;
$cancel_error = isset($_GET['cancel_error']) ? $_GET['cancel_error'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - HotelHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="booking.css">
    <style>
        .booking-history-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }
        
        .booking-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 20px;
            transition: transform 0.3s ease;
        }      
        
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .booking-id {
            font-size: 1.1rem;
            font-weight: bold;
            color: #333;
        }
        
        .booking-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        
        .status-confirmed {
            background-color: #e6f7e6;
            color: #28a745;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #dc3545;
        }
        
        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .booking-detail {
            margin-bottom: 10px;
        }
        
        .detail-label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-size: 1rem;
            color: #333;
            font-weight: 500;
        }
        
        .booking-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            border: none;
            transition: background-color 0.3s ease;
        }

        
        
        .view-btn {
            background-color: #007bff;
            color: white;
        }
        
        .view-btn:hover {
            background-color: #0069d9;
        }
        
        .download-btn {
            background-color: #28a745;
            color: white;
        }
        
        .download-btn:hover {
            background-color: #218838;
        }
        
        .cancel-btn {
            background-color: #dc3545;
            color: white;
        }
        
        .cancel-btn:hover {
            background-color: #c82333;
        }
        
        .disabled-btn {
            background-color: #acb7c1ff;
            cursor: not-allowed;
        }
        
        .no-bookings {
            text-align: center;
            padding: 50px 0;
            color: #666;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 768px) {
            .booking-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .booking-status {
                margin-top: 10px;
            }
            
            .booking-details {
                grid-template-columns: 1fr;
            }
            
            .booking-actions {
                justify-content: center;
            }
        }
        /* =========================================
   📱 Responsive Design for Homepage
   ========================================= */

/* ---------- Tablet Screens (≤1024px) ---------- */
@media (max-width: 1024px) {
  .navbar {
    padding: 15px 25px;
  }

  .logo {
    font-size: 28px;
  }

  .search-container {
    max-width: 450px;
  }

  .hotel-listings {
    gap: 20px;
  }

  .hotel-card {
    width: 250px;
  }

  .carousel-container {
    height: 220px;
  }

  .about-section p {
    font-size: 1rem;
  }

  .footer-content {
    grid-template-columns: repeat(2, 1fr);
    gap: 40px;
  }
}

/* ---------- Mobile Screens (≤768px) ---------- */
@media (max-width: 768px) {
  /* Navbar */
  .navbar {
    flex-direction: column;
    padding: 10px 15px;
    gap: 10px;
  }

  .logo {
    font-size: 30px;
  }

  .search-container {
    order: 2;
    width: 100%;
    max-width: 100%;
    margin: 10px 0;
  }

  .nav-links {
    flex-wrap: wrap;
    justify-content: center;
    gap: 60px;
    font-size: 0.95rem;
  }

  .sign-in-btn {
    width: auto;
    padding: 6px 12px;
  }

  /* Hotels section */
  h2 {
    font-size: 1.5rem;
  }

  .hotel-card {
    width: 250px;
  }

  .book-now-btn {
    padding: 8px 70px;
    font-size: 0.9rem;
  }

  /* Carousel */
  .carousel-container {
    height: 200px;
  }

  /* About section */
  .about-section {
    padding: 15px;
  }

  .about-section p {
    font-size: 1rem;
    line-height: 1.6rem;
  }

  /* Footer */
  .footer-content {
    grid-template-columns: 1fr;
    text-align: center;
    gap: 40px;
  }

  .app-buttons {
    justify-content: center;
  }

  .social-icons {
    justify-content: center;
  }
}

/* ---------- Small Mobile Screens (≤480px) ---------- */
@media (max-width: 480px) {
  .navbar {
    padding: 10px;
  }

  .logo {
    font-size: 30px;
  }

  .search-container {
    padding: 5px 10px;
  }

  .search-input {
    font-size: 0.9rem;
  }

  .hotel-card {
    width: 250px;
  }

  .hotel-details h3 {
    font-size: 16px;
  }

  .book-now-btn {
    padding: 8px 70px;
    font-size: 0.85rem;
  }

  .carousel-container {
    height: 180px;
  }

  .footer-section h3 {
    font-size: 16px;
  }

  .footer-section p {
    font-size: 0.9rem;
  }

  .app-buttons img {
    width: 120px;
  }
}

    </style>
</head>
<body>
    <div class="main-container">
        <nav class="navbar">
        <div class="logo">
          HotelHub
        </div>

<div class="search-container">
  <div class="search-box">
    <input type="search" id="searchInput" class="search-input" placeholder="Search for hotels and locations" aria-label="Search Hotels" autocomplete="off"/>
    <span class="search-icon"><i class="fas fa-search"></i></span>
  </div>
  <ul id="searchResults" class="results-list"></ul>
</div>
        <div class="nav-links">
          <a href="../homepage/home.php#hotels">Hotels</a>
          
          <a href="../homepage/home.php#about">About Us</a>

          <?php if (isset($_SESSION['user']) && isset($_SESSION['role']) && $_SESSION['role'] !== 'admin'): ?>
          <div class="profile-toggle" onclick="toggleProfileMenu()">
            <div class="profile-info" style="display: flex; align-items: center; gap: 8px;">
            <div class="profile-icon">
              <i class="fa-solid fa-user"></i>
            </div>
            <span class="profile-name">
            <?php echo htmlspecialchars($_SESSION['user']); ?>
            <i class="fa-solid fa-angle-down" style="margin-left: 5px;color:#777"></i> 
            </span>
            </div>   
              <!-- Profile Dropdown Menu -->
              <div id="profile-menu" class="profile-menu">
                <a href="../homepage/view_profile.php">View Profile</a>
                <a href="../booking/booking-history.php">My Bookings</a>
                <a href="#" onclick="confirmLogout()">Logout</a>
              </div>
          </div>
          <?php else: ?>
            <a href="../login/index.html" class="sign-in-btn">Sign In</a>
          <?php endif; ?>
        </div> 
      </nav>
        
        <div class="booking-history-container">
            <h1>My Bookings</h1>
            
            <?php if ($cancel_success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Your booking has been successfully cancelled.
            </div>
            <?php endif; ?>
            
            <?php if ($cancel_error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> 
                <?php 
                    if ($cancel_error == 'time_exceeded') {
                        echo 'Cancellation is only allowed within 24 hours of booking.';
                    } elseif ($cancel_error == 'not_found') {
                        echo 'Booking not found or already cancelled.';
                    } else {
                        echo 'An error occurred while cancelling your booking.';
                    }
                ?>
            </div>
            <?php endif; ?>
            
            <?php if (empty($bookings)): ?>
            <div class="no-bookings">
                <i class="fas fa-calendar-times fa-3x" style="margin-bottom: 15px;"></i>
                <h3>You haven't booked anything yet.</h3>
                <p>Explore our hotels and make your first booking!</p>
                <a href="../homepage/home.php#hotels" class="action-btn view-btn" style="margin-top: 15px; display: inline-block;">
                    <i class="fas fa-search"></i> Explore Hotels
                </a>
            </div>
            <?php else: ?>
                <?php foreach ($bookings as $booking): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <div class="booking-id">
                            Booking #<?php echo htmlspecialchars($booking['booking_id']); ?>
                        </div>
                        <div class="booking-status status-<?php echo htmlspecialchars($booking['status']); ?>">
                            <?php echo ucfirst(htmlspecialchars($booking['status'])); ?>
                        </div>
                    </div>
                    
                    <div class="booking-details">
                        <div class="booking-detail">
                            <div class="detail-label">Hotel</div>
                            <div class="detail-value"><?php echo htmlspecialchars($booking['hotel_name']); ?></div>
                        </div>
                        
                        <div class="booking-detail">
                            <div class="detail-label">Location</div>
                            <div class="detail-value"><?php echo htmlspecialchars($booking['location']); ?></div>
                        </div>
                        
                        <div class="booking-detail">
                            <div class="detail-label">Room Type</div>
                            <div class="detail-value"><?php echo htmlspecialchars($booking['room_type']); ?></div>
                        </div>
                        
                        <div class="booking-detail">
                            <div class="detail-label">Check-in Date</div>
                            <div class="detail-value"><?php echo date('d M Y', strtotime($booking['checkin_date'])); ?></div>
                        </div>
                        
                        <div class="booking-detail">
                            <div class="detail-label">Check-out Date</div>
                            <div class="detail-value"><?php 
                                // Display the checkout date from the database
                                echo date('d M Y', strtotime($booking['checkout_date']));
                            ?></div>
                        </div>
                        
                        <div class="booking-detail">
                            <div class="detail-label">Total Nights</div>
                            <div class="detail-value"><?php echo htmlspecialchars($booking['total_nights']); ?></div>
                        </div>
                        
                        <div class="booking-detail">
                            <div class="detail-label">Total Price</div>
                            <div class="detail-value">₹<?php echo number_format($booking['total_price'], 2); ?></div>
                        </div>
                        
                        <div class="booking-detail">
                            <div class="detail-label">Booking Time</div>
                            <div class="detail-value"><?php echo date('d M Y, h:i A', strtotime($booking['booking_time'])); ?></div>
                        </div>
                    </div>
                    
                    <div class="booking-actions">
                        <a href="invoice.php?booking_id=<?php echo $booking['booking_id']; ?>" class="action-btn view-btn">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                        
                        <a href="invoice.php?booking_id=<?php echo $booking['booking_id']; ?>&download=1" class="action-btn download-btn">
                            <i class="fas fa-download"></i> Download Invoice
                        </a>
                        
                        <?php
                        // Check if booking is within 24 hours and not cancelled
                        $booking_time = new DateTime($booking['booking_time']);
                        $current_time = new DateTime();
                        $time_diff = $current_time->diff($booking_time);
                        $hours_diff = $time_diff->h + ($time_diff->days * 24);
                        $can_cancel = $hours_diff <= 24 && $booking['status'] != 'cancelled';
                        ?>
                        
                        <?php if ($can_cancel): ?>
                        <form method="post" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                            <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                            <button type="submit" name="cancel_booking" class="action-btn cancel-btn">
                                <i class="fas fa-times-circle"></i> Cancel Booking
                            </button>
                        </form>
                        <?php else: ?>
                        <button class="action-btn disabled-btn" disabled title="<?php echo $booking['status'] == 'cancelled' ? 'Booking already cancelled' : 'Cancellation only allowed within 24 hours of booking'; ?>">
                            <i class="fas fa-times-circle"></i> Cancel Booking
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../homepage/home.js"></script>
    <script>
    setTimeout(() => {
    const alertBox = document.querySelector('.alert');
    if (alertBox) {
      alertBox.style.display = 'none';
    }
  }, 3000);

  if (window.history.replaceState) {
      const url = new URL(window.location);
      url.searchParams.delete('updated');
      window.history.replaceState({}, document.title, url.pathname);
    }

    // 🔍 Search hotels
document.getElementById("searchInput").addEventListener("input", function () {
  const query = this.value.trim();
  const resultsList = document.getElementById("searchResults");

  if (query.length === 0) {
    resultsList.style.display = "none";
    resultsList.innerHTML = "";
    return;
  }

  // Fetch hotels from backend
  fetch(`../homepage/search.php?q=${encodeURIComponent(query)}`)
    .then((res) => res.json())
    .then((data) => {
      resultsList.innerHTML = "";

      if (data.length === 0) {
        resultsList.style.display = "none";
        return;
      }

      data.forEach((hotel) => {
        const li = document.createElement("li");

        // Show hotel name + optional location
        li.innerHTML = `
          <i class="fas fa-hotel"></i> 
          ${hotel.name} 
          <small style="color:gray;">(${
            hotel.location || "No location"
          })</small>
        `;

        // When clicked, go to hotel details page
        li.onclick = () => {
          window.location.href = `../booking/booking.php?id=${hotel.hotel_id}`;
        };

        resultsList.appendChild(li);
      });

      resultsList.style.display = "block";
    })
    .catch((err) => console.error("Error fetching hotels:", err));
});

// Hide search results when clicking outside
document.addEventListener("click", function (e) {
  const searchBox = document.querySelector(".search-box");
  const resultsList = document.getElementById("searchResults");
  const searchInput = document.getElementById("searchInput");

  if (!searchBox.contains(e.target) && !resultsList.contains(e.target)) {
    resultsList.style.display = "none";
    resultsList.innerHTML = "";
    searchInput.value = "";
  }
});

</script>

</body>
</html>