<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Employee;
use App\Services\ProfilePictureService;

class ProfilePictureController extends Controller
{
    protected $profilePictureService;

    public function __construct(ProfilePictureService $profilePictureService)
    {
        $this->profilePictureService = $profilePictureService;
    }

    /**
     * Upload and update profile picture
     */
    public function upload(Request $request)
    {
        try {
            // Basic validation
            $validator = Validator::make($request->all(), [
                'profile_picture' => 'required|file'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No file provided'
                ], 400);
            }

            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $file = $request->file('profile_picture');
            
            // Use the service to store the file
            $result = $this->profilePictureService->store($file, $user->id);
            
            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

            // Delete old profile picture if exists
            if ($user->profile_picture_url) {
                $this->profilePictureService->delete($user->profile_picture_url);
            }
            
            // Update user profile picture URL in database
            $user->update(['profile_picture_url' => $result['relative_path']]);
            
            return response()->json([
                'success' => true,
                'message' => 'Profile picture updated successfully',
                'profile_picture_url' => $result['relative_path'],
                'full_url' => $result['url'],
                'filename' => $result['filename'],
                'size' => $result['size']
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Profile picture upload error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete profile picture
     */
    public function delete(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
            
            // Delete file if exists
            if ($user->profile_picture_url) {
                $this->profilePictureService->delete($user->profile_picture_url);
            }
            
            // Update database
            $user->update(['profile_picture_url' => null]);
            
            return response()->json([
                'success' => true,
                'message' => 'Profile picture deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Profile picture delete error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get current profile picture info
     */
    public function info(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
            
            $fileInfo = $this->profilePictureService->getFileInfo($user->profile_picture_url);
            
            return response()->json([
                'success' => true,
                'profile_picture_url' => $user->profile_picture_url,
                'full_url' => $fileInfo['url'],
                'file_exists' => $fileInfo['exists'],
                'file_size' => $fileInfo['size'],
                'file_size_human' => $fileInfo['size_human']
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
}
