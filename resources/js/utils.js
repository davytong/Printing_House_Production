/**
 * Utility functions for printing-tracker application
 */

/**
 * Show loading overlay
 */
export function showLoading(message = 'កំពុងដំណើរការ...') {
    window.dispatchEvent(new CustomEvent('loading-start', { detail: { message } }));
}

/**
 * Hide loading overlay
 */
export function hideLoading() {
    window.dispatchEvent(new CustomEvent('loading-stop'));
}

/**
 * Add loading state to all forms
 */
export function initFormLoadingStates() {
    document.querySelectorAll('form[data-loading]').forEach(form => {
        form.addEventListener('submit', function() {
            const button = this.querySelector('button[type="submit"]');
            if (button && !button.disabled) {
                showLoading();
                button.disabled = true;
                button.dataset.originalText = button.innerHTML;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>កំពុងដំណើរការ...';
            }
        });
    });
}

/**
 * Toast notification helper
 */
export function showToast(type, message, duration = 3000) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show toast-notification`;
    toast.style.cssText = 'position: fixed; top: 80px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px; animation: slideInRight 0.3s ease-out;';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

/**
 * Confirm dialog with custom message
 */
export function confirm(message, onConfirm) {
    if (window.confirm(message)) {
        onConfirm();
    }
}

/**
 * Format number with commas
 */
export function formatNumber(num) {
    return new Intl.NumberFormat('en-US').format(num);
}

/**
 * Debounce function
 */
export function debounce(func, wait) {
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
 * Initialize tooltips
 */
export function initTooltips() {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
}

/**
 * Auto-dismiss alerts after delay
 */
export function initAutoDismissAlerts() {
    document.querySelectorAll('.alert[data-auto-dismiss]').forEach(alert => {
        const delay = parseInt(alert.dataset.autoDismiss) || 5000;
        setTimeout(() => {
            alert.classList.remove('show');
            setTimeout(() => alert.remove(), 300);
        }, delay);
    });
}

/**
 * Initialize all utility functions on page load
 */
export function init() {
    initFormLoadingStates();
    initTooltips();
    initAutoDismissAlerts();
    
    console.log('✅ Printing Tracker utilities initialized');
}

// Auto-init on DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
