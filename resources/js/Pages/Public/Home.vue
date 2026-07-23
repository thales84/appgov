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
    { label: t('tracking.submitted'), description: t('tracking.submittedDesc'), date: t('tracking.submittedDate'), status: 'complete' },
    { label: t('tracking.documents'), description: t('tracking.documentsDesc'), date: t('tracking.documentsDate'), status: 'complete' },
    { label: t('tracking.exam'), description: t('tracking.examDesc'), date: t('tracking.examDate'), helper: t('home.currentStep'), status: 'current' },
    { label: t('tracking.decision'), description: t('tracking.decisionDesc'), date: t('tracking.decisionDate'), helper: t('home.nextStep'), status: 'upcoming' },
    { label: t('tracking.production'), description: t('tracking.productionDesc'), date: t('tracking.productionDate'), status: 'upcoming' },
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
                    <div class="space-y-8">
                        <div>
                            <p class="eyebrow text-brand-blue-600">{{ t('home.processEyebrow') }}</p>
                            <h2 class="mt-4 font-display text-3xl font-bold tracking-tight text-slate-900 sm:text-5xl">
                                {{ t('home.processTitle') }}
                            </h2>
                            <p class="mt-4 text-base sm:text-lg leading-relaxed text-slate-600">
                                {{ t('home.processIntro') }}
                            </p>
                        </div>

                        <!-- Visuel d'illustration avec Effet Wahouuu & Glassmorphism -->
                        <div class="relative group mt-6 rounded-2xl p-1 bg-gradient-to-br from-brand-blue-500/30 via-slate-200 to-brand-blue-900/40 shadow-2xl shadow-brand-blue-900/15">
                            <!-- Background Glow Blobs -->
                            <div class="absolute -inset-1 bg-gradient-to-r from-brand-blue-600 to-emerald-500 rounded-3xl blur-lg opacity-25 group-hover:opacity-40 transition duration-1000 group-hover:duration-200" aria-hidden="true" />

                            <div class="relative overflow-hidden rounded-xl bg-slate-900 aspect-[4/3]">
                                <img
                                    :src="'/images/digital_dossier_showcase.png'"
                                    alt="Suivi numérique sécurisé des dossiers administratifs — AppGov France"
                                    class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700 ease-out brightness-95 group-hover:brightness-100"
                                />

                                <!-- Overlay gradient -->
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent pointer-events-none" />

                                <!-- Floating Glass Badge Top Left -->
                                <div class="absolute top-4 left-4 backdrop-blur-md bg-slate-900/70 border border-white/20 rounded-lg px-3 py-2 text-white shadow-lg flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping" />
                                    <span class="text-xs font-semibold tracking-wide">Traçabilité ANTS 24/7</span>
                                </div>

                                <!-- Floating Glass Badge Bottom Right -->
                                <div class="absolute bottom-4 right-4 backdrop-blur-md bg-white/90 border border-slate-200/80 rounded-xl p-3 shadow-xl max-w-[220px]">
                                    <div class="flex items-center gap-2 text-xs font-bold text-slate-900">
                                        <svg class="w-4 h-4 text-brand-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        <span>Horodatage SHA-256</span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 mt-1 leading-snug">Chaque étape et document scellés cryptographiquement.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Points forts d'explication -->
                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <div class="rounded-xl border border-slate-200/70 bg-slate-50/60 p-3.5 flex items-start gap-3">
                                <div class="p-1.5 rounded-lg bg-brand-blue-50 text-brand-blue-600 shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900">Notifications Directes</h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">SMS & e-mail à chaque changement de statut.</p>
                                </div>
                            </div>

                            <div class="rounded-xl border border-slate-200/70 bg-slate-50/60 p-3.5 flex items-start gap-3">
                                <div class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457-.312-2.841-.873-4.084" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900">Identité Garantir</h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Authentification et vérification FranceConnect.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="tracking" class="scroll-mt-6 rounded-2xl border border-slate-200/80 bg-white shadow-xl shadow-slate-900/5 overflow-hidden">
                        <div class="border-b border-slate-100 bg-slate-50/70 p-5 sm:p-6 sm:flex sm:items-center sm:justify-between sm:gap-4">
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-6 overflow-hidden rounded shadow-sm border border-slate-300">
                                    <span class="w-1/3 bg-[#002395]" />
                                    <span class="w-1/3 bg-white" />
                                    <span class="w-1/3 bg-[#ED2939]" />
                                </span>
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ t('home.referenceLabel') }}</p>
                                    <p class="mt-0.5 break-all font-mono text-base font-extrabold text-brand-blue-900 sm:text-lg">
                                        {{ t('home.reference') }}
                                    </p>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center gap-2 sm:mt-0">
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse" />
                                    ANTS — Ministère de l'Intérieur
                                </span>
                                <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-800">
                                    {{ t('common.demo') }}
                                </span>
                            </div>
                        </div>
                        <div class="p-6 sm:p-8">
                            <ApplicationRail :steps="steps" status="under_review" />
                            <div class="mt-6 rounded-lg border border-slate-100 bg-slate-50/80 p-4 text-xs text-slate-600 flex items-center gap-3">
                                <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ t('home.trackingNotice') }}</span>
                            </div>
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
