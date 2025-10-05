<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jetlouge Travels - Discover Amazing Adventures</title>
    <meta name="description" content="Discover amazing travel packages with Jetlouge Travels. From beach getaways to mountain adventures, cultural tours to thrilling experiences.">
    <meta name="keywords" content="travel, tours, packages, adventure, beach, mountain, cultural, Jetlouge">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/favicon.ico') }}">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/landing-style.css') }}">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo">
                <span class="logo-text">Jetlouge Travels</span>
            </div>
            
            <ul class="nav-menu">
                <li><a href="#home" class="nav-link">Home</a></li>
                <li><a href="#packages" class="nav-link">Packages</a></li>
                <li><a href="#features" class="nav-link">Features</a></li>
                <li><a href="#testimonials" class="nav-link">Reviews</a></li>
                <li><a href="#contact" class="nav-link">Contact</a></li>
                <li><a href="{{ route('portal.selection') }}" class="nav-link btn-portal">Portal Access</a></li>
            </ul>
            
            <div class="nav-toggle">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title">Discover Amazing Adventures</h1>
            <p class="hero-subtitle">
                Explore the world with Jetlouge Travels. From pristine beaches to majestic mountains, 
                cultural heritage sites to thrilling adventures - your perfect journey awaits.
            </p>
            <div class="hero-buttons">
                <a href="#packages" class="btn btn-primary">
                    <i class="fas fa-compass"></i>
                    Explore Packages
                </a>
                <a href="#features" class="btn btn-secondary">
                    <i class="fas fa-play"></i>
                    Learn More
                </a>
            </div>
        </div>
        
        <div class="hero-stats">
            <div class="stat-item">
                <span class="stat-number">{{ $stats['total_packages'] ?? '50+' }}</span>
                <span class="stat-label">Travel Packages</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">{{ $stats['happy_customers'] ?? '1000+' }}</span>
                <span class="stat-label">Happy Travelers</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">{{ $stats['destinations'] ?? '25+' }}</span>
                <span class="stat-label">Destinations</span>
            </div>
        </div>
    </section>

    <!-- Featured Packages Section -->
    <section id="packages" class="packages-section">
        <div class="packages-header">
            <div class="container">
                <h2 class="page-title">Featured Travel Packages</h2>
                <p class="page-subtitle">
                    Handpicked destinations and experiences crafted for unforgettable memories
                </p>
            </div>
        </div>
        
        <div class="container">
            <!-- Package Categories Filter -->
            <div class="category-filters" style="text-align: center; margin: 3rem 0;">
                <button class="btn btn-outline category-btn active" data-category="all">All Packages</button>
                <button class="btn btn-outline category-btn" data-category="beach">Beach</button>
                <button class="btn btn-outline category-btn" data-category="mountain">Mountain</button>
                <button class="btn btn-outline category-btn" data-category="cultural">Cultural</button>
                <button class="btn btn-outline category-btn" data-category="adventure">Adventure</button>
            </div>
            
            <!-- Search Bar -->
            <div class="search-container" style="max-width: 500px; margin: 0 auto 3rem;">
                <div class="form-group">
                    <input type="text" id="package-search" placeholder="Search packages by destination, activity, or keyword..." 
                           style="width: 100%; padding: 1rem; border: 2px solid #e2e8f0; border-radius: 25px; font-size: 1rem;">
                </div>
            </div>
            
            <!-- Packages Grid -->
            <div id="packages-grid" class="packages-grid">
                @if(isset($featured_packages) && count($featured_packages) > 0)
                    @foreach($featured_packages as $package)
                        <div class="package-card">
                            <div class="package-content">
                                <h3 class="package-title">{{ $package['title'] }}</h3>
                                <div class="package-destination">
                                    <i class="fas fa-map-marker-alt"></i>
                                    {{ $package['destination'] }}
                                </div>
                                <p class="package-description">
                                    {{ Str::limit($package['description'], 120) }}
                                </p>
                                
                                @php
                                    $features = [];
                                    if (!empty($package['duration'])) $features[] = $package['duration'] . ' days';
                                    if (!empty($package['group_size'])) $features[] = 'Max ' . $package['group_size'] . ' people';
                                    
                                    // Determine category
                                    $searchText = strtolower($package['title'] . ' ' . $package['description'] . ' ' . $package['destination']);
                                    if (str_contains($searchText, 'beach') || str_contains($searchText, 'island')) {
                                        $features[] = 'Beach';
                                    } elseif (str_contains($searchText, 'mountain') || str_contains($searchText, 'hiking')) {
                                        $features[] = 'Mountain';
                                    } elseif (str_contains($searchText, 'heritage') || str_contains($searchText, 'cultural')) {
                                        $features[] = 'Cultural';
                                    } elseif (str_contains($searchText, 'adventure') || str_contains($searchText, 'surfing')) {
                                        $features[] = 'Adventure';
                                    }
                                @endphp
                                
                                @if(count($features) > 0)
                                    <div class="package-features">
                                        @foreach($features as $feature)
                                            <span class="feature-tag">{{ $feature }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                
                                <div class="package-footer">
                                    <div class="package-info">
                                        <div class="package-price">${{ number_format($package['price']) }}</div>
                                        @if(!empty($package['duration']))
                                            <div class="package-duration">{{ $package['duration'] }} days</div>
                                        @endif
                                    </div>
                                    <button class="book-btn" onclick="openBookingModal({{ $package['id'] }})">
                                        <i class="fas fa-calendar-check"></i>
                                        Book Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="loading-spinner">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>Loading amazing packages...</p>
                    </div>
                @endif
            </div>
            
            <!-- Load More Button -->
            <div style="text-align: center; margin-top: 3rem;">
                <button id="load-more-btn" class="btn btn-primary" style="display: none;">
                    <i class="fas fa-plus"></i>
                    Load More Packages
                </button>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Why Choose Jetlouge Travels?</h2>
                <p class="section-subtitle">
                    We provide exceptional travel experiences with personalized service and attention to detail
                </p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="feature-title">Safe & Secure</h3>
                    <p class="feature-description">
                        Your safety is our priority. All our tours are fully insured with 24/7 support and emergency assistance.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="feature-title">Expert Guides</h3>
                    <p class="feature-description">
                        Local expert guides with deep knowledge of destinations, culture, and hidden gems you won't find elsewhere.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 class="feature-title">Personalized Experience</h3>
                    <p class="feature-description">
                        Customized itineraries tailored to your preferences, interests, and travel style for unforgettable memories.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h3 class="feature-title">Best Value</h3>
                    <p class="feature-description">
                        Competitive pricing with no hidden fees. Get the best value for premium travel experiences and services.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">What Our Travelers Say</h2>
                <p class="section-subtitle">
                    Real experiences from real travelers who have journeyed with us
                </p>
            </div>
            
            <div class="testimonials-slider">
                <div class="testimonial-card active">
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">
                        "An absolutely incredible experience! The Bali cultural tour exceeded all expectations. 
                        Our guide was knowledgeable and the itinerary was perfectly planned. Highly recommended!"
                    </p>
                    <div class="author-name">Sarah Johnson</div>
                    <div class="author-location">New York, USA</div>
                </div>
                
                <div class="testimonial-card">
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">
                        "The mountain trekking adventure in Nepal was life-changing. Professional guides, 
                        excellent safety measures, and breathtaking views. Worth every penny!"
                    </p>
                    <div class="author-name">Michael Chen</div>
                    <div class="author-location">Toronto, Canada</div>
                </div>
                
                <div class="testimonial-card">
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">
                        "Perfect beach getaway in Maldives! Everything was organized flawlessly. 
                        The resort, activities, and transfers were all top-notch. Will definitely book again!"
                    </p>
                    <div class="author-name">Emma Rodriguez</div>
                    <div class="author-location">Madrid, Spain</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-content">
                <div>
                    <h3 class="newsletter-title">Stay Updated</h3>
                    <p class="newsletter-subtitle">Get the latest travel deals and destination guides</p>
                </div>
                <form id="newsletter-form" class="newsletter-form">
                    @csrf
                    <input type="email" id="newsletter-email" class="newsletter-input" 
                           placeholder="Enter your email address" required>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i>
                        Subscribe
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="footer">
        <div class="container">
            <div class="footer-content">
                <div>
                    <div class="footer-logo">
                        <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo">
                        <span class="logo-text">Jetlouge Travels</span>
                    </div>
                    <p class="footer-description">
                        Creating unforgettable travel experiences with personalized service, 
                        expert guides, and carefully curated destinations around the world.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div>
                    <h4 class="footer-title">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#packages">Packages</a></li>
                        <li><a href="#features">About Us</a></li>
                        <li><a href="#testimonials">Reviews</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="footer-title">Destinations</h4>
                    <ul class="footer-links">
                        <li><a href="#">Beach Destinations</a></li>
                        <li><a href="#">Mountain Adventures</a></li>
                        <li><a href="#">Cultural Tours</a></li>
                        <li><a href="#">Adventure Sports</a></li>
                        <li><a href="#">City Breaks</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="footer-title">Contact Info</h4>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>123 Travel Street, Adventure City, AC 12345</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>+1 (555) 123-4567</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>info@jetlougetravels.com</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <span>Mon - Fri: 9:00 AM - 6:00 PM</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} Jetlouge Travels. All rights reserved. | Privacy Policy | Terms of Service</p>
            </div>
        </div>
    </footer>

    <!-- Login Modal -->
    <div id="login-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-circle"></i> Employee Portal Login</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="login-options">
                    <p class="login-subtitle">Choose your login method</p>
                    
                    <div class="login-option">
                        <button class="login-btn gmail-btn" id="gmail-login">
                            <i class="fab fa-google"></i>
                            Continue with Gmail
                        </button>
                    </div>
                    
                    <div class="login-option">
                        <button class="login-btn phone-btn" id="phone-login">
                            <i class="fas fa-mobile-alt"></i>
                            Login with Phone Number
                        </button>
                    </div>
                    
                    <div id="phone-form" class="phone-form" style="display: none;">
                        <div class="form-group">
                            <label for="phone-number">Phone Number</label>
                            <div class="phone-input-group">
                                <select class="country-select">
                                    <option value="+1">🇺🇸 +1</option>
                                    <option value="+44">🇬🇧 +44</option>
                                    <option value="+91">🇮🇳 +91</option>
                                    <option value="+86">🇨🇳 +86</option>
                                </select>
                                <input type="tel" id="phone-number" placeholder="Enter your phone number" required>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" id="send-otp" style="width: 100%;">
                            <i class="fas fa-paper-plane"></i>
                            Send OTP
                        </button>
                        
                        <div id="otp-verification" style="display: none;">
                            <div class="form-group">
                                <label for="otp-code">Enter OTP Code</label>
                                <input type="text" id="otp-code" placeholder="6-digit code" maxlength="6" required>
                            </div>
                            <div class="otp-actions">
                                <button type="button" class="btn btn-primary" id="verify-otp">
                                    <i class="fas fa-check"></i>
                                    Verify OTP
                                </button>
                                <button type="button" class="btn btn-outline" onclick="sendOTP()">
                                    <i class="fas fa-redo"></i>
                                    Resend
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="login-divider">
                        <span>or</span>
                    </div>
                    
                    <div class="guest-option">
                        <p class="guest-text">Just browsing? Continue as guest</p>
                        <button class="guest-btn" id="guest-login">
                            <i class="fas fa-user"></i>
                            Continue as Guest
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="{{ asset('assets/js/landing-script.js') }}"></script>
</body>
</html>
