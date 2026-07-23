<script setup>
const props = defineProps({
    colorKey: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
});

const tones = {
    mobility: 'border-service-mobility/30 bg-blue-50 text-blue-800',
    immigration: 'border-service-immigration/30 bg-violet-50 text-violet-800',
    nationality: 'border-service-nationality/30 bg-amber-50 text-amber-900',
    education: 'border-service-education/30 bg-emerald-50 text-emerald-800',
    civil: 'border-service-civil/30 bg-pink-50 text-pink-800',
    business: 'border-service-business/30 bg-teal-50 text-teal-800',
};

const markers = {
    mobility: 'bg-service-mobility',
    immigration: 'bg-service-immigration',
    nationality: 'bg-service-nationality',
    education: 'bg-service-education',
    civil: 'bg-service-civil',
    business: 'bg-service-business',
};
</script>

<template>
    <span class="inline-flex items-center gap-2 rounded-md border px-2.5 py-1 text-sm font-semibold" :class="tones[props.colorKey]">
        <span class="size-2 rounded-sm" :class="markers[props.colorKey]" aria-hidden="true" />
        {{ label }}
    </span>
</template>

