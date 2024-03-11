<?php
session_start();
require 'mailer.php';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "thn_cyberassignment2";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define a variable to store login messages
$loginMessage = "";

// Login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $loginUsername = trim($_POST['loginUsername']);
    $loginPassword = $_POST['loginPassword'];

    // Check reCAPTCHA validation
    $recaptchaSecretKey = '6LdCrVEpAAAAAJ-SgCadf0AV7P6zjR_YuXeZ1wYB';
    $recaptchaResponse = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : null;

    $recaptchaData = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptchaSecretKey}&response={$recaptchaResponse}"));

    if (!$recaptchaData || !$recaptchaData->success) {
        $loginMessage = "reCAPTCHA verification failed. Please check the reCAPTCHA box.";
    } else {
        // Perform login logic (check username in the database)
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $loginUsername);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Verify the password
            if (password_verify($loginPassword, $row['password'])) {
                $_SESSION['username'] = $loginUsername;
                $_SESSION['login_success'] = true;

                // Generate OTP and store in the session
                $otp = rand(100000, 999999);
                $_SESSION['otp'] = $otp;
                $_SESSION['otp_timestamp'] = time(); // Timestamp for OTP

                // Send OTP via email
                sendOTP($loginUsername, $otp);

                // Redirect to OTP verification page
                header("Location: otp_verification.php");
                exit();
            } else {
                $loginMessage = "Incorrect password. Please try again.";
            }
        } else {
            $loginMessage = "Email not found. Please check your email.";
        }

        $stmt->close();
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="styles.php">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <style>
        
        .notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            display: <?= $loginMessage ? 'block' : 'none'; ?>;
            z-index: 1;
        }

        #loginPassword {
            width: 270px;
        }
        
    </style>
</head>
<body>
    <!-- Full-Page Image Container -->
    <div class="full-page-container">
        <!-- Display the notification in the top center -->
        <div class="notification" id="notification">
            <p><?= $loginMessage; ?></p>
        </div>

        <div class="container">
            <form id="loginForm" method="post" action="login.php" onsubmit="return validateLogin();">
                <h2>Login</h2>
                
                <!-- Username -->
                <label for="loginUsername">Email:</label>
                <input type="text" id="loginUsername" name="loginUsername" required>
                
                <!-- Password -->
                <div class="input-container">
                    <label for="loginPassword">Password:</label>
                    <input type="password" id="loginPassword" name="loginPassword" required>
                    <i class="far fa-eye" id="password-eye" onclick="togglePasswordVisibility('loginPassword', 'password-eye')"></i>
                </div>

                <!-- reCAPTCHA -->
                <div class="g-recaptcha" data-sitekey="6LdCrVEpAAAAAGKfqbMuq62X0tg11m9ASBQSb1Zz"></div>

                <button type="submit">Login</button>
            </form>

            <p><a href="forgot_password.php">Forgot password?</a></p>

            <p>Don't have an account? <a href="registration.php">Register here</a></p>
        </div>
    </div>

    <script>
        // Display the notification if there is a message
        window.onload = function () {
            var loginMessage = "<?= $loginMessage; ?>";
            if (loginMessage.trim() !== "") {
                displayNotification(loginMessage);
            }
        };

        // Function to display the notification
        function displayNotification(message) {
            var notification = document.getElementById('notification');
            notification.innerHTML = "<p>" + message + "</p>";
            notification.style.display = 'block';

            // Automatically hide the notification after 5 seconds
            setTimeout(function () {
                notification.style.display = 'none';
            }, 5000);
        }

        // Function to toggle password visibility
        function togglePasswordVisibility(passwordFieldId, eyeIconId) {
            const passwordField = document.getElementById(passwordFieldId);
            const eyeIcon = document.getElementById(eyeIconId);
          
            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.classList.remove("far", "fa-eye");
                eyeIcon.classList.add("far", "fa-eye-slash");
            } else {
                passwordField.type = "password";
                eyeIcon.classList.remove("far", "fa-eye-slash");
                eyeIcon.classList.add("far", "fa-eye");
            }
        }
        
        // Function to validate login
        function validateLogin() {
            
            var username = document.getElementById('loginUsername').value;
            if (username.trim() === "") {
                alert("Please enter an email.");
                return false;
            }


            // Return true if validation succeeds, false otherwise
            return true;
        }
    </script>
</body>
</html>
