<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Employee Login - HR3 System</title>

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
                  <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="HR3 System">
                </div>
                <h1 class="brand-text">HR3 System</h1>
                <p class="brand-subtitle">Employee Self-Service Portal</p>
              </div>

              <h2 class="welcome-text">Welcome Employee!</h2>
              <p class="welcome-subtitle">
                Access your employee self-service portal to manage your attendance,
                view schedules, submit requests, and track your work activities.
              </p>

              <ul class="feature-list">
                <li>
                  <i class="bi bi-check"></i>
                  <span>Clock in/out and track attendance</span>
                </li>
                <li>
                  <i class="bi bi-check"></i>
                  <span>View work schedules & shifts</span>
                </li>
                <li>
                  <i class="bi bi-check"></i>
                  <span>Submit leave & shift requests</span>
                </li>
                <li>
                  <i class="bi bi-check"></i>
                  <span>Manage expense claims</span>
                </li>
                <li>
                  <i class="bi bi-check"></i>
                  <span>View timesheet reports</span>
                </li>
              </ul>
            </div>

            <!-- Right Side - Login Form -->
            <div class="col-lg-6 login-right">
              <div class="login-form-container">
                <div class="login-header">
                  <h3>Employee Login</h3>
                  <p>Enter your employee credentials to access ESS</p>
                </div>

                @if ($errors->any())
                  <div class="alert alert-danger">
                    <ul class="mb-0">
                      @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                      @endforeach
                    </ul>
                  </div>
                @endif

                <form method="POST" action="{{ route('employee.login.submit') }}" class="login-form">
                  @csrf
                  
                  <div class="form-group">
                    <label for="email" class="form-label">Employee Email</label>
                    <div class="input-group">
                      <span class="input-group-text">
                        <i class="bi bi-person"></i>
                      </span>
                      <input 
                        type="email" 
                        class="form-control @error('email') is-invalid @enderror" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        placeholder="Enter your employee email"
                        required 
                        autofocus
                      >
                    </div>
                    @error('email')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                      <span class="input-group-text">
                        <i class="bi bi-lock"></i>
                      </span>
                      <input 
                        type="password" 
                        class="form-control @error('password') is-invalid @enderror" 
                        id="password" 
                        name="password" 
                        placeholder="Enter your password"
                        required
                      >
                      <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="bi bi-eye"></i>
                      </button>
                    </div>
                    @error('password')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>

                  <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="rememberMe" name="rememberMe">
                    <label class="form-check-label" for="rememberMe">
                      Remember me
                    </label>
                  </div>

                  <button type="submit" class="btn btn-primary login-btn">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Login to ESS Portal
                  </button>

                  <div class="login-links">
                    <a href="#" class="forgot-password">Forgot your password?</a>
                  </div>

                  <div class="divider">
                    <span>or</span>
                  </div>

                  <div class="alternative-login">
                    <a href="{{ route('admin.login') }}" class="btn btn-outline-secondary">
                      <i class="bi bi-shield-check"></i>
                      Admin Portal
                    </a>
                  </div>
                </form>

                <div class="login-footer">
                  <p>&copy; 2025 HR3 System. All rights reserved.</p>
                </div>
              </div>
            </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
      const password = document.getElementById('password');
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      
      // Toggle the eye icon
      const icon = this.querySelector('i');
      icon.classList.toggle('bi-eye');
      icon.classList.toggle('bi-eye-slash');
    });

    // Form validation
    document.querySelector('.login-form').addEventListener('submit', function(e) {
      const email = document.getElementById('email').value;
      const password = document.getElementById('password').value;
      
      if (!email || !password) {
        e.preventDefault();
        alert('Please fill in all required fields.');
      }
    });
  </script>
</body>
</html>
