<?php
header("Content-type: text/css; charset: UTF-8");
?>

body {
    font-family: Arial, sans-serif;
    margin: 0;
}

.container {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    max-width: 400px;
    padding: 20px;
    color: white;
    text-align: center;
    backdrop-filter: blur(6px);
    border-radius: 10px;
    background-color: rgba(43, 77, 116, 0.7); 
}

.full-page-container {
    position: relative;
    background-image: url('image/4kbgimage.jpg');
    background-size: cover;
    background-position: center;
    height: 100vh;
}

form {
    display: flex;
    flex-direction: column;
    text-align: left;
    font-weight: bold;
}

h2 {
    text-align: center;
}

label {
    margin-bottom: 5px;
    color: #ffffff;
    display: block;
}

input {
    padding: 8px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 3px;
}

input#registerPassword {
    width: 220px;
}

input#confirmPassword {
    width: 220px;
}

button {
    padding: 10px;
    background-color: #1d656b;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
}

button:hover {
    background-color: #1e7f86;
}

p {
    font-weight: bold;
}

a {
    color: #0a0a0a;
}
