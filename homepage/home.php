<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Hotel Booking System</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    />
    <link rel="stylesheet" href="home.css">
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
          <a href="#hotels">Hotels</a>
          
          <a href="#about">About Us</a>

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
                <a href="view_profile.php">View Profile</a>
                <a href="../booking/booking-history.php">My Bookings</a>
                <a href="#" onclick="confirmLogout()">Logout</a>
              </div>
          </div>
          <?php else: ?>
            <a href="../login/index.html" class="sign-in-btn">Sign In</a>
          <?php endif; ?>
        </div> 
      </nav>


<!-- Main Banner Carousel -->
 <div class="carousel-container">
                <div class="carousel">
                </div>
            </div>

<!-- Hotel Listings Section -->
<section id="hotels" class="hotels-section">
    <div class="container">
        <h2>Discover our top-rated hotels</h2>
        <div class="hotel-carousel-wrapper">
      <button class="scroll-btn left" onclick="scrollHotels('left')">
        <i class="fas fa-chevron-left"></i>
      </button>

        <div class="hotel-listings" id="hotelListings">
  <!-- Hotel cards will be inserted here dynamically -->
</div>

        <button class="scroll-btn right" onclick="scrollHotels('right')">
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>
    </div>
</section>

<!-- Offers Section -->

</div>

<section id="about" class="about-section">
  <div class="container">
    <h2>About Us</h2>
    <p>
      <strong>HotelHub</strong> is an innovative Hotel Booking System designed to simplify and enhance your accommodation experience. With HotelHub, users can effortlessly browse through a wide range of available hotels, explore room options, choose their preferred dates, and make secure online payments—all in just a few clicks. By eliminating the need for traditional booking methods and long waiting times, this platform ensures a seamless and hassle-free booking experience for travelers.
    </p>
    <p>
      Key features include user authentication, real-time room availability updates, secure payment gateways, and instant booking confirmation. Users can also access hotel details, view their booking history, download e-receipts, and modify or cancel bookings when necessary. For hotel owners, the system offers a comprehensive management tool to oversee room availability, pricing, and reservations, making the process efficient and streamlined. <strong>HotelHub</strong> is dedicated to providing a convenient, easy-to-use, and secure platform for both customers and hotel operators alike.
    </p>
    </div>
</section>


   <footer>
    <div class="footer-container">
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



    <script src="../login/main.js"></script>
    <script src="home.js"></script>
   
  </body>
</html>
