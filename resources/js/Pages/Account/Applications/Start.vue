<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeftIcon,
    LockClosedIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';
import { useI18n } from 'vue-i18n';
import AppShell from '@/Layouts/AppShell.vue';
import ServiceBadge from '@/Components/ServiceBadge.vue';

const props = defineProps({
    service: {
        type: Object,
        required: true,
    },
});

const { t, locale } = useI18n();
const text = (value) => value?.[locale.value] ?? value?.fr ?? '';
const form = useForm({});
const title = computed(() => text(props.service.procedure.title));

function start() {
    form.post(`/account/services/${props.service.publicId}/applications`);
}
</script>

<template>
    <Head :title="t('meta.startApplicationTitle')" />

    <AppShell :title="t('applications.start.title')" :eyebrow="t('applications.start.eyebrow')">
        <Link :href="`/services/${service.publicId}`" class="mb-6 inline-flex min-h-11 items-center gap-2 rounded-md font-semibold text-brand-blue-700">
            <ArrowLeftIcon class="size-5" aria-hidden="true" />
            {{ t('catalog.detail.back') }}
        </Link>

        <div class="grid gap-7 lg:grid-cols-[minmax(0,1fr)_320px]">
            <section class="border border-slate-200 bg-white p-6 sm:p-8">
                <ServiceBadge :color-key="service.category.colorKey" :label="text(service.category.name)" />
                <h2 class="mt-5 font-display text-2xl font-semibold text-brand-blue-900">{{ title }}</h2>
                <p class="mt-3 leading-7 text-slate-600">{{ t('applications.start.intro') }}</p>

                <dl class="mt-7 grid gap-5 border-y border-slate-200 py-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-semibold text-slate-500">{{ t('applications.start.version') }}</dt>
                        <dd class="mt-1 font-mono text-brand-blue-900">{{ t('common.version', { number: service.procedure.versionNumber }) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold text-slate-500">{{ t('catalog.detail.responsible') }}</dt>
                        <dd class="mt-1 text-ink-900">{{ text(service.organization.name) }}</dd>
                    </div>
                </dl>

                <div class="mt-6 flex gap-3 bg-brand-blue-50 p-4 text-brand-blue-900">
                    <LockClosedIcon class="mt-0.5 size-5 shrink-0" aria-hidden="true" />
                    <p class="font-semibold">{{ t('applications.start.private') }}</p>
                </div>

                <div v-if="service.isDemo" class="mt-4 flex gap-3 border border-brand-red-700/25 bg-brand-red-50 p-4 text-slate-700">
                    <ExclamationTriangleIcon class="mt-0.5 size-5 shrink-0 text-brand-red-700" aria-hidden="true" />
                    <p>{{ t('applications.start.demoWarning') }}</p>
                </div>
            </section>

            <aside class="border-t-4 border-brand-red-700 bg-brand-blue-900 p-6 text-white lg:self-start">
                <p class="font-display text-xl font-semibold">{{ title }}</p>
                <p class="mt-3 text-sm leading-6 text-blue-100">{{ text(service.procedure.summary) }}</p>
                <button type="button" class="mt-6 min-h-12 w-full rounded-md bg-white px-5 font-semibold text-brand-blue-900 hover:bg-blue-50 disabled:opacity-60" :disabled="form.processing" @click="start">
                    {{ form.processing ? t('common.processing') : t('applications.start.confirm') }}
                </button>
            </aside>
        </div>
    </AppShell>
</template>

