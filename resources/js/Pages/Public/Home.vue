<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import {
    AcademicCapIcon,
    ArrowRightIcon,
    BriefcaseIcon,
    BuildingLibraryIcon,
    IdentificationIcon,
    MapIcon,
    TruckIcon,
} from '@heroicons/vue/24/outline';
import ApplicationRail from '@/Components/ApplicationRail.vue';
import PublicHeader from '@/Components/PublicHeader.vue';
import ServiceTile from '@/Components/ServiceTile.vue';

const { t } = useI18n();

const services = computed(() => [
    { key: 'mobility', color: '#2563EB', icon: TruckIcon, featured: true },
    { key: 'immigration', color: '#7C3AED', icon: IdentificationIcon },
    { key: 'nationality', color: '#A15C00', icon: BuildingLibraryIcon },
    { key: 'education', color: '#047857', icon: AcademicCapIcon },
    { key: 'civil', color: '#BE185D', icon: MapIcon },
    { key: 'business', color: '#0F766E', icon: BriefcaseIcon },
]);

const steps = computed(() => [
    { label: t('tracking.submitted'), date: t('tracking.submittedDate'), status: 'complete' },
    { label: t('tracking.documents'), date: t('tracking.documentsDate'), status: 'complete' },
    { label: t('tracking.exam'), date: t('tracking.examDate'), helper: t('home.currentStep'), status: 'current' },
    { label: t('tracking.decision'), helper: t('home.nextStep'), status: 'upcoming' },
    { label: t('tracking.production'), status: 'upcoming' },
]);
</script>

<template>
    <Head :title="t('meta.homeTitle')" />

    <div class="min-h-screen bg-cloud-50">
        <PublicHeader />

        <main id="main-content">
            <section class="relative overflow-hidden bg-brand-blue-900 text-white">
                <div class="absolute inset-y-0 right-0 hidden w-[34%] border-l border-white/10 lg:block" aria-hidden="true">
                    <div class="absolute inset-y-0 left-12 w-px bg-white/10" />
                    <div class="absolute inset-y-0 left-24 w-px bg-white/10" />
                    <div class="absolute right-16 top-20 size-40 border border-white/15" />
                    <div class="absolute bottom-12 right-40 size-20 bg-brand-red-700" />
                </div>

                <div class="page-container relative grid gap-12 py-16 sm:py-24 lg:grid-cols-[1.5fr_0.7fr] lg:py-28">
                    <div class="max-w-4xl">
                        <p class="eyebrow text-blue-200">{{ t('home.eyebrow') }}</p>
                        <h1 class="mt-6 max-w-4xl font-display text-4xl font-bold leading-[1.08] sm:text-5xl lg:text-7xl">
                            {{ t('home.title') }}
                        </h1>
                        <p class="mt-7 max-w-2xl text-lg leading-8 text-blue-100 sm:text-xl">
                            {{ t('home.intro') }}
                        </p>
                        <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                            <Link href="/services" class="button-primary bg-white text-brand-blue-900 hover:bg-blue-50">
                                {{ t('home.explore') }}
                                <ArrowRightIcon class="size-5" aria-hidden="true" />
                            </Link>
                            <a href="#tracking" class="button-secondary border-white/35 bg-transparent text-white hover:border-white/60 hover:bg-white/10">
                                {{ t('home.seeTracking') }}
                            </a>
                        </div>
                    </div>

                    <aside class="self-end border-l-4 border-brand-red-700 bg-white p-6 text-ink-900 lg:translate-y-8">
                        <p class="eyebrow text-brand-red-700">{{ t('home.digitalFirst') }}</p>
                        <p class="mt-4 leading-7 text-slate-700">{{ t('home.digitalFirstText') }}</p>
                    </aside>
                </div>
            </section>

            <section id="services" class="page-container scroll-mt-6 py-16 sm:py-24">
                <div class="max-w-3xl">
                    <p class="eyebrow text-brand-blue-600">{{ t('home.servicesEyebrow') }}</p>
                    <h2 class="mt-4 font-display text-3xl font-semibold tracking-tight sm:text-5xl">
                        {{ t('home.servicesTitle') }}
                    </h2>
                    <p class="mt-5 text-lg leading-8 text-slate-600">{{ t('home.servicesIntro') }}</p>
                </div>

                <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    <ServiceTile
                        v-for="service in services"
                        :key="service.key"
                        :title="t(`services.${service.key}.name`)"
                        :description="t(`services.${service.key}.description`)"
                        :color="service.color"
                        :badge="service.featured ? t('home.firstService') : t('home.plannedService')"
                        :featured="service.featured"
                    >
                        <template #icon>
                            <component :is="service.icon" class="size-6" />
                        </template>
                    </ServiceTile>
                </div>
            </section>

            <section id="process" class="scroll-mt-6 border-y border-slate-200 bg-white py-16 sm:py-24">
                <div class="page-container grid items-start gap-12 lg:grid-cols-[0.9fr_1.1fr] lg:gap-20">
                    <div>
                        <p class="eyebrow text-brand-blue-600">{{ t('home.processEyebrow') }}</p>
                        <h2 class="mt-4 font-display text-3xl font-semibold tracking-tight sm:text-5xl">
                            {{ t('home.processTitle') }}
                        </h2>
                        <p class="mt-5 text-lg leading-8 text-slate-600">{{ t('home.processIntro') }}</p>
                    </div>

                    <div id="tracking" class="scroll-mt-6 border border-slate-200 bg-cloud-50">
                        <div class="border-b border-slate-200 bg-white p-5 sm:flex sm:items-end sm:justify-between sm:gap-4">
                            <div>
                                <p class="eyebrow text-slate-500">{{ t('home.referenceLabel') }}</p>
                                <p class="mt-2 break-all font-mono text-sm font-medium text-brand-blue-900 sm:text-base">
                                    {{ t('home.reference') }}
                                </p>
                            </div>
                            <span class="mt-3 inline-flex rounded border border-brand-red-700/20 bg-brand-red-50 px-2 py-1 text-xs font-semibold text-brand-red-700 sm:mt-0">
                                {{ t('common.demo') }}
                            </span>
                        </div>
                        <div class="p-6 sm:p-8">
                            <ApplicationRail :steps="steps" />
                            <p class="mt-8 border-t border-slate-200 pt-5 text-sm leading-6 text-slate-600">
                                {{ t('home.trackingNotice') }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="page-container py-16 sm:py-24">
                <div class="grid overflow-hidden rounded-lg bg-brand-blue-900 text-white lg:grid-cols-[1fr_auto]">
                    <div class="p-8 sm:p-12">
                        <h2 class="font-display text-3xl font-semibold">{{ t('home.prepareTitle') }}</h2>
                        <p class="mt-4 max-w-3xl text-lg leading-8 text-blue-100">{{ t('home.prepareText') }}</p>
                    </div>
                    <div class="grid min-h-36 min-w-44 place-items-center bg-brand-red-700 p-8" aria-hidden="true">
                        <span class="font-display text-5xl font-bold">01</span>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-slate-200 bg-white">
            <div class="page-container flex flex-col gap-2 py-8 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between">
                <p>{{ t('common.republic') }} · {{ t('common.motto') }}</p>
                <p>{{ t('home.footerNote') }}</p>
            </div>
        </footer>
    </div>
</template>
