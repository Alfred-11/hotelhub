<?php
session_start();
include '../db.php';

// Get hotel ID from URL parameter
$hotel_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch hotel data
$stmt = $conn->prepare("SELECT * FROM hotels WHERE hotel_id = ?");
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$hotel_result = $stmt->get_result();

if ($hotel_result->num_rows === 0) {
    // Redirect to home page if hotel not found
    header('Location: ../homepage/home.php');
    exit;
}

$hotel = $hotel_result->fetch_assoc();
$stmt->close();

// Fetch hotel facilities
$facilities = [];
$stmt = $conn->prepare("SELECT facility_name FROM hotel_facilities WHERE hotel_id = ?");
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$fac_result = $stmt->get_result();
while ($row = $fac_result->fetch_assoc()) {
    $facilities[] = $row['facility_name'];
}
$stmt->close();

// Fetch hotel rooms
$rooms = [];
$stmt = $conn->prepare("SELECT * FROM rooms WHERE hotel_id = ? ORDER BY room_id ASC");
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$room_result = $stmt->get_result();
while ($row = $room_result->fetch_assoc()) {
    $rooms[] = $row;
}
$stmt->close();

$_SESSION['rooms'] = $rooms;

// Handle booking success message
$booking_success = isset($_GET['success']) && $_GET['success'] == 1;
$booking_error = isset($_GET['error']) ? $_GET['error'] : '';

function getHotelImagePath($dbPath) {
    // Booking page is in ../booking/ folder, uploads are in ../admin/hotel/uploads/
    return '../admin/hotel/' . $dbPath; 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book <?php echo htmlspecialchars($hotel['name']); ?> - HotelHub</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    />
    <link rel="stylesheet" href="booking.css">
</head>
<body>
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
                <a href="booking-history.php">My Bookings</a>
                <a href="#" onclick="confirmLogout()">Logout</a>
              </div>
          </div>
          <?php else: ?>
            <a href="../login/index.html" class="sign-in-btn">Sign In</a>
          <?php endif; ?>
        </div> 
      </nav>

<main>
    <div class="hotel-banner" style="background-image: url('<?php echo getHotelImagePath($hotel['image_main']); ?>')">
    <div class="container">
        <h1><?php echo htmlspecialchars($hotel['name']); ?></h1>
        <p class="location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hotel['location']); ?></p>
    </div>
</div>

    <div class="container">
        <div class="booking-container">
            <!-- Hotel Info -->
            <section class="hotel-info">
                <h2>Hotel Information</h2>
                <div class="hotel-description"><p><?php echo htmlspecialchars($hotel['description']); ?></p></div>

                <!-- Gallery -->
                <div class="hotel-gallery">
    <h3>Gallery</h3>
    <div class="gallery-images">
        <?php for ($i=1; $i<=3; $i++): 
            $imgField = "image_gallery$i";
            if(!empty($hotel[$imgField])): ?>
            <div class="gallery-image">
                <img src="<?php echo getHotelImagePath($hotel[$imgField]); ?>" alt="Hotel Image">
            </div>
        <?php endif; endfor; ?>
    </div>
</div>
                <!-- Facilities -->
                <div class="hotel-facilities">
                    <h3>Facilities</h3>
                    <ul class="facilities-list">
                        <?php foreach ($facilities as $facility): ?>
                            <li><i class="fas fa-check"></i> <?php echo htmlspecialchars($facility); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Address -->
                <div class="hotel-address">
                    <h3>Address</h3>
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hotel['address']); ?></p>
                </div>

                <!-- Policies -->
                <div class="hotel-policies">
                    <h3>Policies and Terms</h3>
                    <ul class="policies-list">
                        <li><strong>Check-in:</strong> <?php echo htmlspecialchars($hotel['checkin_time']); ?></li>
                        <li><strong>Check-out:</strong> <?php echo htmlspecialchars($hotel['checkout_time']); ?></li>
                        <li><strong>Cancellation:</strong> <?php echo htmlspecialchars($hotel['cancellation_policy']); ?></li>
                        <li><strong>Payment:</strong> <?php echo htmlspecialchars($hotel['payment_policy']); ?></li>
                        <li><strong>Rating:</strong> <?php echo htmlspecialchars($hotel['rating']); ?> / 5</li>
                        <li><strong>Price per Night:</strong> Rs. <?php echo htmlspecialchars($hotel['price']); ?></li>
                    </ul>
                </div>
            </section>

            <!-- Booking Form -->
            <section class="booking-options">
                <h2>Book Your Stay</h2>
                <form action="order-summary.php" method="post" class="booking-form">
                    <div class="form-group">
                        <label for="check-in">Check-in Date</label>
                        <input type="date" id="check-in" name="check_in_date" required min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="check-out">Check-out Date</label>
                        <input type="date" id="check-out" name="check_out_date" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                    </div>

                    <div class="form-group">
                        <label for="guests">Number of Guests</label>
                        <select id="guests" name="guests" required>
                            <?php for($i=1;$i<=6;$i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $i==2?'selected':''; ?>><?php echo $i; ?> Guest<?php echo $i>1?'s':''; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="room-selection">
    <h3>Select Room Type</h3>
    
    <?php if (!empty($rooms)): ?>
    <?php foreach ($rooms as $index => $room): ?>
        <div class="room-option">
            <label class="room-card" for="room-<?php echo $index; ?>">
                <input type="radio" id="room-<?php echo $index; ?>" 
                       name="room_index" 
                       value="<?php echo $index; ?>" 
                       <?php echo $index === 0 ? 'checked' : ''; ?>>

                <div class="room-details">
                    <h4><?php echo htmlspecialchars($room['room_type']); ?></h4>
                    <p class="room-capacity"><i class="fas fa-user"></i> <?php echo htmlspecialchars($room['capacity']); ?></p>
                    <p class="room-beds"><i class="fas fa-bed"></i> <?php echo htmlspecialchars($room['beds']); ?></p>
                </div>
                <div class="room-price">
                    <p class="price">Rs. <?php echo htmlspecialchars($room['price_per_night']); ?></p>
                    <p class="per-night">per night</p>
                </div>
            </label>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p class="no-rooms">No rooms available.</p>
<?php endif; ?>
</div>
                    <input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>">
                    <button type="submit" name="confirm_booking" class="book-btn">Confirm Booking</button>
                </form>
            </section>
        </div>
    </div>
</main>

<footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p>Email: support@hotelhub.com</p>
                <p>Phone: +91 9876543210</p>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h3>Download the App</h3>
                <div class="app-buttons">
            <img
              src="../images/appleStore.png"
              alt="apple store"
              class="footer__section_logos"
            />
            <img
              src="../images/playStore.png"
              alt="play store"
              class="footer__section_logos"
            />
          </div>
            </div>
            

        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 HotelHub. All rights reserved.</p>
        </div>
    </div>
</footer>

<script>
 const isLoggedIn = <?= (isset($_SESSION['id']) || isset($_SESSION['google_id'])) ? 'true' : 'false' ?>;
  const userRole = <?= isset($_SESSION['role']) ? json_encode($_SESSION['role']) : 'null' ?>;

  const form = document.querySelector("form");
  form.addEventListener("submit", function (event) {
    // Treat admin as not logged in
    if (!isLoggedIn || userRole === "admin") {
      alert("You must be logged in to confirm your booking.");
      const currentUrl = window.location.href;
      window.location.href = "../login/index.html?redirect=" + encodeURIComponent(currentUrl);
      event.preventDefault();
      return;
    }

    const selectedRoom = document.querySelector('input[name="room_index"]:checked');
    if (!selectedRoom) {
      alert("Please select a room type before confirming your booking.");
      event.preventDefault();
      return;
    }
  });

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
<script src="../homepage/home.js"></script>
</body>
</html>
