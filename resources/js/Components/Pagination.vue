<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowLeftIcon, ArrowRightIcon } from '@heroicons/vue/20/solid';
import { useI18n } from 'vue-i18n';

defineProps({
    meta: {
        type: Object,
        required: true,
    },
});

const { t } = useI18n();
</script>

<template>
    <nav v-if="meta.last_page > 1" class="flex items-center justify-between gap-4 border-t border-slate-200 pt-5" :aria-label="t('common.pageCount', { current: meta.current_page, last: meta.last_page })">
        <Link
            v-if="meta.prev_page_url"
            :href="meta.prev_page_url"
            preserve-scroll
            class="button-secondary px-4"
        >
            <ArrowLeftIcon class="size-4" aria-hidden="true" />
            {{ t('common.previous') }}
        </Link>
        <span v-else class="min-h-11" />

        <span class="font-mono text-sm text-slate-600">
            {{ t('common.pageCount', { current: meta.current_page, last: meta.last_page }) }}
        </span>

        <Link
            v-if="meta.next_page_url"
            :href="meta.next_page_url"
            preserve-scroll
            class="button-secondary px-4"
        >
            {{ t('common.next') }}
            <ArrowRightIcon class="size-4" aria-hidden="true" />
        </Link>
        <span v-else class="min-h-11" />
    </nav>
</template>

