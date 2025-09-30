<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register - Jetlouge Travels</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Register Page Styles -->
  <link rel="stylesheet" href="{{ asset('assets/css/register-style.css') }}">
</head>
<body>
  <div class="register-container">
    <div class="row g-0">
      <!-- Left Side - Welcome -->
      <div class="col-lg-6 register-left">
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
          <p class="brand-subtitle">Customer Portal</p>
        </div>

        <h2 class="welcome-text">Join Our Community!</h2>
        <p class="welcome-subtitle">
          Create your customer account to start booking amazing travel experiences,
          manage your trips, and discover exclusive deals.
        </p>

        <ul class="feature-list">
          <li>
            <i class="bi bi-check"></i>
            <span>Easy booking management</span>
          </li>
          <li>
            <i class="bi bi-check"></i>
            <span>Exclusive travel deals</span>
          </li>
          <li>
            <i class="bi bi-check"></i>
            <span>Trip history & tracking</span>
          </li>
          <li>
            <i class="bi bi-check"></i>
            <span>24/7 customer support</span>
          </li>
        </ul>
      </div>

      <!-- Right Side - Register Form -->
      <div class="col-lg-6 register-right">
        <h3 class="text-center mb-4" style="color: var(--jetlouge-primary); font-weight: 700;">
          Create Your Customer Account
        </h3>

        @if ($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" id="registerForm">
          @csrf
          <div class="mb-3">
            <label for="firstName" class="form-label fw-semibold">First Name</label>
            <div class="input-group">
              <span class="input-group-text">
                <i class="bi bi-person"></i>
              </span>
              <input type="text" class="form-control @error('firstName') is-invalid @enderror" id="firstName" name="firstName" placeholder="Enter your first name" value="{{ old('firstName') }}" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="lastName" class="form-label fw-semibold">Last Name</label>
            <div class="input-group">
              <span class="input-group-text">
                <i class="bi bi-person"></i>
              </span>
              <input type="text" class="form-control @error('lastName') is-invalid @enderror" id="lastName" name="lastName" placeholder="Enter your last name" value="{{ old('lastName') }}" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label fw-semibold">Email Address</label>
            <div class="input-group">
              <span class="input-group-text">
                <i class="bi bi-envelope"></i>
              </span>
              <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Enter your email" value="{{ old('email') }}" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="phone" class="form-label fw-semibold">Phone Number</label>
            <div class="input-group">
              <span class="input-group-text">
                <i class="bi bi-telephone"></i>
              </span>
              <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" placeholder="Enter your phone number" value="{{ old('phone') }}" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="password" class="form-label fw-semibold">Password</label>
            <div class="input-group">
              <span class="input-group-text">
                <i class="bi bi-lock"></i>
              </span>
              <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Create a password" required>
              <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                <i class="bi bi-eye"></i>
              </button>
            </div>
            <div class="password-strength mt-2">
              <div class="strength-bar">
                <div class="strength-fill" id="strengthFill"></div>
              </div>
              <small class="text-muted" id="strengthText">Password strength</small>
            </div>
          </div>

          <div class="mb-3">
            <label for="confirmPassword" class="form-label fw-semibold">Confirm Password</label>
            <div class="input-group">
              <span class="input-group-text">
                <i class="bi bi-lock-fill"></i>
              </span>
              <input type="password" class="form-control @error('confirmPassword') is-invalid @enderror" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>
              <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>

          <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="agreeTerms" name="agreeTerms" required {{ old('agreeTerms') ? 'checked' : '' }}>
            <label class="form-check-label" for="agreeTerms">
              I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> and <a href="#" class="text-decoration-none">Privacy Policy</a>
            </label>
          </div>

          <button type="submit" class="btn btn-register mb-3">
            <i class="bi bi-person-plus me-2"></i>
            Create Account
          </button>

          <div class="text-center">
            <p class="mb-2">Already have an account?</p>
            <a href="{{ route('admin.login') }}" class="btn-back-login">
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
      // Password toggle functionality
      const togglePassword = document.getElementById('togglePassword');
      const passwordInput = document.getElementById('password');
      const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
      const confirmPasswordInput = document.getElementById('confirmPassword');

      togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);

        const icon = this.querySelector('i');
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
      });

      toggleConfirmPassword.addEventListener('click', function() {
        const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPasswordInput.setAttribute('type', type);

        const icon = this.querySelector('i');
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
      });

      // Password strength checker
      passwordInput.addEventListener('input', function() {
        const password = this.value;
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');

        let strength = 0;
        let text = 'Weak';
        let color = '#dc3545';

        if (password.length >= 8) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;

        if (strength >= 4) {
          text = 'Strong';
          color = '#28a745';
        } else if (strength >= 2) {
          text = 'Medium';
          color = '#ffc107';
        }

        strengthFill.style.width = (strength * 20) + '%';
        strengthFill.style.backgroundColor = color;
        strengthText.textContent = text;
        strengthText.style.color = color;
      });

      // Password confirmation validation
      confirmPasswordInput.addEventListener('input', function() {
        const password = passwordInput.value;
        const confirmPassword = this.value;

        if (confirmPassword && password !== confirmPassword) {
          this.classList.add('is-invalid');
          this.classList.remove('is-valid');
        } else if (confirmPassword) {
          this.classList.add('is-valid');
          this.classList.remove('is-invalid');
        }
      });

      // Form submission
      const registerForm = document.getElementById('registerForm');
      registerForm.addEventListener('submit', function(e) {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        if (password !== confirmPassword) {
          alert('Passwords do not match!');
          e.preventDefault();
          return;
        }

        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        // Show loading state
        submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Creating Account...';
        submitBtn.disabled = true;

        // Allow form to submit normally to server
        // The form will be processed by Laravel RegisterController
      });
    });
  </script>
</body>
</html>
