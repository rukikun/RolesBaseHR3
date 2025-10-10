<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA System Test - HR3 System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-shield-check me-2"></i>
                            2FA System Test Dashboard
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Test the 2FA System:</strong> Use this page to test the Two-Factor Authentication implementation.
                        </div>

                        <!-- System Status -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <i class="bi bi-database-check text-success" style="font-size: 2rem;"></i>
                                        <h6 class="mt-2">Database</h6>
                                        <span class="badge bg-success">
                                            @php
                                                try {
                                                    $otpCount = \App\Models\OtpVerification::count();
                                                    echo "Connected ({$otpCount} OTPs)";
                                                } catch (\Exception $e) {
                                                    echo "Error: " . $e->getMessage();
                                                }
                                            @endphp
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <i class="bi bi-envelope-check text-info" style="font-size: 2rem;"></i>
                                        <h6 class="mt-2">Email Config</h6>
                                        <span class="badge bg-info">{{ config('mail.default') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Test Actions -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <i class="bi bi-box-arrow-in-right text-primary" style="font-size: 2rem;"></i>
                                        <h6 class="mt-2">Test Login Flow</h6>
                                        <p class="text-muted small">Test the complete 2FA login process</p>
                                        <a href="{{ route('admin.login') }}" class="btn btn-primary btn-sm">
                                            <i class="bi bi-arrow-right me-1"></i>
                                            Go to Login
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <i class="bi bi-shield-check text-success" style="font-size: 2rem;"></i>
                                        <h6 class="mt-2">OTP Verification</h6>
                                        <p class="text-muted small">Test OTP verification page</p>
                                        <button class="btn btn-success btn-sm" onclick="testOtpPage()">
                                            <i class="bi bi-eye me-1"></i>
                                            View OTP Page
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <i class="bi bi-envelope text-warning" style="font-size: 2rem;"></i>
                                        <h6 class="mt-2">Test Email</h6>
                                        <p class="text-muted small">Send test OTP email</p>
                                        <button class="btn btn-warning btn-sm" onclick="sendTestEmail()">
                                            <i class="bi bi-send me-1"></i>
                                            Send Test
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Current OTPs -->
                        <div class="mt-4">
                            <h6>Current OTP Records:</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Email</th>
                                            <th>Code</th>
                                            <th>Expires</th>
                                            <th>Used</th>
                                            <th>Attempts</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            try {
                                                $otps = \App\Models\OtpVerification::orderBy('created_at', 'desc')->take(5)->get();
                                            } catch (\Exception $e) {
                                                $otps = collect();
                                            }
                                        @endphp
                                        
                                        @forelse($otps as $otp)
                                            <tr>
                                                <td>{{ $otp->email }}</td>
                                                <td>
                                                    <code>{{ $otp->otp_code }}</code>
                                                </td>
                                                <td>
                                                    <small class="{{ $otp->expires_at->isPast() ? 'text-danger' : 'text-success' }}">
                                                        {{ $otp->expires_at->diffForHumans() }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $otp->is_used ? 'secondary' : 'success' }}">
                                                        {{ $otp->is_used ? 'Used' : 'Active' }}
                                                    </span>
                                                </td>
                                                <td>{{ $otp->attempts }}/{{ $otp->max_attempts }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No OTP records found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="mt-4">
                            <div class="alert alert-light">
                                <h6><i class="bi bi-lightbulb me-2"></i>Testing Instructions:</h6>
                                <ol class="mb-0">
                                    <li><strong>Configure Email:</strong> Update your <code>.env</code> file with Gmail SMTP settings</li>
                                    <li><strong>Test Login:</strong> Go to admin login and enter valid credentials</li>
                                    <li><strong>Check Email:</strong> Look for the OTP code in your email inbox</li>
                                    <li><strong>Verify OTP:</strong> Enter the 6-digit code on the verification page</li>
                                    <li><strong>Access Dashboard:</strong> You should be redirected to the admin dashboard</li>
                                </ol>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="mt-4 text-center">
                            <a href="{{ route('admin.login') }}" class="btn btn-primary me-2">
                                <i class="bi bi-box-arrow-in-right me-1"></i>
                                Admin Login
                            </a>
                            <button class="btn btn-secondary me-2" onclick="clearOtps()">
                                <i class="bi bi-trash me-1"></i>
                                Clear OTPs
                            </button>
                            <button class="btn btn-info" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function testOtpPage() {
            // Simulate session for testing
            fetch('/admin/otp-verification', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.ok) {
                    window.open('/admin/otp-verification', '_blank');
                } else {
                    alert('OTP page requires an active session. Please login first.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error accessing OTP page');
            });
        }

        function sendTestEmail() {
            const email = prompt('Enter email address to send test OTP:');
            if (!email) return;

            fetch('/test-send-otp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Test OTP sent successfully! Check your email.');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error sending test email');
            });
        }

        function clearOtps() {
            if (confirm('Are you sure you want to clear all OTP records?')) {
                fetch('/test-clear-otps', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error clearing OTPs');
                });
            }
        }
    </script>
</body>
</html>
