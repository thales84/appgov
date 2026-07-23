<script setup>
import { useI18n } from 'vue-i18n';

defineProps({
    compact: {
        type: Boolean,
        default: false,
    },
});

const { t } = useI18n();
</script>

<template>
    <span class="flex min-w-0 items-center gap-3">
        <span class="grid size-11 shrink-0 place-items-center border border-current/30 bg-current/5 font-display text-lg font-bold">
            AG
        </span>
        <span v-if="!compact" class="min-w-0">
            <span class="block truncate font-display text-lg font-bold">{{ t('common.brand') }}</span>
            <span class="block truncate text-xs opacity-75">{{ t('common.republic') }}</span>
        </span>
    </span>
</template>
