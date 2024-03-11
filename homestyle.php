<?php
header("Content-type: text/css");

?>

body {
    margin: 0;
}

.menu {
    background-color: rgba(0, 0, 0, 0.7);
    position: fixed;
    width: 100%;
    z-index: 1;
}

.menu ul {
    padding: 0;
    margin: 0;
    list-style: none;
}

.menu li {
    float: left;
}

.menu a {
    display: block;
    color: white;
    text-align: center;
    padding: 14px 16px;
    text-decoration: none;
}

.menu a:hover {
    background-color: #ddd;
    color: black;
}

.full-page-container {
    position: relative;
    background-image: url('image/4khomebg.jpg');
    background-size: cover;
    background-position: center;
    height: 100vh;
}

.container {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    max-width: 800px;
    padding: 20px;
    color: white;
}
