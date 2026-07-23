<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRightIcon, IdentificationIcon, ShieldCheckIcon } from '@heroicons/vue/24/outline';
import { useI18n } from 'vue-i18n';
import EmptyState from '@/Components/EmptyState.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import AppShell from '@/Layouts/AppShell.vue';

const props = defineProps({
    identity: {
        type: Object,
        required: true,
    },
});

const { t } = useI18n();
const identityTone = computed(() => ({
    unverified: 'warning',
    email_verified: 'info',
    identity_declared: 'success',
    identity_checked: 'success',
}[props.identity.level] || 'neutral'));
</script>

<template>
    <Head :title="t('meta.accountTitle')" />
    <AppShell :title="t('account.dashboard.title')" :eyebrow="t('account.dashboard.eyebrow')">
        <div class="grid gap-6 lg:grid-cols-[1.35fr_0.65fr]">
            <section>
                <div class="mb-4 flex items-center justify-between gap-4">
                    <h2 class="font-display text-xl font-semibold text-ink-900">{{ t('account.dashboard.applicationsTitle') }}</h2>
                    <span class="font-mono text-sm text-slate-500">0</span>
                </div>
                <EmptyState :title="t('account.dashboard.emptyTitle')" :description="t('account.dashboard.emptyDescription')">
                    <template #action>
                        <Link href="/services" class="button-primary">
                            {{ t('account.dashboard.explore') }}
                            <ArrowRightIcon class="size-5" aria-hidden="true" />
                        </Link>
                    </template>
                </EmptyState>
            </section>

            <aside class="space-y-5">
                <section class="border border-slate-200 bg-white p-6">
                    <div class="flex items-start justify-between gap-4">
                        <span class="grid size-11 place-items-center rounded-lg bg-brand-blue-50 text-brand-blue-900">
                            <IdentificationIcon class="size-6" aria-hidden="true" />
                        </span>
                        <StatusBadge :tone="identityTone">{{ t(`identity.levels.${identity.level}`) }}</StatusBadge>
                    </div>
                    <h2 class="mt-5 font-display text-xl font-semibold text-ink-900">{{ t('account.dashboard.identityTitle') }}</h2>
                    <p class="mt-2 leading-6 text-slate-600">{{ t(`identity.explanations.${identity.level}`) }}</p>
                    <Link href="/account/profile" class="mt-5 inline-flex min-h-11 items-center rounded-sm font-semibold text-brand-blue-700 hover:underline">
                        {{ identity.profileComplete ? t('account.dashboard.reviewProfile') : t('account.dashboard.completeProfile') }}
                    </Link>
                </section>

                <section class="border-l-4 border-brand-red-700 bg-brand-blue-900 p-6 text-white">
                    <ShieldCheckIcon class="size-7 text-blue-200" aria-hidden="true" />
                    <h2 class="mt-4 font-display text-lg font-semibold">{{ t('account.dashboard.securityTitle') }}</h2>
                    <p class="mt-2 text-sm leading-6 text-blue-100">{{ t('account.dashboard.securityDescription') }}</p>
                </section>
            </aside>
        </div>
    </AppShell>
</template>
