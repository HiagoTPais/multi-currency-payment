import axios from 'axios';

export const tokenKey = 'mcp_token';
export const userKey = 'mcp_user';

const api = axios.create({
    baseURL: '/api',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
});

api.interceptors.request.use(config => {
    const token = localStorage.getItem(tokenKey);

    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
});

api.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401 && window.location.pathname !== '/login') {
            localStorage.removeItem(tokenKey);
            localStorage.removeItem(userKey);
            window.location.href = '/login';
        }

        return Promise.reject(error);
    },
);

export function persistSession(payload) {
    localStorage.setItem(tokenKey, payload.access_token);
    localStorage.setItem(userKey, JSON.stringify(payload.user));
    api.defaults.headers.common.Authorization = `Bearer ${payload.access_token}`;
}

export function clearSession() {
    localStorage.removeItem(tokenKey);
    localStorage.removeItem(userKey);
    delete api.defaults.headers.common.Authorization;
}

export function currentUser() {
    const stored = localStorage.getItem(userKey);
    return stored ? JSON.parse(stored) : null;
}

export function isAuthenticated() {
    return Boolean(localStorage.getItem(tokenKey));
}

export default api;
