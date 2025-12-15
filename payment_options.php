<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usermail']) || !isset($_SESSION['pending_booking'])) {
    header("location: home.php");
    exit();
}

$booking = $_SESSION['pending_booking'];
$price_per_night = isset($booking['price']) ? floatval($booking['price']) : 0;
$nights = isset($booking['nodays']) ? intval($booking['nodays']) : 0;
$total_amount = $price_per_night * $nights;
$total_amount = isset($booking['price']) && isset($booking['nodays']) ? $booking['price'] * $booking['nodays'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Options - Juju Homestay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Merienda:wght@400;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .payment-container {
            max-width: 600px;
            width: 100%;
            padding: 20px;
        }
        .payment-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .payment-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .payment-body {
            padding: 40px;
        }
        .payment-option {
            border: 2px solid #e5e7eb;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .payment-option:hover {
            border-color: #667eea;
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.2);
        }
        .payment-option.selected {
            border-color: #667eea;
            background: #f0f4ff;
        }
        .payment-icon {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .payment-icon img {
            max-width: 100%;
            max-height: 100%;
        }
        .payment-icon i {
            font-size: 40px;
            color: #667eea;
        }
        .payment-details h5 {
            margin: 0 0 5px 0;
            color: #1e293b;
        }
        .payment-details p {
            margin: 0;
            color: #64748b;
            font-size: 14px;
        }
        .btn-confirm {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
        }
        .btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        .booking-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .summary-row.total {
            border-top: 2px solid #dee2e6;
            padding-top: 10px;
            margin-top: 10px;
            font-weight: 600;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-card">
            <div class="payment-header">
                <h2 class="mb-0"><i class="fas fa-credit-card me-2"></i>Select Payment Method</h2>
            </div>
            <div class="payment-body">
                <div class="booking-summary">
                    <h5 class="mb-3">Booking Summary</h5>
                    <div class="summary-row">
                        <span>Room Type:</span>
                        <strong><?php echo htmlspecialchars($booking['RoomType']); ?></strong>
                    </div>
                    <div class="summary-row">
                        <span>Check-in:</span>
                        <strong><?php echo htmlspecialchars($booking['cin']); ?></strong>
                    </div>
                    <div class="summary-row">
                        <span>Check-out:</span>
                        <strong><?php echo htmlspecialchars($booking['cout']); ?></strong>
                    </div>
                    <div class="summary-row">
                        <span>Nights:</span>
                        <strong><?php echo $booking['nodays']; ?></strong>
                    </div>
                    <div class="summary-row">
                        <span>Price per Night:</span>
                        <strong>Rs. <?php echo number_format($price_per_night); ?></strong>
                    </div>
                    <div class="summary-row total">
                        <span>Total Amount:</span>
                        <strong>Rs. <?php echo number_format($total_amount); ?></strong>
                    </div>
                </div>

                <form method="POST" action="confirm_payment.php" id="paymentForm">
                    <input type="hidden" name="payment_method" id="payment_method" required>
                    
                    <div class="payment-option" onclick="selectPayment('cash')">
                        <div class="payment-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="payment-details">
                            <h5>Cash in Hand <span class="text-success">Rs. <?php echo number_format($total_amount); ?></span></h5>
                            <p>Pay at hotel during check-in</p>
                        </div>
                    </div>

                    <div class="payment-option" onclick="redirectToEsewa()">
                        <div class="payment-icon" style="background: #60bb46;">
                            <img src="https://esewa.com.np/common/images/esewa_logo.png" alt="eSewa">
                        </div>
                        <div class="payment-details">
                            <h5>eSewa <span class="text-success">Rs. <?php echo number_format($total_amount); ?></span></h5>
                            <p>Pay with eSewa digital wallet</p>
                        </div>
                    </div>

                    <button type="submit" class="btn-confirm">
                        <i class="fas fa-check-circle me-2"></i>Confirm Booking
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function selectPayment(method) {
            document.querySelectorAll('.payment-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');
            document.getElementById('payment_method').value = method;
        }
        
        function redirectToEsewa() {
            window.location.href = 'esewa_payment.php';
        }

        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            if (!document.getElementById('payment_method').value) {
                e.preventDefault();
                alert('Please select a payment method');
            }
        });
    </script>
</body>
</html>
