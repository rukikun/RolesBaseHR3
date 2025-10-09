<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'HR System')</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="{{ asset('assets/images/jetlouge_logo.png') }}">
  <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/jetlouge_logo.png') }}">
  
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
      <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">
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
                <a href="{{ route('settings') }}" class="list-group-item list-group-item-action border-0 py-2 d-flex align-items-center">
                  <i class="fas fa-cog me-3" style="color:#1f7aec;font-size:1.25rem;"></i> <span class="fw-medium">Settings</span>
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
          
          /* Employee dropdown slide-down animation */
          .employee-dropdown, .claims-dropdown {
            position: relative;
          }
          
          .employee-submenu, .claims-submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-in-out, padding 0.3s ease-in-out;
            background-color: #f8f9fa;
            border-left: 3px solid #007bff;
            margin-left: 1rem;
            border-radius: 0 8px 8px 0;
          }
          
          .employee-submenu.show, .claims-submenu.show {
            max-height: 200px;
            padding: 0.5rem 0;
          }
          
          .submenu-item {
            display: block;
            padding: 0.5rem 1rem;
            color: #495057;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
          }
          
          .submenu-item:hover {
            background-color: #e9ecef;
            color: #007bff;
            border-left-color: #007bff;
            text-decoration: none;
          }
          
          .submenu-item.active {
            background-color: #007bff;
            color: white;
            border-left-color: #0056b3;
            font-weight: 500;
          }
          
          .submenu-item i {
            width: 16px;
            text-align: center;
            margin-right: 0.5rem;
          }
          
          /* Dropdown toggle arrow animation */
          .employee-dropdown .dropdown-toggle::after,
          .claims-dropdown .dropdown-toggle::after {
            transition: transform 0.3s ease;
          }
          
          .employee-dropdown.open .dropdown-toggle::after,
          .claims-dropdown.open .dropdown-toggle::after {
            transform: rotate(180deg);
          }
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
        <a href="{{ route('timesheet-management') }}" class="nav-link text-dark {{ request()->routeIs('timesheet-management') ? 'active' : '' }}">
          <i class="fas fa-clock me-2"></i> Timesheet
        </a>
      </li>
      <li class="nav-item">
        <a href="{{ route('shift-schedule-management') }}" class="nav-link text-dark {{ request()->routeIs('shift-schedule-management') ? 'active' : '' }}">
          <i class="fas fa-calendar-alt me-2"></i> Shift & Schedule
        </a>
      </li>
      <li class="nav-item">
        <a href="{{ route('leave-management') }}" class="nav-link text-dark {{ request()->routeIs('leave-management') ? 'active' : '' }}">
          <i class="fas fa-umbrella-beach me-2"></i> Leave
        </a>
      </li>
      <li class="nav-item claims-dropdown">
        <a href="#" class="nav-link text-dark dropdown-toggle {{ request()->routeIs('claims-reimbursement') || request()->routeIs('validate-attachment') ? 'active' : '' }}" onclick="toggleClaimsDropdown(event)" aria-expanded="false">
          <i class="fas fa-file-invoice-dollar me-2"></i> Claims & Reimbursement
        </a>
        <div class="claims-submenu" id="claims-submenu">
          <a class="submenu-item {{ request()->routeIs('claims-reimbursement') ? 'active' : '' }}" href="{{ route('claims-reimbursement') }}">
            <i class="fas fa-receipt me-2"></i>Claim Request
          </a>
          <a class="submenu-item {{ request()->routeIs('validate-attachment') ? 'active' : '' }}" href="{{ route('validate-attachment') }}">
            <i class="fas fa-check-circle me-2"></i>Validate Attachment
          </a>
        </div>
      </li>
      <li class="nav-item">
        <a href="{{ route('payroll-management') }}" class="nav-link text-dark {{ request()->routeIs('payroll-management') ? 'active' : '' }}">
          <i class="fas fa-money-bill-wave me-2"></i> Payroll
        </a>
      </li>
      <li class="nav-item employee-dropdown">
        <a href="#" class="nav-link text-dark dropdown-toggle {{ request()->routeIs('employees.*') ? 'active' : '' }}" onclick="toggleEmployeeDropdown(event)" aria-expanded="false">
          <i class="bi bi-people me-2"></i> Employees
        </a>
        <div class="employee-submenu" id="employee-submenu">
          <a class="submenu-item" href="{{ route('employees.index') }}">
            <i class="fas fa-globe me-2"></i>Employee Directory
          </a>
          <a class="submenu-item" href="{{ route('employees.list') }}">
            <i class="fas fa-database me-2"></i>Employee List
          </a>
        </div>
      </li>
      <li class="nav-item">
        <a href="{{ route('settings') }}" class="nav-link text-dark {{ request()->routeIs('settings*') ? 'active' : '' }}">
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
    
    // Employee dropdown toggle function
    function toggleEmployeeDropdown(event) {
      event.preventDefault();
      event.stopPropagation();
      
      const dropdown = document.querySelector('.employee-dropdown');
      const submenu = document.getElementById('employee-submenu');
      
      // Close claims dropdown if open
      const claimsDropdown = document.querySelector('.claims-dropdown');
      const claimsSubmenu = document.getElementById('claims-submenu');
      if (claimsSubmenu && claimsSubmenu.classList.contains('show')) {
        claimsSubmenu.classList.remove('show');
        claimsDropdown.classList.remove('open');
      }
      
      if (submenu.classList.contains('show')) {
        submenu.classList.remove('show');
        dropdown.classList.remove('open');
      } else {
        submenu.classList.add('show');
        dropdown.classList.add('open');
      }
    }
    
    // Claims dropdown toggle function
    function toggleClaimsDropdown(event) {
      event.preventDefault();
      event.stopPropagation();
      
      const dropdown = document.querySelector('.claims-dropdown');
      const submenu = document.getElementById('claims-submenu');
      
      // Close employee dropdown if open
      const employeeDropdown = document.querySelector('.employee-dropdown');
      const employeeSubmenu = document.getElementById('employee-submenu');
      if (employeeSubmenu && employeeSubmenu.classList.contains('show')) {
        employeeSubmenu.classList.remove('show');
        employeeDropdown.classList.remove('open');
      }
      
      if (submenu.classList.contains('show')) {
        submenu.classList.remove('show');
        dropdown.classList.remove('open');
      } else {
        submenu.classList.add('show');
        dropdown.classList.add('open');
      }
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
      const employeeDropdown = document.querySelector('.employee-dropdown');
      const employeeSubmenu = document.getElementById('employee-submenu');
      const claimsDropdown = document.querySelector('.claims-dropdown');
      const claimsSubmenu = document.getElementById('claims-submenu');
      
      // Close employee dropdown if clicking outside
      if (employeeDropdown && !employeeDropdown.contains(event.target)) {
        employeeSubmenu.classList.remove('show');
        employeeDropdown.classList.remove('open');
      }
      
      // Close claims dropdown if clicking outside
      if (claimsDropdown && !claimsDropdown.contains(event.target)) {
        claimsSubmenu.classList.remove('show');
        claimsDropdown.classList.remove('open');
      }
    });
  </script>
  
  @stack('scripts')
</body>
</html>
