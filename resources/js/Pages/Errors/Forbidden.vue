<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ShieldExclamationIcon } from '@heroicons/vue/24/outline';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <Head :title="t('errors.forbidden.title')" />
    <main class="grid min-h-screen place-items-center bg-cloud-50 px-4 py-12">
        <div class="w-full max-w-xl border-t-4 border-brand-red-700 bg-white p-8 text-center shadow-sm">
            <ShieldExclamationIcon class="mx-auto size-12 text-brand-red-700" aria-hidden="true" />
            <p class="eyebrow mt-6 text-brand-red-700">403</p>
            <h1 class="mt-3 font-display text-3xl font-semibold text-ink-900">{{ t('errors.forbidden.title') }}</h1>
            <p class="mt-4 leading-7 text-slate-600">{{ t('errors.forbidden.description') }}</p>
            <Link href="/" class="button-primary mt-7">{{ t('errors.backHome') }}</Link>
        </div>
    </main>
</template>
