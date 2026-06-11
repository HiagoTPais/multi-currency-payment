<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';
import StatusBadge from '../../Components/StatusBadge.vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import { currentUser } from '../../Services/api';
import { listPaymentRequests } from '../../Services/paymentRequests';

const user = currentUser();
const status = ref('');
const rows = ref([]);
const meta = ref(null);
const page = ref(1);

async function load() {
    const response = await listPaymentRequests({ page: page.value, status: status.value || undefined });
    rows.value = response.data;
    meta.value = response.meta;
}

watch(status, () => {
    page.value = 1;
    load();
});
onMounted(load);
</script>

<template>
    <Head title="Payment Requests" />
    <AppLayout>
        <div class="flex items-end justify-between gap-4">
            <h1 class="text-3xl font-bold text-white">Payment requests</h1>
            <Link v-if="user?.role === 'employee'" href="/payment-requests/create" class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-500">New request</Link>
        </div>
        <select v-model="status" class="mt-6 rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-white focus:border-blue-400 focus:outline-none">
            <option value="">All statuses</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
            <option value="expired">Expired</option>
        </select>
        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-2xl shadow-black/20">
            <table class="min-w-full divide-y divide-slate-800 text-sm">
                <thead class="bg-slate-900 text-left text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <tr>
                        <th class="px-6 py-4">Employee</th>
                        <th class="px-6 py-4">Local</th>
                        <th class="px-6 py-4">EUR</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Created</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 text-slate-300">
                    <tr v-for="request in rows" :key="request.id" class="hover:bg-slate-800/60">
                        <td class="px-6 py-4 font-medium text-white">{{ request.employee?.name }}</td>
                        <td class="px-6 py-4">{{ request.amount_local }} {{ request.currency_code }}</td>
                        <td class="px-6 py-4">€{{ request.amount_eur }}</td>
                        <td class="px-6 py-4"><StatusBadge :status="request.status" /></td>
                        <td class="px-6 py-4">{{ new Date(request.created_at).toLocaleDateString() }}</td>
                        <td class="px-6 py-4 text-right"><Link :href="`/payment-requests/${request.id}`" class="font-semibold text-blue-300 hover:text-blue-200">Details</Link></td>
                    </tr>
                </tbody>
            </table>
            <div v-if="meta" class="flex justify-between border-t border-slate-800 px-6 py-4 text-sm text-slate-300">
                <span>Page {{ meta.current_page }} of {{ meta.last_page }}</span>
                <div class="flex gap-2">
                    <button :disabled="page <= 1" class="rounded-lg border border-slate-700 px-3 py-2 hover:bg-slate-800 disabled:opacity-40" @click="page--; load()">Previous</button>
                    <button :disabled="page >= meta.last_page" class="rounded-lg border border-slate-700 px-3 py-2 hover:bg-slate-800 disabled:opacity-40" @click="page++; load()">Next</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
