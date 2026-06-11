<script setup>
import { Link, router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { currentUser, isAuthenticated } from '../Services/api';
import { logout } from '../Services/auth';

const user = ref(currentUser());

onMounted(() => {
    if (!isAuthenticated()) router.visit('/login');
});

async function signOut() {
    await logout();
    router.visit('/login');
}
</script>

<template>
    <div class="min-h-screen bg-slate-100 text-slate-900">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <Link href="/dashboard" class="text-lg font-bold text-slate-950">Multi-Currency Payment</Link>
                <nav class="hidden items-center gap-6 text-sm font-medium text-slate-600 md:flex">
                    <Link href="/dashboard" class="hover:text-blue-700">Dashboard</Link>
                    <Link href="/payment-requests" class="hover:text-blue-700">Requests</Link>
                    <Link v-if="user?.role === 'employee'" href="/payment-requests/create" class="hover:text-blue-700">New Request</Link>
                </nav>
                <div class="flex items-center gap-4">
                    <div class="hidden text-right text-sm sm:block">
                        <p class="font-semibold">{{ user?.name }}</p>
                        <p class="text-slate-500">{{ user?.role }} · {{ user?.currency_code }}</p>
                    </div>
                    <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700" @click="signOut">Logout</button>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <slot />
        </main>
    </div>
</template>
