/**
 * Shortlink Kay v1 - Admin JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize admin features
    initializeAdmin();
});

/**
 * Initialize admin features
 */
function initializeAdmin() {
    // Setup event listeners
    setupEventListeners();
    
    // Setup tooltips
    setupTooltips();
    
    // Setup modals
    setupModals();
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Delete confirmation
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // Copy to clipboard
    const copyButtons = document.querySelectorAll('[data-copy]');
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const text = this.getAttribute('data-copy');
            copyToClipboard(text);
        });
    });
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Setup tooltips
 */
function setupTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', function() {
            showTooltip(this);
        });
        
        element.addEventListener('mouseleave', function() {
            hideTooltip();
        });
    });
}

/**
 * Setup modals
 */
function setupModals() {
    const modals = document.querySelectorAll('[data-modal]');
    modals.forEach(modal => {
        const closeBtn = modal.querySelector('[data-close]');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        }
        
        // Close on outside click
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    });
}

/**
 * Copy text to clipboard
 * 
 * @param {string} text Text to copy
 */
function copyToClipboard(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    document.body.appendChild(textarea);
    textarea.select();
    
    try {
        document.execCommand('copy');
        showNotification('Copied to clipboard!', 'success');
    } catch (err) {
        showNotification('Failed to copy', 'error');
    }
    
    document.body.removeChild(textarea);
}

/**
 * Validate form
 * 
 * @param {HTMLFormElement} form Form to validate
 * @returns {boolean} True if valid
 */
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('error');
            isValid = false;
        } else {
            input.classList.remove('error');
        }
    });
    
    return isValid;
}

/**
 * Show tooltip
 * 
 * @param {HTMLElement} element Element with tooltip
 */
function showTooltip(element) {
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = element.getAttribute('data-tooltip');
    document.body.appendChild(tooltip);
    
    const rect = element.getBoundingClientRect();
    tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
}

/**
 * Hide tooltip
 */
function hideTooltip() {
    const tooltip = document.querySelector('.tooltip');
    if (tooltip) {
        tooltip.remove();
    }
}

/**
 * Show notification
 * 
 * @param {string} message Message to show
 * @param {string} type Type (success, error, warning, info)
 * @param {number} duration Duration in milliseconds
 */
function showNotification(message, type = 'info', duration = 3000) {
    const notification = document.createElement('div');
    notification.className = 'notification notification-' + type;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Hide notification
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, duration);
}

/**
 * Format number with thousand separator
 * 
 * @param {number} number Number to format
 * @returns {string} Formatted number
 */
function formatNumber(number) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

/**
 * Format date
 * 
 * @param {string} date Date string
 * @param {string} format Date format
 * @returns {string} Formatted date
 */
function formatDate(date, format = 'YYYY-MM-DD') {
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    
    return format
        .replace('YYYY', year)
        .replace('MM', month)
        .replace('DD', day)
        .replace('HH', hours)
        .replace('mm', minutes);
}

/**
 * Debounce function
 * 
 * @param {function} func Function to debounce
 * @param {number} wait Wait time in milliseconds
 * @returns {function} Debounced function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle function
 * 
 * @param {function} func Function to throttle
 * @param {number} limit Limit time in milliseconds
 * @returns {function} Throttled function
 */
function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * Get query parameter
 * 
 * @param {string} param Parameter name
 * @returns {string|null} Parameter value or null
 */
function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}

/**
 * Redirect to URL
 * 
 * @param {string} url URL to redirect to
 */
function redirect(url) {
    window.location.href = url;
}

/**
 * Fetch API wrapper
 * 
 * @param {string} url URL
 * @param {object} options Options
 * @returns {Promise} Promise
 */
async function fetchApi(url, options = {}) {
    try {
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        });
        
        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }
        
        return await response.json();
    } catch (error) {
        console.error('Fetch error:', error);
        throw error;
    }
}

/**
 * Check if mobile device
 * 
 * @returns {boolean} True if mobile
 */
function isMobileDevice() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

/**
 * Export functions for use in other scripts
 */
window.copyToClipboard = copyToClipboard;
window.validateForm = validateForm;
window.showNotification = showNotification;
window.formatNumber = formatNumber;
window.formatDate = formatDate;
window.debounce = debounce;
window.throttle = throttle;
window.getQueryParam = getQueryParam;
window.redirect = redirect;
window.fetchApi = fetchApi;
window.isMobileDevice = isMobileDevice;
