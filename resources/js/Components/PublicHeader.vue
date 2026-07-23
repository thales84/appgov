<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import BrandMark from '@/Components/BrandMark.vue';
import LanguageSwitch from '@/Components/LanguageSwitch.vue';

const { t } = useI18n();
const page = usePage();
</script>

<template>
    <a
        href="#main-content"
        class="fixed left-4 top-3 z-50 -translate-y-24 rounded-md bg-white px-4 py-2 font-semibold text-brand-blue-900 shadow-lg transition focus:translate-y-0"
    >
        {{ t('common.skipToContent') }}
    </a>

    <header class="border-b border-white/10 bg-brand-blue-900 text-white">
        <div class="h-1 bg-brand-red-700" />

        <div class="page-container flex min-h-20 items-center justify-between gap-5 py-3">
            <Link href="/" class="rounded-md"><BrandMark /></Link>

            <nav class="hidden items-center gap-7 text-sm font-semibold lg:flex" :aria-label="t('nav.services')">
                <Link href="/services" class="rounded-sm text-blue-50 hover:text-white">{{ t('nav.services') }}</Link>
                <a href="/#process" class="rounded-sm text-blue-50 hover:text-white">{{ t('nav.howItWorks') }}</a>
                <a href="/#tracking" class="rounded-sm text-blue-50 hover:text-white">{{ t('nav.tracking') }}</a>
            </nav>

            <div class="flex items-center gap-3">
                <LanguageSwitch inverted />
                <Link
                    :href="page.props.auth.user ? (page.props.auth.user.accountType === 'agent' ? '/agent' : '/account') : '/login'"
                    class="hidden min-h-11 items-center rounded-md border border-white/30 px-4 text-sm font-semibold text-white hover:bg-white/10 sm:inline-flex"
                >
                    {{ page.props.auth.user ? t('nav.account') : t('auth.login.submit') }}
                </Link>
            </div>
        </div>
    </header>
</template>
