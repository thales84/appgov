<script setup>
import { Head } from '@inertiajs/vue3';
import { BuildingOffice2Icon, MapPinIcon } from '@heroicons/vue/24/outline';
import { useI18n } from 'vue-i18n';
import EmptyState from '@/Components/EmptyState.vue';
import AgentShell from '@/Layouts/AgentShell.vue';

defineProps({
    scope: {
        type: Object,
        required: true,
    },
});

const { t } = useI18n();
</script>

<template>
    <Head :title="t('meta.agentDashboardTitle')" />
    <AgentShell :title="t('agent.dashboard.title')">
        <section class="mb-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <div class="border border-slate-200 bg-white p-5">
                <BuildingOffice2Icon class="size-6 text-brand-blue-600" aria-hidden="true" />
                <p class="mt-4 text-sm font-semibold text-slate-500">{{ t('agent.dashboard.organization') }}</p>
                <p class="mt-1 font-display text-lg font-semibold text-ink-900">{{ scope.organization }}</p>
                <p v-if="scope.unit" class="mt-1 text-sm text-slate-600">{{ scope.unit }}</p>
            </div>
            <div class="border border-slate-200 bg-white p-5">
                <MapPinIcon class="size-6 text-brand-red-700" aria-hidden="true" />
                <p class="mt-4 text-sm font-semibold text-slate-500">{{ t('agent.dashboard.territory') }}</p>
                <p class="mt-1 font-display text-lg font-semibold text-ink-900">{{ scope.territory || t('agent.dashboard.nationalScope') }}</p>
            </div>
            <div class="border border-slate-200 bg-white p-5 sm:col-span-2 xl:col-span-1">
                <p class="font-mono text-3xl font-medium text-brand-blue-900">0</p>
                <p class="mt-2 text-sm font-semibold text-slate-600">{{ t('agent.dashboard.pendingApplications') }}</p>
            </div>
        </section>

        <section>
            <div class="mb-4 flex items-center justify-between gap-4">
                <h2 class="font-display text-xl font-semibold text-ink-900">{{ t('agent.dashboard.queueTitle') }}</h2>
                <span class="text-sm text-slate-500">{{ t('agent.dashboard.scopeNotice') }}</span>
            </div>
            <EmptyState :title="t('agent.dashboard.emptyTitle')" :description="t('agent.dashboard.emptyDescription')" />
        </section>
    </AgentShell>
</template>
