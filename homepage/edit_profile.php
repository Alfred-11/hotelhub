<?php
session_start();
include '../db.php'; // your database connection

// Ensure user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: ../login/index.html");
    exit();
}

$email = $_SESSION['email']; // use email from session as identifier

// Fetch user details from the database
$stmt = $conn->prepare("SELECT first_name, last_name, email, phone, dob, gender, location FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found.");
}

$user = $result->fetch_assoc();

// Assign variables with fallbacks
$firstName = $user['first_name'] ?? '';
$lastName = $user['last_name'] ?? '';
$fullName = trim($firstName . ' ' . $lastName);
$email = $user['email'] ?? '';
$phone = $user['phone'] ?? '';
$dob = $user['dob'] ?? '';
$gender = $user['gender'] ?? '';
$location = $user['location'] ?? '';
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Account Settings - PickMyRoom</title>
<link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
    />
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      background-color: #f7f7f7;
      font-family: "Poppins", sans-serif;
      margin: 0;
      padding: 0;
    }

    .navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 15px 50px;
  box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
  flex-wrap: nowrap; /* Prevent wrapping */
}

.logo {
  font-size: 1.8rem;
  font-weight: bold;
  color: #0079c2;
  margin-right: 30px;
}

.search-container {
  display: flex;
  justify-content: center;
  padding: 8px 20px;
  flex-grow: 1;
  max-width: 600px;
  position: relative; /* Anchor for absolute positioning */
  margin: 0 auto;
}

.search-box {
  display: flex;
  align-items: center;
  width: 100%;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  background-color: #fff;
  overflow: hidden;
  border: 1px solid #c6c4c4;
  transition: box-shadow 0.3s ease, border 0.3s ease;
}

.search-input {
  flex: 1;
  padding: 12px 16px;
  font-size: 1rem;
  border: none;
  outline: none;
  color: #7e7c7c;
}

.search-icon {
  padding: 0 16px;
  color: #666;
  background-color: transparent;
  font-size: 1.2rem;
}

/* Updated dropdown position and size */
.results-list {
  list-style: none;
  padding: 0;
  margin: 0;
  background: #fff;
  border: 1px solid #ccc;
  max-height: 200px;
  overflow-y: auto;
  display: none;
  position: absolute;
  top: 100%; /* directly below search box */
  left: 18px;
  width: 93%; /* match width of search-box */
  z-index: 10;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.results-list li {
  padding: 10px 16px;
  cursor: pointer;
}

.results-list li:hover {
  background: #f0f0f0;
}

.nav-links {
  display: flex;
  gap: 20px;
  align-items: center;
  white-space: nowrap; /* Prevent wrapping */
  flex-shrink: 0; /* Prevent shrinking */
}

.nav-links a {
  text-decoration: none;
  color: black;
  font-size: 1rem;
  padding-top: 3px;
}

.sign-in-btn {
  background: #0079c2;
  color: white !important;
  padding: 6px;
  border-radius: 5px;
  text-decoration: none;
  font-weight: bold;
  width: 90px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.sign-in-btn:hover {
  background: #005fa3;
}
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
    .settings-container {
      max-width: 550px;
      margin: 30px auto;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
      padding: 30px;
      position: relative;
    }

    .settings-header h2 {
      text-align: center;
      margin-bottom: 30px;
      color: #0079c2;
      font-size: 30px;
    }

    .form-group {
      margin-bottom: 20px;
      display: flex;
      align-items: center;
    }

    label.form-label {
      width: 140px; 
      margin: 0;
      margin-right: 10px;
      font-weight: 500;
      color: #333;
    }

    input[type="text"],
    input[type="email"],
    input[type="date"],
    select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 16px;
      flex: 1;
    }

    input[readonly] {
      background-color: #f1f1f1;
      cursor: not-allowed;
    }

    .btn-orange {
      background-color:  #0079c2;
      color: white;
      border: none;
      padding: 12px 24px;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
      transition: background-color 0.2s ease-in-out;
      width: 50%;
      margin-top: 20px;  
    }

    .btn-orange:hover {
      background-color:  #005fa3;
    }

    .text-center {
      text-align: center;
    }

    .alert {
  position: absolute;
  right: -350px; 
  top: 30px;
  width: 300px;
  padding: 12px;
  background-color: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
  border-radius: 6px;
  font-size: 16px;
  text-align: center;
  box-shadow: 0 0 8px rgba(0, 0, 0, 0.05);
}

/* =========================================
   📱 Responsive Design for Account Settings Page
   ========================================= */

/* ---------- Tablet Screens (≤1024px) ---------- */
@media (max-width: 1024px) {
  /* Navbar */
  .navbar {
    padding: 15px 25px;
  }

  .logo {
    font-size: 28px;
  }

  .search-container {
    max-width: 450px;
  }

  /* Settings Form */
  .settings-container {
    max-width: 500px;
    padding: 25px;
  }

  .form-group label.form-label {
    width: 120px;
  }
}

/* ---------- Mobile Screens (≤768px) ---------- */
@media (max-width: 768px) {
  /* Navbar (keep as is) */
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

  /* Settings Form */
  .settings-container {
    max-width: 90%;
    padding: 20px;
    margin: 20px auto;
  }

  .settings-header h2 {
    font-size: 24px;
  }

  .form-group {
    flex-direction: column;
    align-items: flex-start;
  }

  label.form-label {
    width: 100%;
    margin-bottom: 8px;
  }

  input[type="text"],
  input[type="email"],
  input[type="date"],
  select {
    width: 100%;
  }

  .btn-orange {
    width: 100%;
  }

  .alert {
    position: static;
    margin-top: 15px;
    width: 100%;
  }
}

/* ---------- Small Mobile Screens (≤480px) ---------- */
@media (max-width: 480px) {
  .navbar {
    padding: 10px;
  }

  .logo {
    font-size: 26px;
  }

  .search-container {
    padding: 5px 10px;
  }

  .search-input {
    font-size: 0.9rem;
  }

  /* Settings Form */
  .settings-container {
    max-width: 95%;
    padding: 15px;
  }

  .settings-header h2 {
    font-size: 22px;
  }

  label.form-label {
    font-size: 14px;
  }

  input,
  select {
    font-size: 14px;
    padding: 8px;
  }

  .btn-orange {
    padding: 10px;
    font-size: 15px;
  }
}
 </style>
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
          <a href="home.php#hotels">Hotels</a>
          
          <a href="home.php#about">About Us</a>

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

<div class="settings-container">

  <div class="settings-header">
    <h2><i class="fas fa-user-edit"></i> Edit Profile</h2>
  </div>

  <form method="POST" action="update_profile.php">
    <div class="form-group">
      <label class="form-label">First Name</label>
      <input type="text" name="first_name" value="<?php echo htmlspecialchars($firstName); ?>" required>
    </div>

    <div class="form-group">
      <label class="form-label">Last Name</label>
      <input type="text" name="last_name" value="<?php echo htmlspecialchars($lastName); ?>">
    </div>

    <div class="form-group">
      <label class="form-label">Email (Readonly)</label>
      <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" readonly>
    </div>

    <div class="form-group">
      <label class="form-label">Phone</label>
      <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
    </div>

    <div class="form-group">
      <label class="form-label">Date of Birth</label>
      <input type="date" name="dob" value="<?php echo htmlspecialchars($dob); ?>">
    </div>

    <div class="form-group">
      <label class="form-label">Gender</label>
      <select name="gender">
        <option value="" <?php if (!isset($gender) || $gender == '') echo 'selected'; ?>>-- Select Gender --</option>
        <option value="male" <?php if ($gender == 'male') {
            echo 'selected';
        } ?>>Male</option>
        <option value="female" <?php if ($gender == 'female') {
            echo 'selected';
        } ?>>Female</option>
      </select>
    </div>

    <div class="form-group">
      <label class="form-label">Location</label>
      <input type="text" name="location" value="<?php echo htmlspecialchars($location); ?>">
    </div>

    <div class="text-center">
      <button type="submit" class="btn-orange" id="submitBtn">Save Changes</button>
    </div>
  </form>
  <?php if (isset($_GET['updated'])): ?>
  <div class="alert alert-success">
    <i class="fas fa-check-circle" style="margin-right: 10px;"></i>
    Profile details updated successfully.
  </div>
<?php endif; ?>
</div>

<script>
  // Allow form submit only when Save Changes button is clicked
  let allowSubmit = false;

  submitBtn.onclick = () => allowSubmit = true;

  document.querySelector("form").onsubmit = (e) => {
    if (!allowSubmit) e.preventDefault();
    allowSubmit = false;
  };

  document.querySelector("form").onkeydown = (e) => {
    if (e.key === "Enter") e.preventDefault();
  };


  setTimeout(() => {
    const alertBox = document.querySelector('.alert');
    if (alertBox) {
      alertBox.style.display = 'none';
    }
  }, 3000);

   // Remove ?updated=1 from URL without reloading
    if (window.history.replaceState) {
      const url = new URL(window.location);
      url.searchParams.delete('updated');
      window.history.replaceState({}, document.title, url.pathname);
    }
</script>

<script src="home.js"></script>
</body>
</html>
