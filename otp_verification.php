<?php
session_start();
require 'mailer.php';

// Check if the user is logged in
if (!isset($_SESSION['login_success']) || $_SESSION['login_success'] !== true) {
    header("Location: login.php");
    exit();
}

// Check if the OTP is set in the session
if (!isset($_SESSION['otp'])) {
    header("Location: login.php");
    exit();
}

// Define a variable to store OTP verification messages
$otpVerificationMessage = "";

// OTP verification form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $enteredOTP = $_POST['enteredOTP'];

    // Check if the entered OTP matches the stored OTP
    if ($enteredOTP == $_SESSION['otp']) {
        // Check if OTP has expired (1 minutes expiration)
        $otpTimestamp = $_SESSION['otp_timestamp'];
        $currentTimestamp = time();
        $otpExpiration = 1 * 60; // 1 minutes in seconds

        if (($currentTimestamp - $otpTimestamp) <= $otpExpiration) {
            // OTP is correct and within the expiration time, redirect to the home page
            unset($_SESSION['otp']); // Clear the OTP from the session
            unset($_SESSION['otp_timestamp']); // Clear the OTP timestamp from the session
            header("Location: homepage.php");
            exit();
        } else {
            // OTP has expired, show error message
            $otpVerificationMessage = "OTP has expired. Please <a href='login.php'>login again</a>.";
        }
    } else {
        // Incorrect OTP, show error message
        $otpVerificationMessage = "Incorrect OTP. Please <a href='login.php'>try again</a>.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <link rel="stylesheet" href="styles.php">

    <style>
        body {
            margin: 0;
            padding: 0;
            background: url('image/4kbgimage.jpg') center center fixed; 
            background-size: cover;
            font-family: Arial, sans-serif; 
        }

    </style>

</head>
<body>
    <div class="container">
        <form id="otpVerificationForm" method="post" action="otp_verification.php">
            <h2>OTP Verification</h2>

            <p>Verification for: <?php echo $_SESSION['username']; ?></p>

            <!-- Entered OTP -->
            <label for="enteredOTP">Enter OTP:</label>
            <input type="text" id="enteredOTP" name="enteredOTP" required>

            <!-- Submit button -->
            <button type="submit">Verify OTP</button>
        </form>

        <!-- Display the OTP verification message -->
        <p class="otp-verification-message"><?php echo $otpVerificationMessage; ?></p>
    </div>
</body>
</html>
