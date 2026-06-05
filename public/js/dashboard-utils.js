/**
 * Dashboard Utilities
 * Shared utility functions for the daily report entry dashboard
 * 
 * Usage:
 *   <script src="{{ asset('js/dashboard-utils.js') }}"></script>
 *   Then use window.DashboardUtils.functionName()
 */

window.DashboardUtils = {
    /**
     * Format a date string to Indonesian locale
     * @param {string} dateString - ISO date string (YYYY-MM-DD)
     * @returns {string} Formatted date string
     */
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    },

    /**
     * Format a month string to Indonesian locale
     * @param {string} monthString - ISO month string (YYYY-MM)
     * @returns {string} Formatted month string
     */
    formatMonth(monthString) {
        const date = new Date(monthString + '-01');
        return date.toLocaleDateString('id-ID', { 
            month: 'long', 
            year: 'numeric' 
        });
    },

    /**
     * Check if a date is today
     * @param {string} dateString - ISO date string (YYYY-MM-DD)
     * @returns {boolean}
     */
    isToday(dateString) {
        const today = new Date().toDateString();
        const checkDate = new Date(dateString).toDateString();
        return today === checkDate;
    },

    /**
     * Check if a date is in the future
     * @param {string} dateString - ISO date string (YYYY-MM-DD)
     * @returns {boolean}
     */
    isFutureDate(dateString) {
        const today = new Date();
        today.setHours(23, 59, 59, 999);
        const checkDate = new Date(dateString);
        return checkDate > today;
    },

    /**
     * Format an IMUT version string
     * @param {string} version - Version string (e.g., "/version-1.0")
     * @returns {string} Formatted version (e.g., "v1.0")
     */
    formatImutVersion(version) {
        return version ? version.replace('/version-', 'v') : '';
    },

    /**
     * Format a number using Indonesian locale
     * @param {number} num - Number to format
     * @returns {string} Formatted number
     */
    formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num || 0);
    },

    /**
     * Get next month in YYYY-MM format
     * @param {string} monthString - ISO month string (YYYY-MM)
     * @returns {string} Next month in YYYY-MM format
     */
    getNextMonth(monthString) {
        const date = new Date(monthString + '-01');
        date.setMonth(date.getMonth() + 1);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        return `${year}-${month}`;
    },

    /**
     * Get previous month in YYYY-MM format
     * @param {string} monthString - ISO month string (YYYY-MM)
     * @returns {string} Previous month in YYYY-MM format
     */
    getPreviousMonth(monthString) {
        const date = new Date(monthString + '-01');
        date.setMonth(date.getMonth() - 1);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        return `${year}-${month}`;
    },

    /**
     * Get current month in YYYY-MM format
     * @returns {string} Current month in YYYY-MM format
     */
    getCurrentMonth() {
        return new Date().toISOString().slice(0, 7);
    },

    /**
     * Get today's date in YYYY-MM-DD format
     * @returns {string} Today's date in YYYY-MM-DD format
     */
    getTodayDate() {
        return new Date().toISOString().slice(0, 10);
    },

    /**
     * Clone and merge objects safely
     * @param {...object} objects - Objects to merge
     * @returns {object} Merged object
     */
    mergeObjects(...objects) {
        return objects.reduce((acc, obj) => ({ ...acc, ...obj }), {});
    },

    /**
     * Debounce a function
     * @param {function} func - Function to debounce
     * @param {number} wait - Wait time in milliseconds
     * @returns {function} Debounced function
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    /**
     * Throttle a function
     * @param {function} func - Function to throttle
     * @param {number} limit - Time limit in milliseconds
     * @returns {function} Throttled function
     */
    throttle(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
};
