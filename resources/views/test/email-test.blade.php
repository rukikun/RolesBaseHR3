<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Configuration Test - HR3 System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-envelope-exclamation me-2"></i>
                            Email Configuration Diagnostics
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Email Not Working?</strong> Use this diagnostic tool to test and fix email configuration issues.
                        </div>

                        <!-- Current Configuration -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5><i class="bi bi-gear me-2"></i>Current Email Configuration</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <tr>
                                            <td><strong>Mail Driver</strong></td>
                                            <td><code>{{ config('mail.default') }}</code></td>
                                            <td>
                                                @if(config('mail.default') === 'smtp')
                                                    <span class="badge bg-success">✓ SMTP</span>
                                                @else
                                                    <span class="badge bg-warning">⚠ {{ config('mail.default') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>SMTP Host</strong></td>
                                            <td><code>{{ config('mail.mailers.smtp.host') }}</code></td>
                                            <td>
                                                @if(config('mail.mailers.smtp.host') === 'smtp.gmail.com')
                                                    <span class="badge bg-success">✓ Gmail</span>
                                                @else
                                                    <span class="badge bg-warning">⚠ Check Host</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>SMTP Port</strong></td>
                                            <td><code>{{ config('mail.mailers.smtp.port') }}</code></td>
                                            <td>
                                                @if(config('mail.mailers.smtp.port') == 587)
                                                    <span class="badge bg-success">✓ TLS Port</span>
                                                @elseif(config('mail.mailers.smtp.port') == 465)
                                                    <span class="badge bg-info">✓ SSL Port</span>
                                                @else
                                                    <span class="badge bg-warning">⚠ Check Port</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Encryption</strong></td>
                                            <td><code>{{ config('mail.mailers.smtp.encryption') ?? 'None' }}</code></td>
                                            <td>
                                                @if(config('mail.mailers.smtp.encryption') === 'tls')
                                                    <span class="badge bg-success">✓ TLS</span>
                                                @elseif(config('mail.mailers.smtp.encryption') === 'ssl')
                                                    <span class="badge bg-info">✓ SSL</span>
                                                @else
                                                    <span class="badge bg-danger">✗ No Encryption</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Username Set</strong></td>
                                            <td>{{ config('mail.mailers.smtp.username') ? 'Yes' : 'No' }}</td>
                                            <td>
                                                @if(config('mail.mailers.smtp.username'))
                                                    <span class="badge bg-success">✓ Set</span>
                                                @else
                                                    <span class="badge bg-danger">✗ Missing</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Password Set</strong></td>
                                            <td>{{ config('mail.mailers.smtp.password') ? 'Yes' : 'No' }}</td>
                                            <td>
                                                @if(config('mail.mailers.smtp.password'))
                                                    <span class="badge bg-success">✓ Set</span>
                                                @else
                                                    <span class="badge bg-danger">✗ Missing</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>From Address</strong></td>
                                            <td><code>{{ config('mail.from.address') }}</code></td>
                                            <td>
                                                @if(config('mail.from.address') && config('mail.from.address') !== 'hello@example.com')
                                                    <span class="badge bg-success">✓ Set</span>
                                                @else
                                                    <span class="badge bg-warning">⚠ Default</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Test Actions -->
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 border-primary">
                                    <div class="card-body text-center">
                                        <i class="bi bi-envelope-check text-primary" style="font-size: 2rem;"></i>
                                        <h6 class="mt-2">Test Basic Email</h6>
                                        <p class="text-muted small">Send a simple test email</p>
                                        <button class="btn btn-primary btn-sm" onclick="sendTestEmail()">
                                            <i class="bi bi-send me-1"></i>
                                            Send Test
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 border-success">
                                    <div class="card-body text-center">
                                        <i class="bi bi-shield-check text-success" style="font-size: 2rem;"></i>
                                        <h6 class="mt-2">Test OTP Email</h6>
                                        <p class="text-muted small">Send actual OTP template</p>
                                        <button class="btn btn-success btn-sm" onclick="sendOtpTest()">
                                            <i class="bi bi-key me-1"></i>
                                            Send OTP
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 border-info">
                                    <div class="card-body text-center">
                                        <i class="bi bi-bug text-info" style="font-size: 2rem;"></i>
                                        <h6 class="mt-2">Debug Mode</h6>
                                        <p class="text-muted small">Check Laravel logs</p>
                                        <button class="btn btn-info btn-sm" onclick="checkLogs()">
                                            <i class="bi bi-file-text me-1"></i>
                                            View Logs
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Email Input -->
                        <div class="mb-4">
                            <label for="testEmail" class="form-label"><strong>Test Email Address:</strong></label>
                            <input type="email" class="form-control" id="testEmail" placeholder="Enter your email address" value="">
                            <div class="form-text">Enter the email address where you want to receive test messages</div>
                        </div>

                        <!-- Configuration Guide -->
                        <div class="alert alert-info">
                            <h6><i class="bi bi-lightbulb me-2"></i>Gmail SMTP Setup Guide:</h6>
                            <ol class="mb-2">
                                <li><strong>Enable 2-Step Verification</strong> on your Gmail account</li>
                                <li><strong>Generate App Password:</strong>
                                    <ul>
                                        <li>Go to Google Account → Security → 2-Step Verification</li>
                                        <li>Scroll down to "App passwords"</li>
                                        <li>Generate password for "Mail"</li>
                                        <li>Copy the 16-character password</li>
                                    </ul>
                                </li>
                                <li><strong>Update .env file:</strong></li>
                            </ol>
                            <pre class="bg-dark text-light p-3 rounded"><code>MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-gmail@gmail.com
MAIL_PASSWORD=your-16-char-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-gmail@gmail.com"
MAIL_FROM_NAME="Jetlouge Travels Admin"</code></pre>
                        </div>

                        <!-- Test Results -->
                        <div id="testResults" class="mt-4" style="display: none;">
                            <h6>Test Results:</h6>
                            <div id="resultContent" class="alert"></div>
                        </div>

                        <!-- Common Issues -->
                        <div class="mt-4">
                            <h6><i class="bi bi-exclamation-triangle me-2"></i>Common Issues & Solutions:</h6>
                            <div class="accordion" id="troubleshootAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#issue1">
                                            "Authentication failed" Error
                                        </button>
                                    </h2>
                                    <div id="issue1" class="accordion-collapse collapse" data-bs-parent="#troubleshootAccordion">
                                        <div class="accordion-body">
                                            <ul>
                                                <li>Make sure you're using an <strong>App Password</strong>, not your regular Gmail password</li>
                                                <li>Enable 2-Step Verification first</li>
                                                <li>Double-check the 16-character app password</li>
                                                <li>Make sure MAIL_USERNAME matches MAIL_FROM_ADDRESS</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#issue2">
                                            "Connection refused" Error
                                        </button>
                                    </h2>
                                    <div id="issue2" class="accordion-collapse collapse" data-bs-parent="#troubleshootAccordion">
                                        <div class="accordion-body">
                                            <ul>
                                                <li>Check firewall settings</li>
                                                <li>Verify SMTP host: <code>smtp.gmail.com</code></li>
                                                <li>Verify port: <code>587</code> for TLS or <code>465</code> for SSL</li>
                                                <li>Check internet connection</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#issue3">
                                            Email goes to Spam/Junk
                                        </button>
                                    </h2>
                                    <div id="issue3" class="accordion-collapse collapse" data-bs-parent="#troubleshootAccordion">
                                        <div class="accordion-body">
                                            <ul>
                                                <li>Check your spam/junk folder</li>
                                                <li>Add sender to your contacts</li>
                                                <li>Mark as "Not Spam" if found in spam folder</li>
                                                <li>Consider using a dedicated email service for production</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function sendTestEmail() {
            const email = document.getElementById('testEmail').value;
            if (!email) {
                alert('Please enter an email address first');
                return;
            }

            showLoading('Sending test email...');

            fetch('/test-basic-email', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                showResult(data.success, data.message, data.details);
            })
            .catch(error => {
                showResult(false, 'Network error: ' + error.message);
            });
        }

        function sendOtpTest() {
            const email = document.getElementById('testEmail').value;
            if (!email) {
                alert('Please enter an email address first');
                return;
            }

            showLoading('Sending OTP test email...');

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
                showResult(data.success, data.message, data.details);
            })
            .catch(error => {
                showResult(false, 'Network error: ' + error.message);
            });
        }

        function checkLogs() {
            showLoading('Checking Laravel logs...');

            fetch('/test-check-logs', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                showResult(data.success, data.message, data.logs);
            })
            .catch(error => {
                showResult(false, 'Network error: ' + error.message);
            });
        }

        function showLoading(message) {
            const resultsDiv = document.getElementById('testResults');
            const contentDiv = document.getElementById('resultContent');
            
            contentDiv.className = 'alert alert-info';
            contentDiv.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>' + message;
            resultsDiv.style.display = 'block';
        }

        function showResult(success, message, details) {
            const resultsDiv = document.getElementById('testResults');
            const contentDiv = document.getElementById('resultContent');
            
            contentDiv.className = success ? 'alert alert-success' : 'alert alert-danger';
            
            let html = '<i class="bi bi-' + (success ? 'check-circle' : 'x-circle') + ' me-2"></i>';
            html += '<strong>' + (success ? 'Success!' : 'Error!') + '</strong> ' + message;
            
            if (details) {
                html += '<hr><pre style="white-space: pre-wrap; font-size: 0.9em;">' + details + '</pre>';
            }
            
            contentDiv.innerHTML = html;
            resultsDiv.style.display = 'block';
        }

        // Set default email if available
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('testEmail');
            // You can set a default email here if needed
            // emailInput.value = 'your-email@gmail.com';
        });
    </script>
</body>
</html>
