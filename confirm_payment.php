<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usermail']) || !isset($_SESSION['pending_booking'])) {
    header("location: home.php");
    exit();
}

$booking = $_SESSION['pending_booking'];
$payment_method = isset($_POST['payment_method']) ? mysqli_real_escape_string($conn, $_POST['payment_method']) : (isset($_GET['payment_method']) ? mysqli_real_escape_string($conn, $_GET['payment_method']) : 'cash');

// Check if payment_method column exists in roombook table
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM roombook LIKE 'payment_method'");
if(mysqli_num_rows($check_column) == 0) {
    mysqli_query($conn, "ALTER TABLE roombook ADD COLUMN payment_method VARCHAR(50) DEFAULT 'cash'");
}

// Check if room_price column exists
$check_price_column = mysqli_query($conn, "SHOW COLUMNS FROM roombook LIKE 'room_price'");
if(mysqli_num_rows($check_price_column) == 0) {
    mysqli_query($conn, "ALTER TABLE roombook ADD COLUMN room_price DECIMAL(10,2) DEFAULT 0");
}

$room_price = isset($booking['price']) ? floatval($booking['price']) : 0;

// Insert booking into database
$sql = "INSERT INTO roombook (Name, Email, Phone, RoomType, Bed, cin, cout, stat, nodays, NoofRoom, payment_method, room_price) 
        VALUES ('{$booking['Name']}', '{$booking['Email']}', '{$booking['Phone']}', '{$booking['RoomType']}', 
        '{$booking['Bed']}', '{$booking['cin']}', '{$booking['cout']}', 'NotConfirm', '{$booking['nodays']}', 1, '$payment_method', '$room_price')";

if (mysqli_query($conn, $sql)) {
    $bookingId = mysqli_insert_id($conn);
    
    $_SESSION['booked'] = [
        'id' => $bookingId,
        'name' => $booking['Name'],
        'email' => $booking['Email'],
        'phone' => $booking['Phone'],
        'roomType' => $booking['RoomType'],
        'bed' => $booking['Bed'],
        'checkIn' => $booking['cin'],
        'checkOut' => $booking['cout'],
        'nights' => $booking['nodays'],
        'status' => 'Pending',
        'payment_method' => $payment_method,
        'room_price' => $room_price
    ];
    
    unset($_SESSION['pending_booking']);
    header("location: booked.php?id=$bookingId");
    exit();
} else {
    $_SESSION['booking_error'] = "Booking failed. Please try again.";
    header("location: home.php");
    exit();
}
?>
