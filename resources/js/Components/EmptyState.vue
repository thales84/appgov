<script setup>
import { DocumentPlusIcon } from '@heroicons/vue/24/outline';

defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <div class="border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
        <span class="mx-auto grid size-12 place-items-center rounded-lg bg-brand-blue-50 text-brand-blue-900">
            <DocumentPlusIcon class="size-6" aria-hidden="true" />
        </span>
        <h3 class="mt-5 font-display text-xl font-semibold text-ink-900">{{ title }}</h3>
        <p class="mx-auto mt-2 max-w-lg leading-6 text-slate-600">{{ description }}</p>
        <div v-if="$slots.action" class="mt-6">
            <slot name="action" />
        </div>
    </div>
</template>
