<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import StatusBadge from '../../Components/StatusBadge.vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import { currentUser } from '../../Services/api';
import { approvePaymentRequest, getPaymentRequest, rejectPaymentRequest } from '../../Services/paymentRequests';

const props = defineProps({ id: { type: [Number, String], required: true } });
const user = currentUser();
const request = ref(null);
const notes = ref('');

async function load() {
    request.value = await getPaymentRequest(props.id);
}

async function approve() {
    request.value = await approvePaymentRequest(props.id);
}

async function reject() {
    request.value = await rejectPaymentRequest(props.id, notes.value || null);
}

onMounted(load);
</script>

<template>
    <Head title="Payment Request Details" />
    <AppLayout>
        <div v-if="request">
            <Link href="/payment-requests" class="text-sm font-semibold text-blue-300 hover:text-blue-200">Back</Link>
            <h1 class="mt-2 text-3xl font-bold text-white">Request #{{ request.id }}</h1>
            <div class="mt-3"><StatusBadge :status="request.status" /></div>
            <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_420px]">
                <section class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-2xl shadow-black/20">
                    <dl class="grid gap-5 sm:grid-cols-2">
                        <div><dt class="text-sm text-slate-400">Employee</dt><dd class="font-semibold text-white">{{ request.employee?.name }}</dd></div>
                        <div><dt class="text-sm text-slate-400">Currency</dt><dd class="font-semibold text-white">{{ request.currency_code }}</dd></div>
                        <div><dt class="text-sm text-slate-400">Local amount</dt><dd class="font-semibold text-white">{{ request.amount_local }}</dd></div>
                        <div><dt class="text-sm text-slate-400">Amount EUR</dt><dd class="font-semibold text-white">€{{ request.amount_eur }}</dd></div>
                        <div><dt class="text-sm text-slate-400">Created</dt><dd class="font-semibold text-white">{{ new Date(request.created_at).toLocaleString() }}</dd></div>
                        <div><dt class="text-sm text-slate-400">Expires</dt><dd class="font-semibold text-white">{{ new Date(request.expires_at).toLocaleString() }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-sm text-slate-400">Notes</dt><dd class="font-semibold text-white">{{ request.notes || 'No notes.' }}</dd></div>
                    </dl>
                </section>
                <aside class="space-y-6">
                    <section class="rounded-2xl border border-slate-800 bg-slate-900 p-6 text-white shadow-2xl shadow-black/20">
                        <h2 class="text-xl font-bold">Exchange snapshot</h2>
                        <p class="mt-4">1 EUR = {{ request.exchange_rate }} {{ request.currency_code }}</p>
                        <p class="text-sm text-slate-400">{{ request.exchange_rate_source }} · {{ new Date(request.exchange_rate_timestamp).toLocaleString() }}</p>
                    </section>
                    <section v-if="user?.role === 'finance' && request.status === 'pending'" class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-2xl shadow-black/20">
                        <textarea v-model="notes" rows="3" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-500 focus:border-blue-400 focus:outline-none" placeholder="Optional rejection notes"></textarea>
                        <div class="mt-4 flex gap-3">
                            <button class="rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-500" @click="approve">Approve</button>
                            <button class="rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white hover:bg-rose-500" @click="reject">Reject</button>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>
