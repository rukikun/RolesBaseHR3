/* =============================================
   JETLOUGE TRAVELS LANDING PAGE SCRIPTS
   Consolidated and Optimized JavaScript
   ============================================= */

// Global Variables
let currentOffset = 0;
let currentCategory = 'all';
let hasMorePackages = true;
let isLoading = false;

// DOM Content Loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeNavigation();
    initializePackages();
    initializeModals();
    initializeTestimonials();
    initializeNewsletter();
    initializeAnimations();
    initializeScrollEffects();
});

/* =============================================
   NAVIGATION FUNCTIONALITY
   ============================================= */

function initializeNavigation() {
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');
    const navbar = document.querySelector('.navbar');
    
    // Mobile menu toggle
    if (navToggle) {
        navToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            
            // Animate hamburger menu
            const bars = navToggle.querySelectorAll('.bar');
            bars.forEach((bar, index) => {
                bar.style.transform = navMenu.classList.contains('active') 
                    ? `rotate(${index === 1 ? 0 : index === 0 ? 45 : -45}deg) translate(${index === 1 ? 0 : index === 0 ? 5 : -5}px, ${index === 1 ? 0 : index === 0 ? 5 : -5}px)`
                    : 'none';
            });
        });
    }
    
    // Close mobile menu when clicking on links
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', () => {
            if (navMenu.classList.contains('active')) {
                navMenu.classList.remove('active');
            }
        });
    });
    
    // Navbar scroll effect
    window.addEventListener('scroll', () => {
        if (window.scrollY > 100) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/* =============================================
   PACKAGES FUNCTIONALITY
   ============================================= */

function initializePackages() {
    // Load initial packages
    loadPackages(true);
    
    // Category filter buttons
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            // Update active button
            document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            // Update category and reload packages
            currentCategory = btn.dataset.category || 'all';
            loadPackages(true);
        });
    });
    
    // Load more button
    const loadMoreBtn = document.getElementById('load-more-btn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', () => loadPackages(false));
    }
    
    // Search functionality
    const searchInput = document.getElementById('package-search');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchPackages(e.target.value);
            }, 300);
        });
    }
}

async function loadPackages(reset = false) {
    if (isLoading) return;
    
    isLoading = true;
    const packagesGrid = document.getElementById('packages-grid');
    
    if (reset) {
        currentOffset = 0;
        packagesGrid.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i><p>Loading amazing packages...</p></div>';
    }
    
    try {
        const response = await fetch(`/api/get_packages.php?limit=6&offset=${currentOffset}&category=${currentCategory}`);
        const data = await response.json();
        
        if (data.success) {
            if (reset) {
                packagesGrid.innerHTML = '';
            } else {
                // Remove loading spinner if it exists
                const loadingSpinner = packagesGrid.querySelector('.loading-spinner');
                if (loadingSpinner) {
                    loadingSpinner.remove();
                }
            }
            
            // Add packages to grid
            data.data.forEach((package, index) => {
                const packageCard = createPackageCard(package);
                packagesGrid.appendChild(packageCard);
                
                // Animate card appearance
                setTimeout(() => {
                    packageCard.style.opacity = '1';
                    packageCard.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            // Update pagination
            currentOffset += data.data.length;
            hasMorePackages = data.pagination.has_more;
            
            // Update load more button
            updateLoadMoreButton();
            
        } else {
            throw new Error(data.message || 'Failed to load packages');
        }
    } catch (error) {
        console.error('Error loading packages:', error);
        if (reset) {
            packagesGrid.innerHTML = `
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Failed to load packages. Please try again later.</p>
                    <button class="btn btn-primary" onclick="loadPackages(true)">Retry</button>
                </div>
            `;
        }
    } finally {
        isLoading = false;
    }
}

function createPackageCard(package) {
    const card = document.createElement('div');
    card.className = 'package-card';
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    
    // Determine category for styling
    const category = determinePackageCategory(package);
    
    // Create features array
    const features = [];
    if (package.duration) features.push(`${package.duration} days`);
    if (package.group_size) features.push(`Max ${package.group_size} people`);
    if (category !== 'general') features.push(category);
    
    card.innerHTML = `
        <div class="package-content">
            <h3 class="package-title">${escapeHtml(package.title)}</h3>
            <div class="package-destination">
                <i class="fas fa-map-marker-alt"></i>
                ${escapeHtml(package.destination)}
            </div>
            <p class="package-description">${escapeHtml(truncateText(package.description, 120))}</p>
            
            ${features.length > 0 ? `
                <div class="package-features">
                    ${features.map(feature => `<span class="feature-tag">${escapeHtml(feature)}</span>`).join('')}
                </div>
            ` : ''}
            
            <div class="package-footer">
                <div class="package-info">
                    <div class="package-price">₱${parseFloat(package.price).toLocaleString()}</div>
                    ${package.duration ? `<div class="package-duration">${package.duration} days</div>` : ''}
                </div>
                <button class="book-btn" onclick="openBookingModal(${package.id})">
                    <i class="fas fa-calendar-check"></i>
                    Book Now
                </button>
            </div>
        </div>
    `;
    
    return card;
}

function determinePackageCategory(package) {
    const title = package.title.toLowerCase();
    const description = package.description.toLowerCase();
    const destination = package.destination.toLowerCase();
    const searchText = `${title} ${description} ${destination}`;
    
    if (searchText.includes('beach') || searchText.includes('island') || searchText.includes('coastal')) {
        return 'beach';
    } else if (searchText.includes('mountain') || searchText.includes('hiking') || searchText.includes('trek')) {
        return 'mountain';
    } else if (searchText.includes('heritage') || searchText.includes('cultural') || searchText.includes('historic')) {
        return 'cultural';
    } else if (searchText.includes('adventure') || searchText.includes('surfing') || searchText.includes('extreme')) {
        return 'adventure';
    }
    
    return 'general';
}

function updateLoadMoreButton() {
    const loadMoreBtn = document.getElementById('load-more-btn');
    if (loadMoreBtn) {
        if (hasMorePackages) {
            loadMoreBtn.style.display = 'block';
            loadMoreBtn.innerHTML = '<i class="fas fa-plus"></i> Load More Packages';
        } else {
            loadMoreBtn.style.display = 'none';
        }
    }
}

async function searchPackages(query) {
    if (!query.trim()) {
        loadPackages(true);
        return;
    }
    
    isLoading = true;
    const packagesGrid = document.getElementById('packages-grid');
    packagesGrid.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i><p>Searching packages...</p></div>';
    
    try {
        const response = await fetch(`/api/search_packages.php?q=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (data.success) {
            packagesGrid.innerHTML = '';
            
            if (data.data.length === 0) {
                packagesGrid.innerHTML = `
                    <div class="no-packages">
                        <i class="fas fa-search"></i>
                        <p>No packages found for "${escapeHtml(query)}"</p>
                        <button class="btn btn-outline" onclick="document.getElementById('package-search').value=''; loadPackages(true);">Clear Search</button>
                    </div>
                `;
            } else {
                data.data.forEach((package, index) => {
                    const packageCard = createPackageCard(package);
                    packagesGrid.appendChild(packageCard);
                    
                    setTimeout(() => {
                        packageCard.style.opacity = '1';
                        packageCard.style.transform = 'translateY(0)';
                    }, index * 100);
                });
            }
            
            // Hide load more button for search results
            const loadMoreBtn = document.getElementById('load-more-btn');
            if (loadMoreBtn) loadMoreBtn.style.display = 'none';
            
        } else {
            throw new Error(data.message || 'Search failed');
        }
    } catch (error) {
        console.error('Search error:', error);
        packagesGrid.innerHTML = `
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Search failed. Please try again.</p>
            </div>
        `;
    } finally {
        isLoading = false;
    }
}

/* =============================================
   MODAL FUNCTIONALITY
   ============================================= */

function initializeModals() {
    // Login modal
    const loginModal = document.getElementById('login-modal');
    const loginBtn = document.getElementById('login-btn');
    const closeModal = document.querySelector('.close');
    
    if (loginBtn) {
        loginBtn.addEventListener('click', () => {
            loginModal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });
    }
    
    if (closeModal) {
        closeModal.addEventListener('click', () => {
            closeLoginModal();
        });
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === loginModal) {
            closeLoginModal();
        }
    });
    
    // Login options
    initializeLoginOptions();
    
    // Booking modal initialization
    initializeBookingModal();
}

function initializeLoginOptions() {
    // Gmail login
    const gmailBtn = document.getElementById('gmail-login');
    if (gmailBtn) {
        gmailBtn.addEventListener('click', () => {
            showNotification('Gmail login integration coming soon!', 'info');
        });
    }
    
    // Phone login
    const phoneBtn = document.getElementById('phone-login');
    const phoneForm = document.getElementById('phone-form');
    
    if (phoneBtn) {
        phoneBtn.addEventListener('click', () => {
            phoneForm.style.display = phoneForm.style.display === 'none' ? 'block' : 'none';
        });
    }
    
    // Send OTP
    const sendOtpBtn = document.getElementById('send-otp');
    if (sendOtpBtn) {
        sendOtpBtn.addEventListener('click', sendOTP);
    }
    
    // Verify OTP
    const verifyOtpBtn = document.getElementById('verify-otp');
    if (verifyOtpBtn) {
        verifyOtpBtn.addEventListener('click', verifyOTP);
    }
    
    // Guest login
    const guestBtn = document.getElementById('guest-login');
    if (guestBtn) {
        guestBtn.addEventListener('click', () => {
            showNotification('Welcome, Guest! Browse our amazing packages.', 'success');
            closeLoginModal();
        });
    }
}

function sendOTP() {
    const phoneInput = document.getElementById('phone-number');
    const phone = phoneInput.value.trim();
    
    if (!phone) {
        showNotification('Please enter your phone number', 'error');
        return;
    }
    
    if (!/^\+?[\d\s-()]+$/.test(phone)) {
        showNotification('Please enter a valid phone number', 'error');
        return;
    }
    
    // Simulate OTP sending
    showNotification('OTP sent successfully! Check your phone.', 'success');
    document.getElementById('otp-verification').style.display = 'block';
    
    // Focus on OTP input
    setTimeout(() => {
        document.getElementById('otp-code').focus();
    }, 100);
}

function verifyOTP() {
    const otpInput = document.getElementById('otp-code');
    const otp = otpInput.value.trim();
    
    if (!otp) {
        showNotification('Please enter the OTP code', 'error');
        return;
    }
    
    if (otp.length !== 6) {
        showNotification('OTP must be 6 digits', 'error');
        return;
    }
    
    // Simulate OTP verification
    if (otp === '123456') {
        showNotification('Login successful! Welcome to Jetlouge Travels.', 'success');
        closeLoginModal();
        // Redirect to dashboard or update UI
    } else {
        showNotification('Invalid OTP. Please try again.', 'error');
    }
}

function closeLoginModal() {
    const loginModal = document.getElementById('login-modal');
    loginModal.style.display = 'none';
    document.body.style.overflow = 'auto';
    
    // Reset forms
    document.getElementById('phone-form').style.display = 'none';
    document.getElementById('otp-verification').style.display = 'none';
    document.getElementById('phone-number').value = '';
    document.getElementById('otp-code').value = '';
}

function initializeBookingModal() {
    // Booking modal will be created dynamically when needed
}

function openBookingModal(packageId) {
    // Create booking modal dynamically
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.id = 'booking-modal';
    
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-calendar-check"></i> Book Your Adventure</h3>
                <span class="close" onclick="closeBookingModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="booking-form">
                    <div class="form-group">
                        <label for="booking-name">Full Name</label>
                        <input type="text" id="booking-name" placeholder="Enter your full name" required>
                    </div>
                    <div class="form-group">
                        <label for="booking-email">Email Address</label>
                        <input type="email" id="booking-email" placeholder="Enter your email" required>
                    </div>
                    <div class="form-group">
                        <label for="booking-phone">Phone Number</label>
                        <input type="tel" id="booking-phone" placeholder="Enter your phone number" required>
                    </div>
                    <div class="form-group">
                        <label for="booking-date">Preferred Date</label>
                        <input type="date" id="booking-date" required>
                    </div>
                    <div class="form-group">
                        <label for="booking-guests">Number of Guests</label>
                        <select id="booking-guests" required>
                            <option value="">Select number of guests</option>
                            <option value="1">1 Guest</option>
                            <option value="2">2 Guests</option>
                            <option value="3">3 Guests</option>
                            <option value="4">4 Guests</option>
                            <option value="5+">5+ Guests</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="booking-message">Special Requests (Optional)</label>
                        <textarea id="booking-message" rows="3" placeholder="Any special requirements or requests..."></textarea>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="submitBooking(${packageId})" style="width: 100%; margin-top: 1rem;">
                        <i class="fas fa-paper-plane"></i> Submit Booking Request
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Set minimum date to today
    const dateInput = document.getElementById('booking-date');
    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;
}

function closeBookingModal() {
    const modal = document.getElementById('booking-modal');
    if (modal) {
        modal.remove();
        document.body.style.overflow = 'auto';
    }
}

function submitBooking(packageId) {
    const name = document.getElementById('booking-name').value.trim();
    const email = document.getElementById('booking-email').value.trim();
    const phone = document.getElementById('booking-phone').value.trim();
    const date = document.getElementById('booking-date').value;
    const guests = document.getElementById('booking-guests').value;
    const message = document.getElementById('booking-message').value.trim();
    
    // Validation
    if (!name || !email || !phone || !date || !guests) {
        showNotification('Please fill in all required fields', 'error');
        return;
    }
    
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showNotification('Please enter a valid email address', 'error');
        return;
    }
    
    // Simulate booking submission
    showNotification('Booking request submitted successfully! We will contact you soon.', 'success');
    closeBookingModal();
    
    // Here you would typically send the data to your backend
    console.log('Booking data:', { packageId, name, email, phone, date, guests, message });
}

/* =============================================
   TESTIMONIALS FUNCTIONALITY
   ============================================= */

function initializeTestimonials() {
    const testimonials = document.querySelectorAll('.testimonial-card');
    let currentTestimonial = 0;
    
    if (testimonials.length === 0) return;
    
    // Show first testimonial
    testimonials[0].classList.add('active');
    
    // Auto-rotate testimonials
    setInterval(() => {
        testimonials[currentTestimonial].classList.remove('active');
        currentTestimonial = (currentTestimonial + 1) % testimonials.length;
        testimonials[currentTestimonial].classList.add('active');
    }, 5000);
}

/* =============================================
   NEWSLETTER FUNCTIONALITY
   ============================================= */

function initializeNewsletter() {
    const newsletterForm = document.getElementById('newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', handleNewsletterSubmit);
    }
}

function handleNewsletterSubmit(e) {
    e.preventDefault();
    
    const emailInput = document.getElementById('newsletter-email');
    const email = emailInput.value.trim();
    
    if (!email) {
        showNotification('Please enter your email address', 'error');
        return;
    }
    
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showNotification('Please enter a valid email address', 'error');
        return;
    }
    
    // Simulate newsletter subscription
    showNotification('Thank you for subscribing! Welcome to Jetlouge Travels.', 'success');
    emailInput.value = '';
}

/* =============================================
   ANIMATIONS AND SCROLL EFFECTS
   ============================================= */

function initializeAnimations() {
    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    document.querySelectorAll('.feature-card, .testimonial-card, .section-header').forEach(el => {
        observer.observe(el);
    });
}

function initializeScrollEffects() {
    // Parallax effect for hero section
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const hero = document.querySelector('.hero');
        
        if (hero) {
            hero.style.transform = `translateY(${scrolled * 0.5}px)`;
        }
    });
    
    // Scroll to top button
    const scrollTopBtn = document.createElement('button');
    scrollTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
    scrollTopBtn.className = 'scroll-top-btn';
    scrollTopBtn.style.cssText = `
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: var(--jetlouge-primary);
        color: white;
        border: none;
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
    `;
    
    document.body.appendChild(scrollTopBtn);
    
    scrollTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            scrollTopBtn.style.opacity = '1';
            scrollTopBtn.style.visibility = 'visible';
        } else {
            scrollTopBtn.style.opacity = '0';
            scrollTopBtn.style.visibility = 'hidden';
        }
    });
}

/* =============================================
   NOTIFICATION SYSTEM
   ============================================= */

function showNotification(message, type = 'info') {
    // Remove existing notifications
    document.querySelectorAll('.notification').forEach(n => n.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        info: 'fas fa-info-circle',
        warning: 'fas fa-exclamation-triangle'
    };
    
    notification.innerHTML = `
        <i class="${icons[type] || icons.info}"></i>
        <span>${escapeHtml(message)}</span>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    notification.style.cssText = `
        position: fixed;
        top: 2rem;
        right: 2rem;
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : type === 'warning' ? '#f59e0b' : '#3b82f6'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 2000;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        max-width: 400px;
        animation: slideInRight 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

/* =============================================
   UTILITY FUNCTIONS
   ============================================= */

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function truncateText(text, maxLength) {
    if (text.length <= maxLength) return text;
    return text.substr(0, maxLength) + '...';
}

// Add CSS animations for notifications
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .notification-close {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        padding: 0;
        margin-left: auto;
        opacity: 0.8;
        transition: opacity 0.2s ease;
    }
    
    .notification-close:hover {
        opacity: 1;
    }
    
    .animate-in {
        animation: fadeInUp 0.6s ease forwards;
    }
`;
document.head.appendChild(style);
