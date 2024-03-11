<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "thn_cyberassignment2";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define a variable to store reset password messages
$resetPasswordMessage = "";

// Check if the reset token is provided in the URL
if (isset($_GET['token'])) {
    $resetToken = $_GET['token'];

    // Verify the reset token in the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $resetToken);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Token is valid, allow the user to reset the password
        $_SESSION['reset_token'] = $resetToken;
    } else {
        // Invalid token, inform the user
        $resetPasswordMessage = "Invalid reset token. Please try again or request a new reset link.";

        // Debug: Print the received token and the database query result
        var_dump('Received Token:', $resetToken);
        var_dump('Database Query Result:', $result->fetch_assoc());  // Assuming one row is expected
    }

    $stmt->close();
} else {
    // No reset token provided, redirect to the login page
    header("Location: login.php");
    exit();
}

// Reset password form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Validate the new password against the specified standards
    $isValidPassword = validatePassword($newPassword);

    if (!$isValidPassword) {
        $resetPasswordMessage = "New password does not meet the required standards. Please try again.";
    } elseif ($newPassword !== $confirmPassword) {
        $resetPasswordMessage = "New password and confirm password do not match. Please try again.";
    } else {
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the password in the database
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE reset_token = ?");
        $stmt->bind_param("ss", $hashedPassword, $_SESSION['reset_token']);
        $stmt->execute();

        // Check if the update query was successful
        if ($stmt->affected_rows > 0) {
            // Password reset successful
            $resetPasswordMessage = "Password reset successful. You can now <a href='login.php'>login</a> with your new password.";
        } else {
            // No rows affected, indicate an issue with the update
            $resetPasswordMessage = "Password reset failed. Please try again.";

            // Debug: Print the affected rows just to help identify the issue
            var_dump('Affected Rows:', $stmt->affected_rows);
        }

        // Remove reset token from session
        unset($_SESSION['reset_token']);

        $stmt->close();
    }
}

// Close the database connection
$conn->close();

// Function to validate the new password against the specified standards
function validatePassword($password) {
    // Define patterns for special characters, uppercase letters, lowercase letters, and numbers
    $specialChars = '/[!@#$%^&*]/';
    $uppercaseChars = '/[A-Z]/';
    $lowercaseChars = '/[a-z]/';
    $numericChars = '/[0-9]/';

    // Check if the password includes at least one of each required element
    $hasSpecialChars = preg_match($specialChars, $password);
    $hasUppercaseChars = preg_match($uppercaseChars, $password);
    $hasLowercaseChars = preg_match($lowercaseChars, $password);
    $hasNumericChars = preg_match($numericChars, $password);

    // Check for minimum length
    $hasMinLength = strlen($password) >= 9;

    // Check if the password includes at least one of each required element
    return $hasSpecialChars && $hasUppercaseChars && $hasLowercaseChars && $hasNumericChars && $hasMinLength;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="styles.php">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: url('image/4kbgimage.jpg') center center fixed; 
            background-size: cover;
            font-family: Arial, sans-serif; 
        }
        .container {
            max-height: 400px; 
            overflow: hidden;
            width: 300px;
        }

        #resetPasswordForm {
            max-width: 400px; 
            margin: auto; 
        }

        #resetPasswordForm label {
            display: block;
            margin-top: 10px;
        }

        #resetPasswordForm input {
            width: 91%; 
            box-sizing: border-box; 
            margin-bottom: 10px; 
        }

        .reset-password-message {
            max-height: 100px; 
            margin-top: 10px; 
            overflow-y: auto;
            word-wrap: break-word;
            padding: 10px;
            border: 1px solid #ccc;
        }
    </style>


</head>
<body>
    <div class="container">
        <form id="resetPasswordForm" method="post" action="reset_password.php?token=<?php echo $resetToken; ?>" onsubmit="return validateForm()">
            <h2>Reset Password</h2>

            <!-- New Password -->
            <label for="newPassword">New Password:</label>
            <div class="input-container">
                <input type="password" id="newPassword" name="newPassword" required oninput="checkPasswordStrength()">
                <i class="far fa-eye" id="new-password-eye" onclick="togglePasswordVisibility('newPassword', 'new-password-eye')"></i>
            </div>
            <!-- Password Strength Indicator -->
            <div id="password-strength">Password Strength: 0%</div>

            <!-- Confirm New Password -->
            <label for="confirmPassword">Confirm New Password:</label>
            <div class="input-container">
                <input type="password" id="confirmPassword" name="confirmPassword" required>
                <i class="far fa-eye" id="confirm-password-eye" onclick="togglePasswordVisibility('confirmPassword', 'confirm-password-eye')"></i>
            </div>

            <!-- Submit button -->
            <button type="submit">Reset Password</button>
        </form>
        <p><a href="login.php">Back to Login</a></p>

        <!-- Display the reset password message -->
        <p class="reset-password-message"><?php echo $resetPasswordMessage; ?></p>

        <script>
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

            // Function to check password strength
            function checkPasswordStrength() {
                const password = document.getElementById('newPassword').value;
                const strengthIndicator = document.getElementById('password-strength');

                // Define patterns for special characters, uppercase letters, lowercase letters, and numbers
                const specialChars = /[!@#$%^&*]/;
                const uppercaseChars = /[A-Z]/;
                const lowercaseChars = /[a-z]/;
                const numericChars = /[0-9]/;

                // Check if the password includes at least one of each required element
                const hasSpecialChars = specialChars.test(password);
                const hasUppercaseChars = uppercaseChars.test(password);
                const hasLowercaseChars = lowercaseChars.test(password);
                const hasNumericChars = numericChars.test(password);

                // Check for minimum length
                const hasMinLength = password.length >= 9;

                // Calculate password strength
                const strength = (hasSpecialChars + hasUppercaseChars + hasLowercaseChars + hasNumericChars + hasMinLength) * 20;

                // Update the strength indicator
                strengthIndicator.innerHTML = `Password Strength: ${strength}%`;
            }

            // Function to validate form on submission
            function validateForm() {
                
                // Perform password strength check
                const strength = (document.getElementById('password-strength').innerText.match(/\d+/) || [0])[0];
                if (strength < 80) {
                    alert('Password is not strong enough. Please follow the password requirements.');
                    return false;
                }

                return true;
            }
        </script>
    </div>
</body>
</html>
