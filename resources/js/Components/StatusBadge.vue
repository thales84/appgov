<script setup>
import {
    CheckCircleIcon,
    ClockIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/20/solid';

const props = defineProps({
    tone: {
        type: String,
        default: 'neutral',
        validator: (value) => ['neutral', 'info', 'warning', 'success', 'danger'].includes(value),
    },
});

const tones = {
    neutral: 'border-slate-300 bg-slate-50 text-slate-700',
    info: 'border-brand-blue-600/25 bg-brand-blue-50 text-brand-blue-900',
    warning: 'border-amber-700/25 bg-amber-50 text-amber-800',
    success: 'border-green-700/25 bg-green-50 text-green-800',
    danger: 'border-brand-red-700/25 bg-brand-red-50 text-brand-red-700',
};

const icons = {
    neutral: ClockIcon,
    info: ClockIcon,
    warning: ExclamationTriangleIcon,
    success: CheckCircleIcon,
    danger: ExclamationTriangleIcon,
};
</script>

<template>
    <span class="inline-flex items-center gap-1.5 rounded-md border px-2.5 py-1 text-sm font-semibold" :class="tones[props.tone]">
        <component :is="icons[props.tone]" class="size-4" aria-hidden="true" />
        <slot />
    </span>
</template>
