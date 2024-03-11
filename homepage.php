<?php
session_start();

// Check if the user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Check if the login was successful (session variable set)
if (isset($_SESSION['login_success']) && $_SESSION['login_success'] === true) {
    // Clear the session variable
    unset($_SESSION['login_success']);

} 

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link rel="stylesheet" href="homestyle.php">

    <style>
        
        .poem-container {
            float: right;
            width: 40%; 
            padding: 20px;
            margin-top: 60px; 
            text-align: justify;
            margin-right: 20px;
        }

       
        .poem-text {
            font-family: 'Arial', sans-serif;
            font-size: 16px;
            line-height: 1.5;
            color: whitesmoke;
        }

        
        .regards-container {
            clear: both; 
            text-align: right; 
            margin-top: 20px; 
            padding: 20px;
            margin-right: 20px;
        }

        
        .regards-text {
            font-family: 'Arial', sans-serif;
            font-size: 16px;
            line-height: 1.5;
            color: whitesmoke; 
        }

    </style>

</head>
<body>
    <!-- Menu Bar -->
    <div class="menu">
        <ul>
            <li><a href="#home">Home</a></li>
            <li><a href="#about">About Us</a></li>
            <li><a href="#contact">Contact Us</a></li>
            <li><a href="login.php">Logout</a></li>
        </ul>
    </div>

    <!-- Full-Page Image Container -->
    <div class="full-page-container">
        

        <!-- Login Form Container -->
        <div class="container">
            <form id="loginForm">
                
            </form>
        </div>

        <!-- Poem Container -->
        <div class="poem-container">
            <div class="poem-text">
                
                In the halls of learning, where wisdom flows,
                A heartfelt tribute to the ones who guide and compose.
                Dear teachers at our university, we say with glee,
                Thank you for being the pillars of our academic journey.

                In classrooms bright, you light the spark,
                Each lesson learned leaves a lasting mark.
                With patience and care, you help us see,
                The wonders of knowledge, the roots of a tree.

                Oh, professors kind, with knowledge vast,
                You make learning an adventure, a journey so vast.
                Through the semesters, you lead the way,
                Nurturing minds, helping us seize the day.

                In labs and libraries, where curiosity thrives,
                You create an atmosphere where learning survives.
                Champions of wisdom, mentors so true,
                We're grateful for the guidance you always imbue.

                For every question, you patiently address,
                For every challenge, you help us progress.
                To our teachers, mentors, friends so dear,
                Our gratitude flows, loud and clear.

                In the world of academia, you shine so bright,
                A beacon of knowledge, guiding us right.
                For being our mentors, our guiding star,
                Thank you, dear teachers, near and far.

            </div>
        </div>

        <div class="regards-container">
            <div class="regards-text">
                <p>Best regards,</p>
                <p>Thar Htut Nyan</p>
            </div>
        </div>


    </div>

</body>
</html>
