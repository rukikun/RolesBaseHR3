<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>OTP Verification - Jetlouge Travels Admin</title>
  
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="{{ asset('assets/images/jetlouge_logo.png') }}">
  <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/jetlouge_logo.png') }}">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Login Page Styles -->
  <link rel="stylesheet" href="{{ asset('assets/css/login-style.css') }}">
  
  <style>
    .otp-input-container {
      display: flex;
      gap: 10px;
      justify-content: center;
      margin: 20px 0;
    }
    
    .otp-input {
      width: 50px;
      height: 50px;
      text-align: center;
      font-size: 24px;
      font-weight: bold;
      border: 2px solid #dee2e6;
      border-radius: 8px;
      background: #f8f9fa;
      transition: all 0.3s ease;
    }
    
    .otp-input:focus {
      outline: none;
      border-color: var(--jetlouge-primary);
      background: white;
      box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .otp-input.filled {
      border-color: var(--jetlouge-primary);
      background: white;
    }
    
    .countdown-timer {
      font-size: 14px;
      color: #6c757d;
      text-align: center;
      margin-top: 10px;
    }
    
    .resend-link {
      color: var(--jetlouge-primary);
      text-decoration: none;
      font-weight: 500;
    }
    
    .resend-link:hover {
      text-decoration: underline;
    }
    
    .resend-link.disabled {
      color: #6c757d;
      cursor: not-allowed;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="row g-0">
            <!-- Left Side - Welcome -->
            <div class="col-lg-6 login-left">
              <div class="floating-shapes">
                <div class="shape"></div>
                <div class="shape"></div>
                <div class="shape"></div>
              </div>

              <div class="logo-container">
                <div class="logo-box">
                  <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels">
                </div>
                <h1 class="brand-text">Jetlouge Travels</h1>
                <p class="brand-subtitle">Admin Portal</p>
              </div>

              <h2 class="welcome-text">Welcome Back!</h2>
              <p class="welcome-subtitle">
                Access your travel management dashboard to monitor bookings,
                manage customers, and grow your travel business.
              </p>

              <ul class="feature-list">
                <li>
                  <i class="bi bi-check"></i>
                  <span>Manage bookings & reservations</span>
                </li>
                <li>
                  <i class="bi bi-check"></i>
                  <span>Track customer interactions</span>
                </li>
                <li>
                  <i class="bi bi-check"></i>
                  <span>Monitor business analytics</span>
                </li>
                <li>
                  <i class="bi bi-check"></i>
                  <span>Secure admin access</span>
                </li>
              </ul>
            </div>

            <!-- Right Side - OTP Form -->
            <div class="col-lg-6 login-right">
              <h3 class="text-center mb-4" style="color: var(--jetlouge-primary); font-weight: 700;">
                Verify Your Identity
              </h3>

              @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-2" role="alert" style="padding: 0.5rem 0.75rem; margin-bottom: 0.5rem;">
                  <div class="d-flex align-items-start">
                    <i class="bi bi-exclamation-triangle-fill me-2 mt-1" style="font-size: 0.875rem;"></i>
                    <div class="flex-grow-1">
                      @foreach ($errors->all() as $error)
                        <div style="font-size: 0.875rem;">{{ $error }}</div>
                      @endforeach
                    </div>
                    <button type="button" class="btn-close btn-close-sm ms-2" data-bs-dismiss="alert" aria-label="Close" style="font-size: 0.75rem; padding: 0.25rem;"></button>
                  </div>
                </div>
              @endif

              @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <i class="bi bi-check-circle-fill me-2"></i>
                  {{ session('success') }}
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              @endif

              @if (session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                  <i class="bi bi-info-circle-fill me-2"></i>
                  {{ session('info') }}
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              @endif

              <div class="text-center mb-4">
                <i class="bi bi-shield-check" style="font-size: 3rem; color: var(--jetlouge-primary);"></i>
                <p class="mt-3 mb-1">We've sent a verification code to:</p>
                <strong>{{ session('otp_email', $email ?? '') }}</strong>
              </div>

              <form method="POST" action="{{ route('admin.otp.verify') }}" id="otpForm">
                  @csrf
                  <input type="hidden" name="email" value="{{ session('otp_email', $email ?? '') }}">
                  
                  <div class="mb-3">
                    <label class="form-label fw-semibold text-center d-block">Enter 6-Digit Verification Code</label>
                    <div class="otp-input-container">
                      <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="0">
                      <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="1">
                      <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="2">
                      <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="3">
                      <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="4">
                      <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" data-index="5">
                    </div>
                    <input type="hidden" name="otp_code" id="otpCodeInput">
                  </div>

                  <div class="countdown-timer">
                    <span id="countdown">Code expires in <strong id="timer">10:00</strong></span>
                  </div>

                  <button type="submit" class="btn btn-login mb-3" id="verifyBtn" disabled>
                    <i class="bi bi-shield-check me-2"></i>
                    Verify Code
                  </button>

                  <div class="text-center">
                    <p class="mb-2">Didn't receive the code?</p>
                    <a href="#" class="resend-link" id="resendLink" onclick="resendOtp(event)">
                      <i class="bi bi-arrow-clockwise me-1"></i>
                      Resend Code
                    </a>
                  </div>

                  <hr class="my-4">

                  <div class="text-center">
                    <a href="{{ route('admin.login') }}" class="btn btn-outline-secondary">
                      <i class="bi bi-arrow-left me-2"></i>
                      Back to Login
                    </a>
                  </div>
                </form>
            </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const otpInputs = document.querySelectorAll('.otp-input');
      const otpCodeInput = document.getElementById('otpCodeInput');
      const verifyBtn = document.getElementById('verifyBtn');
      const resendLink = document.getElementById('resendLink');
      const timerElement = document.getElementById('timer');
      
      let countdown = 600; // 10 minutes in seconds
      let countdownInterval;

      // OTP Input handling
      otpInputs.forEach((input, index) => {
        input.addEventListener('input', function(e) {
          const value = e.target.value;
          
          // Only allow numbers
          if (!/^[0-9]$/.test(value)) {
            e.target.value = '';
            return;
          }

          // Add filled class
          if (value) {
            e.target.classList.add('filled');
          } else {
            e.target.classList.remove('filled');
          }

          // Move to next input
          if (value && index < otpInputs.length - 1) {
            otpInputs[index + 1].focus();
          }

          // Update hidden input and verify button
          updateOtpCode();
        });

        input.addEventListener('keydown', function(e) {
          // Handle backspace
          if (e.key === 'Backspace' && !e.target.value && index > 0) {
            otpInputs[index - 1].focus();
            otpInputs[index - 1].value = '';
            otpInputs[index - 1].classList.remove('filled');
            updateOtpCode();
          }
          
          // Handle paste
          if (e.key === 'v' && (e.ctrlKey || e.metaKey)) {
            e.preventDefault();
            navigator.clipboard.readText().then(text => {
              const digits = text.replace(/\D/g, '').slice(0, 6);
              digits.split('').forEach((digit, i) => {
                if (otpInputs[i]) {
                  otpInputs[i].value = digit;
                  otpInputs[i].classList.add('filled');
                }
              });
              updateOtpCode();
            });
          }
        });

        // Handle focus
        input.addEventListener('focus', function() {
          this.select();
        });
      });

      function updateOtpCode() {
        const code = Array.from(otpInputs).map(input => input.value).join('');
        otpCodeInput.value = code;
        verifyBtn.disabled = code.length !== 6;
      }

      // Countdown timer
      function startCountdown() {
        countdownInterval = setInterval(() => {
          countdown--;
          
          const minutes = Math.floor(countdown / 60);
          const seconds = countdown % 60;
          timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
          
          if (countdown <= 0) {
            clearInterval(countdownInterval);
            timerElement.textContent = 'Expired';
            document.getElementById('countdown').innerHTML = 'Code has <strong style="color: #dc3545;">expired</strong>';
            verifyBtn.disabled = true;
            verifyBtn.innerHTML = '<i class="bi bi-x-circle me-2"></i>Code Expired';
            resendLink.classList.remove('disabled');
          }
        }, 1000);
      }

      // Start countdown
      startCountdown();

      // Form submission
      const otpForm = document.getElementById('otpForm');
      otpForm.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        // Show loading state
        submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Verifying...';
        submitBtn.disabled = true;
      });

      // Add floating animation to shapes
      const shapes = document.querySelectorAll('.shape');
      shapes.forEach((shape, index) => {
        shape.style.animationDelay = `${index * 2}s`;
      });

      // Focus first input
      otpInputs[0].focus();
    });

    // Resend OTP function
    function resendOtp(event) {
      event.preventDefault();
      
      const resendLink = document.getElementById('resendLink');
      if (resendLink.classList.contains('disabled')) {
        return;
      }

      // Show loading state
      resendLink.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Sending...';
      resendLink.classList.add('disabled');

      // Make AJAX request to resend OTP
      fetch('{{ route("admin.otp.resend") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          email: '{{ session("otp_email", $email ?? "") }}'
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Reset countdown
          countdown = 600;
          document.getElementById('timer').textContent = '10:00';
          document.getElementById('countdown').innerHTML = 'Code expires in <strong id="timer">10:00</strong>';
          
          // Reset form
          document.querySelectorAll('.otp-input').forEach(input => {
            input.value = '';
            input.classList.remove('filled');
          });
          document.getElementById('otpCodeInput').value = '';
          document.getElementById('verifyBtn').disabled = true;
          document.getElementById('verifyBtn').innerHTML = '<i class="bi bi-shield-check me-2"></i>Verify Code';
          
          // Show success message
          const alertDiv = document.createElement('div');
          alertDiv.className = 'alert alert-success alert-dismissible fade show';
          alertDiv.innerHTML = `
            <i class="bi bi-check-circle-fill me-2"></i>
            New verification code sent successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          `;
          document.querySelector('.login-right h3').after(alertDiv);
          
          // Auto-dismiss after 3 seconds
          setTimeout(() => {
            alertDiv.remove();
          }, 3000);
        } else {
          alert('Failed to resend code. Please try again.');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Failed to resend code. Please try again.');
      })
      .finally(() => {
        resendLink.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Resend Code';
        
        // Disable resend for 30 seconds
        setTimeout(() => {
          resendLink.classList.remove('disabled');
        }, 30000);
      });
    }
  </script>
</body>
</html>
