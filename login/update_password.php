<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $pass = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Connect to DB
    include '../db.php';

    // Function to show alert and stay
    function showAlert($message) {
        echo "<script>alert('$message');window.history.back();</script>";
        exit;
    }

    // Validation
    if (empty($token) || empty($pass) || empty($confirm)) {
        showAlert("All fields are required.");
    }

    if ($pass !== $confirm) {
        showAlert("Passwords do not match.");
    }

    if (strlen($pass) < 6) {
        showAlert("Password should be at least 6 characters.");
    }

    // Validate token
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();
        $userId = $user['id'];
        $hashed = password_hash($pass, PASSWORD_DEFAULT);

        $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL  WHERE id = ?");
        $update->bind_param("si", $hashed, $userId);
        $update->execute();

        echo "<script>
                alert('Password updated successfully!');
                window.location.href = 'index.html';
              </script>";
    } else {
        showAlert("Invalid or expired token.");
    }

    $stmt->close();
    $conn->close();
}
?>
