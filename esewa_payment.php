<?php
session_start();
include 'config.php';

if (!isset($_SESSION['usermail']) || !isset($_SESSION['pending_booking'])) {
    header("location: home.php");
    exit();
}

$booking = $_SESSION['pending_booking'];
$total_amount = $booking['price'] * $booking['nodays'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>eSewa Payment</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
    }
  </style>
</head>
<body class="bg-gray-100">
  <div class="min-h-screen bg-gray-800 bg-opacity-50 flex items-center justify-center p-4">
    <div class="w-full max-w-4xl bg-white rounded-lg shadow-2xl flex md:flex-row flex-col overflow-hidden relative">
      
      <!-- Close Button -->
      <button onclick="window.history.back()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 z-10 p-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>

      <!-- Left Panel (Green) -->
      <div class="w-full md:w-2/5 bg-[#60bb46] p-12 flex-col justify-center items-center text-white hidden md:flex">
        <img src="images/esewa.png" alt="eSewa Logo" class="h-24 mb-16">
        <h1 class="text-4xl font-bold mb-4 leading-tight">Simple & Fast Payment</h1>
        <p class="text-base text-gray-100 mb-4">
          Pay Rs. <?php echo number_format($total_amount); ?> for your hotel booking.
        </p>
        <p class="text-sm text-gray-100 mb-16">
          Secure payment via eSewa digital wallet.
        </p>
        <div class="w-24 h-1.5 bg-white bg-opacity-30 rounded-full">
          <div class="w-1/3 h-full bg-white rounded-full"></div>
        </div>
      </div>

      <!-- Right Panel (Form) -->
      <div class="w-full md:w-3/5 p-8 sm:p-12">
        <h2 class="text-3xl font-bold text-gray-800 mb-1">Welcome,</h2>
        <p class="text-gray-500 mb-8">Sign in to continue</p>

        <div id="error-message" class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-md hidden">
        </div>

        <div id="success-message" class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-md hidden">
        </div>

        <form id="login-form" class="space-y-5">
          <div>
            <label for="esewa-id" class="block text-sm font-semibold text-gray-600 mb-2">eSewa ID</label>
            <input 
              type="text" 
              id="esewa-id" 
              name="esewa_id" 
              placeholder="10 digit Mobile No." 
              maxlength="10"
              pattern="[0-9]{10}"
              required
              class="w-full px-4 py-3 border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-[#60bb46] focus:border-[#60bb46] transition">
          </div>

          <div>
            <label for="password" class="block text-sm font-semibold text-gray-600 mb-2">Password (MPIN)</label>
            <input 
              type="password" 
              id="password" 
              name="password" 
              placeholder="4 digit MPIN" 
              maxlength="4"
              pattern="[0-9]{4}"
              required
              class="w-full px-4 py-3 border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-[#60bb46] focus:border-[#60bb46] transition">
          </div>

          <!-- reCAPTCHA -->
          <div class="border border-gray-200 rounded-md p-2 flex justify-between items-center bg-gray-50/50">
            <div class="flex items-center space-x-3">
              <input type="checkbox" id="captcha-check" required class="w-7 h-7 border-2 border-gray-300 cursor-pointer">
              <label for="captcha-check" class="text-sm text-gray-700 cursor-pointer">I'm not a robot</label>
            </div>
            <div class="text-center">
              <img src="https://www.gstatic.com/recaptcha/api2/logo_48.png" alt="reCAPTCHA" class="h-8 mx-auto">
              <div class="text-[10px] text-gray-400 leading-tight">
                reCAPTCHA
                <div class="-mt-1">
                  <a href="#" class="hover:underline">Privacy</a> - <a href="#" class="hover:underline">Terms</a>
                </div>
              </div>
            </div>
          </div>

          <div class="text-right !mt-3">
            <a href="#" onclick="alert('Forgot Password functionality would be implemented here'); return false;" class="text-sm text-[#60bb46] hover:underline font-semibold">Forgot Password?</a>
          </div>
          
          <div class="pt-4">
            <button 
              type="submit" 
              class="w-full bg-[#a0d88f] text-gray-800 py-3 rounded-md hover:bg-[#8cc97a] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#60bb46] transition-colors">
              Login & Pay Rs. <?php echo number_format($total_amount); ?>
            </button>
          </div>
        </form>
        
        <div class="mt-8 text-center">
          <a href="#" onclick="alert('Registration functionality would be implemented here'); return false;" class="font-semibold text-sm text-[#60bb46] hover:underline">REGISTER FOR FREE</a>
        </div>
      </div>
    </div>
  </div>

  <!-- OTP Modal -->
  <div id="otp-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
      <div class="text-center mb-6">
        <img src="images/esewa.webp" alt="eSewa Logo" class="h-14 mx-auto">
      </div>
      <h3 class="text-2xl font-bold text-gray-800 mb-4">Enter OTP</h3>
      <p class="text-gray-600 mb-6">A 6-digit OTP has been sent to your mobile number</p>
      <div class="flex gap-2 mb-6 justify-center">
        <input type="text" maxlength="1" class="otp-box w-12 h-14 text-center text-2xl font-bold border-2 border-[#60bb46] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#60bb46] bg-green-50" data-index="0">
        <input type="text" maxlength="1" class="otp-box w-12 h-14 text-center text-2xl font-bold border-2 border-[#60bb46] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#60bb46] bg-green-50" data-index="1">
        <input type="text" maxlength="1" class="otp-box w-12 h-14 text-center text-2xl font-bold border-2 border-[#60bb46] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#60bb46] bg-green-50" data-index="2">
        <input type="text" maxlength="1" class="otp-box w-12 h-14 text-center text-2xl font-bold border-2 border-[#60bb46] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#60bb46] bg-green-50" data-index="3">
        <input type="text" maxlength="1" class="otp-box w-12 h-14 text-center text-2xl font-bold border-2 border-[#60bb46] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#60bb46] bg-green-50" data-index="4">
        <input type="text" maxlength="1" class="otp-box w-12 h-14 text-center text-2xl font-bold border-2 border-[#60bb46] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#60bb46] bg-green-50" data-index="5">
      </div>
      <div class="flex gap-3">
        <button onclick="verifyOTP()" class="flex-1 bg-[#60bb46] text-white py-3 rounded-md hover:bg-[#50a536]">Verify & Pay</button>
        <button onclick="closeOTPModal()" class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-md hover:bg-gray-400">Cancel</button>
      </div>
    </div>
  </div>

  <style>
    .otp-box {
      -webkit-text-security: disc;
      text-security: disc;
    }
  </style>

  <script>
    document.getElementById('login-form').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const esewaId = document.getElementById('esewa-id').value;
      const password = document.getElementById('password').value;
      const captcha = document.getElementById('captcha-check').checked;
      
      const errorMessage = document.getElementById('error-message');
      const successMessage = document.getElementById('success-message');
      
      errorMessage.classList.add('hidden');
      successMessage.classList.add('hidden');
      
      if (!captcha) {
        errorMessage.textContent = 'Please verify you are not a robot';
        errorMessage.classList.remove('hidden');
        return;
      }
      
      if (esewaId.length !== 10) {
        errorMessage.textContent = 'eSewa ID must be 10 digits';
        errorMessage.classList.remove('hidden');
        return;
      }
      
      if (password.length !== 4) {
        errorMessage.textContent = 'Password must be 4 digits';
        errorMessage.classList.remove('hidden');
        return;
      }
      
      if (esewaId && password) {
        // Show OTP modal
        document.getElementById('otp-modal').classList.remove('hidden');
        document.getElementById('otp-modal').classList.add('flex');
        document.querySelectorAll('.otp-box')[0].focus();
      }
    });
    
    // OTP box navigation
    const otpBoxes = document.querySelectorAll('.otp-box');
    otpBoxes.forEach((box, index) => {
      box.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value && index < 5) {
          otpBoxes[index + 1].focus();
        }
      });
      
      box.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && !this.value && index > 0) {
          otpBoxes[index - 1].focus();
        }
      });
    });
    
    function verifyOTP() {
      let enteredOTP = '';
      document.querySelectorAll('.otp-box').forEach(box => {
        enteredOTP += box.value;
      });
      
      const errorMessage = document.getElementById('error-message');
      
      if (enteredOTP.length === 6) {
        document.getElementById('otp-modal').classList.add('hidden');
        document.getElementById('success-message').textContent = 'Payment successful! Redirecting...';
        document.getElementById('success-message').classList.remove('hidden');
        
        setTimeout(function() {
          window.location.href = 'confirm_payment.php?payment_method=esewa';
        }, 1500);
      } else {
        errorMessage.textContent = 'Please enter complete OTP';
        errorMessage.classList.remove('hidden');
      }
    }
    
    function closeOTPModal() {
      document.getElementById('otp-modal').classList.add('hidden');
      document.getElementById('otp-modal').classList.remove('flex');
      document.querySelectorAll('.otp-box').forEach(box => box.value = '');
    }
    
    // Only allow numbers in inputs
    document.getElementById('esewa-id').addEventListener('input', function(e) {
      this.value = this.value.replace(/[^0-9]/g, '');
    });
    
    document.getElementById('password').addEventListener('input', function(e) {
      this.value = this.value.replace(/[^0-9]/g, '');
    });
  </script>
</body>
</html>
