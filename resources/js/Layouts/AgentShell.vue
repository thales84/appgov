<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    ArrowRightStartOnRectangleIcon,
    BuildingOffice2Icon,
    ClipboardDocumentCheckIcon,
    Cog6ToothIcon,
    BookOpenIcon,
    HomeIcon,
    ShieldCheckIcon,
} from '@heroicons/vue/24/outline';
import { useI18n } from 'vue-i18n';
import BrandMark from '@/Components/BrandMark.vue';
import LanguageSwitch from '@/Components/LanguageSwitch.vue';

defineProps({
    title: {
        type: String,
        required: true,
    },
    section: {
        type: String,
        default: 'workspace',
    },
});

const page = usePage();
const { t } = useI18n();

const navigation = [
    { key: 'workspace', href: '/agent', icon: HomeIcon },
    { key: 'applications', href: '/agent', icon: ClipboardDocumentCheckIcon, disabled: true },
    {
        key: 'catalog',
        href: '/admin/catalog',
        icon: BookOpenIcon,
        hidden: !page.props.auth.user.permissions.includes('catalog.view'),
    },
    { key: 'security', href: '/agent/security', icon: ShieldCheckIcon },
];

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div class="min-h-screen bg-slate-100 lg:grid lg:grid-cols-[280px_1fr]">
        <a href="#main-content" class="fixed left-4 top-3 z-50 -translate-y-24 rounded-md bg-white px-4 py-2 font-semibold text-brand-blue-900 shadow-lg transition focus:translate-y-0">
            {{ t('common.skipToContent') }}
        </a>

        <aside class="hidden min-h-screen bg-brand-blue-900 text-white lg:flex lg:flex-col">
            <div class="h-1 bg-brand-red-700" />
            <Link href="/agent" class="mx-6 mt-7 rounded-md"><BrandMark /></Link>
            <div class="mx-6 mt-8 border-y border-white/10 py-4">
                <p class="eyebrow text-blue-200">{{ t('agent.portal') }}</p>
                <p class="mt-2 truncate font-semibold">{{ page.props.auth.user.name }}</p>
            </div>
            <nav class="mt-5 flex-1 px-3" :aria-label="t('agent.navigation')">
                <template v-for="item in navigation.filter((entry) => !entry.hidden)" :key="item.key">
                    <Link
                        v-if="!item.disabled"
                        :href="item.href"
                        class="flex min-h-12 items-center gap-3 rounded-md px-4 text-sm font-semibold"
                        :class="section === item.key ? 'bg-white text-brand-blue-900' : 'text-blue-100 hover:bg-white/10 hover:text-white'"
                    >
                        <component :is="item.icon" class="size-5" aria-hidden="true" />
                        {{ t(`agent.nav.${item.key}`) }}
                    </Link>
                    <span v-else class="flex min-h-12 cursor-not-allowed items-center gap-3 rounded-md px-4 text-sm font-semibold text-blue-300/60">
                        <component :is="item.icon" class="size-5" aria-hidden="true" />
                        {{ t(`agent.nav.${item.key}`) }}
                        <span class="ml-auto text-[10px] uppercase">{{ t('common.soon') }}</span>
                    </span>
                </template>
            </nav>
            <div class="border-t border-white/10 p-4">
                <button type="button" class="flex min-h-11 w-full items-center gap-3 rounded-md px-3 text-sm font-semibold text-blue-100 hover:bg-white/10 hover:text-white" @click="logout">
                    <ArrowRightStartOnRectangleIcon class="size-5" aria-hidden="true" />
                    {{ t('auth.logout') }}
                </button>
            </div>
        </aside>

        <div class="min-w-0">
            <header class="border-b border-slate-200 bg-white">
                <div class="flex min-h-20 items-center justify-between gap-4 px-4 sm:px-7 lg:px-10">
                    <div class="flex items-center gap-3 lg:hidden">
                        <BrandMark compact class="text-brand-blue-900" />
                        <span class="font-display font-semibold text-brand-blue-900">{{ t('agent.portalShort') }}</span>
                    </div>
                    <div class="hidden items-center gap-2 text-sm text-slate-600 lg:flex">
                        <BuildingOffice2Icon class="size-5" aria-hidden="true" />
                        {{ t('agent.secureWorkspace') }}
                    </div>
                    <div class="flex items-center gap-3">
                        <LanguageSwitch />
                        <Link
                            v-if="page.props.auth.user.permissions.includes('platform.manage') || page.props.auth.user.permissions.includes('catalog.view')"
                            :href="page.props.auth.user.permissions.includes('platform.manage') ? '/admin' : '/admin/catalog'"
                            class="grid min-h-11 min-w-11 place-items-center rounded-md text-brand-blue-900 hover:bg-brand-blue-50"
                            :aria-label="t('admin.open')"
                        >
                            <Cog6ToothIcon class="size-5" aria-hidden="true" />
                        </Link>
                    </div>
                </div>
            </header>

            <nav class="flex gap-1 overflow-x-auto border-b border-slate-200 bg-white px-3 py-2 lg:hidden" :aria-label="t('agent.navigation')">
                <template v-for="item in navigation.filter((entry) => !entry.hidden && !entry.disabled)" :key="item.key">
                    <Link
                        :href="item.href"
                        class="flex min-h-11 shrink-0 items-center gap-2 rounded-md px-3 text-sm font-semibold"
                        :class="section === item.key ? 'bg-brand-blue-50 text-brand-blue-900' : 'text-slate-600'"
                    >
                        <component :is="item.icon" class="size-4" aria-hidden="true" />
                        {{ t(`agent.nav.${item.key}`) }}
                    </Link>
                </template>
            </nav>

            <main id="main-content" class="px-4 py-8 sm:px-7 lg:px-10 lg:py-10">
                <div class="mb-8 border-b border-slate-300 pb-6">
                    <p class="eyebrow text-brand-red-700">{{ t('agent.portal') }}</p>
                    <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight text-ink-900">{{ title }}</h1>
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>
