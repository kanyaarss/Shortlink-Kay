/**
 * Shortlink Kay v1 - Main JavaScript
 */

// Toggle custom code input
document.addEventListener('DOMContentLoaded', function() {
    const codeTypeRadios = document.querySelectorAll('input[name="code_type"]');
    const customCodeGroup = document.getElementById('customCodeGroup');
    
    if (codeTypeRadios.length > 0 && customCodeGroup) {
        codeTypeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customCodeGroup.style.display = 'block';
                } else {
                    customCodeGroup.style.display = 'none';
                }
            });
        });
        
        // Check initial state
        const selectedRadio = document.querySelector('input[name="code_type"]:checked');
        if (selectedRadio && selectedRadio.value === 'custom') {
            customCodeGroup.style.display = 'block';
        }
    }
});

/**
 * Copy text to clipboard
 * 
 * @param {string} elementId Element ID to copy
 */
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    
    if (!element) {
        console.error('Element not found: ' + elementId);
        return;
    }
    
    // Select text
    element.select();
    element.setSelectionRange(0, 99999); // For mobile
    
    // Copy to clipboard
    try {
        document.execCommand('copy');
        
        // Show feedback
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = '✓ Copied!';
        
        setTimeout(() => {
            button.textContent = originalText;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy: ' + err);
        alert('Failed to copy to clipboard');
    }
}

/**
 * Format URL
 * 
 * @param {string} url URL to format
 * @returns {string} Formatted URL
 */
function formatUrl(url) {
    if (!url.startsWith('http://') && !url.startsWith('https://')) {
        return 'https://' + url;
    }
    return url;
}

/**
 * Validate URL
 * 
 * @param {string} url URL to validate
 * @returns {boolean} True if valid
 */
function validateUrl(url) {
    try {
        new URL(url);
        return true;
    } catch (e) {
        return false;
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
 * Check if mobile device
 * 
 * @returns {boolean} True if mobile
 */
function isMobileDevice() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

/**
 * Add event listener with delegation
 * 
 * @param {string} selector Selector
 * @param {string} event Event name
 * @param {function} handler Event handler
 */
function on(selector, event, handler) {
    document.addEventListener(event, function(e) {
        if (e.target.matches(selector)) {
            handler.call(e.target, e);
        }
    });
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
 * Initialize tooltips
 */
function initTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.getAttribute('data-tooltip');
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
        });
        
        element.addEventListener('mouseleave', function() {
            const tooltip = document.querySelector('.tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        });
    });
}

/**
 * Initialize modals
 */
function initModals() {
    const modals = document.querySelectorAll('[data-modal]');
    modals.forEach(modal => {
        const closeBtn = modal.querySelector('[data-close]');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        }
    });
}

// Export functions for use in other scripts
window.copyToClipboard = copyToClipboard;
window.formatUrl = formatUrl;
window.validateUrl = validateUrl;
window.showNotification = showNotification;
window.debounce = debounce;
window.throttle = throttle;
window.getQueryParam = getQueryParam;
window.isMobileDevice = isMobileDevice;
window.on = on;
window.fetchApi = fetchApi;
window.initTooltips = initTooltips;
window.initModals = initModals;
