<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Profile Picture Upload - Jetlouge Travels</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .profile-upload-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .current-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin: 0 auto 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 4px solid #e9ecef;
            overflow: hidden;
            position: relative;
        }
        
        .current-picture img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .initials-display {
            font-size: 3rem;
            font-weight: bold;
            color: white;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .upload-zone {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .upload-zone:hover {
            border-color: #667eea;
            background-color: #f8f9ff;
        }
        
        .upload-zone.dragover {
            border-color: #667eea;
            background-color: #f0f2ff;
        }
        
        .preview-container {
            display: none;
            margin-top: 1rem;
        }
        
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        
        .upload-progress {
            display: none;
            margin-top: 1rem;
        }
        
        .btn-jetlouge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-jetlouge:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .alert-custom {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body style="background-color: #f8f9fa;">
    <div class="container-fluid">
        <div class="profile-upload-container">
            <!-- Header -->
            <div class="text-center mb-4">
                <h2 class="fw-bold text-dark">
                    <i class="bi bi-person-circle me-2"></i>
                    Update Profile Picture
                </h2>
                <p class="text-muted">Upload a new profile picture for {{ Auth::user()->name ?? 'your account' }}</p>
            </div>

            <!-- Current Profile Picture -->
            <div class="text-center mb-4">
                <div class="current-picture" id="currentPicture">
                    @if(Auth::user()->profile_picture_url && file_exists(public_path(Auth::user()->profile_picture_url)))
                        <img src="{{ asset(Auth::user()->profile_picture_url) }}" alt="Current Profile Picture">
                    @else
                        <div class="initials-display">
                            {{ Auth::user()->initials ?? strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                        </div>
                    @endif
                </div>
                <small class="text-muted">Current Profile Picture</small>
            </div>

            <!-- Upload Form -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <!-- Upload Zone -->
                    <div class="upload-zone" id="uploadZone">
                        <i class="bi bi-cloud-upload fs-1 text-muted mb-3"></i>
                        <h5 class="text-muted mb-2">Drag & Drop or Click to Upload</h5>
                        <p class="text-muted small mb-3">
                            Supported formats: JPG, PNG, GIF, WEBP<br>
                            Maximum size: 2MB
                        </p>
                        <input type="file" id="fileInput" accept="image/*" style="display: none;">
                        <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('fileInput').click()">
                            <i class="bi bi-folder2-open me-2"></i>Choose File
                        </button>
                    </div>

                    <!-- Preview Container -->
                    <div class="preview-container text-center" id="previewContainer">
                        <h6 class="text-muted mb-2">Preview:</h6>
                        <img id="previewImage" class="preview-image" alt="Preview">
                        <div class="mt-2">
                            <span id="fileName" class="text-muted small"></span>
                            <span id="fileSize" class="text-muted small ms-2"></span>
                        </div>
                    </div>

                    <!-- Upload Progress -->
                    <div class="upload-progress" id="uploadProgress">
                        <div class="progress mb-2">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 0%" id="progressBar"></div>
                        </div>
                        <small class="text-muted" id="progressText">Uploading...</small>
                    </div>

                    <!-- Action Buttons -->
                    <div class="text-center mt-4" id="actionButtons" style="display: none;">
                        <button type="button" class="btn btn-jetlouge me-2" id="uploadBtn">
                            <i class="bi bi-upload me-2"></i>Upload Picture
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="cancelBtn">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </button>
                    </div>

                    <!-- Delete Button -->
                    @if(Auth::user()->profile_picture_url)
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-outline-danger btn-sm" id="deleteBtn">
                            <i class="bi bi-trash me-2"></i>Remove Current Picture
                        </button>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Results -->
            <div id="results" class="mt-4"></div>

            <!-- Back Button -->
            <div class="text-center mt-4">
                <a href="{{ route('admin.profile.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Profile
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // DOM elements
        const uploadZone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('fileInput');
        const previewContainer = document.getElementById('previewContainer');
        const previewImage = document.getElementById('previewImage');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const actionButtons = document.getElementById('actionButtons');
        const uploadBtn = document.getElementById('uploadBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const deleteBtn = document.getElementById('deleteBtn');
        const uploadProgress = document.getElementById('uploadProgress');
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const currentPicture = document.getElementById('currentPicture');
        
        let selectedFile = null;

        // File input change handler
        fileInput.addEventListener('change', function(e) {
            handleFileSelect(e.target.files[0]);
        });

        // Drag and drop handlers
        uploadZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });

        uploadZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
        });

        uploadZone.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFileSelect(files[0]);
            }
        });

        // Upload zone click handler
        uploadZone.addEventListener('click', function() {
            fileInput.click();
        });

        // Handle file selection
        function handleFileSelect(file) {
            if (!file) return;

            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                showResult('❌ Invalid file type. Please select a JPG, PNG, GIF, or WEBP image.', 'danger');
                return;
            }

            // Validate file size (2MB = 2 * 1024 * 1024 bytes)
            const maxSize = 2 * 1024 * 1024;
            if (file.size > maxSize) {
                showResult('❌ File too large. Maximum size is 2MB.', 'danger');
                return;
            }

            selectedFile = file;

            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                fileName.textContent = file.name;
                fileSize.textContent = `(${formatFileSize(file.size)})`;
                previewContainer.style.display = 'block';
                actionButtons.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }

        // Upload button handler
        uploadBtn.addEventListener('click', function() {
            if (!selectedFile) {
                showResult('❌ Please select a file first.', 'danger');
                return;
            }

            uploadFile(selectedFile);
        });

        // Cancel button handler
        cancelBtn.addEventListener('click', function() {
            resetForm();
        });

        // Delete button handler
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to remove your current profile picture?')) {
                    deleteProfilePicture();
                }
            });
        }

        // Upload file function
        function uploadFile(file) {
            const formData = new FormData();
            formData.append('profile_picture', file);

            // Show progress
            uploadProgress.style.display = 'block';
            actionButtons.style.display = 'none';
            progressBar.style.width = '0%';
            progressText.textContent = 'Uploading...';

            // Create XMLHttpRequest for progress tracking
            const xhr = new XMLHttpRequest();

            // Progress handler
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    progressBar.style.width = percentComplete + '%';
                    progressText.textContent = `Uploading... ${Math.round(percentComplete)}%`;
                }
            });

            // Success handler
            xhr.addEventListener('load', function() {
                uploadProgress.style.display = 'none';
                
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            showResult('✅ Profile picture updated successfully!', 'success');
                            updateCurrentPicture(response.full_url);
                            resetForm();
                            
                            // Show delete button if it wasn't there before
                            if (!deleteBtn) {
                                location.reload();
                            }
                        } else {
                            showResult('❌ Upload failed: ' + response.message, 'danger');
                            actionButtons.style.display = 'block';
                        }
                    } catch (e) {
                        showResult('❌ Invalid response from server.', 'danger');
                        actionButtons.style.display = 'block';
                    }
                } else {
                    showResult('❌ Upload failed. Server error: ' + xhr.status, 'danger');
                    actionButtons.style.display = 'block';
                }
            });

            // Error handler
            xhr.addEventListener('error', function() {
                uploadProgress.style.display = 'none';
                showResult('❌ Network error occurred during upload.', 'danger');
                actionButtons.style.display = 'block';
            });

            // Send request
            xhr.open('POST', '/api/profile-picture/upload');
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
            xhr.send(formData);
        }

        // Delete profile picture function
        function deleteProfilePicture() {
            fetch('/api/profile-picture/delete', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showResult('✅ Profile picture removed successfully!', 'success');
                    resetCurrentPicture();
                    if (deleteBtn) {
                        deleteBtn.style.display = 'none';
                    }
                } else {
                    showResult('❌ Failed to remove picture: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                showResult('❌ Network error: ' + error.message, 'danger');
            });
        }

        // Helper functions
        function resetForm() {
            selectedFile = null;
            fileInput.value = '';
            previewContainer.style.display = 'none';
            actionButtons.style.display = 'none';
            uploadProgress.style.display = 'none';
        }

        function updateCurrentPicture(url) {
            currentPicture.innerHTML = `<img src="${url}" alt="Current Profile Picture">`;
        }

        function resetCurrentPicture() {
            const initials = '{{ Auth::user()->initials ?? strtoupper(substr(Auth::user()->name ?? "U", 0, 2)) }}';
            currentPicture.innerHTML = `<div class="initials-display">${initials}</div>`;
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function showResult(message, type) {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = `
                <div class="alert alert-${type} alert-custom alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Auto-dismiss success messages after 5 seconds
            if (type === 'success') {
                setTimeout(() => {
                    const alert = resultsDiv.querySelector('.alert');
                    if (alert) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                }, 5000);
            }
        }
    </script>
</body>
</html>
