<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: [Number, String], required: true },
    tone: { type: String, default: 'blue' },
});

const tones = {
    blue: 'bg-blue-50 text-blue-700 ring-blue-100',
    green: 'bg-emerald-50 text-emerald-700 ring-emerald-100',
    red: 'bg-rose-50 text-rose-700 ring-rose-100',
    gray: 'bg-slate-50 text-slate-700 ring-slate-100',
};
</script>

<template>
    <div :class="['rounded-2xl p-6 ring-1', tones[tone]]">
        <p class="text-sm font-medium opacity-80">{{ label }}</p>
        <p class="mt-3 text-4xl font-bold">{{ value }}</p>
    </div>
</template>
