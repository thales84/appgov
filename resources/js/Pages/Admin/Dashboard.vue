<script setup>
import { Head } from '@inertiajs/vue3';
import {
    BuildingOffice2Icon,
    MapIcon,
    ShieldCheckIcon,
    UsersIcon,
} from '@heroicons/vue/24/outline';
import { useI18n } from 'vue-i18n';
import AgentShell from '@/Layouts/AgentShell.vue';

const { t } = useI18n();
const modules = [
    { key: 'organizations', icon: BuildingOffice2Icon },
    { key: 'territories', icon: MapIcon },
    { key: 'agents', icon: UsersIcon },
    { key: 'permissions', icon: ShieldCheckIcon },
];
</script>

<template>
    <Head :title="t('meta.adminTitle')" />
    <AgentShell :title="t('admin.title')" section="admin">
        <p class="max-w-3xl text-lg leading-7 text-slate-600">{{ t('admin.description') }}</p>
        <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <section v-for="module in modules" :key="module.key" class="border border-slate-200 bg-white p-6">
                <component :is="module.icon" class="size-7 text-brand-blue-600" aria-hidden="true" />
                <h2 class="mt-5 font-display text-lg font-semibold text-ink-900">{{ t(`admin.modules.${module.key}.title`) }}</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">{{ t(`admin.modules.${module.key}.description`) }}</p>
                <span class="mt-5 inline-flex rounded border border-slate-300 bg-slate-50 px-2 py-1 text-xs font-semibold uppercase text-slate-600">{{ t('common.skeleton') }}</span>
            </section>
        </div>
    </AgentShell>
</template>
