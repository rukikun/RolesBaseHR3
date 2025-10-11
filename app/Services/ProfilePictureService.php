<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class ProfilePictureService
{
    private $uploadPath;
    private $maxFileSize;
    private $allowedMimeTypes;

    public function __construct()
    {
        $this->uploadPath = 'uploads/profile_pictures';
        $this->maxFileSize = 2 * 1024 * 1024; // 2MB
        $this->allowedMimeTypes = [
            'image/jpeg',
            'image/jpg', 
            'image/png',
            'image/gif',
            'image/webp'
        ];
    }

    /**
     * Store uploaded profile picture
     */
    public function store(UploadedFile $file, $userId): array
    {
        try {
            // Validate file
            $validation = $this->validateFile($file);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message']
                ];
            }

            // Create upload directory if it doesn't exist
            $fullUploadPath = public_path($this->uploadPath);
            if (!file_exists($fullUploadPath)) {
                if (!mkdir($fullUploadPath, 0755, true)) {
                    return [
                        'success' => false,
                        'message' => 'Failed to create upload directory'
                    ];
                }
            }

            // Generate unique filename
            $extension = $file->getClientOriginalExtension();
            $filename = 'profile_' . $userId . '_' . time() . '_' . Str::random(8) . '.' . $extension;
            $relativePath = $this->uploadPath . '/' . $filename;
            $absolutePath = public_path($relativePath);

            // Move file to destination
            if ($file->move($fullUploadPath, $filename)) {
                // Verify file was actually saved
                if (file_exists($absolutePath)) {
                    // Set proper permissions
                    chmod($absolutePath, 0644);
                    
                    return [
                        'success' => true,
                        'filename' => $filename,
                        'relative_path' => $relativePath,
                        'absolute_path' => $absolutePath,
                        'url' => asset($relativePath),
                        'size' => filesize($absolutePath)
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'File upload completed but file not found on server'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to move uploaded file to destination'
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Upload error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete profile picture file
     */
    public function delete($filePath): array
    {
        try {
            if (!$filePath) {
                return [
                    'success' => true,
                    'message' => 'No file to delete'
                ];
            }

            // Handle both relative and absolute paths
            if (strpos($filePath, public_path()) === 0) {
                $absolutePath = $filePath;
            } else {
                $absolutePath = public_path($filePath);
            }

            if (file_exists($absolutePath)) {
                if (unlink($absolutePath)) {
                    return [
                        'success' => true,
                        'message' => 'File deleted successfully'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Failed to delete file'
                    ];
                }
            } else {
                return [
                    'success' => true,
                    'message' => 'File does not exist (already deleted)'
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Delete error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get file information
     */
    public function getFileInfo($filePath): array
    {
        try {
            if (!$filePath) {
                return [
                    'exists' => false,
                    'size' => 0,
                    'url' => null
                ];
            }

            // Handle both relative and absolute paths
            if (strpos($filePath, public_path()) === 0) {
                $absolutePath = $filePath;
                $relativePath = str_replace(public_path() . '/', '', $filePath);
            } else {
                $absolutePath = public_path($filePath);
                $relativePath = $filePath;
            }

            $exists = file_exists($absolutePath);
            $size = $exists ? filesize($absolutePath) : 0;
            $url = $exists ? asset($relativePath) : null;

            return [
                'exists' => $exists,
                'size' => $size,
                'size_human' => $this->formatBytes($size),
                'url' => $url,
                'absolute_path' => $absolutePath,
                'relative_path' => $relativePath
            ];

        } catch (Exception $e) {
            return [
                'exists' => false,
                'size' => 0,
                'url' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate uploaded file
     */
    private function validateFile(UploadedFile $file): array
    {
        // Check if file was uploaded successfully
        if (!$file->isValid()) {
            return [
                'valid' => false,
                'message' => 'File upload failed: ' . $file->getErrorMessage()
            ];
        }

        // Check file size
        if ($file->getSize() > $this->maxFileSize) {
            return [
                'valid' => false,
                'message' => 'File too large. Maximum size is ' . $this->formatBytes($this->maxFileSize)
            ];
        }

        // Check MIME type
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            return [
                'valid' => false,
                'message' => 'Invalid file type. Allowed types: JPG, PNG, GIF, WEBP'
            ];
        }

        // Check if it's actually an image
        $imageInfo = getimagesize($file->getPathname());
        if ($imageInfo === false) {
            return [
                'valid' => false,
                'message' => 'File is not a valid image'
            ];
        }

        // Check image dimensions (minimum 100x100, maximum 2000x2000)
        list($width, $height) = $imageInfo;
        if ($width < 100 || $height < 100) {
            return [
                'valid' => false,
                'message' => 'Image too small. Minimum size is 100x100 pixels'
            ];
        }

        if ($width > 2000 || $height > 2000) {
            return [
                'valid' => false,
                'message' => 'Image too large. Maximum size is 2000x2000 pixels'
            ];
        }

        return [
            'valid' => true,
            'message' => 'File is valid'
        ];
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        if ($bytes == 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $base = log($bytes, 1024);
        $pow = floor($base);
        
        return round(pow(1024, $base - $pow), $precision) . ' ' . $units[$pow];
    }

    /**
     * Create upload directory structure
     */
    public function createUploadDirectories(): array
    {
        try {
            $directories = [
                public_path('uploads'),
                public_path($this->uploadPath)
            ];

            $results = [];
            foreach ($directories as $dir) {
                if (!file_exists($dir)) {
                    if (mkdir($dir, 0755, true)) {
                        chmod($dir, 0755);
                        $results[] = "Created: $dir";
                    } else {
                        $results[] = "Failed to create: $dir";
                    }
                } else {
                    $results[] = "Exists: $dir";
                }
            }

            return [
                'success' => true,
                'results' => $results
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Directory creation error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test file operations
     */
    public function testFileOperations(): array
    {
        try {
            $testFile = public_path($this->uploadPath . '/test_write.txt');
            $testContent = 'Test file created at ' . date('Y-m-d H:i:s');

            // Test write
            if (file_put_contents($testFile, $testContent) === false) {
                return [
                    'success' => false,
                    'message' => 'Cannot write to upload directory'
                ];
            }

            // Test read
            $readContent = file_get_contents($testFile);
            if ($readContent !== $testContent) {
                unlink($testFile);
                return [
                    'success' => false,
                    'message' => 'Cannot read from upload directory'
                ];
            }

            // Test delete
            if (!unlink($testFile)) {
                return [
                    'success' => false,
                    'message' => 'Cannot delete from upload directory'
                ];
            }

            return [
                'success' => true,
                'message' => 'All file operations working correctly'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'File operation test error: ' . $e->getMessage()
            ];
        }
    }
}
