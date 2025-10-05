<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Selection - Jetlouge Travels</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --jetlouge-primary: #1e3a8a;
            --jetlouge-secondary: #3b82f6;
            --jetlouge-accent: #fbbf24;
            --jetlouge-light: #dbeafe;
            --jetlouge-dark: #1f2937;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--jetlouge-primary), var(--jetlouge-secondary));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .portal-selection-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            margin: 2rem;
        }
        
        .portal-header {
            background: linear-gradient(135deg, var(--jetlouge-primary), var(--jetlouge-secondary));
            color: white;
            padding: 3rem 2rem;
            text-align: center;
        }
        
        .portal-header .logo {
            width: 80px;
            height: 80px;
            margin-bottom: 1rem;
        }
        
        .portal-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .portal-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        .portal-options {
            padding: 3rem 2rem;
        }
        
        .portal-card {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .portal-card:hover {
            border-color: var(--jetlouge-accent);
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(30, 58, 138, 0.15);
        }
        
        .portal-card.employee:hover {
            border-color: var(--jetlouge-secondary);
            box-shadow: 0 15px 40px rgba(59, 130, 246, 0.15);
        }
        
        .portal-card.admin:hover {
            border-color: var(--jetlouge-accent);
            box-shadow: 0 15px 40px rgba(251, 191, 36, 0.15);
        }
        
        .portal-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.5rem;
            color: white;
        }
        
        .portal-icon.employee {
            background: linear-gradient(135deg, var(--jetlouge-secondary), #60a5fa);
        }
        
        .portal-icon.admin {
            background: linear-gradient(135deg, var(--jetlouge-accent), #fcd34d);
        }
        
        .portal-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--jetlouge-dark);
        }
        
        .portal-card p {
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            flex-grow: 1;
        }
        
        .portal-features {
            list-style: none;
            padding: 0;
            margin: 1.5rem 0;
        }
        
        .portal-features li {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            color: #6b7280;
        }
        
        .portal-features li i {
            color: var(--jetlouge-secondary);
            margin-right: 0.5rem;
            font-size: 0.8rem;
        }
        
        .portal-btn {
            background: linear-gradient(135deg, var(--jetlouge-primary), var(--jetlouge-secondary));
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .portal-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(30, 58, 138, 0.3);
            color: white;
        }
        
        .portal-btn.admin {
            background: linear-gradient(135deg, var(--jetlouge-accent), #fcd34d);
            color: var(--jetlouge-dark);
        }
        
        .portal-btn.admin:hover {
            box-shadow: 0 8px 25px rgba(251, 191, 36, 0.3);
            color: var(--jetlouge-dark);
        }
        
        .back-link {
            position: absolute;
            top: 2rem;
            left: 2rem;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            color: var(--jetlouge-accent);
            transform: translateX(-5px);
        }
        
        @media (max-width: 768px) {
            .portal-header {
                padding: 2rem 1rem;
            }
            
            .portal-header h1 {
                font-size: 2rem;
            }
            
            .portal-options {
                padding: 2rem 1rem;
            }
            
            .portal-card {
                margin-bottom: 2rem;
            }
        }
    </style>
</head>
<body>
    <a href="{{ route('landing') }}" class="back-link">
        <i class="bi bi-arrow-left"></i>
        Back to Home
    </a>
    
    <div class="portal-selection-container">
        <div class="portal-header">
            <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo">
            <h1>Choose Your Portal</h1>
            <p>Select the appropriate portal to access your account</p>
        </div>
        
        <div class="portal-options">
            <div class="row g-4">
                <!-- Employee Portal -->
                <div class="col-md-6">
                    <div class="portal-card employee" onclick="window.location.href='/employee/login'">
                        <div>
                            <div class="portal-icon employee">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <h3>Employee Portal</h3>
                            <p>Access your personal dashboard, manage leave applications, view payslips, and handle employee-related tasks.</p>
                            
                            <ul class="portal-features">
                                <li><i class="bi bi-check"></i> Personal Dashboard</li>
                                <li><i class="bi bi-check"></i> Leave Applications</li>
                                <li><i class="bi bi-check"></i> Attendance Tracking</li>
                                <li><i class="bi bi-check"></i> Payslip Access</li>
                                <li><i class="bi bi-check"></i> Profile Management</li>
                            </ul>
                        </div>
                        
                        <a href="/employee/login" class="portal-btn">
                            <i class="bi bi-person-circle"></i>
                            Employee Login
                        </a>
                    </div>
                </div>
                
                <!-- Admin Portal -->
                <div class="col-md-6">
                    <div class="portal-card admin" onclick="window.location.href='/admin/login'">
                        <div>
                            <div class="portal-icon admin">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <h3>Admin Portal</h3>
                            <p>Manage the entire system, handle bookings, oversee employees, and access administrative functions.</p>
                            
                            <ul class="portal-features">
                                <li><i class="bi bi-check"></i> System Management</li>
                                <li><i class="bi bi-check"></i> Employee Oversight</li>
                                <li><i class="bi bi-check"></i> Booking Management</li>
                                <li><i class="bi bi-check"></i> Business Analytics</li>
                                <li><i class="bi bi-check"></i> Full System Access</li>
                            </ul>
                        </div>
                        
                        <a href="{{ route('admin.login') }}" class="portal-btn admin">
                            <i class="bi bi-gear-fill"></i>
                            Admin Login
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Need help? Contact your system administrator for access credentials.
                </p>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Add click animation
        document.querySelectorAll('.portal-card').forEach(card => {
            card.addEventListener('click', function() {
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });
    </script>
</body>
</html>
