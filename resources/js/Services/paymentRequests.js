import api from './api';

export async function dashboardStats() {
    const { data } = await api.get('/dashboard');
    return data.data;
}

export async function listPaymentRequests(params = {}) {
    const { data } = await api.get('/payment-requests', { params });
    return data;
}

export async function createPaymentRequest(payload) {
    const { data } = await api.post('/payment-requests', payload);
    return data.data;
}

export async function getPaymentRequest(id) {
    const { data } = await api.get(`/payment-requests/${id}`);
    return data.data;
}

export async function approvePaymentRequest(id) {
    const { data } = await api.patch(`/payment-requests/${id}/approve`);
    return data.data;
}

export async function rejectPaymentRequest(id, notes = null) {
    const { data } = await api.patch(`/payment-requests/${id}/reject`, { notes });
    return data.data;
}
