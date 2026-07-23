<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowRightIcon,
    BookOpenIcon,
    PlusIcon,
} from '@heroicons/vue/24/outline';
import { useI18n } from 'vue-i18n';
import AgentShell from '@/Layouts/AgentShell.vue';
import EmptyState from '@/Components/EmptyState.vue';
import FormField from '@/Components/FormField.vue';
import Pagination from '@/Components/Pagination.vue';
import ServiceBadge from '@/Components/ServiceBadge.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({
    services: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    organizations: {
        type: Array,
        required: true,
    },
    canCreate: {
        type: Boolean,
        required: true,
    },
    canCreateCategory: {
        type: Boolean,
        required: true,
    },
});

const { t, te, locale } = useI18n();
const page = usePage();
const text = (value) => value?.[locale.value] ?? value?.fr ?? '';
const form = useForm({
    organization_public_id: props.organizations[0]?.publicId ?? '',
    category_public_id: props.categories[0]?.publicId ?? '',
    code: '',
    name_fr: '',
    name_en: '',
    description_fr: '',
    description_en: '',
});
const categoryForm = useForm({
    code: '',
    name_fr: '',
    name_en: '',
    description_fr: '',
    description_en: '',
    color_key: 'mobility',
    sort_order: 70,
});
const colorKeys = ['mobility', 'immigration', 'nationality', 'education', 'civil', 'business'];

const flashMessage = computed(() => {
    const key = page.props.flash.status;
    return key && te(key) ? t(key) : '';
});

const statusTone = (status) => ({
    draft: 'neutral',
    under_review: 'warning',
    published: 'success',
    retired: 'danger',
}[status] ?? 'neutral');

function createService() {
    form.post('/admin/catalog/services', {
        preserveScroll: true,
    });
}

function createCategory() {
    categoryForm.post('/admin/catalog/categories', {
        preserveScroll: true,
        onSuccess: () => categoryForm.reset(),
    });
}
</script>

<template>
    <Head :title="t('meta.catalogAdminTitle')" />

    <AgentShell :title="t('catalog.admin.title')" section="catalog">
        <p class="max-w-3xl text-lg leading-7 text-slate-600">{{ t('catalog.admin.intro') }}</p>

        <div v-if="flashMessage" role="status" class="mt-6 border-l-4 border-green-700 bg-green-50 p-4 font-semibold text-green-800">
            {{ flashMessage }}
        </div>

        <details v-if="canCreateCategory" class="mt-8 border border-slate-300 bg-white">
            <summary class="flex min-h-14 cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 font-display text-lg font-semibold text-ink-900">
                {{ t('catalog.admin.newCategory') }}
                <PlusIcon class="size-5 text-brand-blue-600" aria-hidden="true" />
            </summary>
            <form class="grid gap-5 border-t border-slate-200 p-5 sm:grid-cols-2 sm:p-7" @submit.prevent="createCategory">
                <p class="text-slate-600 sm:col-span-2">{{ t('catalog.admin.newCategoryIntro') }}</p>
                <FormField id="category-code" :label="t('catalog.admin.code')" :error="categoryForm.errors.code" required>
                    <template #default="{ describedBy }"><input id="category-code" v-model="categoryForm.code" class="form-input font-mono uppercase" :aria-describedby="describedBy"></template>
                </FormField>
                <FormField id="category-color" :label="t('catalog.admin.categoryColor')" :error="categoryForm.errors.color_key" required>
                    <template #default="{ describedBy }">
                        <select id="category-color" v-model="categoryForm.color_key" class="form-input" :aria-describedby="describedBy">
                            <option v-for="color in colorKeys" :key="color" :value="color">{{ t(`services.${color}.name`) }}</option>
                        </select>
                    </template>
                </FormField>
                <FormField id="category-name-fr" :label="t('catalog.admin.nameFr')" :error="categoryForm.errors.name_fr" required><template #default="{ describedBy }"><input id="category-name-fr" v-model="categoryForm.name_fr" class="form-input" :aria-describedby="describedBy"></template></FormField>
                <FormField id="category-name-en" :label="t('catalog.admin.nameEn')" :error="categoryForm.errors.name_en" required><template #default="{ describedBy }"><input id="category-name-en" v-model="categoryForm.name_en" class="form-input" :aria-describedby="describedBy"></template></FormField>
                <FormField id="category-description-fr" :label="t('catalog.admin.descriptionFr')" :error="categoryForm.errors.description_fr"><template #default="{ describedBy }"><textarea id="category-description-fr" v-model="categoryForm.description_fr" class="form-input min-h-24" :aria-describedby="describedBy" /></template></FormField>
                <FormField id="category-description-en" :label="t('catalog.admin.descriptionEn')" :error="categoryForm.errors.description_en"><template #default="{ describedBy }"><textarea id="category-description-en" v-model="categoryForm.description_en" class="form-input min-h-24" :aria-describedby="describedBy" /></template></FormField>
                <FormField id="category-order" :label="t('catalog.admin.sortOrder')" :error="categoryForm.errors.sort_order" required><template #default="{ describedBy }"><input id="category-order" v-model.number="categoryForm.sort_order" type="number" min="0" max="1000" class="form-input font-mono" :aria-describedby="describedBy"></template></FormField>
                <div class="self-end">
                    <button type="submit" class="button-primary" :disabled="categoryForm.processing">{{ categoryForm.processing ? t('common.processing') : t('catalog.admin.createCategory') }}</button>
                </div>
            </form>
        </details>

        <section v-if="canCreate" class="mt-8 border border-slate-300 bg-white">
            <div class="border-b border-slate-200 px-5 py-5 sm:px-7">
                <p class="eyebrow text-brand-red-700">{{ t('catalog.admin.eyebrow') }}</p>
                <h2 class="mt-2 font-display text-2xl font-semibold text-ink-900">{{ t('catalog.admin.newService') }}</h2>
                <p class="mt-2 text-slate-600">{{ t('catalog.admin.newServiceIntro') }}</p>
            </div>

            <form class="grid gap-5 p-5 sm:grid-cols-2 sm:p-7" @submit.prevent="createService">
                <FormField
                    id="organization_public_id"
                    :label="t('catalog.admin.organization')"
                    :error="form.errors.organization_public_id"
                    required
                >
                    <template #default="{ describedBy }">
                        <select id="organization_public_id" v-model="form.organization_public_id" class="form-input" :aria-describedby="describedBy">
                            <option v-for="organization in organizations" :key="organization.publicId" :value="organization.publicId">
                                {{ text(organization.name) }}
                            </option>
                        </select>
                    </template>
                </FormField>

                <FormField
                    id="category_public_id"
                    :label="t('catalog.admin.category')"
                    :error="form.errors.category_public_id"
                    required
                >
                    <template #default="{ describedBy }">
                        <select id="category_public_id" v-model="form.category_public_id" class="form-input" :aria-describedby="describedBy">
                            <option v-for="category in categories" :key="category.publicId" :value="category.publicId">
                                {{ text(category.name) }}
                            </option>
                        </select>
                    </template>
                </FormField>

                <FormField
                    id="code"
                    :label="t('catalog.admin.code')"
                    :hint="t('catalog.admin.codeHint')"
                    :error="form.errors.code"
                    required
                >
                    <template #default="{ describedBy }">
                        <input id="code" v-model="form.code" class="form-input font-mono uppercase" maxlength="80" :aria-describedby="describedBy">
                    </template>
                </FormField>

                <div class="hidden sm:block" />

                <FormField id="name_fr" :label="t('catalog.admin.nameFr')" :error="form.errors.name_fr" required>
                    <template #default="{ describedBy }">
                        <input id="name_fr" v-model="form.name_fr" class="form-input" maxlength="255" :aria-describedby="describedBy">
                    </template>
                </FormField>
                <FormField id="name_en" :label="t('catalog.admin.nameEn')" :error="form.errors.name_en" required>
                    <template #default="{ describedBy }">
                        <input id="name_en" v-model="form.name_en" class="form-input" maxlength="255" :aria-describedby="describedBy">
                    </template>
                </FormField>
                <FormField id="description_fr" :label="t('catalog.admin.descriptionFr')" :error="form.errors.description_fr" required>
                    <template #default="{ describedBy }">
                        <textarea id="description_fr" v-model="form.description_fr" class="form-input min-h-28" maxlength="1500" :aria-describedby="describedBy" />
                    </template>
                </FormField>
                <FormField id="description_en" :label="t('catalog.admin.descriptionEn')" :error="form.errors.description_en" required>
                    <template #default="{ describedBy }">
                        <textarea id="description_en" v-model="form.description_en" class="form-input min-h-28" maxlength="1500" :aria-describedby="describedBy" />
                    </template>
                </FormField>

                <div class="sm:col-span-2">
                    <button type="submit" class="button-primary" :disabled="form.processing">
                        <PlusIcon class="size-5" aria-hidden="true" />
                        {{ form.processing ? t('common.processing') : t('catalog.admin.create') }}
                    </button>
                </div>
            </form>
        </section>

        <section class="mt-10">
            <div class="flex items-center gap-3 border-b border-slate-300 pb-4">
                <BookOpenIcon class="size-6 text-brand-blue-600" aria-hidden="true" />
                <h2 class="font-display text-2xl font-semibold text-ink-900">{{ t('catalog.admin.servicesTitle') }}</h2>
            </div>

            <div v-if="services.data.length" class="mt-5 overflow-hidden border border-slate-200 bg-white">
                <article
                    v-for="service in services.data"
                    :key="service.publicId"
                    class="relative grid gap-5 border-b border-slate-200 p-5 last:border-b-0 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center sm:p-6"
                >
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <ServiceBadge :color-key="service.category.colorKey" :label="text(service.category.name)" />
                            <StatusBadge v-if="service.latestVersion" :tone="statusTone(service.latestVersion.status)">
                                {{ t(`catalog.status.${service.latestVersion.status}`) }}
                            </StatusBadge>
                            <span v-if="service.latestVersion?.isDemo" class="rounded border border-brand-red-700/25 bg-brand-red-50 px-2 py-1 text-xs font-semibold text-brand-red-700">
                                {{ t('common.demo') }}
                            </span>
                        </div>
                        <h3 class="mt-4 font-display text-xl font-semibold text-brand-blue-900">{{ text(service.name) }}</h3>
                        <p class="mt-1 font-mono text-xs uppercase tracking-wide text-slate-500">{{ service.code }}</p>
                        <p class="mt-2 text-sm text-slate-600">{{ text(service.organization.name) }}</p>
                    </div>
                    <Link
                        v-if="service.latestVersion"
                        :href="`/admin/catalog/versions/${service.latestVersion.publicId}`"
                        class="button-secondary after:absolute after:inset-0"
                    >
                        {{ t('catalog.admin.openVersion') }}
                        <ArrowRightIcon class="size-5" aria-hidden="true" />
                    </Link>
                </article>
            </div>

            <EmptyState
                v-else
                class="mt-5"
                :title="t('catalog.admin.emptyTitle')"
                :description="t('catalog.admin.emptyDescription')"
            />

            <Pagination class="mt-7" :meta="services" />
        </section>
    </AgentShell>
</template>
