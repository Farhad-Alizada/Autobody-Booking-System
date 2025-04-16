<?php
$conn = new mysqli("localhost", "root", "", "AutoBodyBooking");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "✅ Connected to AutoBodyBooking!";
?>
