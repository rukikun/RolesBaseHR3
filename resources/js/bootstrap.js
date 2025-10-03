/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

// Simple axios setup without imports for now
window.axios = {
    get: function(url, config = {}) {
        return fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                ...config.headers
            },
            ...config
        }).then(response => response.json());
    },
    post: function(url, data = {}, config = {}) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                ...config.headers
            },
            body: JSON.stringify(data),
            ...config
        }).then(response => response.json());
    }
};

/**
 * CSRF Token setup
 */
const token = document.head.querySelector('meta[name="csrf-token"]');
if (!token) {
    console.warn('CSRF token not found in meta tags');
}
