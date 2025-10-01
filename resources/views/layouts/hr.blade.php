<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'HR System')</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Custom HR Styles -->
  <link href="{{ asset('assets/css/hr-style.css') }}" rel="stylesheet">
  <!-- Universal Modal Fix -->
  <link href="{{ asset('assets/css/modal-universal-fix.css') }}" rel="stylesheet">
  
  @stack('styles')
  
</head>
<body style="background-color: #ffffff !important;">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color: var(--jetlouge-primary);">
    <div class="container-fluid">
      <button class="sidebar-toggle desktop-toggle me-3" id="desktop-toggle" title="Toggle Sidebar">
        <i class="bi bi-list fs-5"></i>
      </button>
      <a class="navbar-brand fw-bold" href="/admin/dashboard">
        <i class="bi bi-airplane me-2"></i>Jetlouge Travels
      </a>
      <div class="d-flex align-items-center gap-3">
        <!-- HR System Settings Dropdown -->
        <!-- Jetlouge Neat Profile Dropdown -->
        <div class="dropdown">
          <button class="btn btn-outline-light d-flex align-items-center px-2 py-1" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 24px;">
            <span class="rounded-circle d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;font-size:18px;background:var(--jetlouge-primary);color:#fff;font-weight:bold;box-shadow:0 2px 8px rgba(0,0,0,0.10);">
              {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
            </span>
            <span class="fw-semibold d-none d-md-inline">{{ Auth::user()->name ?? 'Admin' }}</span>
            <i class="fas fa-chevron-down ms-2"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end p-0" aria-labelledby="profileDropdown" style="min-width:300px;max-width:340px;box-shadow:0 6px 32px rgba(0,80,160,0.10);border-radius:16px;overflow:hidden;border:1px solid #e3e8f0;">
            <div class="px-4 pt-4 pb-2 text-center" style="background: #fff; border-radius: 16px 16px 0 0;">
              <span class="rounded-circle d-flex align-items-center justify-content-center mb-2 mx-auto" style="width:56px;height:56px;font-size:28px;background:var(--jetlouge-primary);color:#fff;font-weight:bold;box-shadow:0 2px 8px rgba(0,0,0,0.10);">
                {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
              </span>
              <div class="fw-bold fs-5" style="color:#222;">{{ Auth::user()->name ?? 'Admin' }}</div>
              <div class="small text-muted mb-2">{{ Auth::user()->email ?? '' }}</div>
            </div>
            <hr class="my-0" style="border:0;border-top:1px solid #f0f2fa;">
            <div class="list-group list-group-flush bg-white py-2" style="border-radius: 0 0 16px 16px;">
              <a href="{{ route('admin.profile.index') }}" class="list-group-item list-group-item-action border-0 py-2 d-flex align-items-center">
                <i class="fas fa-user-circle me-3" style="color:#6f42c1;font-size:1.25rem;"></i> <span class="fw-medium">My Profile</span>
              </a>
              <a href="{{ route('admin.profile.change-password') }}" class="list-group-item list-group-item-action border-0 py-2 d-flex align-items-center">
                <i class="fas fa-key me-3" style="color:#f0b429;font-size:1.25rem;"></i> <span class="fw-medium">Change Password</span>
              </a>
              @if(Auth::user()->isSuperAdmin())
                <a href="{{ route('admin.profile.manage-admins') }}" class="list-group-item list-group-item-action border-0 py-2 d-flex align-items-center">
                  <i class="fas fa-users-cog me-3" style="color:#1f7aec;font-size:1.25rem;"></i> <span class="fw-medium">Manage Admins</span>
                </a>
              @endif
              <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="list-group-item list-group-item-action border-0 py-2 d-flex align-items-center text-danger" style="background:none;">
                  <i class="fas fa-sign-out-alt me-3" style="color:#e74c3c;font-size:1.25rem;"></i> <span class="fw-medium">Logout</span>
                </button>
              </form>
            </div>
          </div>
        </div>
        <style>
          .dropdown-menu { box-shadow: 0 6px 32px rgba(0,80,160,0.10) !important; border-radius: 16px !important; border:1px solid #e3e8f0; }
          .list-group-item { background: #fff; font-size: 1rem; transition: background .12s; }
          .list-group-item:active, .list-group-item:hover { background: #f5f7fa !important; }
          .fw-medium { font-weight: 500; }
        </style>
        <button class="sidebar-toggle mobile-toggle" id="menu-btn" title="Open Menu">
          <i class="bi bi-list fs-5"></i>
        </button>
      </div>
    </div>
  </nav>

  <!-- Sidebar -->
  <aside id="sidebar" class="bg-white border-end p-3 shadow-sm">
    <!-- Profile Section -->
    <div class="profile-section text-center">
      <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop&crop=face"
           alt="Admin Profile" class="profile-img mb-2">
      <h6 class="fw-semibold mb-1">{{ Auth::user()->name ?? 'John Anderson' }}</h6>
      <small class="text-muted">HR Administrator</small>
    </div>

    <!-- Navigation Menu -->
    <ul class="nav flex-column">
      <li class="nav-item">
        <a href="{{ route('dashboard') }}" class="nav-link text-dark {{ request()->routeIs('dashboard') ? 'active' : '' }}">
          <i class="fas fa-tachometer-alt me-2"></i> Dashboard
        </a>
      </li>
      <li class="nav-item">
        <a href="{{ route('timesheet.index') }}" class="nav-link text-dark {{ request()->routeIs('timesheet.*') ? 'active' : '' }}">
          <i class="fas fa-clock me-2"></i> Timesheet
        </a>
      </li>
      <li class="nav-item">
        <a href="{{ route('shift-schedule-management') }}" class="nav-link text-dark {{ request()->routeIs('shift-schedule-management') ? 'active' : '' }}">
          <i class="fas fa-calendar-alt me-2"></i> Shift & Schedule
        </a>
      </li>
      <li class="nav-item">
        <a href="{{ route('leave.index') }}" class="nav-link text-dark {{ request()->routeIs('leave.*') ? 'active' : '' }}">
          <i class="fas fa-umbrella-beach me-2"></i> Leave Management
        </a>
      </li>
      <li class="nav-item">
        <a href="{{ route('claims.index') }}" class="nav-link text-dark {{ request()->routeIs('claims.*') ? 'active' : '' }}">
          <i class="fas fa-file-invoice-dollar me-2"></i> Claims & Reimbursement
        </a>
      </li>
      <li class="nav-item">
        <a href="{{ route('employees') }}" class="nav-link text-dark {{ request()->routeIs('employees.*') ? 'active' : '' }}">
          <i class="bi bi-people me-2"></i> Employees
        </a>
      </li>
      <li class="nav-item">
        <a href="#" class="nav-link text-dark">
          <i class="bi bi-gear me-2"></i> Settings
        </a>
      </li>
      <li class="nav-item mt-3">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="nav-link text-danger btn btn-link p-0 w-100 text-start" style="text-decoration:none;">
            <i class="bi bi-box-arrow-right me-2"></i> Logout
          </button>
        </form>
      </li>
    </ul>
  </aside>

  <!-- Overlay for mobile -->
  <div id="overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index:1030; display: none;"></div>

  <!-- Main Content -->
  <main id="main-content">
    @yield('content')
  </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Universal Modal Initialization -->
    <script src="{{ asset('assets/js/modal-universal-init.js') }}"></script>
    <!-- HR Database Integration -->
    <script src="{{ asset('assets/js/hr-database-integration.js') }}"></script>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('overlay');
      const desktopToggle = document.getElementById('desktop-toggle');
      const mainContent = document.getElementById('main-content');

      // Desktop toggle
      if (desktopToggle) {
        desktopToggle.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          sidebar.classList.toggle('collapsed');
          mainContent.classList.toggle('expanded');
          
          // Show/hide overlay for mobile
          if (window.innerWidth <= 768) {
            if (overlay) {
              if (sidebar.classList.contains('collapsed')) {
                overlay.style.display = 'none';
              } else {
                overlay.style.display = 'block';
              }
            }
          }
        });
      }

      // Close sidebar when clicking overlay
      if (overlay) {
        overlay.addEventListener('click', function() {
          sidebar.classList.add('collapsed');
          mainContent.classList.add('expanded');
          overlay.style.display = 'none';
        });
      }

      // Handle window resize
      window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
          if (overlay) {
            overlay.style.display = 'none';
          }
        }
      });
    });
  </script>
  
  @stack('scripts')
</body>
</html>
