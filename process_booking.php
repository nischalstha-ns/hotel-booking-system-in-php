<?php
session_start();
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['usermail']) || empty($_SESSION['usermail'])) {
    header("location: index.php");
    exit();
}

// Check if form was submitted
if (isset($_POST['guestdetailsubmit'])) {
    // Get form data
    $Name = mysqli_real_escape_string($conn, $_POST['Name']);
    $Email = mysqli_real_escape_string($conn, $_SESSION['usermail']); // Use email from session
    $Phone = mysqli_real_escape_string($conn, $_POST['Phone']);
    $RoomType = mysqli_real_escape_string($conn, $_POST['RoomType']);
    $Bed = mysqli_real_escape_string($conn, $_POST['Bed']);
    $NoofRoom = 1; // Fixed to 1 room
    $cin = mysqli_real_escape_string($conn, $_POST['cin']);
    $cout = mysqli_real_escape_string($conn, $_POST['cout']);
    $roomId = isset($_POST['roomId']) ? mysqli_real_escape_string($conn, $_POST['roomId']) : '';
    $roomNumber = isset($_POST['roomNumber']) ? mysqli_real_escape_string($conn, $_POST['roomNumber']) : '';
    
    // Validate dates
    $cinDate = new DateTime($cin);
    $coutDate = new DateTime($cout);
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    
    if ($cinDate < $today) {
        $_SESSION['booking_error'] = "Check-in date cannot be in the past.";
        header("location: home.php");
        exit();
    }
    
    if ($coutDate <= $cinDate) {
        $_SESSION['booking_error'] = "Check-out date must be after check-in date.";
        header("location: home.php");
        exit();
    }
    
    // Calculate number of days
    $interval = $cinDate->diff($coutDate);
    $nodays = $interval->days;
    
    // Check room availability
    $availability_sql = "SELECT COUNT(*) as booked_count FROM roombook 
                        WHERE RoomType = '$RoomType' 
                        AND ((cin <= '$cin' AND cout >= '$cin') 
                        OR (cin <= '$cout' AND cout >= '$cout') 
                        OR (cin >= '$cin' AND cout <= '$cout'))
                        AND stat = 'Confirm'";
    
    $availability_result = mysqli_query($conn, $availability_sql);
    $availability = mysqli_fetch_assoc($availability_result);
    
    // Get total rooms of this type
    $room_count_sql = "SELECT COUNT(*) as total_rooms FROM room WHERE type = '$RoomType'";
    $room_count_result = mysqli_query($conn, $room_count_sql);
    $room_count = mysqli_fetch_assoc($room_count_result);
    
    $available_rooms = $room_count['total_rooms'] - $availability['booked_count'];
    
    if ($available_rooms < 1) {
        $_SESSION['booking_error'] = "Sorry, this room type is not available for the selected dates.";
        header("location: home.php");
        exit();
    }
    
    // Get the structure of the roombook table to check required columns
    $table_structure = mysqli_query($conn, "DESCRIBE roombook");
    $columns = [];
    while ($col = mysqli_fetch_assoc($table_structure)) {
        $columns[$col['Field']] = $col;
    }
    
    // Check for required columns and add them if missing
    $required_columns = [
        'room_no' => "VARCHAR(10) DEFAULT NULL",
        'Phone' => "VARCHAR(30) DEFAULT NULL",
        'Country' => "VARCHAR(30) DEFAULT 'Not Specified'",
        'NoofRoom' => "INT(11) DEFAULT 1",
        'Meal' => "VARCHAR(30) DEFAULT 'Room only'"
    ];
    
    foreach ($required_columns as $column => $definition) {
        if (!isset($columns[$column])) {
            $add_column = mysqli_query($conn, "ALTER TABLE roombook ADD COLUMN $column $definition");
            if (!$add_column) {
                $_SESSION['booking_error'] = "Database error while adding column $column: " . mysqli_error($conn);
                header("location: home.php");
                exit();
            }
        }
    }
    
    // Get room price - use roomId if available, otherwise get by type
    if (!empty($roomId)) {
        $price_sql = "SELECT price FROM room WHERE id = '$roomId' LIMIT 1";
    } else {
        $price_sql = "SELECT price FROM room WHERE type = '$RoomType' LIMIT 1";
    }
    $price_result = mysqli_query($conn, $price_sql);
    $price_row = mysqli_fetch_assoc($price_result);
    $room_price = ($price_row && isset($price_row['price'])) ? floatval($price_row['price']) : 0;
    
    // Store booking data in session for payment page
    $_SESSION['pending_booking'] = [
        'Name' => $Name,
        'Email' => $Email,
        'Phone' => $Phone,
        'RoomType' => $RoomType,
        'Bed' => $Bed,
        'cin' => $cin,
        'cout' => $cout,
        'nodays' => $nodays,
        'price' => $room_price
    ];
    
    // Redirect to payment options page
    header("location: payment_options.php");
    exit();
} else {
    // If form was not submitted, redirect to home page
    header("location: home.php");
    exit();
}
?>

