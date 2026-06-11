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
            <Link href="/payment-requests" class="text-sm font-semibold text-blue-600">Back</Link>
            <h1 class="mt-2 text-3xl font-bold text-slate-950">Request #{{ request.id }}</h1>
            <div class="mt-3"><StatusBadge :status="request.status" /></div>
            <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_420px]">
                <section class="rounded-2xl bg-white p-6 shadow-sm">
                    <dl class="grid gap-5 sm:grid-cols-2">
                        <div><dt class="text-sm text-slate-500">Employee</dt><dd class="font-semibold">{{ request.employee?.name }}</dd></div>
                        <div><dt class="text-sm text-slate-500">Currency</dt><dd class="font-semibold">{{ request.currency_code }}</dd></div>
                        <div><dt class="text-sm text-slate-500">Local amount</dt><dd class="font-semibold">{{ request.amount_local }}</dd></div>
                        <div><dt class="text-sm text-slate-500">Amount EUR</dt><dd class="font-semibold">€{{ request.amount_eur }}</dd></div>
                        <div><dt class="text-sm text-slate-500">Created</dt><dd class="font-semibold">{{ new Date(request.created_at).toLocaleString() }}</dd></div>
                        <div><dt class="text-sm text-slate-500">Expires</dt><dd class="font-semibold">{{ new Date(request.expires_at).toLocaleString() }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-sm text-slate-500">Notes</dt><dd class="font-semibold">{{ request.notes || 'No notes.' }}</dd></div>
                    </dl>
                </section>
                <aside class="space-y-6">
                    <section class="rounded-2xl bg-slate-950 p-6 text-white">
                        <h2 class="text-xl font-bold">Exchange snapshot</h2>
                        <p class="mt-4">1 EUR = {{ request.exchange_rate }} {{ request.currency_code }}</p>
                        <p class="text-sm text-slate-300">{{ request.exchange_rate_source }} · {{ new Date(request.exchange_rate_timestamp).toLocaleString() }}</p>
                    </section>
                    <section v-if="user?.role === 'finance' && request.status === 'pending'" class="rounded-2xl bg-white p-6 shadow-sm">
                        <textarea v-model="notes" rows="3" class="w-full rounded-xl border px-4 py-3" placeholder="Optional rejection notes"></textarea>
                        <div class="mt-4 flex gap-3">
                            <button class="rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white" @click="approve">Approve</button>
                            <button class="rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white" @click="reject">Reject</button>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>
