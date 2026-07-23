<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowRightIcon,
    MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline';
import { useI18n } from 'vue-i18n';
import EmptyState from '@/Components/EmptyState.vue';
import Pagination from '@/Components/Pagination.vue';
import PublicHeader from '@/Components/PublicHeader.vue';
import ServiceBadge from '@/Components/ServiceBadge.vue';

const props = defineProps({
    services: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

const { t, locale } = useI18n();
const query = ref(props.filters.q);
const selectedCategory = ref(props.filters.category);

const text = (value) => value?.[locale.value] ?? value?.fr ?? '';

function search() {
    router.get('/services', {
        q: query.value || undefined,
        category: selectedCategory.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function selectCategory(category) {
    selectedCategory.value = category;
    search();
}

function clearFilters() {
    query.value = '';
    selectedCategory.value = '';
    router.get('/services', {}, { replace: true });
}

const resultCount = computed(() => props.services.total);
</script>

<template>
    <Head :title="t('meta.catalogTitle')" />

    <div class="min-h-screen bg-cloud-50">
        <PublicHeader />

        <main id="main-content">
            <section class="border-b border-slate-200 bg-white">
                <div class="page-container py-12 sm:py-16 lg:py-20">
                    <div class="max-w-4xl">
                        <p class="eyebrow text-brand-red-700">{{ t('catalog.index.eyebrow') }}</p>
                        <h1 class="mt-3 max-w-3xl font-display text-4xl font-semibold leading-tight tracking-tight text-brand-blue-900 sm:text-5xl">
                            {{ t('catalog.index.title') }}
                        </h1>
                        <p class="mt-5 max-w-3xl text-lg leading-8 text-slate-600">{{ t('catalog.index.intro') }}</p>
                    </div>

                    <form class="mt-9 max-w-5xl border border-slate-300 bg-white p-3 shadow-sm sm:flex sm:items-end sm:gap-3 sm:p-4" @submit.prevent="search">
                        <div class="flex-1">
                            <label for="catalog-search" class="block text-sm font-semibold text-ink-900">
                                {{ t('catalog.index.searchLabel') }}
                            </label>
                            <div class="relative mt-2">
                                <MagnifyingGlassIcon class="pointer-events-none absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-slate-400" aria-hidden="true" />
                                <input
                                    id="catalog-search"
                                    v-model="query"
                                    type="search"
                                    class="form-input pl-11"
                                    :placeholder="t('catalog.index.searchPlaceholder')"
                                >
                            </div>
                        </div>
                        <button type="submit" class="button-primary mt-3 w-full sm:mt-0 sm:w-auto">
                            {{ t('catalog.index.search') }}
                        </button>
                    </form>

                    <div class="mt-3 grid h-1.5 grid-cols-6 overflow-hidden rounded-sm" aria-hidden="true">
                        <span class="bg-service-mobility" />
                        <span class="bg-service-immigration" />
                        <span class="bg-service-nationality" />
                        <span class="bg-service-education" />
                        <span class="bg-service-civil" />
                        <span class="bg-service-business" />
                    </div>
                </div>
            </section>

            <section class="page-container py-10 sm:py-14">
                <div class="flex gap-2 overflow-x-auto pb-3" :aria-label="t('catalog.admin.category')">
                    <button
                        type="button"
                        class="min-h-11 shrink-0 rounded-md border px-4 text-sm font-semibold"
                        :class="!selectedCategory ? 'border-brand-blue-600 bg-brand-blue-50 text-brand-blue-900' : 'border-slate-300 bg-white text-slate-700 hover:border-slate-400'"
                        @click="selectCategory('')"
                    >
                        {{ t('catalog.index.allCategories') }}
                    </button>
                    <button
                        v-for="category in categories"
                        :key="category.publicId"
                        type="button"
                        class="min-h-11 shrink-0 rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 hover:border-slate-400"
                        :class="{ 'border-brand-blue-600 bg-brand-blue-50 text-brand-blue-900': selectedCategory === category.publicId }"
                        @click="selectCategory(category.publicId)"
                    >
                        {{ text(category.name) }}
                    </button>
                </div>

                <div class="mt-6 flex items-center justify-between border-b border-slate-300 pb-4">
                    <h2 class="font-display text-xl font-semibold text-ink-900">
                        {{ t('catalog.index.results', resultCount) }}
                    </h2>
                </div>

                <div v-if="services.data.length" class="divide-y divide-slate-200 border-b border-slate-200">
                    <article
                        v-for="service in services.data"
                        :key="service.publicId"
                        class="group relative grid gap-5 bg-white px-5 py-7 sm:grid-cols-[1fr_auto] sm:items-center sm:px-7"
                    >
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <ServiceBadge :color-key="service.category.colorKey" :label="text(service.category.name)" />
                                <span v-if="service.isDemo" class="rounded border border-brand-red-700/25 bg-brand-red-50 px-2 py-1 text-xs font-semibold text-brand-red-700">
                                    {{ t('common.demo') }}
                                </span>
                            </div>
                            <h3 class="mt-4 font-display text-2xl font-semibold text-brand-blue-900">
                                <Link :href="`/services/${service.publicId}`" class="rounded-sm after:absolute after:inset-0">
                                    {{ text(service.procedure.title) }}
                                </Link>
                            </h3>
                            <p class="mt-2 max-w-3xl leading-7 text-slate-600">{{ text(service.procedure.summary) }}</p>
                            <p class="mt-4 font-mono text-xs uppercase tracking-wide text-slate-500">
                                {{ t('catalog.index.publishedVersion', { number: service.procedure.versionNumber }) }}
                            </p>
                        </div>
                        <span class="inline-flex items-center gap-2 font-semibold text-brand-blue-700">
                            {{ t('catalog.index.open') }}
                            <ArrowRightIcon class="size-5 transition group-hover:translate-x-1" aria-hidden="true" />
                        </span>
                    </article>
                </div>

                <EmptyState
                    v-else
                    class="mt-8"
                    :title="t('catalog.index.emptyTitle')"
                    :description="t('catalog.index.emptyDescription')"
                >
                    <template #action>
                        <button type="button" class="button-secondary" @click="clearFilters">
                            {{ t('catalog.index.clearFilters') }}
                        </button>
                    </template>
                </EmptyState>

                <Pagination class="mt-8" :meta="services" />
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
