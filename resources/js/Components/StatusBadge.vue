<script setup>
defineProps({ status: { type: String, required: true } });

const styles = {
    pending: 'bg-amber-100 text-amber-800',
    approved: 'bg-emerald-100 text-emerald-800',
    rejected: 'bg-rose-100 text-rose-800',
    expired: 'bg-slate-200 text-slate-700',
};
</script>

<template>
    <span :class="['inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide', styles[status] || styles.expired]">
        {{ status }}
    </span>
</template>
