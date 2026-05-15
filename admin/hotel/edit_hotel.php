<?php
include '../../db.php';
session_start();

if (!isset($_GET['id'])) {
    die("Hotel ID not provided.");
}

$hotel_id = intval($_GET['id']);

// ======== Handle Add/Delete Facility Actions ========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_facility') {
        $facility_name = trim($_POST['facility_name']);
        if ($facility_name !== '') {
            $sql = "INSERT INTO hotel_facilities (hotel_id, facility_name) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $hotel_id, $facility_name);
            $stmt->execute();
            echo json_encode(['success' => true, 'facility_id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    if ($_POST['action'] === 'delete_facility') {
        $facility_id = intval($_POST['facility_id']);
        $sql = "DELETE FROM hotel_facilities WHERE facility_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $facility_id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        exit;
    }
}

// ======== Fetch Hotel Details ========
$sql = "SELECT * FROM hotels WHERE hotel_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$result = $stmt->get_result();
$hotel = $result->fetch_assoc();

if (!$hotel) {
    die("Hotel not found!");
}

// ======== Fetch Hotel Facilities ========
$facilities = [];
$sql2 = "SELECT facility_id, facility_name FROM hotel_facilities WHERE hotel_id = ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $hotel_id);
$stmt2->execute();
$res2 = $stmt2->get_result();
while ($row = $res2->fetch_assoc()) {
    $facilities[] = $row;
}
$stmt->close();
$stmt2->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Hotel - <?php echo htmlspecialchars($hotel['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body {
            background-color: #f4f6f8;
            font-family: 'Poppins', sans-serif;
        }
        .edit-container {
            max-width: 950px;
            background: #fff;
            padding: 30px;
            margin: 40px auto;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .page-title {
            color: #0079c2;
            font-weight: 600;
            margin-bottom: 20px;
            text-align:center;
        }
        .facility-badge {
            display: inline-block;
            background: #e7f3ff;
            color: #0079c2;
            border-radius: 20px;
            padding: 6px 12px;
            margin: 4px;
            font-size: 0.9rem;
        }
        .facility-badge button {
            background: none;
            border: none;
            color: #dc3545;
            margin-left: 6px;
            cursor: pointer;
            font-size: 1rem;
            line-height: 1;
        }
        .save-btn {
            background: #0079c2;
            border: none;
            padding: 10px 20px;
            color: white;
            border-radius: 6px;
            font-weight: 500;
        }
        .img-preview {
            width: 100%;
            max-width: 180px;
            height: auto;
            border-radius: 8px;
            margin-top: 5px;
        }
    </style>
</head>
<body>

<div class="edit-container">
    <h2 class="page-title">Edit Hotel - <?php echo htmlspecialchars($hotel['name']); ?></h2>

    <form action="update_hotel.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>">

        <!-- Basic Info -->
        <div class="mb-3">
            <label class="form-label">Hotel Name</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($hotel['name']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Location</label>
            <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($hotel['location']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($hotel['address']); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($hotel['description']); ?></textarea>
        </div>

        <!-- Check-in/out -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Check-in Time</label>
                <input type="time" name="checkin_time" class="form-control" value="<?php echo htmlspecialchars($hotel['checkin_time']); ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Check-out Time</label>
                <input type="time" name="checkout_time" class="form-control" value="<?php echo htmlspecialchars($hotel['checkout_time']); ?>">
            </div>
        </div>

        <!-- Policies -->
        <div class="mb-3">
            <label class="form-label">Cancellation Policy</label>
            <textarea name="cancellation_policy" class="form-control" rows="2"><?php echo htmlspecialchars($hotel['cancellation_policy']); ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Payment Policy</label>
            <textarea name="payment_policy" class="form-control" rows="2"><?php echo htmlspecialchars($hotel['payment_policy']); ?></textarea>
        </div>

        <!-- Rating & Price -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Rating (1-5)</label>
                <input type="number" step="0.1" name="rating" class="form-control" value="<?php echo htmlspecialchars($hotel['rating']); ?>" min="1" max="5">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Price per Night (₹)</label>
                <input type="number" name="price" class="form-control" value="<?php echo htmlspecialchars($hotel['price']); ?>" required>
            </div>
        </div>

        <!-- Image Uploads -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Main Image</label>
                <input type="file" name="image_main" class="form-control">
                <?php if ($hotel['image_main']): ?>
                    <img src="<?php echo $hotel['image_main']; ?>" class="img-preview" alt="Main Image">
                <?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Gallery Image 1</label>
                <input type="file" name="image_gallery1" class="form-control">
                <?php if ($hotel['image_gallery1']): ?>
                    <img src="<?php echo $hotel['image_gallery1']; ?>" class="img-preview">
                <?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Gallery Image 2</label>
                <input type="file" name="image_gallery2" class="form-control">
                <?php if ($hotel['image_gallery2']): ?>
                    <img src="<?php echo $hotel['image_gallery2']; ?>" class="img-preview">
                <?php endif; ?>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Gallery Image 3</label>
                <input type="file" name="image_gallery3" class="form-control">
                <?php if ($hotel['image_gallery3']): ?>
                    <img src="<?php echo $hotel['image_gallery3']; ?>" class="img-preview">
                <?php endif; ?>
            </div>
        </div>

        <!-- Facilities Section -->
        <div class="mb-3">
            <label class="form-label">Facilities</label>
            <div id="facilityList">
                <?php foreach ($facilities as $facility): ?>
                    <span class="facility-badge" data-id="<?php echo $facility['facility_id']; ?>">
                        <?php echo htmlspecialchars($facility['facility_name']); ?>
                        <button type="button" class="remove-facility" data-id="<?php echo $facility['facility_id']; ?>">&times;</button>
                    </span>
                <?php endforeach; ?>
            </div>
            <input type="text" id="newFacility" class="form-control mt-2" placeholder="Add new facility and press Enter">
        </div>

        <button type="submit" class="save-btn">Save Changes</button>
    </form>
</div>

<script>
$(document).ready(function() {
    // Add facility
    $('#newFacility').keypress(function(e) {
        if (e.which === 13) {
            e.preventDefault();
            const facility = $(this).val().trim();
            if (facility === '') return;
            $.post('', {
                action: 'add_facility',
                facility_name: facility
            }, function(response) {
                const res = JSON.parse(response);
                if (res.success) {
                    $('#facilityList').append(
                        `<span class="facility-badge" data-id="${res.facility_id}">
                            ${facility}
                            <button type="button" class="remove-facility" data-id="${res.facility_id}">&times;</button>
                        </span>`
                    );
                    $('#newFacility').val('');
                } else {
                    alert('Error adding facility.');
                }
            });
        }
    });

    // Delete facility
    $(document).on('click', '.remove-facility', function() {
        const facilityId = $(this).data('id');
        if (confirm('Remove this facility?')) {
            $.post('', {
                action: 'delete_facility',
                facility_id: facilityId
            }, function(response) {
                const res = JSON.parse(response);
                if (res.success) {
                    $(`.facility-badge[data-id="${facilityId}"]`).fadeOut(300, function() {
                        $(this).remove();
                    });
                } else {
                    alert('Error deleting facility.');
                }
            });
        }
    });
});
</script>

</body>
</html>
