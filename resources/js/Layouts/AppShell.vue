<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { ArrowRightStartOnRectangleIcon, UserCircleIcon } from '@heroicons/vue/24/outline';
import { useI18n } from 'vue-i18n';
import BrandMark from '@/Components/BrandMark.vue';
import LanguageSwitch from '@/Components/LanguageSwitch.vue';

defineProps({
    title: {
        type: String,
        required: true,
    },
    eyebrow: {
        type: String,
        default: '',
    },
});

const page = usePage();
const { t } = useI18n();

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div class="min-h-screen bg-cloud-50">
        <a href="#main-content" class="fixed left-4 top-3 z-50 -translate-y-24 rounded-md bg-white px-4 py-2 font-semibold text-brand-blue-900 shadow-lg transition focus:translate-y-0">
            {{ t('common.skipToContent') }}
        </a>

        <header class="border-b border-white/10 bg-brand-blue-900 text-white">
            <div class="h-1 bg-brand-red-700" />
            <div class="page-container flex min-h-20 items-center justify-between gap-5 py-3">
                <Link href="/account" class="rounded-md"><BrandMark /></Link>
                <nav class="hidden items-center gap-6 text-sm font-semibold md:flex" :aria-label="t('account.navigation')">
                    <Link href="/account" class="rounded-sm text-blue-50 hover:text-white">{{ t('account.dashboard.nav') }}</Link>
                    <Link href="/account/profile" class="rounded-sm text-blue-50 hover:text-white">{{ t('account.profile.nav') }}</Link>
                </nav>
                <div class="flex items-center gap-2">
                    <LanguageSwitch inverted />
                    <button type="button" class="grid min-h-11 min-w-11 place-items-center rounded-md text-blue-100 hover:bg-white/10 hover:text-white" :aria-label="t('auth.logout')" @click="logout">
                        <ArrowRightStartOnRectangleIcon class="size-5" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </header>

        <main id="main-content" class="page-container py-8 sm:py-12">
            <div class="mb-8 flex flex-col gap-4 border-b border-slate-200 pb-7 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p v-if="eyebrow" class="eyebrow text-brand-blue-600">{{ eyebrow }}</p>
                    <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight text-ink-900 sm:text-4xl">{{ title }}</h1>
                </div>
                <div class="flex items-center gap-2 text-sm text-slate-600">
                    <UserCircleIcon class="size-5" aria-hidden="true" />
                    <span>{{ page.props.auth.user.name }}</span>
                </div>
            </div>
            <slot />
        </main>
    </div>
</template>
