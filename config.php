<?php
/**
 * Database Configuration File
 * W.I.Y Laundry Shop - Payroll Management System
 */

// Database credentials
$servername = "localhost";
$db_user = "root";
$db_password = "";
$database = "laundry_shop_db";

// Create connection
$conn = new mysqli($servername, $db_user, $db_password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

?>
