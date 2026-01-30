<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "taskworkflow";

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>


<!-- $host = "sql202.infinityfree.com";
$username = "if0_39429923";
$password = "sinopal1";
$dbname = "if0_39429923_dbtaskworkflow"; -->