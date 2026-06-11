import api, { clearSession, persistSession } from './api';

export async function login(credentials) {
    const { data } = await api.post('/auth/login', credentials);
    persistSession(data);
    return data;
}

export async function me() {
    const { data } = await api.get('/auth/me');
    return data.data;
}

export async function register(payload) {
    const { data } = await api.post('/auth/register', payload);
    return data;
}

export async function logout() {
    try {
        await api.post('/auth/logout');
    } finally {
        clearSession();
    }
}
