@php
    // Use the 'employee' guard if available, fallback to default
    $employee = null;
    if (Auth::guard('employee')->check()) {
        $employee = Auth::guard('employee')->user();
    } elseif (Auth::check()) {
        $employee = Auth::user();
    }
@endphp

<!-- Sidebar -->
<aside id="sidebar" class="bg-white border-end p-3 shadow-sm">
  <!-- Profile Section -->
  <div class="profile-section text-center d-flex flex-column align-items-center">
    @php
      // Auto-generate profile picture for any employee
      $profilePicUrl = null;
      $initials = '??';
      $colors = [
        '#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe', '#00f2fe',
        '#43e97b', '#38f9d7', '#ffecd2', '#fcb69f', '#a8edea', '#fed6e3'
      ];
      
      if ($employee) {
        // Generate initials
        $firstName = $employee->first_name ?? '';
        $lastName = $employee->last_name ?? '';
        $initials = strtoupper(substr($firstName, 0, 1)) . strtoupper(substr($lastName, 0, 1));
        
        // Check if profile picture exists
        if ($employee->profile_picture) {
          // The profile_picture field contains: profile_pictures/filename.jpg
          // This should be in storage/app/public/profile_pictures/filename.jpg
          $profilePicPath = 'app/public/' . $employee->profile_picture;
          
          if (file_exists(storage_path($profilePicPath))) {
            // File exists, create the public URL
            $profilePicUrl = asset('storage/' . $employee->profile_picture);
          } else {
            // File doesn't exist, but try the URL anyway (might be a symlink issue)
            $profilePicUrl = asset('storage/' . $employee->profile_picture);
          }
        }
        
        // Generate consistent color based on employee name
        $colorIndex = (ord($firstName[0] ?? 'A') + ord($lastName[0] ?? 'A')) % count($colors);
        $bgColor = $colors[$colorIndex];
      }
    @endphp
    
    
    @if($profilePicUrl)
      <img src="{{ $profilePicUrl }}" 
           alt="Employee Profile" class="profile-img mb-2 mx-auto" 
           style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 3px solid #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    @else
      <div class="profile-img mb-2 mx-auto d-flex align-items-center justify-content-center" 
           style="width: 60px; height: 60px; border-radius: 50%; background: {{ $bgColor ?? '#667eea' }}; color: white; font-weight: bold; font-size: 18px; border: 3px solid #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        {{ $initials }}
      </div>
    @endif
    <h6 class="fw-semibold mb-1">
      @if($employee && trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')))
        {{ trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) }}
      @else
        Employee
      @endif
    </h6>
    <p class="text-muted">{{ $employee && $employee->position ? $employee->position : 'Position not set' }}</p>
    <div class="mt-2">
      <small class="badge
        {{
          ($employee && isset($employee->status) && ($employee->status ?? '') == 'active') ? 'bg-success' :
          (($employee && isset($employee->status) && ($employee->status ?? '') == 'inactive') ? 'bg-secondary' :
          (($employee && isset($employee->status) && ($employee->status ?? '') == 'suspended') ? 'bg-danger' : 'bg-info'))
        }}">
        {{ $employee && isset($employee->status) ? ucfirst($employee->status ?? 'unknown') : 'Unknown' }}
      </small>
      <small class="text-muted d-block mt-1">
        Employee ID: {{ $employee && $employee->employee_id ? $employee->employee_id : 'N/A' }}
      </small>
    </div>
  </div>

  <!-- Navigation Menu -->
  <ul class="nav flex-column">

    <!-- Dashboard -->
    <li class="nav-item">
      <a href="{{ route('employee.dashboard') }}" class="nav-link{{ request()->routeIs('employee.dashboard') ? ' active fw-bold bg-primary text-white' : ' text-primary' }} d-flex align-items-center">
        <i class="bi bi-speedometer2 me-2"></i>
        Dashboard
      </a>
    </li>


    <!-- Shift & Schedule -->
    <li class="nav-item">
      <a href="{{ route('employee.schedule') }}" class="nav-link{{ request()->routeIs('employee.schedule*') ? ' active fw-bold bg-primary text-white' : ' text-primary' }} d-flex align-items-center">
        <i class="bi bi-calendar3 me-2"></i>
        Shift & Schedule
      </a>
    </li>

    <!-- Attendance & Time Logs -->
    <li class="nav-item">
      <a href="{{ route('employee.attendance_logs.index') }}" class="nav-link{{ request()->routeIs('employee.attendance_logs.*') ? ' active fw-bold bg-primary text-white' : ' text-primary' }} d-flex align-items-center">
        <i class="bi bi-clock-history me-2"></i>
        Attendance & Time Logs
      </a>
    </li>

    <!-- Leave Application & Balance -->
    <li class="nav-item">
      <a href="{{ route('employee.leave_applications.index') }}" class="nav-link{{ request()->routeIs('employee.leave_applications.*') ? ' active fw-bold bg-primary text-white' : ' text-primary' }} d-flex align-items-center">
        <i class="bi bi-calendar-event me-2"></i>
        Leave Application & Balance
      </a>
    </li>


    <li class="nav-item">
      <a href="{{ route('employee.claim_reimbursements.index') }}" 
         class="nav-link{{ request()->routeIs('employee.claim_reimbursements.*') ? ' active fw-bold bg-primary text-white' : ' text-primary' }} d-flex align-items-center">
          <i class="bi bi-cash-stack me-2"></i>
          Claim & Reimbursement
      </a>
  </li>




    <!-- Logout -->
    <li class="nav-item mt-3">
      <form action="{{ route('employee.logout') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="nav-link text-danger border-0 bg-transparent w-100 text-start d-flex align-items-center">
          <i class="bi bi-box-arrow-right me-2"></i>
          <span>Logout</span>
        </button>
      </form>
    </li>

  </ul>
</aside>
