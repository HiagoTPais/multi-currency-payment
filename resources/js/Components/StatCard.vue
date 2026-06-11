<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: [Number, String], required: true },
    tone: { type: String, default: 'blue' },
});

const tones = {
    blue: 'bg-amber-500/10 text-amber-200 ring-amber-400/20',
    green: 'bg-emerald-500/10 text-emerald-200 ring-emerald-400/20',
    red: 'bg-rose-500/10 text-rose-200 ring-rose-400/20',
    gray: 'bg-slate-800 text-slate-200 ring-slate-700',
};
</script>

<template>
    <div :class="['rounded-2xl p-6 shadow-lg shadow-black/20 ring-1', tones[tone]]">
        <p class="text-sm font-medium opacity-80">{{ label }}</p>
        <p class="mt-3 text-4xl font-bold">{{ value }}</p>
    </div>
</template>
