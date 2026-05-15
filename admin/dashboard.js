// ======= DOM Elements =======
const menuLinks = document.querySelectorAll(".menu a");
const statsSection = document.querySelector(".stats");
const hotelsSection = document.getElementById("hotelsSection");
const roomsSection = document.getElementById("roomsSection");
const usersSection = document.getElementById("usersSection");
const bookingsSection = document.getElementById("bookingsSection");

const addHotelModal = document.getElementById("addHotelModal");
const closeHotelModal = document.querySelector("#addHotelModal .close-modal");
const addHotelForm = document.getElementById("addHotelForm");
const addHotelBtn = document.getElementById("addHotelBtn");

const facilityInput = document.getElementById("facilityInput");
const addFacilityBtn = document.getElementById("addFacilityBtn");
const facilityList = document.getElementById("facilityList");

// ===== Rooms Section =====
const addRoomBtn = document.getElementById("addRoomBtn");
const addRoomModal = document.getElementById("addRoomModal");
const closeRoomModal = addRoomModal.querySelector(".close-modal");
const editRoomModal = document.getElementById("editRoomModal");
const closeModal = editRoomModal.querySelector(".close-modal");

// ===== Users Section =====
const userTableBody = document.getElementById("userTableBody");

// ===== Bookings Section =====
const bookingsTableBody = document.getElementById("bookingsTableBody");

// ======= Data Storage =======
let hotels = []; // Store all hotels
let selectedFacilities = []; // Temporary facilities array for the hotel being added

// ======= Sidebar Navigation =======
menuLinks.forEach((link) => {
  link.addEventListener("click", (e) => {
    if (link.classList.contains("logout-link")) return;
    e.preventDefault();
    menuLinks.forEach((l) => l.classList.remove("active"));
    link.classList.add("active");

    const text = link.textContent.trim();

    if (text === "Hotels") {
      statsSection.classList.add("hidden");
      hotelsSection.classList.remove("hidden");
      roomsSection.classList.add("hidden");
      usersSection.classList.add("hidden");
      bookingsSection.style.display = "none";
      addHotelModal.classList.add("hidden");
      addRoomModal.classList.add("hidden");
    } else if (text === "Rooms") {
      statsSection.classList.add("hidden");
      hotelsSection.classList.add("hidden");
      roomsSection.classList.remove("hidden");
      usersSection.classList.add("hidden");
      bookingsSection.style.display = "none";
      addHotelModal.classList.add("hidden");
      addRoomModal.classList.add("hidden");
    } else if (text === "Users") {
      statsSection.classList.add("hidden");
      hotelsSection.classList.add("hidden");
      roomsSection.classList.add("hidden");
      usersSection.classList.remove("hidden");
      bookingsSection.style.display = "none";
      addHotelModal.classList.add("hidden");
      addRoomModal.classList.add("hidden");
      loadUsers(); // Load users data when clicking on Users link
    } else if (text === "Bookings") {
      statsSection.classList.add("hidden");
      hotelsSection.classList.add("hidden");
      roomsSection.classList.add("hidden");
      usersSection.classList.add("hidden");
      bookingsSection.style.display = "block";
      addHotelModal.classList.add("hidden");
      addRoomModal.classList.add("hidden");
      loadBookings();
    } else {
      statsSection.classList.remove("hidden");
      hotelsSection.classList.add("hidden");
      roomsSection.classList.add("hidden");
      usersSection.classList.add("hidden");
      bookingsSection.style.display = "none";
      addHotelModal.classList.add("hidden");
      addRoomModal.classList.add("hidden");
    }
  });
});

// ======= Open / Close Add Hotel Modal =======
addHotelBtn.addEventListener("click", () => {
  addHotelModal.classList.remove("hidden");
});

closeHotelModal.addEventListener("click", () => {
  addHotelModal.classList.add("hidden");
  resetHotelForm();
});

// Open Add Room Modal
addRoomBtn.addEventListener("click", () => {
  addRoomModal.classList.remove("hidden");
});

// Close Room Modal
closeRoomModal.addEventListener("click", () =>
  addRoomModal.classList.add("hidden")
);

window.addEventListener("click", (e) => {
  if (e.target === addHotelModal) {
    addHotelModal.classList.add("hidden");
    resetHotelForm();
  }
});

// ======= Facilities Handling =======
// Add Facility
addFacilityBtn.addEventListener("click", () => {
  const facility = facilityInput.value.trim();
  if (facility) {
    selectedFacilities.push(facility);
    facilityInput.value = "";
    displayFacilities();
  }
});

// Display Facilities
function displayFacilities() {
  facilityList.innerHTML = "";
  selectedFacilities.forEach((fac, index) => {
    const tag = document.createElement("div");
    tag.classList.add("facility-tag");
    tag.innerHTML = `${fac} <i class="fa-solid fa-times" data-index="${index}"></i>`;
    facilityList.appendChild(tag);
  });
}

// Remove Facility
facilityList.addEventListener("click", (e) => {
  if (e.target.tagName === "I") {
    const index = e.target.getAttribute("data-index");
    selectedFacilities.splice(index, 1);
    displayFacilities();
  }
});

function loadDashboardStats() {
  fetch("get_stats.php")
    .then((res) => res.json())
    .then((data) => {
      document.getElementById("hotelCount").textContent =
        data.total_hotels || 0;
      document.getElementById("roomCount").textContent = data.total_rooms || 0;
      document.getElementById("userCount").textContent = data.total_users || 0;
      document.getElementById("bookingCount").textContent =
        data.total_bookings || 0;
      document.getElementById("totalRevenue").textContent =
        "₹" +
        (data.total_revenue
          ? parseFloat(data.total_revenue).toLocaleString("en-IN")
          : "0");
      document.getElementById("mostBookedHotel").textContent =
        data.most_booked_hotel || "-";
    })
    .catch((err) => console.error("Error loading dashboard stats:", err));
}

// ======= Users Handling =======
// Load Users
function loadUsers() {
  fetch("users/get_users.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.error) {
        console.error("Error loading users:", data.error);
        return;
      }
      displayUsers(data);
    })
    .catch((error) => {
      console.error("Error fetching users:", error);
    });
}

// Display Users
function displayUsers(users) {
  userTableBody.innerHTML = "";

  if (users.length === 0) {
    const row = document.createElement("tr");
    row.innerHTML = `<td colspan="5" class="text-center">No users found</td>`;
    userTableBody.appendChild(row);
    return;
  }

  users.forEach((user) => {
    const row = document.createElement("tr");
    // Combine first_name and last_name for the Name column
    const fullName = `${user.first_name || ""} ${user.last_name || ""}`.trim();
    row.innerHTML = `
      <td>${user.id || ""}</td>
      <td>${fullName}</td>
      <td>${user.email || ""}</td>
      <td>${user.phone || ""}</td>
      <td>${user.gender || ""}</td>
    `;
    userTableBody.appendChild(row);
  });
}

// ======= Bookings Handling =======
// Load Bookings
function loadBookings() {
  console.log("Loading bookings...");

  fetch("bookings/get_bookings.php")
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }
      return response.text();
    })
    .then((text) => {
      try {
        // Try to parse the response as JSON
        const data = JSON.parse(text);
        if (data.error) {
          console.error("Error loading bookings:", data.error);
          return;
        }
        console.log("Bookings data:", data);
        displayBookings(data);
      } catch (e) {
        console.error("Error parsing JSON:", e, "Response text:", text);
        if (bookingsTableBody) {
          bookingsTableBody.innerHTML = `<tr><td colspan="7" class="text-center">Error parsing bookings data</td></tr>`;
        }
      }
    })
    .catch((error) => {
      console.error("Error fetching bookings:", error);
      // Display error message in the table
      if (bookingsTableBody) {
        bookingsTableBody.innerHTML = `<tr><td colspan="7" class="text-center">Error loading bookings: ${error.message}</td></tr>`;
      }
    });
}

// Display Bookings
function displayBookings(bookings) {
  bookingsTableBody.innerHTML = "";

  if (!bookings || bookings.length === 0) {
    console.log("No bookings found");
    const row = document.createElement("tr");
    row.innerHTML = `<td colspan="8" class="no-data">No bookings found</td>`;
    bookingsTableBody.appendChild(row);
    return;
  }

  bookings.forEach((booking) => {
    try {
      const row = document.createElement("tr");

      // Format dates for better display
      let bookingDate = booking.booking_date
        ? new Date(booking.booking_date).toLocaleDateString()
        : "N/A";
      let checkInDate = booking.check_in_date
        ? new Date(booking.check_in_date).toLocaleDateString()
        : "N/A";
      let checkOutDate = booking.check_out_date
        ? new Date(booking.check_out_date).toLocaleDateString()
        : "N/A";

      // Handle invalid dates
      if (bookingDate === "Invalid Date")
        bookingDate = booking.booking_date || "N/A";
      if (checkInDate === "Invalid Date")
        checkInDate = booking.check_in_date || "N/A";
      if (checkOutDate === "Invalid Date")
        checkOutDate = booking.check_out_date || "N/A";

      row.innerHTML = `
        <td>${booking.booking_id || booking.id || ""}</td>
        <td>${booking.user_id || ""}</td>
        <td>${booking.hotel_id || ""}</td>
        <td>${bookingDate}</td>
        <td>${checkInDate}</td>
        <td>${checkOutDate}</td>
        <td>${booking.status || "confirmed"}</td>
      `;

      bookingsTableBody.appendChild(row);
    } catch (err) {
      console.error("Error displaying booking:", err, booking);
    }
  });

  console.log("Bookings display complete");
}

// Initialize data when page loads
document.addEventListener("DOMContentLoaded", function () {
  // Load initial data
  loadUsers();
  loadBookings();
});

// ======= Add Hotel =======
addHotelForm.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(addHotelForm);

  // Append facilities as array
  selectedFacilities.forEach((fac) => formData.append("facilities[]", fac));

  fetch("hotel/add_hotel.php", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      alert(data.message);
      if (data.status === "success") {
        addHotelModal.classList.add("hidden");
        addHotelForm.reset();
        selectedFacilities = [];
        displayFacilities();
        loadHotels(); // refresh admin table
        fetchHotelCards(); // refresh hotel cards on frontend
      }
    })
    .catch((err) => console.error(err));
});

// ======= Load Hotels for Admin Table =======
function loadHotels() {
  fetch("hotel/get_hotels.php")
    .then((res) => res.json())
    .then((data) => {
      hotels = data;
      updateHotelTable();
    })
    .catch((err) => console.error(err));
}

// Update Admin Table
function updateHotelTable() {
  const tableBody = document.getElementById("hotelTableBody");
  tableBody.innerHTML = "";

  hotels.forEach((hotel) => {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td>${hotel.hotel_id}</td>
      <td>${hotel.name}</td>
      <td>${hotel.location}</td>
      <td class="action-btns">
          <a href="hotel/view_hotel.php?id=${hotel.hotel_id}" class="btn-view" title="View">
              <i class="fas fa-eye"></i>
          </a>
          <a href="hotel/edit_hotel.php?id=${hotel.hotel_id}" class="btn-edit" title="Edit">
              <i class="fas fa-edit"></i>
          </a>
          <a href="#" class="btn-delete" title="Delete" onclick="deleteHotel(${hotel.hotel_id}, this)">
    <i class="fas fa-trash"></i>
</a>

      </td>
    `;
    tableBody.appendChild(row);
  });
}

function deleteHotel(hotelId, elem) {
  if (!confirm("Are you sure you want to delete this hotel?")) return;

  fetch(`hotel/delete_hotel.php?id=${hotelId}`)
    .then((response) => response.text())
    .then((data) => {
      alert("Hotel deleted successfully.");
      // Remove the row from the table
      const row = elem.closest("tr");
      row.remove();

      // Optional: update your `hotels` array if you use it elsewhere
      hotels = hotels.filter((h) => h.hotel_id !== hotelId);
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Error deleting hotel.");
    });
}

// ======= Star Rating Input for Admin Form =======
const stars = document.querySelectorAll("#starRating span");
const ratingInput = document.getElementById("hotelRatingInput");

stars.forEach((star) => {
  star.addEventListener("mouseover", () => {
    const value = star.getAttribute("data-value");
    highlightStars(value);
  });

  star.addEventListener("mouseout", () => {
    resetStars();
  });

  star.addEventListener("click", () => {
    const value = star.getAttribute("data-value");
    ratingInput.value = value; // save rating to hidden input
    setSelectedStars(value);
  });
});

function highlightStars(value) {
  stars.forEach((star) => {
    star.classList.toggle("hover", star.getAttribute("data-value") <= value);
  });
}

function resetStars() {
  stars.forEach((star) => {
    star.classList.remove("hover");
  });
}

function setSelectedStars(value) {
  stars.forEach((star) => {
    star.classList.toggle("selected", star.getAttribute("data-value") <= value);
  });
}

// ======= Reset Hotel Form =======
function resetHotelForm() {
  addHotelForm.reset();
  selectedFacilities = [];
  displayFacilities();
  if (ratingInput) ratingInput.value = 0;
  stars.forEach((star) => star.classList.remove("selected", "hover"));
}

// ======= Initial Load =======
document.addEventListener("DOMContentLoaded", () => {
  // Hide users section on page load
  usersSection.classList.add("hidden");

  loadHotels();
  loadDashboardStats();
});

// ======= Rooms Section JS =======
let rooms = []; // Store rooms

const roomForm = document.getElementById("addRoomForm");
const roomTableBody = document.getElementById("roomTableBody");

// Close modal when clicking outside
window.addEventListener("click", (e) => {
  if (e.target === addRoomModal) {
    addRoomModal.classList.add("hidden");
    roomForm.reset();
  }
});

// Handle Add Room Form submit via AJAX
roomForm.addEventListener("submit", (e) => {
  e.preventDefault();
  const formData = new FormData(roomForm);

  fetch("room/add_room.php", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      alert(data.message);
      if (data.status === "success") {
        addRoomModal.classList.add("hidden");
        roomForm.reset();
        loadRooms(); // Refresh rooms table dynamically
      }
    })
    .catch((err) => console.error(err));
});

// Load rooms from DB and populate table
function loadRooms() {
  fetch("room/get_rooms.php")
    .then((res) => res.json())
    .then((data) => {
      rooms = data;
      updateRoomTable();
    })
    .catch((err) => console.error(err));
}

function updateRoomTable() {
  const roomTableBody = document.getElementById("roomTableBody");
  roomTableBody.innerHTML = "";

  rooms.forEach((room) => {
    const row = document.createElement("tr");
    row.id = `roomRow${room.room_id}`; // Assign ID for easy DOM updates
    row.innerHTML = `
            <td>${room.room_id}</td>
            <td>${room.hotel_name}</td>
            <td class="room-type">${room.room_type}</td>
            <td class="room-price">${room.price_per_night}</td>
            <td class="room-capacity">${room.capacity}</td>
            <td class="room-beds">${room.beds}</td>
            <td class="action-btns">
                <button class="btn-edit" title="Edit" onclick='openEditRoomModal(${JSON.stringify(
                  room
                )})'>
                    <i class="fas fa-edit"></i>
                </button>
                <a href="#" class="btn-delete" title="Delete" onclick="deleteRoom(${
                  room.room_id
                }, this)">
  <i class="fas fa-trash"></i>
</a>

            </td>
        `;
    roomTableBody.appendChild(row);
  });
}

function deleteRoom(roomId, elem) {
  if (!confirm("Are you sure you want to delete this room?")) return;

  fetch(`room/delete_room.php?id=${roomId}`)
    .then((response) => response.text())
    .then((data) => {
      if (data.trim() === "success") {
        alert("Room deleted successfully.");
        const row = elem.closest("tr");
        row.remove();

        // Optional: update local room array if you maintain one
        rooms = rooms.filter((r) => r.room_id !== roomId);
      } else {
        alert("Error deleting room.");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Error deleting room.");
    });
}

function openEditRoomModal(room) {
  editRoomModal.classList.remove("hidden");
  document.getElementById("roomId").value = room.room_id;
  document.getElementById("roomType").value = room.room_type;
  document.getElementById("roomPrice").value = room.price_per_night;
  document.getElementById("roomCapacity").value = room.capacity;
  document.getElementById("roomBeds").value = room.beds;
}

closeModal.addEventListener("click", () =>
  editRoomModal.classList.add("hidden")
);
window.addEventListener("click", (e) => {
  if (e.target === editRoomModal) editRoomModal.classList.add("hidden");
});

// Edit Room Form AJAX
document
  .getElementById("editRoomForm")
  .addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData();
    formData.append("room_id", document.getElementById("roomId").value);
    formData.append("room_type", document.getElementById("roomType").value);
    formData.append(
      "price_per_night",
      document.getElementById("roomPrice").value
    );
    formData.append("capacity", document.getElementById("roomCapacity").value);
    formData.append("beds", document.getElementById("roomBeds").value);

    fetch("room/edit_room.php", {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.status === "success") {
          alert("Room updated successfully!");
          const row = document.getElementById("roomRow" + data.room.room_id);
          if (row) {
            row.querySelector(".room-type").textContent = data.room.room_type;
            row.querySelector(".room-price").textContent =
              data.room.price_per_night;
            row.querySelector(".room-capacity").textContent =
              data.room.capacity;
            row.querySelector(".room-beds").textContent = data.room.beds;
          }
          editRoomModal.classList.add("hidden");
        } else {
          alert("Error: " + data.message);
        }
      })
      .catch((err) => {
        console.error(err);
        alert("Error updating room.");
      });
  });

// Call this when rendering table rows
function addEditButtonListener(btn, room) {
  btn.addEventListener("click", () => openEditRoomModal(room));
}

// ======= Initial Load for rooms
document.addEventListener("DOMContentLoaded", () => {
  loadRooms();
});

// ======= Booking Status Update Functions =======
function addBookingButtonListeners() {
  // Add event listeners for Accept buttons
  document.querySelectorAll(".btn-accept").forEach((button) => {
    button.addEventListener("click", function () {
      const bookingId = this.getAttribute("data-booking-id");
      updateBookingStatus(bookingId, "accepted");
    });
  });

  // Add event listeners for Reject buttons
  document.querySelectorAll(".btn-reject").forEach((button) => {
    button.addEventListener("click", function () {
      const bookingId = this.getAttribute("data-booking-id");
      updateBookingStatus(bookingId, "rejected");
    });
  });
}

function updateBookingStatus(bookingId, status) {
  // Create form data
  const formData = new FormData();
  formData.append("booking_id", bookingId);
  formData.append("status", status);

  // Send request to update status
  fetch("bookings/update_booking_status.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Reload bookings to refresh the table
        loadBookings();
      } else {
        alert("Error: " + data.message);
      }
    })
    .catch((error) => {
      console.error("Error updating booking status:", error);
      alert("An error occurred while updating the booking status.");
    });
}
