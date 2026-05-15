<?php

require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Connect to DB
    include '../db.php';

    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(50));
        $reset_link = "http://localhost:80/HotelBookingSystem(8)/login/reset_password.php?token=" . $token;

        // Store token in DB
        $update = $conn->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
        $update->bind_param("ss", $token, $email);
        $update->execute();

        // Send email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'alfredarouza5@gmail.com';
            $mail->Password   = 'mdjw zrzu niob vktm';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('contact.alfredarouza5@gmail.com', 'HotelHub Support');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset - HotelHub';
            $mail->Body    = "
                <p>Click the link below to reset your password:</p>
                <p><a href='$reset_link'>$reset_link</a></p>
            ";

            $mail->send();
             echo "<script>alert('Reset link sent. Check your email.');window.history.back();</script>";
        } catch (Exception $e) {
            echo "Email sending failed. Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Email not found!";
    }
}
