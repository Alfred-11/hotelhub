<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Hotel Admin Dashboard</title>
  <link rel="stylesheet" href="dashboard.css" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
  />
</head>
<body>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
  <div class="top-section">
    <h2 class="logo">HotelHub</h2>
    <div class="profile-container">
      <div class="profile-header">
        <img
          src="../images/profile.jpg"
          alt="User Image"
          onerror="this.onerror=null; this.src='../images/default_avatar.png';"
          class="profile-img"
        />
        <h3>Admin</h3>
      </div>
    </div>
  </div>

  <nav class="menu">
    <a href="#" class="active"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
    <a href="#" id="hotelsLink"><i class="fa-solid fa-hotel"></i> Hotels</a>
    <a href="#" id="roomsLink"><i class="fa-solid fa-bed"></i> Rooms</a>
    <a href="#" id="bookingsLink"><i class="fa-solid fa-calendar-check"></i> Bookings</a>
    <a href="#" id="usersLink"><i class="fa-solid fa-users"></i> Users</a>
    <a href="../login/logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </nav>
</aside>


    <!-- Main Content -->
    <main class="main-content">
  <!-- Users Section at Top -->
  <section class="users-section" id="usersSection">
    <div class="section-header">
      <h2>Users</h2>
    </div>

    <!-- Common Admin Users table Table -->
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Gender</th>
        </tr>
      </thead>
      <tbody id="userTableBody">
        <!-- Users will be added dynamically -->
      </tbody>
    </table>
  </section>

  <!-- Bookings Section -->
  <section class="bookings-section" id="bookingsSection" style="display: none;">
    <div class="section-header">
      <h2>Bookings</h2>
    </div>

    <!-- Bookings Table -->
    <table class="admin-table">
      <thead>
        <tr>
          <th>Booking ID</th>
          <th>User ID</th>
          <th>Hotel ID</th>
          <th>Booking Date</th>
          <th>Check-in Date</th>
          <th>Check-out Date</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody id="bookingsTableBody">
        <!-- Booking data will be loaded here -->
      </tbody>
    </table>
  </section>
      
<!-- ========================= STATS SECTION ========================= -->
<section class="stats" id="dashboardStats">
  <div class="card">
    <i class="fa-solid fa-hotel"></i>
    <h3 id="hotelCount">0</h3>
    <p>Total Hotels</p>
  </div>

  <div class="card">
    <i class="fa-solid fa-bed"></i>
    <h3 id="roomCount">0</h3>
    <p>Total Rooms</p>
  </div>

  <div class="card">
    <i class="fa-solid fa-users"></i>
    <h3 id="userCount">0</h3>
    <p>Total Users</p>
  </div>

  <div class="card">
    <i class="fa-solid fa-ticket"></i>
    <h3 id="bookingCount">0</h3>
    <p>Total Bookings</p>
  </div>

  <div class="card">
    <i class="fa-solid fa-indian-rupee-sign"></i>
    <h3 id="totalRevenue">₹0</h3>
    <p>Total Revenue</p>
  </div>

  <div class="card">
    <i class="fa-solid fa-star"></i>
    <h3 id="mostBookedHotel">-</h3>
    <p>Most Booked Hotel</p>
  </div>
</section>

 <!-- ========================= HOTELS SECTION ========================= -->
<section class="hotels-section hidden" id="hotelsSection">
  <div class="section-header">
    <h2>Hotels</h2>
    <button id="addHotelBtn" class="save-btn">+ Add Hotel</button>
  </div>

  <!-- Common Admin Table -->
  <table class="admin-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Hotel Name</th>
        <th>Location</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody id="hotelTableBody">
      <!-- Hotels will be added here dynamically -->
    </tbody>
  </table>

  <!-- ========================= ADD HOTEL MODAL ========================= -->
  <div class="modal hidden" id="addHotelModal">
    <div class="modal-content">
      <span class="close-modal">&times;</span>
      <h2>Add New Hotel</h2>

      <form id="addHotelForm" enctype="multipart/form-data">
        <label>Hotel Name</label>
        <input type="text" name="name" required>

        <label>Location</label>
        <input type="text" name="location" required>

        <label>Address</label>
        <textarea name="address" rows="2" required></textarea>

        <label>Description</label>
        <textarea name="description" rows="3"></textarea>

        <label>Hotel Rating</label>
        <div class="star-rating" id="starRating">
          <span data-value="1">&#9733;</span>
          <span data-value="2">&#9733;</span>
          <span data-value="3">&#9733;</span>
          <span data-value="4">&#9733;</span>
          <span data-value="5">&#9733;</span>
        </div>
        <input type="hidden" name="rating" id="hotelRatingInput" value="0">

        <label for="price">Price per Night</label>
        <input type="number" step="0.01" id="price" name="price" required>

        <label>Check-in Time</label>
        <input type="text" name="checkin_time" placeholder="12:00 PM">

        <label>Check-out Time</label>
        <input type="text" name="checkout_time" placeholder="11:00 AM">

        <label>Main Image</label>
        <input type="file" name="image_main" accept="image/*">

        <label>Gallery Image 1</label>
        <input type="file" name="image_gallery1" accept="image/*">

        <label>Gallery Image 2</label>
        <input type="file" name="image_gallery2" accept="image/*">

        <label>Gallery Image 3</label>
        <input type="file" name="image_gallery3" accept="image/*">

        <label>Cancellation Policy</label>
        <textarea name="cancellation_policy" rows="2"></textarea>

        <label>Payment Policy</label>
        <textarea name="payment_policy" rows="2"></textarea>

        <!-- ===== Facilities Section ===== -->
        <div class="facilities-section">
          <label>Hotel Facilities</label>
          <div id="facilityList"></div>

          <div class="add-facility-container">
            <input type="text" id="facilityInput" placeholder="Enter facility name">
            <button type="button" id="addFacilityBtn">Add</button>
          </div>
        </div>

        <button type="submit" class="save-btn">Save Hotel</button>
      </form>
    </div>
  </div>
</section>


<!-- ========================= ROOMS SECTION ========================= -->
<section class="rooms-section hidden" id="roomsSection">
  <div class="section-header">
    <h2>Rooms</h2>
    <button id="addRoomBtn" class="save-btn">+ Add Room</button>
  </div>

  <!-- Common Admin Table -->
  <table class="admin-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Hotel</th>
        <th>Room Type</th>
        <th>Price per Night</th>
        <th>Capacity</th>
        <th>Beds</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody id="roomTableBody">
      <!-- Rooms will be added dynamically -->
    </tbody>
  </table>

  <!-- Edit Room Modal -->
<div id="editRoomModal" class="modal hidden">
  <div class="edit-container">
    <span class="close-modal">&times;</span>
    <h2 class="page-title">Edit Room</h2>
    <form id="editRoomForm">
      <input type="hidden" name="room_id" id="roomId">
      <div class="mb-3">
        <label class="form-label">Room Type</label>
        <input type="text" name="room_type" id="roomType" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Price per Night</label>
        <input type="number" step="0.01" name="price_per_night" id="roomPrice" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Capacity</label>
        <input type="text" name="capacity" id="roomCapacity" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Beds</label>
        <input type="text" name="beds" id="roomBeds" class="form-control" required>
      </div>
      <button type="submit" class="save-btn">Save Changes</button>
    </form>
  </div>
</div>


  <!-- Add Room Modal (embedded) -->
  <div class="modal hidden" id="addRoomModal">
    <div class="modal-content">
      <span class="close-modal">&times;</span>
      <h2>Add New Room</h2>

      <form id="addRoomForm" method="POST" action="room/add_room.php">
        <label>Select Hotel</label>
        <select name="hotel_id" id="hotelSelect" required>
          <option value="">--Select Hotel--</option>
          <?php
            include '../db.php';
            $res = mysqli_query($conn, "SELECT hotel_id, name FROM hotels");
            while($row = mysqli_fetch_assoc($res)){
              echo "<option value='{$row['hotel_id']}'>{$row['name']}</option>";
            }
          ?>
        </select>

        <label>Room Type</label>
        <input type="text" name="room_type" required>

        <label>Price per Night</label>
        <input type="number" name="price_per_night" step="0.01" required>

        <label>Capacity</label>
        <input type="text" name="capacity" required>

        <label>Beds</label>
        <input type="text" name="beds" required>

        <button type="submit" class="save-btn">Save Room</button>
      </form>
    </div>
  </div>
</section>
</main>

  </div>

  <!-- Users section moved to the top of the page -->

  <script src="dashboard.js"></script>
</body>
</html>
