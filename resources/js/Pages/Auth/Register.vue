<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { register } from '../../Services/auth';

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'employee',
    country_code: 'PT',
    currency_code: 'EUR',
});
const errors = ref({});
const countries = [['PT', 'EUR'], ['BR', 'BRL'], ['US', 'USD'], ['GB', 'GBP'], ['JP', 'JPY'], ['CA', 'CAD']];

function applyCountry(code) {
    const country = countries.find(item => item[0] === code);
    form.country_code = country[0];
    form.currency_code = country[1];
}

async function submit() {
    errors.value = {};
    try {
        await register(form);
        router.visit('/login');
    } catch (exception) {
        errors.value = exception.response?.data?.errors || { general: [exception.response?.data?.message || 'Registration failed.'] };
    }
}
</script>

<template>
    <Head title="Register" />
    <div class="min-h-screen bg-slate-100 px-4 py-10">
        <div class="mx-auto w-full max-w-2xl rounded-3xl bg-white p-8 shadow-xl">
            <h1 class="text-3xl font-bold text-slate-950">Create account</h1>
            <form class="mt-8 grid gap-5 sm:grid-cols-2" @submit.prevent="submit">
                <input v-model="form.name" class="rounded-xl border border-slate-300 px-4 py-3 sm:col-span-2" placeholder="Name" required>
                <input v-model="form.email" type="email" class="rounded-xl border border-slate-300 px-4 py-3 sm:col-span-2" placeholder="Email" required>
                <input v-model="form.password" type="password" class="rounded-xl border border-slate-300 px-4 py-3" placeholder="Password" required>
                <input v-model="form.password_confirmation" type="password" class="rounded-xl border border-slate-300 px-4 py-3" placeholder="Confirm password" required>
                <select v-model="form.role" class="rounded-xl border border-slate-300 px-4 py-3">
                    <option value="employee">Employee</option>
                    <option value="finance">Finance</option>
                </select>
                <select :value="form.country_code" class="rounded-xl border border-slate-300 px-4 py-3" @change="applyCountry($event.target.value)">
                    <option v-for="[code, currency] in countries" :key="code" :value="code">{{ code }} ({{ currency }})</option>
                </select>
                <p v-if="Object.keys(errors).length" class="rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700 sm:col-span-2">
                    {{ Object.values(errors)[0][0] }}
                </p>
                <button class="rounded-xl bg-blue-600 px-4 py-3 font-semibold text-white sm:col-span-2">Create account</button>
            </form>
            <p class="mt-6 text-center text-sm text-slate-500">
                Already registered? <Link href="/login" class="font-semibold text-blue-600">Sign in</Link>
            </p>
        </div>
    </div>
</template>
