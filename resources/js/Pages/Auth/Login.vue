<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { login, me } from '../../Services/auth';
import { clearSession } from '../../Services/api';

const form = reactive({ email: 'finance@example.com', password: 'password' });
const loading = ref(false);
const error = ref('');

async function submit() {
    loading.value = true;
    error.value = '';
    try {
        await login(form);
        await me();
        router.visit('/dashboard');
    } catch (exception) {
        clearSession();
        error.value = exception.response?.data?.message || 'Unable to sign in.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <Head title="Login" />
    <div class="flex min-h-screen items-center justify-center bg-slate-950 px-4 text-slate-100">
        <div class="w-full max-w-md rounded-3xl border border-slate-800 bg-slate-900 p-8 shadow-2xl shadow-black/40">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-blue-300">Buzzvel Test</p>
            <h1 class="mt-3 text-3xl font-bold text-white">Sign in</h1>
            <form class="mt-8 space-y-5" @submit.prevent="submit">
                <input v-model="form.email" type="email" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-500 focus:border-blue-400 focus:outline-none" placeholder="Email" required>
                <input v-model="form.password" type="password" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-white placeholder:text-slate-500 focus:border-blue-400 focus:outline-none" placeholder="Password" required>
                <p v-if="error" class="rounded-xl border border-rose-400/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">{{ error }}</p>
                <button class="w-full rounded-xl bg-blue-600 px-4 py-3 font-semibold text-white hover:bg-blue-500 disabled:opacity-60" :disabled="loading">
                    {{ loading ? 'Signing in...' : 'Sign in' }}
                </button>
            </form>
            <p class="mt-6 text-center text-sm text-slate-400">
                Need an account? <Link href="/register" class="font-semibold text-blue-300 hover:text-blue-200">Create one</Link>
            </p>
        </div>
    </div>
</template>
