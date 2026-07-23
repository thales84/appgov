<script setup>
defineProps({
    id: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
    error: {
        type: String,
        default: '',
    },
    hint: {
        type: String,
        default: '',
    },
    required: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div>
        <label :for="id" class="block text-sm font-semibold text-ink-900">
            {{ label }}
            <span v-if="required" class="text-brand-red-700" aria-hidden="true">*</span>
        </label>
        <p v-if="hint" :id="`${id}-hint`" class="mt-1 text-sm leading-5 text-slate-600">{{ hint }}</p>
        <div class="mt-2">
            <slot :described-by="[hint ? `${id}-hint` : '', error ? `${id}-error` : ''].filter(Boolean).join(' ') || undefined" />
        </div>
        <p v-if="error" :id="`${id}-error`" class="mt-2 flex items-start gap-2 text-sm font-semibold text-brand-red-700">
            <span aria-hidden="true">!</span>
            <span>{{ error }}</span>
        </p>
    </div>
</template>
