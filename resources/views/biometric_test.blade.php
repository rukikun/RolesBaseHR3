<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Biometric Authentication Test - Jetlouge Travels</title>
  
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="{{ asset('assets/images/jetlouge_logo.png') }}">
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Login Page Styles -->
  <link rel="stylesheet" href="{{ asset('assets/css/login-style.css') }}">
</head>
<body>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h3 class="text-center mb-0" style="color: var(--jetlouge-primary);">
              <i class="bi bi-fingerprint me-2"></i>
              Biometric Authentication Test
            </h3>
          </div>
          <div class="card-body">
            
            <!-- Test Info -->
            <div class="alert alert-info">
              <h5><i class="bi bi-info-circle me-2"></i>Test Information</h5>
              <p><strong>Email:</strong> {{ session('otp_email', 'Not set') }}</p>
              <p><strong>Employee Name:</strong> {{ session('employee_name', 'Not set') }}</p>
              <p><strong>Remember Me:</strong> {{ session('remember_me') ? 'Yes' : 'No' }}</p>
            </div>

            <!-- Test Buttons -->
            <div class="row">
              <div class="col-md-6">
                <div class="card mb-3">
                  <div class="card-body text-center">
                    <h5>Check Biometric Status</h5>
                    <button class="btn btn-primary" onclick="testCheckBiometricStatus()">
                      <i class="bi bi-search me-2"></i>Check Status
                    </button>
                  </div>
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="card mb-3">
                  <div class="card-body text-center">
                    <h5>Show Biometric Modal</h5>
                    <button class="btn btn-success" onclick="testShowBiometricModal()">
                      <i class="bi bi-fingerprint me-2"></i>Show Modal
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="card mb-3">
                  <div class="card-body text-center">
                    <h5>Test Registration</h5>
                    <button class="btn btn-warning" onclick="testRegisterBiometric()">
                      <i class="bi bi-shield-plus me-2"></i>Register
                    </button>
                  </div>
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="card mb-3">
                  <div class="card-body text-center">
                    <h5>Test Verification</h5>
                    <button class="btn btn-info" onclick="testVerifyBiometric()">
                      <i class="bi bi-shield-check me-2"></i>Verify
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Debug Output -->
            <div class="card">
              <div class="card-header">
                <h5>Debug Output</h5>
                <button class="btn btn-sm btn-outline-secondary" onclick="clearDebugOutput()">Clear</button>
              </div>
              <div class="card-body">
                <pre id="debugOutput" style="max-height: 300px; overflow-y: auto; background: #f8f9fa; padding: 10px; border-radius: 5px;"></pre>
              </div>
            </div>

            <!-- Navigation -->
            <div class="text-center mt-4">
              <a href="{{ route('admin.otp.form') }}" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left me-2"></i>Back to OTP
              </a>
              <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
                <i class="bi bi-house me-2"></i>Go to Dashboard
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Biometric Authentication Modal -->
  <div class="modal fade" id="biometricModal" tabindex="-1" aria-labelledby="biometricModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0 text-center">
          <h5 class="modal-title w-100" id="biometricModalLabel" style="color: var(--jetlouge-primary); font-weight: 700;">
            <i class="bi bi-fingerprint me-2" style="font-size: 1.5rem;"></i>
            Biometric Authentication
          </h5>
        </div>
        <div class="modal-body text-center py-4">
          <div id="biometricContent">
            <!-- Registration Content -->
            <div id="biometricRegister" style="display: none;">
              <div class="mb-4">
                <i class="bi bi-shield-plus" style="font-size: 4rem; color: var(--jetlouge-primary);"></i>
              </div>
              <h6 class="mb-3">Set up Biometric Authentication</h6>
              <p class="text-muted mb-4">
                Enhance your account security by registering your fingerprint or face recognition.
                This will be required for future logins.
              </p>
              <button type="button" class="btn btn-login mb-3" onclick="registerBiometric()">
                <i class="bi bi-fingerprint me-2"></i>
                Register Biometric
              </button>
              <div class="text-center">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="skipBiometric()">
                  Skip for now
                </button>
              </div>
            </div>

            <!-- Verification Content -->
            <div id="biometricVerify" style="display: none;">
              <div class="mb-4">
                <i class="bi bi-fingerprint" style="font-size: 4rem; color: var(--jetlouge-primary);"></i>
              </div>
              <h6 class="mb-3">Verify Your Identity</h6>
              <p class="text-muted mb-4">
                Please use your registered biometric authentication to complete the login process.
              </p>
              <button type="button" class="btn btn-login mb-3" onclick="verifyBiometric()">
                <i class="bi bi-shield-check me-2"></i>
                Authenticate
              </button>
            </div>

            <!-- Loading Content -->
            <div id="biometricLoading" style="display: none;">
              <div class="mb-4">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
              </div>
              <h6 class="mb-3">Processing...</h6>
              <p class="text-muted">
                Please follow the prompts on your device to complete biometric authentication.
              </p>
            </div>

            <!-- Error Content -->
            <div id="biometricError" style="display: none;">
              <div class="mb-4">
                <i class="bi bi-exclamation-triangle" style="font-size: 4rem; color: #dc3545;"></i>
              </div>
              <h6 class="mb-3">Authentication Failed</h6>
              <p class="text-muted mb-4" id="biometricErrorMessage">
                Biometric authentication failed. Please try again.
              </p>
              <button type="button" class="btn btn-login mb-3" onclick="retryBiometric()">
                <i class="bi bi-arrow-clockwise me-2"></i>
                Try Again
              </button>
              <div class="text-center">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="skipBiometric()">
                  Skip for now
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Global variables for biometric authentication
    let currentEmployeeData = {
      employee_id: 1,
      employee_name: 'Test User'
    };
    let biometricModal = null;

    // Debug logging function
    function debugLog(message, data = null) {
      const timestamp = new Date().toLocaleTimeString();
      const debugOutput = document.getElementById('debugOutput');
      let logMessage = `[${timestamp}] ${message}`;
      
      if (data) {
        logMessage += '\n' + JSON.stringify(data, null, 2);
      }
      
      debugOutput.textContent += logMessage + '\n\n';
      debugOutput.scrollTop = debugOutput.scrollHeight;
      console.log(message, data);
    }

    // Clear debug output
    function clearDebugOutput() {
      document.getElementById('debugOutput').textContent = '';
    }

    // Test functions
    function testCheckBiometricStatus() {
      debugLog('Testing biometric status check...');
      checkBiometricStatus(1);
    }

    function testShowBiometricModal() {
      debugLog('Testing biometric modal display...');
      showBiometricModal(currentEmployeeData);
    }

    function testRegisterBiometric() {
      debugLog('Testing biometric registration...');
      showBiometricState('register');
      if (!biometricModal) {
        biometricModal = new bootstrap.Modal(document.getElementById('biometricModal'));
      }
      biometricModal.show();
    }

    function testVerifyBiometric() {
      debugLog('Testing biometric verification...');
      showBiometricState('verify');
      if (!biometricModal) {
        biometricModal = new bootstrap.Modal(document.getElementById('biometricModal'));
      }
      biometricModal.show();
    }

    // Show biometric authentication modal
    function showBiometricModal(employeeData) {
      debugLog('showBiometricModal called with:', employeeData);
      currentEmployeeData = employeeData;
      
      const modalElement = document.getElementById('biometricModal');
      if (!modalElement) {
        debugLog('ERROR: Biometric modal element not found!');
        return;
      }
      
      biometricModal = new bootstrap.Modal(modalElement);
      
      // Check if user already has biometric registered
      debugLog('Checking biometric status for employee:', employeeData.employee_id);
      checkBiometricStatus(employeeData.employee_id);
    }

    // Check biometric status
    function checkBiometricStatus(employeeId) {
      debugLog('Checking biometric status...');
      fetch('{{ route("admin.biometric.check") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          email: '{{ session("otp_email", "johnkaizer19.jh@gmail.com") }}'
        })
      })
      .then(response => {
        debugLog('Biometric status response status:', response.status);
        return response.json();
      })
      .then(data => {
        debugLog('Biometric status data:', data);
        if (data.has_biometric) {
          debugLog('User has biometric, showing verify screen');
          showBiometricState('verify');
        } else {
          debugLog('User does not have biometric, showing register screen');
          showBiometricState('register');
        }
        debugLog('Showing biometric modal...');
        if (biometricModal) {
          biometricModal.show();
        }
      })
      .catch(error => {
        debugLog('Error checking biometric status:', error);
        showBiometricState('register');
        if (biometricModal) {
          biometricModal.show();
        }
      });
    }

    // Show different biometric states
    function showBiometricState(state) {
      debugLog('Showing biometric state:', state);
      
      // Hide all states
      document.getElementById('biometricRegister').style.display = 'none';
      document.getElementById('biometricVerify').style.display = 'none';
      document.getElementById('biometricLoading').style.display = 'none';
      document.getElementById('biometricError').style.display = 'none';

      // Show requested state
      const targetElement = document.getElementById('biometric' + state.charAt(0).toUpperCase() + state.slice(1));
      if (targetElement) {
        targetElement.style.display = 'block';
        debugLog('Successfully showed state:', state);
      } else {
        debugLog('ERROR: Could not find element for state:', state);
      }
    }

    // Register biometric authentication
    async function registerBiometric() {
      debugLog('Starting biometric registration...');
      
      if (!navigator.credentials || !window.PublicKeyCredential) {
        debugLog('ERROR: Biometric authentication not supported');
        showBiometricError('Biometric authentication is not supported on this device or browser.');
        return;
      }

      showBiometricState('loading');

      try {
        debugLog('Creating credential options...');
        
        // Create credential options
        const credentialCreationOptions = {
          publicKey: {
            challenge: new Uint8Array(32),
            rp: {
              name: "Jetlouge Travels HR System",
              id: window.location.hostname === '127.0.0.1' ? 'localhost' : window.location.hostname,
            },
            user: {
              id: new TextEncoder().encode(currentEmployeeData.employee_id.toString()),
              name: '{{ session("otp_email", "johnkaizer19.jh@gmail.com") }}',
              displayName: currentEmployeeData.employee_name,
            },
            pubKeyCredParams: [{alg: -7, type: "public-key"}],
            authenticatorSelection: {
              authenticatorAttachment: "platform",
              userVerification: "required"
            },
            timeout: 60000,
            attestation: "direct"
          }
        };

        debugLog('Calling navigator.credentials.create...');
        
        // Create credential
        const credential = await navigator.credentials.create(credentialCreationOptions);
        
        debugLog('Credential created successfully:', credential);
        
        // Convert credential data for server
        const credentialData = {
          email: '{{ session("otp_email", "johnkaizer19.jh@gmail.com") }}',
          credential_id: btoa(String.fromCharCode(...new Uint8Array(credential.rawId))),
          public_key: btoa(String.fromCharCode(...new Uint8Array(credential.response.getPublicKey()))),
          authenticator_data: {
            clientDataJSON: btoa(String.fromCharCode(...new Uint8Array(credential.response.clientDataJSON))),
            attestationObject: btoa(String.fromCharCode(...new Uint8Array(credential.response.attestationObject)))
          },
          device_name: navigator.userAgent.includes('Mobile') ? 'Mobile Device' : 'Desktop'
        };

        debugLog('Sending credential data to server:', credentialData);

        // Send to server
        const response = await fetch('{{ route("admin.biometric.register") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
          },
          body: JSON.stringify(credentialData)
        });

        const result = await response.json();
        debugLog('Server response:', result);

        if (result.success) {
          debugLog('Registration successful, switching to verify screen');
          showBiometricState('verify');
        } else {
          debugLog('Registration failed:', result.error);
          showBiometricError(result.error || 'Failed to register biometric authentication');
        }

      } catch (error) {
        debugLog('Biometric registration error:', error);
        showBiometricError('Biometric registration failed: ' + error.message);
      }
    }

    // Verify biometric authentication
    async function verifyBiometric() {
      debugLog('Starting biometric verification...');
      
      if (!navigator.credentials || !window.PublicKeyCredential) {
        debugLog('ERROR: Biometric authentication not supported');
        showBiometricError('Biometric authentication is not supported on this device or browser.');
        return;
      }

      showBiometricState('loading');

      try {
        debugLog('Creating assertion options...');
        
        // Create assertion options
        const credentialRequestOptions = {
          publicKey: {
            challenge: new Uint8Array(32),
            timeout: 60000,
            userVerification: "required"
          }
        };

        debugLog('Calling navigator.credentials.get...');
        
        // Get credential
        const assertion = await navigator.credentials.get(credentialRequestOptions);
        
        debugLog('Assertion created successfully:', assertion);
        
        // Convert assertion data for server
        const assertionData = {
          email: '{{ session("otp_email", "johnkaizer19.jh@gmail.com") }}',
          credential_id: btoa(String.fromCharCode(...new Uint8Array(assertion.rawId))),
          authenticator_data: btoa(String.fromCharCode(...new Uint8Array(assertion.response.authenticatorData))),
          signature: btoa(String.fromCharCode(...new Uint8Array(assertion.response.signature)))
        };

        debugLog('Sending assertion data to server:', assertionData);

        // Send to server for verification
        const response = await fetch('{{ route("admin.biometric.verify") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
          },
          body: JSON.stringify(assertionData)
        });

        const result = await response.json();
        debugLog('Server response:', result);

        if (result.success) {
          debugLog('Verification successful, redirecting...');
          window.location.href = result.redirect_url || '{{ route("dashboard") }}';
        } else {
          debugLog('Verification failed:', result.error);
          showBiometricError(result.error || 'Biometric verification failed');
        }

      } catch (error) {
        debugLog('Biometric verification error:', error);
        showBiometricError('Biometric verification failed: ' + error.message);
      }
    }

    // Retry biometric authentication
    function retryBiometric() {
      debugLog('Retrying biometric authentication...');
      checkBiometricStatus(currentEmployeeData.employee_id);
    }

    // Skip biometric authentication
    function skipBiometric() {
      debugLog('Skipping biometric authentication...');
      window.location.href = '{{ route("dashboard") }}';
    }

    // Show biometric error
    function showBiometricError(message) {
      debugLog('Showing biometric error:', message);
      document.getElementById('biometricErrorMessage').textContent = message;
      showBiometricState('error');
    }

    // Initialize debug output
    document.addEventListener('DOMContentLoaded', function() {
      debugLog('Biometric test page loaded');
      debugLog('Session data:', {
        otp_email: '{{ session("otp_email", "Not set") }}',
        employee_name: '{{ session("employee_name", "Not set") }}',
        remember_me: {{ session('remember_me') ? 'true' : 'false' }}
      });
    });
  </script>
</body>
</html>
