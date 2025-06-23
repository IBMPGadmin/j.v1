// Automatically detect the base URL up to 'testlaw'
const BASE_URL = (function() {
    const pathParts = window.location.pathname.split('/');
    const index = pathParts.indexOf('testlaw');
    if (index !== -1) {
        return window.location.origin + pathParts.slice(0, index + 1).join('/');
    } else {
        console.warn("Warning: 'testlaw' folder not found in URL, BASE_URL may not work properly.");
        return window.location.origin; // fallback
    }
})();
