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

// Define a variable to store messages
$message = "";

// reCAPTCHA validation
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recaptchaSecretKey = '6LdCrVEpAAAAAJ-SgCadf0AV7P6zjR_YuXeZ1wYB';
    $recaptchaResponse = $_POST['g-recaptcha-response'];

    $recaptchaUrl = "https://www.google.com/recaptcha/api/siteverify?secret={$recaptchaSecretKey}&response={$recaptchaResponse}";
    $recaptchaData = json_decode(file_get_contents($recaptchaUrl));

    if (!$recaptchaData->success) {
        $message = "reCAPTCHA verification failed. Please check the reCAPTCHA box.";
    }
}

// Registration form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($message)) {
    $username = $_POST['registerUsername'];
    $password = $_POST['registerPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Check if the username already exists
    $stmtCheckUser = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmtCheckUser->bind_param("s", $username);
    $stmtCheckUser->execute();
    $resultCheckUser = $stmtCheckUser->get_result();

    if ($resultCheckUser->num_rows > 0) {
        $message = "Account already exists.";
    } else {
        // Check if passwords match
        if ($password !== $confirmPassword) {
            $message = "Passwords do not match. Please confirm your password.";
        } else {
            // Check password strength
            $strength = checkPasswordStrength($password);

            if ($strength === 'weak') {
                $message = "Password is weak. Please choose a stronger password.";
            } elseif ($strength === 'moderate') {
                $message = "Password is moderate. Please choose a stronger password.";
            } elseif ($strength === 'strong' && $recaptchaData->success) {
                // Perform registration logic (insert into database, hash password, etc.)
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmtInsertUser = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");

                if ($stmtInsertUser) {
                    $stmtInsertUser->bind_param("ss", $username, $hashedPassword);

                    if ($stmtInsertUser->execute()) {
                        $message = "Registration successful!";
                    } else {
                        $message = "Error: " . $stmtInsertUser->error;
                    }
                } else {
                    $message = "Error preparing statement.";
                }

                $stmtInsertUser->close();
            }
    
            $stmtCheckUser->close();
        }
    }
}


// Close the database connection
$conn->close();

// Function to check the strength of a password
function checkPasswordStrength($password) {
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
    if ($hasSpecialChars && $hasUppercaseChars && $hasLowercaseChars && $hasNumericChars && $hasMinLength) {
        return 'strong';
    } else if (($hasUppercaseChars || $hasNumericChars) && $hasMinLength) {
        return 'moderate';
    } else {
        return 'weak';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Page</title>
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
            display: none; /* Initially hide the notification */
            z-index: 1;
        }
    </style>
</head>
<body>
    <!-- Full-Page Image Container -->
    <div class="full-page-container">
        <!-- Display the notification in the top center -->
        <div class="notification" id="notification">
            <p><?php echo $message; ?></p>
        </div>

        <div class="container">
            <form id="registrationForm" method="post" action="registration.php" onsubmit="return validateRegistration();">
                <h2>Register</h2>
                
                <!-- Username -->
                <label for="registerUsername">Email:</label>
                <input type="text" id="registerUsername" name="registerUsername" required>
                
                
                <!-- Password -->
                <label for="registerPassword" title="Password must be at least 9 characters, include at least one uppercase letter, one number, and one special character.">
                    Password:
                </label>
                <div class="input-container">
                    <input type="password" id="registerPassword" name="registerPassword" 
                        oninput="evaluatePasswordStrength()" required 
                        title="Password must be at least 9 characters, include at least one uppercase letter, one number, and one special character.">
                    <i class="far fa-eye" id="password-eye" onclick="togglePasswordVisibility('registerPassword', 'password-eye')"></i>
                </div>

               
                <progress value="0" max="4" id="password-strength-meter"></progress>
                <p id="password-strength-text"></p>

                <!-- Confirm Password -->
                <label for="confirmPassword">Confirm Password:</label>
                <div class="input-container">
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                    <i class="far fa-eye" id="confirm-eye" onclick="togglePasswordVisibility('confirmPassword', 'confirm-eye')"></i>
                </div>

                <!-- reCAPTCHA -->
                <div class="g-recaptcha" data-sitekey="6LdCrVEpAAAAAGKfqbMuq62X0tg11m9ASBQSb1Zz"></div>

                <button type="submit">Register</button>
            </form>
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js"></script>
    <script>
        // Display the notification if there is a message
        window.onload = function () {
            var message = "<?php echo $message; ?>";
            if (message.trim() !== "") {
                displayNotification(message);
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

        // Function to check the strength of a password
        function checkPasswordStrength(password) {
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
          
            // Check if the password includes at least one of each required element
            if (hasSpecialChars && hasUppercaseChars && hasLowercaseChars && hasNumericChars && hasMinLength) {
              return 'strong';
            } else if ((hasUppercaseChars || hasNumericChars) && hasMinLength) {
              return 'moderate';
            } else {
              return 'weak';
            }
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
        
        // Function to validate registration
        function validateRegistration() {
            
            var username = document.getElementById('registerUsername').value;
            if (username.trim() === "") {
                alert("Please enter a username.");
                return false;
            }

            // Check if the username contains "@gmail.com"
            if (username.indexOf("@gmail.com") === -1) {
                alert("Email must contain '@gmail.com'.");
                return false;
            }


            // Return true if validation succeeds, false otherwise
            return true;
        }

        // Function to evaluate password strength
        function evaluatePasswordStrength() {
            const password = document.getElementById('registerPassword').value;
            const passwordMeter = document.getElementById('password-strength-meter');
            const passwordText = document.getElementById('password-strength-text');
          
            // Check the strength of the password
            const strength = checkPasswordStrength(password);
          
            // Set the progress bar value and provide feedback on the password strength
            if (strength === 'strong') {
              passwordMeter.value = 4;
              passwordText.textContent = 'Password: Strong';
            } else if (strength === 'moderate') {
              passwordMeter.value = 2;
              passwordText.textContent = 'Password: Moderate';
            } else {
              passwordMeter.value = 0;
              passwordText.textContent = 'Password: Weak';
            }
        }
    </script>
</body>
</html>
