<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "thn_cyberassignment2";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

require 'mailer.php';

// Define a variable to store forgot password messages
$forgotPasswordMessage = "";

// Check if the reset password link is sent to the email
if (isset($_SESSION['reset_email_sent']) && $_SESSION['reset_email_sent']) {
    // Show an alert message
    echo '<script>alert("Password reset link sent to your email. Please check your inbox.");</script>';

    // Reset the session variable
    $_SESSION['reset_email_sent'] = false;
}

// Forgot password form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userEmail = $_POST['userEmail'];

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generate a unique reset token
        $resetToken = bin2hex(random_bytes(32));

        // Store the reset token in the database
        $stmt = $conn->prepare("UPDATE users SET reset_token = ? WHERE username = ?");
        $stmt->bind_param("ss", $resetToken, $userEmail);
        $stmt->execute();

        // Check if the update query was successful
        if ($stmt->affected_rows > 0) {
            // Send a reset email with a link containing the reset token
            $resetLink = "http://localhost/cyberlogin/reset_password.php?token=" . urlencode($resetToken);
            $subject = "Password Reset";
            $message = "Click the following link to reset your password: $resetLink";

            // Call the sendEmail function from mailer.php
            $result = sendEmail($userEmail, $subject, $message);

            if ($result === true) {
                // Set the session variable for alert message
                $_SESSION['reset_email_sent'] = true;
                // Redirect to the same page to show the alert
                header("Location: forgot_password.php");
                exit();
            } else {
                $forgotPasswordMessage = "Failed to send reset email. Error: $result";
            }
        } else {
            $forgotPasswordMessage = "Failed to update reset token in the database.";
        }
    } else {
        $forgotPasswordMessage = "Email not found. Please check your email.";
    }

    $stmt->close();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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
        <form id="forgotPasswordForm" method="post" action="forgot_password.php">
            <h2>Forgot Password</h2>

            <!-- User Email -->
            <label for="userEmail">Email:</label>
            <input type="email" id="userEmail" name="userEmail" required>

            <!-- Submit button -->
            <button type="submit">Reset Password</button>
        </form>
        <p><a href="login.php">Back to Login</a></p>

        <!-- Display the forgot password message -->
        <p class="forgot-password-message"><?php echo $forgotPasswordMessage; ?></p>
    </div>
</body>
</html>
