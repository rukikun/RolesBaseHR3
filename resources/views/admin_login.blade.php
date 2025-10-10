<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - Jetlouge Travels Admin</title>
  
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="{{ asset('assets/images/jetlouge_logo.png') }}">
  <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/jetlouge_logo.png') }}">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Login Page Styles -->
  <link rel="stylesheet" href="{{ asset('assets/css/login-style.css') }}">
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
                <p class="brand-subtitle">Employee Portal</p>
              </div>

              <h2 class="welcome-text">Welcome Back!</h2>
              <p class="welcome-subtitle">
                Access your HR management dashboard to monitor employee attendance,
                process payroll, and streamline your HR operations.
              </p>

              <ul class="feature-list">
                <li>
                  <i class="bi bi-check"></i>
                  <span>Employee timesheets</span>
                </li>
                <li>
                  <i class="bi bi-check"></i>
                  <span>Payroll processing</span>
                </li>
                <li>
                  <i class="bi bi-check"></i>
                  <span>AI timesheet generation</span>
                </li>
                <li>
                  <i class="bi bi-check"></i>
                  <span>Claims management</span>
                </li>
              </ul>
            </div>

            <!-- Right Side - Login Form -->
            <div class="col-lg-6 login-right">
              <h3 class="text-center mb-4" style="color: var(--jetlouge-primary); font-weight: 700;">
                Sign In to Your Account
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

              <form method="POST" action="{{ route('admin.login') }}" id="loginForm">
                  @csrf
                  <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email Address</label>
                    <div class="input-group">
                      <span class="input-group-text">
                        <i class="bi bi-envelope"></i>
                      </span>
                      <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email" placeholder="Enter your email" value="{{ old('email') }}" required autofocus>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                      <span class="input-group-text">
                        <i class="bi bi-lock"></i>
                      </span>
                      <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="password" placeholder="Enter your password" required>
                      <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="bi bi-eye"></i>
                      </button>
                    </div>
                  </div>

                  <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="rememberMe" name="rememberMe" {{ old('rememberMe') ? 'checked' : '' }}>
                    <label class="form-check-label" for="rememberMe">
                      Remember me
                    </label>
                  </div>

                  <button type="submit" class="btn btn-login mb-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Sign In
                  </button>

                  <div class="text-center">
                    <a href="#" class="btn-forgot">Forgot your password?</a>
                  </div>

                  <hr class="my-4">

                  <div class="text-center">
                    <p class="mb-2">Don't have an account?</p>
                    <a href="{{ route('register') }}" class="btn btn-outline-primary">
                      <i class="bi bi-person-plus me-2"></i>
                      Create New Account
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

      togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);

        const icon = this.querySelector('i');
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
      });

      // Form submission
      const loginForm = document.getElementById('loginForm');
      loginForm.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        // Show loading state
        submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Signing In...';
        submitBtn.disabled = true;

        // Allow form to submit normally to server
        // The form will be processed by Laravel AuthController
      });

      // Add floating animation to shapes
      const shapes = document.querySelectorAll('.shape');
      shapes.forEach((shape, index) => {
        shape.style.animationDelay = `${index * 2}s`;
      });
    });
  </script>
</body>
</html>
