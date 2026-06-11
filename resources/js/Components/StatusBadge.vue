<script setup>
defineProps({ status: { type: String, required: true } });

const styles = {
    pending: 'bg-amber-400/15 text-amber-200 ring-amber-400/25',
    approved: 'bg-emerald-400/15 text-emerald-200 ring-emerald-400/25',
    rejected: 'bg-rose-400/15 text-rose-200 ring-rose-400/25',
    expired: 'bg-slate-700 text-slate-200 ring-slate-600',
};
</script>

<template>
    <span :class="['inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide ring-1', styles[status] || styles.expired]">
        {{ status }}
    </span>
</template>
