<?php
/**
 * API endpoint to fetch travel packages with pagination and filtering
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
$host = 'localhost';
$dbname = 'hr3systemdb';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get parameters
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 6;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $category = isset($_GET['category']) ? $_GET['category'] : 'all';
    
    // Validate parameters
    $limit = max(1, min(50, $limit)); // Between 1 and 50
    $offset = max(0, $offset);
    
    // Check if packages table exists
    $tableExists = $pdo->query("SHOW TABLES LIKE 'packages'")->rowCount() > 0;
    
    if (!$tableExists) {
        // Return sample data if table doesn't exist
        $samplePackages = getSamplePackages();
        $filteredPackages = filterPackagesByCategory($samplePackages, $category);
        $paginatedPackages = array_slice($filteredPackages, $offset, $limit);
        
        echo json_encode([
            'success' => true,
            'data' => $paginatedPackages,
            'pagination' => [
                'total' => count($filteredPackages),
                'limit' => $limit,
                'offset' => $offset,
                'has_more' => ($offset + $limit) < count($filteredPackages)
            ]
        ]);
        exit;
    }
    
    // Build query based on category
    $whereClause = "WHERE status = 'active'";
    $params = [];
    
    if ($category !== 'all') {
        $whereClause .= " AND (
            LOWER(title) LIKE ? OR 
            LOWER(description) LIKE ? OR 
            LOWER(destination) LIKE ?
        )";
        $categoryPattern = '%' . getCategoryKeywords($category) . '%';
        $params = [$categoryPattern, $categoryPattern, $categoryPattern];
    }
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM packages $whereClause";
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($params);
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get packages with pagination
    $query = "SELECT id, title, description, destination, price, duration, group_size, 
                     created_at, updated_at 
              FROM packages 
              $whereClause 
              ORDER BY featured DESC, created_at DESC 
              LIMIT ? OFFSET ?";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute(array_merge($params, [$limit, $offset]));
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format packages
    $formattedPackages = [];
    foreach ($packages as $package) {
        $formattedPackages[] = [
            'id' => (int)$package['id'],
            'title' => $package['title'],
            'description' => $package['description'],
            'destination' => $package['destination'],
            'price' => (float)$package['price'],
            'duration' => $package['duration'],
            'group_size' => $package['group_size']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $formattedPackages,
        'pagination' => [
            'total' => (int)$totalCount,
            'limit' => $limit,
            'offset' => $offset,
            'has_more' => ($offset + $limit) < $totalCount
        ]
    ]);
    
} catch (PDOException $e) {
    error_log('Database error in get_packages.php: ' . $e->getMessage());
    
    // Return sample data on database error
    $samplePackages = getSamplePackages();
    $filteredPackages = filterPackagesByCategory($samplePackages, $category);
    $paginatedPackages = array_slice($filteredPackages, $offset, $limit);
    
    echo json_encode([
        'success' => true,
        'data' => $paginatedPackages,
        'pagination' => [
            'total' => count($filteredPackages),
            'limit' => $limit,
            'offset' => $offset,
            'has_more' => ($offset + $limit) < count($filteredPackages)
        ]
    ]);
    
} catch (Exception $e) {
    error_log('General error in get_packages.php: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load packages',
        'error' => $e->getMessage()
    ]);
}

function getSamplePackages() {
    return [
        [
            'id' => 1,
            'title' => 'Bali Cultural Heritage Tour',
            'description' => 'Immerse yourself in the rich cultural heritage of Bali with visits to ancient temples, traditional villages, and local artisan workshops. Experience authentic Balinese cuisine and witness traditional dance performances.',
            'destination' => 'Bali, Indonesia',
            'price' => 1299,
            'duration' => '7',
            'group_size' => '12'
        ],
        [
            'id' => 2,
            'title' => 'Maldives Beach Paradise',
            'description' => 'Escape to pristine white sand beaches and crystal-clear turquoise waters. Enjoy luxury overwater bungalows, world-class diving, and unforgettable sunset views in this tropical paradise.',
            'destination' => 'Maldives',
            'price' => 2499,
            'duration' => '5',
            'group_size' => '8'
        ],
        [
            'id' => 3,
            'title' => 'Nepal Mountain Trekking Adventure',
            'description' => 'Challenge yourself with an epic mountain trekking adventure in the Himalayas. Experience breathtaking mountain vistas, local Sherpa culture, and the thrill of high-altitude hiking.',
            'destination' => 'Nepal Himalayas',
            'price' => 1899,
            'duration' => '14',
            'group_size' => '10'
        ],
        [
            'id' => 4,
            'title' => 'Costa Rica Adventure Sports',
            'description' => 'Get your adrenaline pumping with zip-lining through rainforest canopies, white-water rafting, volcano hiking, and wildlife spotting in one of the world\'s most biodiverse countries.',
            'destination' => 'Costa Rica',
            'price' => 1699,
            'duration' => '10',
            'group_size' => '15'
        ],
        [
            'id' => 5,
            'title' => 'Japan Cultural Discovery',
            'description' => 'Discover the perfect blend of ancient traditions and modern innovation. Visit historic temples, experience traditional tea ceremonies, explore bustling Tokyo, and witness the beauty of cherry blossoms.',
            'destination' => 'Tokyo & Kyoto, Japan',
            'price' => 2199,
            'duration' => '12',
            'group_size' => '16'
        ],
        [
            'id' => 6,
            'title' => 'Iceland Northern Lights',
            'description' => 'Witness the magical Northern Lights dancing across the Arctic sky. Explore ice caves, geothermal hot springs, dramatic waterfalls, and the unique landscapes of the Land of Fire and Ice.',
            'destination' => 'Reykjavik, Iceland',
            'price' => 1799,
            'duration' => '8',
            'group_size' => '12'
        ],
        [
            'id' => 7,
            'title' => 'Thailand Island Hopping',
            'description' => 'Explore the stunning islands of Thailand with crystal-clear waters, white sandy beaches, vibrant coral reefs, and delicious street food. Perfect for beach lovers and adventure seekers.',
            'destination' => 'Phuket & Phi Phi Islands, Thailand',
            'price' => 1399,
            'duration' => '9',
            'group_size' => '14'
        ],
        [
            'id' => 8,
            'title' => 'Peru Machu Picchu Trek',
            'description' => 'Embark on the classic Inca Trail to reach the ancient citadel of Machu Picchu. Experience breathtaking Andean landscapes, rich history, and the achievement of a lifetime.',
            'destination' => 'Cusco & Machu Picchu, Peru',
            'price' => 1999,
            'duration' => '11',
            'group_size' => '12'
        ]
    ];
}

function filterPackagesByCategory($packages, $category) {
    if ($category === 'all') {
        return $packages;
    }
    
    $keywords = getCategoryKeywords($category);
    $filtered = [];
    
    foreach ($packages as $package) {
        $searchText = strtolower($package['title'] . ' ' . $package['description'] . ' ' . $package['destination']);
        if (strpos($searchText, $keywords) !== false) {
            $filtered[] = $package;
        }
    }
    
    return $filtered;
}

function getCategoryKeywords($category) {
    $keywords = [
        'beach' => 'beach',
        'mountain' => 'mountain',
        'cultural' => 'cultural',
        'adventure' => 'adventure'
    ];
    
    return isset($keywords[$category]) ? $keywords[$category] : '';
}
?>
