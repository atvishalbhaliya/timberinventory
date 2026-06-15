import axios from 'axios';
window.axios = axios;

// Set API base URL - adjust based on your environment
const apiBaseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
window.axios.defaults.baseURL = apiBaseUrl;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;

// Add auth token to requests if available
const token = localStorage.getItem('auth_token');
if (token) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

window.axios.interceptors.response.use(
    response => response,
    error => {
        const status = error.response?.status;

        if (status === 401) {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = '/login';
        }

        if (status === 403) {
            error.normalizedMessage = 'You do not have permission to perform this action.';
        } else if (status === 422) {
            const errors = error.response?.data?.errors || {};
            error.normalizedMessage = Object.values(errors).flat().join(' ') || error.response?.data?.message || 'Validation failed.';
        } else if (status >= 500) {
            error.normalizedMessage = 'Server error. Please try again or contact support.';
        } else {
            error.normalizedMessage = error.response?.data?.message || error.message || 'Request failed.';
        }

        return Promise.reject(error);
    }
);
