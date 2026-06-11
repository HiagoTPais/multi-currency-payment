<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import { currentUser } from '../../Services/api';
import { createPaymentRequest } from '../../Services/paymentRequests';

const user = currentUser();
const form = reactive({ amount_local: '', notes: '' });
const created = ref(null);
const error = ref('');

async function submit() {
    error.value = '';
    created.value = null;
    try {
        created.value = await createPaymentRequest({ amount_local: form.amount_local, notes: form.notes || null });
        form.amount_local = '';
        form.notes = '';
    } catch (exception) {
        error.value = exception.response?.data?.message || 'Unable to create request.';
    }
}
</script>

<template>
    <Head title="New Payment Request" />
    <AppLayout>
        <h1 class="text-3xl font-bold text-white">Create payment request</h1>
        <p class="mt-2 text-slate-400">Your local currency is {{ user?.currency_code }}.</p>
        <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_420px]">
            <form class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-2xl shadow-black/20" @submit.prevent="submit">
                <input v-model="form.amount_local" type="number" min="0.01" step="0.01" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-500 focus:border-blue-400 focus:outline-none" :placeholder="`Amount in ${user?.currency_code}`" required>
                <textarea v-model="form.notes" rows="4" class="mt-5 w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-500 focus:border-blue-400 focus:outline-none" placeholder="Notes"></textarea>
                <p v-if="error" class="mt-5 rounded-xl border border-rose-400/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">{{ error }}</p>
                <button class="mt-6 rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-500">Create request</button>
            </form>
            <aside class="rounded-2xl border border-slate-800 bg-slate-900 p-6 text-white shadow-2xl shadow-black/20">
                <h2 class="text-xl font-bold">Conversion snapshot</h2>
                <div v-if="created" class="mt-6 space-y-3">
                    <p>1 EUR = {{ created.exchange_rate }} {{ created.currency_code }}</p>
                    <p class="text-2xl font-bold">€{{ created.amount_eur }}</p>
                    <p class="text-sm text-slate-400">{{ created.exchange_rate_source }} · {{ new Date(created.exchange_rate_timestamp).toLocaleString() }}</p>
                    <Link :href="`/payment-requests/${created.id}`" class="inline-flex rounded-xl bg-white px-4 py-3 text-sm font-semibold text-slate-950 hover:bg-slate-200">View details</Link>
                </div>
                <p v-else class="mt-6 text-sm text-slate-400">Submit to snapshot the live exchange rate.</p>
            </aside>
        </div>
    </AppLayout>
</template>
