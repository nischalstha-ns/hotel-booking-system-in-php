<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usermail']) || empty($_SESSION['usermail'])) {
    header("location: index.php");
    exit();
}

$usermail = $_SESSION['usermail'];
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($booking_id) {
    $sql = "SELECT rb.*, COALESCE(rb.room_price, r.price, 0) as final_price FROM roombook rb LEFT JOIN room r ON rb.RoomType = r.type WHERE rb.id = '$booking_id' AND rb.Email = '$usermail' LIMIT 1";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        $status = 'Pending';
        if ($row['stat'] == 'Confirm') {
            $status = 'Confirmed';
        } elseif ($row['stat'] == 'Cancel') {
            $status = 'Cancelled';
        }
        
        $room_price = floatval($row['final_price']);
        
        $_SESSION['booked'] = [
            'id' => $row['id'],
            'name' => $row['Name'],
            'email' => $row['Email'],
            'phone' => isset($row['Phone']) ? $row['Phone'] : '',
            'roomType' => $row['RoomType'],
            'bed' => $row['Bed'],
            'checkIn' => $row['cin'],
            'checkOut' => $row['cout'],
            'nights' => $row['nodays'],
            'status' => $status,
            'roomNumber' => isset($row['room_no']) ? $row['room_no'] : '',
            'payment_method' => isset($row['payment_method']) ? $row['payment_method'] : 'cash',
            'room_price' => $room_price,
        ];
    } elseif (isset($_SESSION['booked']) && isset($_SESSION['booked']['id']) && $_SESSION['booked']['id'] == $booking_id) {
    } else {
        unset($_SESSION['booked']);
        $_SESSION['booking_error'] = "Booking not found or does not belong to your account.";
        header("location: home.php");
        exit();
    }
} else {
    $recent_booking_sql = "SELECT * FROM roombook WHERE Email = '$usermail' ORDER BY id DESC LIMIT 1";
    $recent_booking_result = mysqli_query($conn, $recent_booking_sql);
    
    if ($recent_booking_result && mysqli_num_rows($recent_booking_result) > 0) {
        $recent_booking = mysqli_fetch_assoc($recent_booking_result);
        $booking_id = $recent_booking['id'];
        header("location: booked.php?id=$booking_id");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - Juju Homestay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Merienda:wght@400;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-hover: #3a56d4;
            --primary-light: rgba(67, 97, 238, 0.1);
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gray-100: #f1f5f9;
            --gray-300: #cbd5e1;
            --gray-500: #64748b;
            --dark: #1e293b;
        }
        body { font-family: 'Poppins', sans-serif; background-color: var(--gray-100); min-height: 100vh; display: flex; flex-direction: column; }
        .booking-card { background: white; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); margin: 2rem 0; }
        .booking-header { background: var(--primary); color: white; padding: 1.5rem; position: relative; }
        .booking-header::after { content: ''; position: absolute; bottom: -20px; left: 0; right: 0; height: 20px; background: linear-gradient(135deg, transparent 49%, var(--primary) 50%); background-size: 20px 20px; }
        .booking-body { padding: 2rem; }
        .booking-info-item { display: flex; margin-bottom: 1rem; align-items: center; }
        .booking-info-icon { width: 40px; height: 40px; background: var(--primary-light); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary); margin-right: 1rem; }
        .booking-info-label { font-size: 0.85rem; color: var(--gray-500); margin-bottom: 0.25rem; }
        .booking-info-value { font-weight: 500; color: var(--dark); }
        .booking-dates { display: flex; background: var(--gray-100); border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem; }
        .booking-date { flex: 1; text-align: center; padding: 0.5rem; }
        .booking-date:first-child { border-right: 1px dashed var(--gray-300); }
        .booking-status { display: inline-block; padding: 0.5rem 1rem; border-radius: 50px; font-weight: 500; font-size: 0.875rem; margin-bottom: 1.5rem; }
        .booking-status.pending { background: rgba(245,158,11,0.1); color: var(--warning); }
        .booking-status.confirmed { background: rgba(16,185,129,0.1); color: var(--success); }
        .btn-back { background: var(--primary); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 500; text-decoration: none; display: inline-block; }
        .navbar { background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .navbar-brand { font-family: 'Merienda', cursive; font-weight: 700; color: var(--primary); }
        .footer { background: var(--dark); color: white; padding: 1rem 0; margin-top: auto; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="home.php">Juju Homestay</a>
            <div class="ms-auto">
                <a href="home.php" class="btn btn-outline-primary btn-sm"><i class="fas fa-home"></i> Home</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if (isset($_SESSION['booked'])): 
            $b = $_SESSION['booked'];
            $status = isset($b['status']) ? htmlspecialchars($b['status']) : 'Pending';
            $statusClass = $status == 'Confirmed' ? 'confirmed' : 'pending';
            
            $room_price = isset($b['room_price']) && $b['room_price'] > 0 ? floatval($b['room_price']) : 0;
            if ($room_price == 0) {
                $price_sql = "SELECT price FROM room WHERE type = '{$b['roomType']}' LIMIT 1";
                $price_result = mysqli_query($conn, $price_sql);
                if ($price_result && $price_row = mysqli_fetch_assoc($price_result)) {
                    $room_price = floatval($price_row['price']);
                }
            }
            
            $total_amount = $room_price * $b['nights'];
            $payment_method = isset($b['payment_method']) ? $b['payment_method'] : 'cash';
            $paid_amount = ($payment_method == 'esewa') ? $total_amount : 0;
            $due_amount = $total_amount - $paid_amount;
        ?>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="booking-card">
                    <div class="booking-header">
                        <h2 class="text-center mb-0">Booking Confirmation</h2>
                    </div>
                    <div class="booking-body">
                        <div class="text-center mb-4">
                            <span class="booking-status <?php echo $statusClass; ?>">
                                <i class="fas fa-<?php echo $status == 'Confirmed' ? 'check-circle' : 'clock'; ?>"></i> <?php echo $status; ?>
                            </span>
                            <div class="mt-2 small text-muted">Booking ID: #<?php echo $b['id']; ?></div>
                        </div>
                        
                        <div class="booking-dates">
                            <div class="booking-date">
                                <div class="booking-date-label">CHECK-IN</div>
                                <div class="booking-date-value"><?php echo htmlspecialchars($b['checkIn']); ?></div>
                            </div>
                            <div class="booking-date">
                                <div class="booking-date-label">CHECK-OUT</div>
                                <div class="booking-date-value"><?php echo htmlspecialchars($b['checkOut']); ?></div>
                            </div>
                        </div>
                        
                        <div class="booking-info">
                            <div class="booking-info-item">
                                <div class="booking-info-icon"><i class="fas fa-user"></i></div>
                                <div class="booking-info-content">
                                    <div class="booking-info-label">GUEST NAME</div>
                                    <div class="booking-info-value"><?php echo htmlspecialchars($b['name']); ?></div>
                                </div>
                            </div>
                            <div class="booking-info-item">
                                <div class="booking-info-icon"><i class="fas fa-envelope"></i></div>
                                <div class="booking-info-content">
                                    <div class="booking-info-label">EMAIL</div>
                                    <div class="booking-info-value"><?php echo htmlspecialchars($b['email']); ?></div>
                                </div>
                            </div>
                            <div class="booking-info-item">
                                <div class="booking-info-icon"><i class="fas fa-phone"></i></div>
                                <div class="booking-info-content">
                                    <div class="booking-info-label">PHONE</div>
                                    <div class="booking-info-value"><?php echo htmlspecialchars($b['phone']); ?></div>
                                </div>
                            </div>
                            <div class="booking-info-item">
                                <div class="booking-info-icon"><i class="fas fa-bed"></i></div>
                                <div class="booking-info-content">
                                    <div class="booking-info-label">ROOM TYPE</div>
                                    <div class="booking-info-value"><?php echo htmlspecialchars($b['roomType']); ?></div>
                                </div>
                            </div>
                            <div class="booking-info-item">
                                <div class="booking-info-icon"><i class="fas fa-couch"></i></div>
                                <div class="booking-info-content">
                                    <div class="booking-info-label">BED TYPE</div>
                                    <div class="booking-info-value"><?php echo htmlspecialchars($b['bed']); ?></div>
                                </div>
                            </div>
                            <div class="booking-info-item">
                                <div class="booking-info-icon"><i class="fas fa-door-open"></i></div>
                                <div class="booking-info-content">
                                    <div class="booking-info-label">NUMBER OF NIGHTS</div>
                                    <div class="booking-info-value"><?php echo htmlspecialchars($b['nights']); ?></div>
                                </div>
                            </div>
                            <div class="booking-info-item">
                                <div class="booking-info-icon"><i class="fas fa-credit-card"></i></div>
                                <div class="booking-info-content">
                                    <div class="booking-info-label">PAYMENT METHOD</div>
                                    <div class="booking-info-value"><?php echo ucfirst(htmlspecialchars($payment_method)); ?></div>
                                </div>
                            </div>
                            <div class="booking-info-item">
                                <div class="booking-info-icon"><i class="fas fa-receipt"></i></div>
                                <div class="booking-info-content">
                                    <div class="booking-info-label">TOTAL AMOUNT</div>
                                    <div class="booking-info-value fw-bold">Rs. <?php echo number_format($total_amount); ?></div>
                                </div>
                            </div>
                            <div class="booking-info-item">
                                <div class="booking-info-icon"><i class="fas fa-<?php echo $payment_method == 'esewa' ? 'check-circle' : 'clock'; ?>"></i></div>
                                <div class="booking-info-content">
                                    <div class="booking-info-label">PAID AMOUNT</div>
                                    <div class="booking-info-value fw-bold <?php echo $payment_method == 'esewa' ? 'text-success' : 'text-secondary'; ?>">
                                        Rs. <?php echo number_format($paid_amount); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="booking-info-item">
                                <div class="booking-info-icon"><i class="fas fa-money-bill-wave"></i></div>
                                <div class="booking-info-content">
                                    <div class="booking-info-label">PAYMENT STATUS</div>
                                    <div class="booking-info-value">
                                        <?php echo $payment_method == 'esewa' ? '<span class="text-success"><i class="fas fa-check-circle"></i> Paid via eSewa</span>' : 'Not Paid'; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="booking-info-item">
                                <div class="booking-info-icon"><i class="fas fa-exclamation-circle"></i></div>
                                <div class="booking-info-content">
                                    <div class="booking-info-label">DUE AMOUNT</div>
                                    <div class="booking-info-value <?php echo $payment_method == 'esewa' ? 'text-success' : 'text-danger'; ?>">
                                        Rs. <?php echo number_format($due_amount); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <a href="home.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="booking-card">
                    <div class="text-center p-5">
                        <i class="fas fa-calendar-times" style="font-size: 4rem; color: #cbd5e1;"></i>
                        <h3 class="mt-3">No Recent Booking</h3>
                        <p class="text-muted">You don't have any recent booking to display.</p>
                        <a href="home.php" class="btn-back"><i class="fas fa-home"></i> Go to Homepage</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Juju Homestay. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
