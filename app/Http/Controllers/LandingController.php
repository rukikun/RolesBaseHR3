<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class LandingController extends Controller
{
    /**
     * Display the landing page with featured packages
     */
    public function index()
    {
        try {
            // Get featured packages from database
            $featured_packages = $this->getFeaturedPackages();
            
            // Get statistics for hero section
            $stats = $this->getStats();
            
            return view('landing.index', compact('featured_packages', 'stats'));
            
        } catch (Exception $e) {
            // Log error and return view with empty data
            \Log::error('Landing page error: ' . $e->getMessage());
            
            return view('landing.index', [
                'featured_packages' => [],
                'stats' => [
                    'total_packages' => '50+',
                    'happy_customers' => '1000+',
                    'destinations' => '25+'
                ]
            ]);
        }
    }
    
    /**
     * Get featured packages for the landing page
     */
    private function getFeaturedPackages($limit = 6)
    {
        try {
            // Check if packages table exists
            $tables = DB::select("SHOW TABLES LIKE 'packages'");
            if (empty($tables)) {
                return $this->getSamplePackages();
            }
            
            // Get active packages from database
            $packages = DB::select("
                SELECT id, title, description, destination, price, duration, group_size, 
                       created_at, updated_at
                FROM packages 
                WHERE status = 'active' 
                ORDER BY featured DESC, created_at DESC 
                LIMIT ?
            ", [$limit]);
            
            // Convert to array format
            $featured_packages = [];
            foreach ($packages as $package) {
                $featured_packages[] = [
                    'id' => $package->id,
                    'title' => $package->title,
                    'description' => $package->description,
                    'destination' => $package->destination,
                    'price' => $package->price,
                    'duration' => $package->duration,
                    'group_size' => $package->group_size
                ];
            }
            
            // If no packages found, return sample data
            if (empty($featured_packages)) {
                return $this->getSamplePackages();
            }
            
            return $featured_packages;
            
        } catch (Exception $e) {
            \Log::error('Error fetching featured packages: ' . $e->getMessage());
            return $this->getSamplePackages();
        }
    }
    
    /**
     * Get sample packages for demo purposes
     */
    private function getSamplePackages()
    {
        return [
            [
                'id' => 1,
                'title' => 'Bali Cultural Heritage Tour',
                'description' => 'Immerse yourself in the rich cultural heritage of Bali with visits to ancient temples, traditional villages, and local artisan workshops. Experience authentic Balinese cuisine and witness traditional dance performances.',
                'destination' => 'Bali, Indonesia',
                'price' => 1299,
                'duration' => 7,
                'group_size' => 12
            ],
            [
                'id' => 2,
                'title' => 'Maldives Beach Paradise',
                'description' => 'Escape to pristine white sand beaches and crystal-clear turquoise waters. Enjoy luxury overwater bungalows, world-class diving, and unforgettable sunset views in this tropical paradise.',
                'destination' => 'Maldives',
                'price' => 2499,
                'duration' => 5,
                'group_size' => 8
            ],
            [
                'id' => 3,
                'title' => 'Nepal Mountain Trekking Adventure',
                'description' => 'Challenge yourself with an epic mountain trekking adventure in the Himalayas. Experience breathtaking mountain vistas, local Sherpa culture, and the thrill of high-altitude hiking.',
                'destination' => 'Nepal Himalayas',
                'price' => 1899,
                'duration' => 14,
                'group_size' => 10
            ],
            [
                'id' => 4,
                'title' => 'Costa Rica Adventure Sports',
                'description' => 'Get your adrenaline pumping with zip-lining through rainforest canopies, white-water rafting, volcano hiking, and wildlife spotting in one of the world\'s most biodiverse countries.',
                'destination' => 'Costa Rica',
                'price' => 1699,
                'duration' => 10,
                'group_size' => 15
            ],
            [
                'id' => 5,
                'title' => 'Japan Cultural Discovery',
                'description' => 'Discover the perfect blend of ancient traditions and modern innovation. Visit historic temples, experience traditional tea ceremonies, explore bustling Tokyo, and witness the beauty of cherry blossoms.',
                'destination' => 'Tokyo & Kyoto, Japan',
                'price' => 2199,
                'duration' => 12,
                'group_size' => 16
            ],
            [
                'id' => 6,
                'title' => 'Iceland Northern Lights',
                'description' => 'Witness the magical Northern Lights dancing across the Arctic sky. Explore ice caves, geothermal hot springs, dramatic waterfalls, and the unique landscapes of the Land of Fire and Ice.',
                'destination' => 'Reykjavik, Iceland',
                'price' => 1799,
                'duration' => 8,
                'group_size' => 12
            ]
        ];
    }
    
    /**
     * Get statistics for the hero section
     */
    private function getStats()
    {
        try {
            $stats = [
                'total_packages' => '50+',
                'happy_customers' => '1000+',
                'destinations' => '25+'
            ];
            
            // Try to get real statistics from database
            $tables = DB::select("SHOW TABLES LIKE 'packages'");
            if (!empty($tables)) {
                $packageCount = DB::selectOne("SELECT COUNT(*) as count FROM packages WHERE status = 'active'");
                if ($packageCount && $packageCount->count > 0) {
                    $stats['total_packages'] = $packageCount->count . '+';
                }
            }
            
            // Check for bookings table
            $bookingTables = DB::select("SHOW TABLES LIKE 'bookings'");
            if (!empty($bookingTables)) {
                $customerCount = DB::selectOne("SELECT COUNT(DISTINCT customer_email) as count FROM bookings");
                if ($customerCount && $customerCount->count > 0) {
                    $stats['happy_customers'] = $customerCount->count . '+';
                }
            }
            
            return $stats;
            
        } catch (Exception $e) {
            \Log::error('Error fetching stats: ' . $e->getMessage());
            return [
                'total_packages' => '50+',
                'happy_customers' => '1000+',
                'destinations' => '25+'
            ];
        }
    }
    
    /**
     * Handle newsletter subscription
     */
    public function subscribeNewsletter(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|max:255'
            ]);
            
            $email = $request->input('email');
            
            // Check if newsletter_subscribers table exists, create if not
            $tables = DB::select("SHOW TABLES LIKE 'newsletter_subscribers'");
            if (empty($tables)) {
                DB::statement("
                    CREATE TABLE newsletter_subscribers (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        email VARCHAR(255) UNIQUE NOT NULL,
                        subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        status ENUM('active', 'unsubscribed') DEFAULT 'active',
                        INDEX idx_email (email),
                        INDEX idx_status (status)
                    )
                ");
            }
            
            // Insert or update subscription
            DB::statement("
                INSERT INTO newsletter_subscribers (email, subscribed_at, status) 
                VALUES (?, NOW(), 'active')
                ON DUPLICATE KEY UPDATE 
                status = 'active', 
                subscribed_at = NOW()
            ", [$email]);
            
            return response()->json([
                'success' => true,
                'message' => 'Successfully subscribed to newsletter!'
            ]);
            
        } catch (Exception $e) {
            \Log::error('Newsletter subscription error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Subscription failed. Please try again later.'
            ], 500);
        }
    }
    
    /**
     * Handle booking requests
     */
    public function submitBooking(Request $request)
    {
        try {
            $request->validate([
                'package_id' => 'required|integer',
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'date' => 'required|date|after:today',
                'guests' => 'required|string|max:10',
                'message' => 'nullable|string|max:1000'
            ]);
            
            // Check if booking_requests table exists, create if not
            $tables = DB::select("SHOW TABLES LIKE 'booking_requests'");
            if (empty($tables)) {
                DB::statement("
                    CREATE TABLE booking_requests (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        package_id INT,
                        customer_name VARCHAR(255) NOT NULL,
                        customer_email VARCHAR(255) NOT NULL,
                        customer_phone VARCHAR(20) NOT NULL,
                        preferred_date DATE NOT NULL,
                        number_of_guests VARCHAR(10) NOT NULL,
                        special_requests TEXT,
                        status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX idx_package_id (package_id),
                        INDEX idx_email (customer_email),
                        INDEX idx_status (status),
                        INDEX idx_date (preferred_date)
                    )
                ");
            }
            
            // Insert booking request
            DB::insert("
                INSERT INTO booking_requests 
                (package_id, customer_name, customer_email, customer_phone, 
                 preferred_date, number_of_guests, special_requests, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ", [
                $request->package_id,
                $request->name,
                $request->email,
                $request->phone,
                $request->date,
                $request->guests,
                $request->message
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Booking request submitted successfully! We will contact you soon.'
            ]);
            
        } catch (Exception $e) {
            \Log::error('Booking submission error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Booking submission failed. Please try again later.'
            ], 500);
        }
    }
}
