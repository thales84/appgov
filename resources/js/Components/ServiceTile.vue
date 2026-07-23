<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        required: true,
    },
    color: {
        type: String,
        required: true,
    },
    badge: {
        type: String,
        required: true,
    },
    featured: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <article
        class="group relative flex min-h-56 flex-col overflow-hidden rounded-lg border bg-white p-6 transition hover:-translate-y-0.5 hover:border-slate-300"
        :class="featured ? 'border-brand-blue-600' : 'border-slate-200'"
    >
        <div class="absolute inset-y-0 left-0 w-1.5" :style="{ backgroundColor: color }" />

        <div class="mb-8 flex items-start justify-between gap-4">
            <span
                class="grid size-11 place-items-center rounded-md text-white"
                :style="{ backgroundColor: color }"
                aria-hidden="true"
            >
                <slot name="icon" />
            </span>
            <span
                class="rounded border px-2 py-1 text-xs font-semibold"
                :class="featured ? 'border-brand-blue-600/25 bg-brand-blue-50 text-brand-blue-900' : 'border-slate-200 bg-slate-50 text-slate-600'"
            >
                {{ badge }}
            </span>
        </div>

        <h3 class="font-display text-xl font-semibold text-ink-900">{{ title }}</h3>
        <p class="mt-2 leading-6 text-slate-600">{{ description }}</p>
    </article>
</template>
