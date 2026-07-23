<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeftIcon,
    ArrowRightIcon,
    CheckIcon,
    DocumentTextIcon,
    InformationCircleIcon,
    ShieldCheckIcon,
} from '@heroicons/vue/24/outline';
import { useI18n } from 'vue-i18n';
import Money from '@/Components/Money.vue';
import PublicHeader from '@/Components/PublicHeader.vue';
import ServiceBadge from '@/Components/ServiceBadge.vue';

const props = defineProps({
    service: {
        type: Object,
        required: true,
    },
});

const { t, locale } = useI18n();
const text = (value) => value?.[locale.value] ?? value?.fr ?? '';
const categoryBorders = {
    mobility: 'border-service-mobility',
    immigration: 'border-service-immigration',
    nationality: 'border-service-nationality',
    education: 'border-service-education',
    civil: 'border-service-civil',
    business: 'border-service-business',
};
const dateFormatter = computed(() => new Intl.DateTimeFormat(
    locale.value === 'fr' ? 'fr-CM' : 'en-CM',
    { dateStyle: 'long', timeZone: 'Africa/Douala' },
));
</script>

<template>
    <Head :title="text(service.procedure.title)" />

    <div class="min-h-screen bg-cloud-50">
        <PublicHeader />

        <main id="main-content">
            <section class="border-b border-slate-200 bg-white">
                <div class="page-container py-8 sm:py-12">
                    <Link href="/services" class="inline-flex min-h-11 items-center gap-2 rounded-md font-semibold text-brand-blue-700 hover:text-brand-blue-900">
                        <ArrowLeftIcon class="size-5" aria-hidden="true" />
                        {{ t('catalog.detail.back') }}
                    </Link>

                    <div class="mt-6 border-l-4 pl-5 sm:pl-7" :class="categoryBorders[service.category.colorKey]">
                        <div class="flex flex-wrap items-center gap-3">
                            <ServiceBadge :color-key="service.category.colorKey" :label="text(service.category.name)" />
                            <span v-if="service.isDemo" class="rounded border border-brand-red-700/25 bg-brand-red-50 px-2 py-1 text-xs font-semibold text-brand-red-700">
                                {{ t('common.demo') }}
                            </span>
                        </div>
                        <h1 class="mt-5 max-w-4xl font-display text-4xl font-semibold leading-tight tracking-tight text-brand-blue-900 sm:text-5xl">
                            {{ text(service.procedure.title) }}
                        </h1>
                        <p class="mt-5 max-w-4xl text-lg leading-8 text-slate-600">{{ text(service.procedure.summary) }}</p>
                    </div>

                    <div class="mt-8 grid gap-4 border-y border-slate-200 py-5 text-sm sm:grid-cols-2">
                        <div>
                            <p class="font-semibold text-ink-900">{{ t('catalog.detail.responsible') }}</p>
                            <p class="mt-1 text-slate-600">{{ text(service.organization.name) }}</p>
                        </div>
                        <div>
                            <p class="font-semibold text-ink-900">{{ t('common.version', { number: service.procedure.versionNumber }) }}</p>
                            <p class="mt-1 text-slate-600">
                                {{ t('catalog.detail.effectiveFrom', { date: dateFormatter.format(new Date(service.procedure.effectiveFrom)) }) }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <div class="page-container grid gap-10 py-10 lg:grid-cols-[minmax(0,1fr)_320px] lg:py-14">
                <div class="min-w-0 space-y-12">
                    <section v-if="service.isDemo" class="border border-brand-red-700/25 bg-brand-red-50 p-5 sm:flex sm:gap-4">
                        <InformationCircleIcon class="size-7 shrink-0 text-brand-red-700" aria-hidden="true" />
                        <div class="mt-3 sm:mt-0">
                            <h2 class="font-display text-lg font-semibold text-brand-red-700">{{ t('catalog.detail.demoTitle') }}</h2>
                            <p class="mt-1 leading-7 text-slate-700">{{ t('catalog.detail.demoNotice') }}</p>
                        </div>
                    </section>

                    <section>
                        <h2 class="font-display text-2xl font-semibold text-ink-900">{{ t('catalog.detail.about') }}</h2>
                        <p class="mt-4 whitespace-pre-line leading-7 text-slate-700">{{ text(service.procedure.description) }}</p>
                    </section>

                    <section>
                        <h2 class="font-display text-2xl font-semibold text-ink-900">{{ t('catalog.detail.eligibility') }}</h2>
                        <p class="mt-4 whitespace-pre-line border-l-2 border-brand-blue-600 pl-5 leading-7 text-slate-700">
                            {{ text(service.procedure.eligibility) }}
                        </p>
                    </section>

                    <section>
                        <h2 class="font-display text-2xl font-semibold text-ink-900">{{ t('catalog.detail.steps') }}</h2>
                        <p class="mt-2 text-slate-600">{{ t('catalog.detail.stepsIntro') }}</p>
                        <ol class="mt-7">
                            <li v-for="(step, index) in service.procedure.steps" :key="step.publicId" class="relative grid grid-cols-[44px_1fr] gap-4 pb-7 last:pb-0">
                                <span v-if="index < service.procedure.steps.length - 1" class="absolute left-[21px] top-10 h-full w-px bg-slate-300" aria-hidden="true" />
                                <span class="relative z-10 grid size-11 place-items-center rounded-full border-2 border-brand-blue-600 bg-white font-mono text-sm font-semibold text-brand-blue-900">
                                    {{ String(index + 1).padStart(2, '0') }}
                                </span>
                                <div class="pt-1">
                                    <h3 class="font-display text-lg font-semibold text-ink-900">{{ text(step.name) }}</h3>
                                    <p class="mt-1 leading-6 text-slate-600">{{ text(step.description) }}</p>
                                </div>
                            </li>
                        </ol>
                    </section>

                    <section v-if="service.procedure.fields.length">
                        <h2 class="font-display text-2xl font-semibold text-ink-900">{{ t('catalog.detail.requestedInformation') }}</h2>
                        <ul class="mt-5 divide-y divide-slate-200 border-y border-slate-200">
                            <li v-for="field in service.procedure.fields" :key="field.publicId" class="flex gap-3 py-4">
                                <CheckIcon class="mt-0.5 size-5 shrink-0 text-brand-blue-600" aria-hidden="true" />
                                <div>
                                    <p class="font-semibold text-ink-900">{{ text(field.label) }}</p>
                                    <p v-if="text(field.help)" class="mt-1 text-sm text-slate-600">{{ text(field.help) }}</p>
                                </div>
                            </li>
                        </ul>
                    </section>

                    <section v-if="service.procedure.documents.length">
                        <h2 class="font-display text-2xl font-semibold text-ink-900">{{ t('catalog.detail.documents') }}</h2>
                        <ul class="mt-5 grid gap-3 sm:grid-cols-2">
                            <li v-for="document in service.procedure.documents" :key="document.publicId" class="border border-slate-200 bg-white p-5">
                                <DocumentTextIcon class="size-6 text-brand-blue-600" aria-hidden="true" />
                                <p class="mt-3 font-semibold text-ink-900">{{ text(document.name) }}</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">{{ text(document.description) }}</p>
                                <span class="mt-3 inline-block text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    {{ document.isRequired ? t('common.required') : t('common.optional') }}
                                </span>
                            </li>
                        </ul>
                    </section>

                    <section v-if="service.procedure.rules.length">
                        <h2 class="font-display text-2xl font-semibold text-ink-900">{{ t('catalog.detail.rules') }}</h2>
                        <dl class="mt-5 divide-y divide-slate-200 border-y border-slate-200">
                            <div v-for="rule in service.procedure.rules" :key="rule.publicId" class="py-4">
                                <dt class="font-semibold text-ink-900">{{ text(rule.name) }}</dt>
                                <dd class="mt-1 leading-6 text-slate-600">{{ text(rule.description) }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section>
                        <h2 class="font-display text-2xl font-semibold text-ink-900">{{ t('catalog.detail.fees') }}</h2>
                        <div v-if="service.procedure.fees.length" class="mt-5 overflow-hidden border border-slate-200 bg-white">
                            <div v-for="fee in service.procedure.fees" :key="fee.publicId" class="grid gap-3 border-b border-slate-200 p-5 last:border-b-0 sm:grid-cols-[1fr_auto]">
                                <div>
                                    <p class="font-semibold text-ink-900">{{ text(fee.label) }}</p>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">{{ text(fee.description) }}</p>
                                    <p v-if="text(fee.dueWhen)" class="mt-2 text-sm text-slate-500">{{ t('catalog.detail.feeMoment', { moment: text(fee.dueWhen) }) }}</p>
                                </div>
                                <Money :amount-minor="fee.amountMinor" :currency="fee.currency" :minor-unit-exponent="fee.minorUnitExponent" class="text-lg text-brand-blue-900" />
                            </div>
                        </div>
                        <p v-else class="mt-4 text-slate-600">{{ t('catalog.detail.noFees') }}</p>
                    </section>

                    <section v-if="text(service.procedure.legalBasis)">
                        <h2 class="font-display text-2xl font-semibold text-ink-900">{{ t('catalog.detail.legalBasis') }}</h2>
                        <p class="mt-4 leading-7 text-slate-600">{{ text(service.procedure.legalBasis) }}</p>
                    </section>
                </div>

                <aside class="lg:sticky lg:top-6 lg:self-start">
                    <div class="border-t-4 border-brand-red-700 bg-brand-blue-900 p-6 text-white">
                        <ShieldCheckIcon class="size-8 text-blue-200" aria-hidden="true" />
                        <h2 class="mt-5 font-display text-2xl font-semibold">{{ t('catalog.detail.startTitle') }}</h2>
                        <p class="mt-3 leading-7 text-blue-100">{{ t('catalog.detail.startText') }}</p>
                        <Link :href="`/account/services/${service.publicId}/start`" class="mt-6 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-md bg-white px-5 font-semibold text-brand-blue-900 hover:bg-blue-50">
                            {{ t('catalog.detail.start') }}
                            <ArrowRightIcon class="size-5" aria-hidden="true" />
                        </Link>
                    </div>
                </aside>
            </div>
        </main>

        <footer class="border-t border-slate-200 bg-white">
            <div class="page-container flex flex-col gap-2 py-8 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between">
                <p>{{ t('common.republic') }} · {{ t('common.motto') }}</p>
                <p>{{ t('home.footerNote') }}</p>
            </div>
        </footer>
    </div>
</template>
