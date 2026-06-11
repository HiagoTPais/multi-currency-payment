<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import StatCard from '../../Components/StatCard.vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import { clearSession, currentUser } from '../../Services/api';
import { dashboardStats } from '../../Services/paymentRequests';

const user = currentUser();
const stats = ref({ pending: 0, approved: 0, rejected: 0, expired: 0 });
const exchangeRate = ref(null);
const error = ref('');

onMounted(async () => {
    try {
        const dashboard = await dashboardStats();
        stats.value = {
            pending: dashboard.pending,
            approved: dashboard.approved,
            rejected: dashboard.rejected,
            expired: dashboard.expired,
        };
        exchangeRate.value = dashboard.exchange_rate;
    } catch (exception) {
        error.value = exception.response?.data?.message || 'Unable to load dashboard.';

        if (exception.response?.status === 401) {
            clearSession();
        }
    }
});

function formatRate(rate) {
    return new Intl.NumberFormat(undefined, { maximumFractionDigits: 6 }).format(rate);
}
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout>
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-blue-300">Dashboard</p>
                <h1 class="mt-2 text-3xl font-bold text-white">Payment overview</h1>
            </div>
            <Link v-if="user?.role === 'employee'" href="/payment-requests/create" class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500">New request</Link>
        </div>
        <p v-if="error" class="mt-6 rounded-xl border border-rose-400/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">{{ error }}</p>
        <section class="mt-8 rounded-2xl border border-slate-800 bg-slate-900 p-6 text-white shadow-2xl shadow-black/20">
            <p class="text-sm font-semibold uppercase tracking-[0.25em] text-blue-300">Current exchange rate</p>
            <div v-if="exchangeRate" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-3xl font-bold">
                        1 {{ exchangeRate.base_currency }} = {{ formatRate(exchangeRate.rate) }} {{ exchangeRate.target_currency }}
                    </p>
                    <p class="mt-2 text-sm text-slate-400">
                        {{ exchangeRate.source }} · {{ new Date(exchangeRate.timestamp).toLocaleString() }}
                    </p>
                </div>
            </div>
            <p v-else class="mt-4 text-sm text-slate-400">Loading exchange rate...</p>
        </section>
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard label="Pending" :value="stats.pending" tone="blue" />
            <StatCard label="Approved" :value="stats.approved" tone="green" />
            <StatCard label="Rejected" :value="stats.rejected" tone="red" />
            <StatCard label="Expired" :value="stats.expired" tone="gray" />
        </div>
    </AppLayout>
</template>
