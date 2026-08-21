import './bootstrap';

// Alpine.js for smooth animations and interactivity
import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';

Alpine.plugin(intersect);

window.Alpine = Alpine;
Alpine.start();

// Import utilities
import { showLoading, hideLoading, showToast, init as initUtils } from './utils';

// Export utilities to window for global access
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.showToast = showToast;

// Initialize utilities
initUtils();
