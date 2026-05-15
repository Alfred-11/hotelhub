//Hotel scroll buttons
function scrollHotels(direction) {
  const carousel = document.querySelector(".hotel-listings");
  const scrollAmount = 300;

  if (direction === "left") {
    carousel.scrollBy({ left: -scrollAmount, behavior: "smooth" });
  } else {
    carousel.scrollBy({ left: scrollAmount, behavior: "smooth" });
  }
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
  fetch(`search.php?q=${encodeURIComponent(query)}`)
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
          <small style="color:black;">(${
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

// Toggle profile dropdown
function toggleProfileMenu() {
  const menu = document.getElementById("profile-menu");
  menu.classList.toggle("show");
}

document.addEventListener("click", function (e) {
  const profileToggle = document.querySelector(".profile-toggle");
  const profileMenu = document.getElementById("profile-menu");

  // Close only if clicked outside the toggle area
  if (profileToggle && !profileToggle.contains(e.target)) {
    profileMenu.classList.remove("show");
  }
});

// Banner Carousel
let movies = [
  {
    image: "../images/banner4.png",
  },
  {
    image: "../images/banner9.jpg",
  },
  {
    image: "../images/banner10.jpg",
  },
];

const carousel = document.querySelector(".carousel");
let sliders = [];

let slideIndex = 0; // to track current slide index.

const createSlide = () => {
  if (slideIndex >= movies.length) {
    slideIndex = 0;
  }

  // creating DOM element
  let slide = document.createElement("div");
  let imgElement = document.createElement("img");

  // attaching all elements
  imgElement.appendChild(document.createTextNode(""));
  slide.appendChild(imgElement);
  carousel.appendChild(slide);

  // setting up image
  imgElement.src = movies[slideIndex].image;
  slideIndex++;

  // setting elements classname
  slide.className = "slider";

  sliders.push(slide);

  if (sliders.length) {
    sliders[0].style.marginLeft = `calc(-${100 * (sliders.length - 2)}% - ${
      10 * (sliders.length - 2)
    }px)`;
  }
};

for (let i = 0; i < 3; i++) {
  createSlide();
}

setInterval(() => {
  createSlide();
}, 6000);

function confirmLogout() {
  const confirmed = confirm("Are you sure you want to logout?");
  if (confirmed) {
    window.location.href = "../login/logout.php"; // Adjust path if needed
  }
}

function fetchHotelCards() {
  fetch("../admin/hotel/get_hotels.php") // <-- fixed path
    .then((res) => res.json())
    .then((data) => {
      const hotelContainer = document.getElementById("hotelListings");
      hotelContainer.innerHTML = "";

      if (!data || data.length === 0) {
        hotelContainer.innerHTML = "<p>No hotels found.</p>";
        return;
      }

      data.forEach((hotel) => {
        let starsHTML = "";
        const rating = parseInt(hotel.rating) || 0;

        for (let i = 1; i <= 5; i++) {
          starsHTML +=
            i <= rating
              ? '<i class="fas fa-star"></i>'
              : '<i class="far fa-star"></i>';
        }

        const price = hotel.price ? parseFloat(hotel.price).toFixed(2) : "0.00";

        hotelContainer.innerHTML += `
          <div class="hotel-card">
            <div class="hotel-image">
              <img src="../admin/hotel/${
                hotel.image_main || "default.jpg"
              }" alt="${hotel.name}">
            </div>
            <div class="hotel-details">
              <h3>${hotel.name}</h3>
              <p class="location"><i class="fas fa-map-marker-alt"></i> ${
                hotel.location
              }</p>
              <div class="rating"><span class="stars">${starsHTML}</span></div>
              <p class="price">Rs.${price} /night</p>
              <a href="../booking/booking.php?id=${
                hotel.hotel_id
              }" class="book-now-btn">Check Deal</a>
            </div>
          </div>
        `;
      });
    })
    .catch((err) => {
      console.error("Error fetching hotels:", err);
      document.getElementById("hotelListings").innerHTML =
        "<p>Failed to load hotels.</p>";
    });
}

// Call on page load
document.addEventListener("DOMContentLoaded", fetchHotelCards);

// Call on page load
document.addEventListener("DOMContentLoaded", fetchHotelCards);
