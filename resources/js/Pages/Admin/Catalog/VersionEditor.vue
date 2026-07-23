<script setup>
import { computed } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowLeftIcon,
    ArrowPathIcon,
    CheckCircleIcon,
    ChevronDownIcon,
    DocumentDuplicateIcon,
    PlusIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline';
import { useI18n } from 'vue-i18n';
import AgentShell from '@/Layouts/AgentShell.vue';
import FormField from '@/Components/FormField.vue';
import ServiceBadge from '@/Components/ServiceBadge.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({
    version: {
        type: Object,
        required: true,
    },
    options: {
        type: Object,
        required: true,
    },
});

const { t, te, locale } = useI18n();
const page = usePage();
const text = (value) => value?.[locale.value] ?? value?.fr ?? '';
const clone = (value) => JSON.parse(JSON.stringify(value));

const form = useForm({
    title_fr: props.version.title_fr,
    title_en: props.version.title_en,
    summary_fr: props.version.summary_fr,
    summary_en: props.version.summary_en,
    description_fr: props.version.description_fr,
    description_en: props.version.description_en,
    eligibility_fr: props.version.eligibility_fr,
    eligibility_en: props.version.eligibility_en,
    legal_basis_fr: props.version.legal_basis_fr,
    legal_basis_en: props.version.legal_basis_en,
    effective_from: props.version.effective_from,
    is_demo: props.version.is_demo,
    steps: clone(props.version.steps),
    fields: clone(props.version.fields),
    documents: clone(props.version.documents),
    rules: clone(props.version.rules),
    fees: clone(props.version.fees),
});

const returnForm = useForm({ reason: '' });
const retireForm = useForm({ reason: '' });
const editable = computed(() => props.version.permissions.update);
const errors = computed(() => [...new Set(Object.values(form.errors))]);
const flashMessage = computed(() => {
    const key = page.props.flash.status;
    return key && te(key) ? t(key) : '';
});
const statusTone = computed(() => ({
    draft: 'neutral',
    under_review: 'warning',
    published: 'success',
    retired: 'danger',
}[props.version.status] ?? 'neutral'));

const fieldError = (collection, index, field) => form.errors[`${collection}.${index}.${field}`];

function addStep() {
    form.steps.push({
        code: '',
        name_fr: '',
        name_en: '',
        description_fr: '',
        description_en: '',
        step_type: 'form',
        is_required: true,
    });
}

function addField() {
    form.fields.push({
        step_code: form.steps[0]?.code ?? '',
        code: '',
        label_fr: '',
        label_en: '',
        help_fr: '',
        help_en: '',
        field_type: 'text',
        is_required: false,
        options_fr: '',
        options_en: '',
    });
}

function addDocument() {
    form.documents.push({
        step_code: form.steps[0]?.code ?? null,
        code: '',
        name_fr: '',
        name_en: '',
        description_fr: '',
        description_en: '',
        is_required: true,
    });
}

function addRule() {
    form.rules.push({
        code: '',
        name_fr: '',
        name_en: '',
        description_fr: '',
        description_en: '',
        rule_type: 'eligibility',
    });
}

function addFee() {
    form.fees.push({
        step_code: null,
        code: '',
        label_fr: '',
        label_en: '',
        description_fr: '',
        description_en: '',
        amount_minor: 0,
        currency: 'XAF',
        minor_unit_exponent: 0,
        is_mandatory: false,
        due_when_fr: '',
        due_when_en: '',
        legal_basis_fr: '',
        legal_basis_en: '',
    });
}

function save() {
    form.put(`/admin/catalog/versions/${props.version.publicId}`, {
        preserveScroll: true,
    });
}

function submitReview() {
    router.post(`/admin/catalog/versions/${props.version.publicId}/submit-review`, {}, { preserveScroll: true });
}

function publish() {
    router.post(`/admin/catalog/versions/${props.version.publicId}/publish`, {}, { preserveScroll: true });
}

function returnToDraft() {
    returnForm.post(`/admin/catalog/versions/${props.version.publicId}/return-draft`, { preserveScroll: true });
}

function retire() {
    retireForm.post(`/admin/catalog/versions/${props.version.publicId}/retire`, { preserveScroll: true });
}

function createVersion() {
    router.post(`/admin/catalog/services/${props.version.service.publicId}/versions`);
}
</script>

<template>
    <Head :title="t('meta.procedureEditorTitle')" />

    <AgentShell :title="t('catalog.editor.title', { number: version.versionNumber, service: text(version.service.name) })" section="catalog">
        <Link href="/admin/catalog" class="inline-flex min-h-11 items-center gap-2 rounded-md font-semibold text-brand-blue-700">
            <ArrowLeftIcon class="size-5" aria-hidden="true" />
            {{ t('catalog.editor.back') }}
        </Link>

        <div v-if="flashMessage" role="status" class="mt-5 border-l-4 border-green-700 bg-green-50 p-4 font-semibold text-green-800">
            {{ flashMessage }}
        </div>

        <div class="mt-6 grid gap-7 xl:grid-cols-[minmax(0,1fr)_320px]">
            <form class="min-w-0 space-y-5" @submit.prevent="save">
                <div class="border border-slate-300 bg-white p-5 sm:flex sm:items-start sm:justify-between sm:gap-5 sm:p-6">
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <ServiceBadge :color-key="version.service.category.colorKey" :label="text(version.service.category.name)" />
                            <StatusBadge :tone="statusTone">{{ t(`catalog.status.${version.status}`) }}</StatusBadge>
                            <span v-if="version.is_demo" class="rounded border border-brand-red-700/25 bg-brand-red-50 px-2 py-1 text-xs font-semibold text-brand-red-700">
                                {{ t('common.demo') }}
                            </span>
                        </div>
                        <p class="mt-4 font-mono text-xs uppercase tracking-wide text-slate-500">{{ version.service.code }}</p>
                        <p class="mt-2 text-sm text-slate-600">
                            {{ t('catalog.editor.owner') }} · {{ text(version.service.organization.name) }}
                        </p>
                    </div>
                    <p class="mt-4 max-w-sm text-sm leading-6 text-slate-600 sm:mt-0">{{ t(`catalog.statusHelp.${version.status}`) }}</p>
                </div>

                <div v-if="!editable" class="border-l-4 border-brand-blue-600 bg-brand-blue-50 p-4 font-semibold text-brand-blue-900">
                    {{ t('catalog.editor.immutableNotice') }}
                </div>
                <div v-if="version.reviewNote" class="border-l-4 border-amber-700 bg-amber-50 p-4 text-amber-900">
                    {{ t('catalog.editor.reviewNote', { note: version.reviewNote }) }}
                </div>
                <div v-if="errors.length" role="alert" class="border border-brand-red-700/25 bg-brand-red-50 p-5 text-brand-red-700">
                    <p class="font-semibold">{{ t('catalog.editor.errorsTitle') }}</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        <li v-for="error in errors" :key="error">{{ error }}</li>
                    </ul>
                </div>

                <details open class="group border border-slate-300 bg-white">
                    <summary class="flex min-h-16 cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 font-display text-xl font-semibold text-ink-900 sm:px-6">
                        {{ t('catalog.editor.identitySection') }}
                        <ChevronDownIcon class="size-5 transition group-open:rotate-180" aria-hidden="true" />
                    </summary>
                    <div class="grid gap-5 border-t border-slate-200 p-5 sm:grid-cols-2 sm:p-6">
                        <FormField id="title_fr" :label="t('catalog.editor.titleFr')" :error="form.errors.title_fr" required>
                            <template #default="{ describedBy }"><input id="title_fr" v-model="form.title_fr" class="form-input" :disabled="!editable" :aria-describedby="describedBy"></template>
                        </FormField>
                        <FormField id="title_en" :label="t('catalog.editor.titleEn')" :error="form.errors.title_en" required>
                            <template #default="{ describedBy }"><input id="title_en" v-model="form.title_en" class="form-input" :disabled="!editable" :aria-describedby="describedBy"></template>
                        </FormField>
                        <FormField id="summary_fr" :label="t('catalog.editor.summaryFr')" :error="form.errors.summary_fr" required>
                            <template #default="{ describedBy }"><textarea id="summary_fr" v-model="form.summary_fr" class="form-input min-h-28" :disabled="!editable" :aria-describedby="describedBy" /></template>
                        </FormField>
                        <FormField id="summary_en" :label="t('catalog.editor.summaryEn')" :error="form.errors.summary_en" required>
                            <template #default="{ describedBy }"><textarea id="summary_en" v-model="form.summary_en" class="form-input min-h-28" :disabled="!editable" :aria-describedby="describedBy" /></template>
                        </FormField>
                        <FormField id="description_fr" :label="t('catalog.editor.descriptionFr')" :error="form.errors.description_fr">
                            <template #default="{ describedBy }"><textarea id="description_fr" v-model="form.description_fr" class="form-input min-h-36" :disabled="!editable" :aria-describedby="describedBy" /></template>
                        </FormField>
                        <FormField id="description_en" :label="t('catalog.editor.descriptionEn')" :error="form.errors.description_en">
                            <template #default="{ describedBy }"><textarea id="description_en" v-model="form.description_en" class="form-input min-h-36" :disabled="!editable" :aria-describedby="describedBy" /></template>
                        </FormField>
                        <FormField id="eligibility_fr" :label="t('catalog.editor.eligibilityFr')" :error="form.errors.eligibility_fr">
                            <template #default="{ describedBy }"><textarea id="eligibility_fr" v-model="form.eligibility_fr" class="form-input min-h-28" :disabled="!editable" :aria-describedby="describedBy" /></template>
                        </FormField>
                        <FormField id="eligibility_en" :label="t('catalog.editor.eligibilityEn')" :error="form.errors.eligibility_en">
                            <template #default="{ describedBy }"><textarea id="eligibility_en" v-model="form.eligibility_en" class="form-input min-h-28" :disabled="!editable" :aria-describedby="describedBy" /></template>
                        </FormField>
                        <FormField id="legal_basis_fr" :label="t('catalog.editor.legalBasisFr')" :error="form.errors.legal_basis_fr">
                            <template #default="{ describedBy }"><textarea id="legal_basis_fr" v-model="form.legal_basis_fr" class="form-input min-h-24" :disabled="!editable" :aria-describedby="describedBy" /></template>
                        </FormField>
                        <FormField id="legal_basis_en" :label="t('catalog.editor.legalBasisEn')" :error="form.errors.legal_basis_en">
                            <template #default="{ describedBy }"><textarea id="legal_basis_en" v-model="form.legal_basis_en" class="form-input min-h-24" :disabled="!editable" :aria-describedby="describedBy" /></template>
                        </FormField>
                        <FormField id="effective_from" :label="t('catalog.editor.effectiveFrom')" :error="form.errors.effective_from">
                            <template #default="{ describedBy }"><input id="effective_from" v-model="form.effective_from" type="date" class="form-input" :disabled="!editable" :aria-describedby="describedBy"></template>
                        </FormField>
                        <label class="flex min-h-11 items-center gap-3 self-end rounded-md border border-slate-300 px-4 py-3 font-semibold text-ink-900">
                            <input v-model="form.is_demo" type="checkbox" class="size-5 rounded border-slate-300 text-brand-blue-600" :disabled="!editable">
                            {{ t('catalog.editor.demoLabel') }}
                        </label>
                    </div>
                </details>

                <details open class="group border border-slate-300 bg-white">
                    <summary class="flex min-h-16 cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 sm:px-6">
                        <span>
                            <span class="font-display text-xl font-semibold text-ink-900">{{ t('catalog.editor.steps') }}</span>
                            <span class="ml-2 font-mono text-sm text-slate-500">{{ form.steps.length }}</span>
                        </span>
                        <ChevronDownIcon class="size-5 transition group-open:rotate-180" aria-hidden="true" />
                    </summary>
                    <div class="border-t border-slate-200 p-5 sm:p-6">
                        <p class="mb-5 text-sm text-slate-600">{{ t('catalog.editor.stepsHelp') }}</p>
                        <div class="space-y-4">
                            <fieldset v-for="(step, index) in form.steps" :key="step.public_id ?? index" class="border border-slate-200 p-4">
                                <legend class="px-2 font-mono text-sm font-semibold text-brand-blue-900">{{ t('catalog.editor.step', { number: index + 1 }) }}</legend>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <FormField :id="`step-code-${index}`" :label="t('catalog.admin.code')" :error="fieldError('steps', index, 'code')" required>
                                        <template #default="{ describedBy }"><input :id="`step-code-${index}`" v-model="step.code" class="form-input font-mono uppercase" :disabled="!editable" :aria-describedby="describedBy"></template>
                                    </FormField>
                                    <FormField :id="`step-type-${index}`" :label="t('catalog.editor.type')" :error="fieldError('steps', index, 'step_type')" required>
                                        <template #default="{ describedBy }">
                                            <select :id="`step-type-${index}`" v-model="step.step_type" class="form-input" :disabled="!editable" :aria-describedby="describedBy">
                                                <option v-for="type in options.stepTypes" :key="type" :value="type">{{ t(`catalog.stepTypes.${type}`) }}</option>
                                            </select>
                                        </template>
                                    </FormField>
                                    <FormField :id="`step-name-fr-${index}`" :label="t('catalog.editor.nameFr')" :error="fieldError('steps', index, 'name_fr')" required>
                                        <template #default="{ describedBy }"><input :id="`step-name-fr-${index}`" v-model="step.name_fr" class="form-input" :disabled="!editable" :aria-describedby="describedBy"></template>
                                    </FormField>
                                    <FormField :id="`step-name-en-${index}`" :label="t('catalog.editor.nameEn')" :error="fieldError('steps', index, 'name_en')" required>
                                        <template #default="{ describedBy }"><input :id="`step-name-en-${index}`" v-model="step.name_en" class="form-input" :disabled="!editable" :aria-describedby="describedBy"></template>
                                    </FormField>
                                    <FormField :id="`step-description-fr-${index}`" :label="t('catalog.editor.descriptionFr')" :error="fieldError('steps', index, 'description_fr')">
                                        <template #default="{ describedBy }"><textarea :id="`step-description-fr-${index}`" v-model="step.description_fr" class="form-input min-h-24" :disabled="!editable" :aria-describedby="describedBy" /></template>
                                    </FormField>
                                    <FormField :id="`step-description-en-${index}`" :label="t('catalog.editor.descriptionEn')" :error="fieldError('steps', index, 'description_en')">
                                        <template #default="{ describedBy }"><textarea :id="`step-description-en-${index}`" v-model="step.description_en" class="form-input min-h-24" :disabled="!editable" :aria-describedby="describedBy" /></template>
                                    </FormField>
                                </div>
                                <div v-if="editable" class="mt-4 flex items-center justify-between gap-4">
                                    <label class="flex items-center gap-2 text-sm font-semibold"><input v-model="step.is_required" type="checkbox" class="size-5 rounded border-slate-300 text-brand-blue-600"> {{ t('catalog.editor.isRequired') }}</label>
                                    <button type="button" class="inline-flex min-h-11 items-center gap-2 rounded-md px-3 font-semibold text-brand-red-700 hover:bg-brand-red-50" @click="form.steps.splice(index, 1)">
                                        <TrashIcon class="size-4" aria-hidden="true" /> {{ t('common.remove') }}
                                    </button>
                                </div>
                            </fieldset>
                        </div>
                        <button v-if="editable" type="button" class="button-secondary mt-5" @click="addStep">
                            <PlusIcon class="size-5" aria-hidden="true" /> {{ t('catalog.editor.addStep') }}
                        </button>
                    </div>
                </details>

                <details class="group border border-slate-300 bg-white">
                    <summary class="flex min-h-16 cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 sm:px-6">
                        <span><span class="font-display text-xl font-semibold text-ink-900">{{ t('catalog.editor.fields') }}</span><span class="ml-2 font-mono text-sm text-slate-500">{{ form.fields.length }}</span></span>
                        <ChevronDownIcon class="size-5 transition group-open:rotate-180" aria-hidden="true" />
                    </summary>
                    <div class="space-y-4 border-t border-slate-200 p-5 sm:p-6">
                        <fieldset v-for="(field, index) in form.fields" :key="field.public_id ?? index" class="border border-slate-200 p-4">
                            <legend class="px-2 font-mono text-sm font-semibold text-brand-blue-900">{{ t('catalog.editor.item', { number: index + 1 }) }}</legend>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <FormField :id="`field-code-${index}`" :label="t('catalog.editor.fieldCode')" :error="fieldError('fields', index, 'code')" required><template #default="{ describedBy }"><input :id="`field-code-${index}`" v-model="field.code" class="form-input font-mono uppercase" :disabled="!editable" :aria-describedby="describedBy"></template></FormField>
                                <FormField :id="`field-step-${index}`" :label="t('catalog.editor.stepCode')" :error="fieldError('fields', index, 'step_code')" required><template #default="{ describedBy }"><select :id="`field-step-${index}`" v-model="field.step_code" class="form-input" :disabled="!editable" :aria-describedby="describedBy"><option v-for="step in form.steps" :key="step.code" :value="step.code">{{ step.code || text({ fr: step.name_fr, en: step.name_en }) }}</option></select></template></FormField>
                                <FormField :id="`field-type-${index}`" :label="t('catalog.editor.type')" :error="fieldError('fields', index, 'field_type')" required><template #default="{ describedBy }"><select :id="`field-type-${index}`" v-model="field.field_type" class="form-input" :disabled="!editable" :aria-describedby="describedBy"><option v-for="type in options.fieldTypes" :key="type" :value="type">{{ t(`catalog.fieldTypes.${type}`) }}</option></select></template></FormField>
                                <label class="flex min-h-11 items-center gap-2 self-end font-semibold"><input v-model="field.is_required" type="checkbox" class="size-5 rounded border-slate-300 text-brand-blue-600" :disabled="!editable"> {{ t('catalog.editor.isRequired') }}</label>
                                <FormField :id="`field-label-fr-${index}`" :label="t('catalog.editor.nameFr')" :error="fieldError('fields', index, 'label_fr')" required><template #default="{ describedBy }"><input :id="`field-label-fr-${index}`" v-model="field.label_fr" class="form-input" :disabled="!editable" :aria-describedby="describedBy"></template></FormField>
                                <FormField :id="`field-label-en-${index}`" :label="t('catalog.editor.nameEn')" :error="fieldError('fields', index, 'label_en')" required><template #default="{ describedBy }"><input :id="`field-label-en-${index}`" v-model="field.label_en" class="form-input" :disabled="!editable" :aria-describedby="describedBy"></template></FormField>
                                <FormField :id="`field-help-fr-${index}`" :label="t('catalog.editor.helpFr')" :error="fieldError('fields', index, 'help_fr')"><template #default="{ describedBy }"><textarea :id="`field-help-fr-${index}`" v-model="field.help_fr" class="form-input min-h-20" :disabled="!editable" :aria-describedby="describedBy" /></template></FormField>
                                <FormField :id="`field-help-en-${index}`" :label="t('catalog.editor.helpEn')" :error="fieldError('fields', index, 'help_en')"><template #default="{ describedBy }"><textarea :id="`field-help-en-${index}`" v-model="field.help_en" class="form-input min-h-20" :disabled="!editable" :aria-describedby="describedBy" /></template></FormField>
                                <template v-if="['select', 'radio'].includes(field.field_type)">
                                    <FormField :id="`field-options-fr-${index}`" :label="t('catalog.editor.optionsFr')" :hint="t('catalog.editor.optionsHint')" :error="fieldError('fields', index, 'options_fr')" required><template #default="{ describedBy }"><textarea :id="`field-options-fr-${index}`" v-model="field.options_fr" class="form-input min-h-28 font-mono text-sm" :disabled="!editable" :aria-describedby="describedBy" /></template></FormField>
                                    <FormField :id="`field-options-en-${index}`" :label="t('catalog.editor.optionsEn')" :hint="t('catalog.editor.optionsHint')" :error="fieldError('fields', index, 'options_en')" required><template #default="{ describedBy }"><textarea :id="`field-options-en-${index}`" v-model="field.options_en" class="form-input min-h-28 font-mono text-sm" :disabled="!editable" :aria-describedby="describedBy" /></template></FormField>
                                </template>
                            </div>
                            <button v-if="editable" type="button" class="mt-4 inline-flex min-h-11 items-center gap-2 rounded-md px-3 font-semibold text-brand-red-700 hover:bg-brand-red-50" @click="form.fields.splice(index, 1)"><TrashIcon class="size-4" aria-hidden="true" /> {{ t('common.remove') }}</button>
                        </fieldset>
                        <button v-if="editable" type="button" class="button-secondary" :disabled="!form.steps.length" @click="addField"><PlusIcon class="size-5" aria-hidden="true" /> {{ t('catalog.editor.addField') }}</button>
                    </div>
                </details>

                <details class="group border border-slate-300 bg-white">
                    <summary class="flex min-h-16 cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 sm:px-6">
                        <span><span class="font-display text-xl font-semibold text-ink-900">{{ t('catalog.editor.documents') }}</span><span class="ml-2 font-mono text-sm text-slate-500">{{ form.documents.length }}</span></span>
                        <ChevronDownIcon class="size-5 transition group-open:rotate-180" aria-hidden="true" />
                    </summary>
                    <div class="space-y-4 border-t border-slate-200 p-5 sm:p-6">
                        <fieldset v-for="(document, index) in form.documents" :key="document.public_id ?? index" class="border border-slate-200 p-4">
                            <legend class="px-2 font-mono text-sm font-semibold text-brand-blue-900">{{ t('catalog.editor.item', { number: index + 1 }) }}</legend>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <FormField :id="`document-code-${index}`" :label="t('catalog.editor.documentCode')" :error="fieldError('documents', index, 'code')" required><template #default="{ describedBy }"><input :id="`document-code-${index}`" v-model="document.code" class="form-input font-mono uppercase" :disabled="!editable" :aria-describedby="describedBy"></template></FormField>
                                <FormField :id="`document-step-${index}`" :label="t('catalog.editor.stepCode')" :error="fieldError('documents', index, 'step_code')"><template #default="{ describedBy }"><select :id="`document-step-${index}`" v-model="document.step_code" class="form-input" :disabled="!editable" :aria-describedby="describedBy"><option :value="null">—</option><option v-for="step in form.steps" :key="step.code" :value="step.code">{{ step.code }}</option></select></template></FormField>
                                <FormField :id="`document-name-fr-${index}`" :label="t('catalog.editor.nameFr')" :error="fieldError('documents', index, 'name_fr')" required><template #default="{ describedBy }"><input :id="`document-name-fr-${index}`" v-model="document.name_fr" class="form-input" :disabled="!editable" :aria-describedby="describedBy"></template></FormField>
                                <FormField :id="`document-name-en-${index}`" :label="t('catalog.editor.nameEn')" :error="fieldError('documents', index, 'name_en')" required><template #default="{ describedBy }"><input :id="`document-name-en-${index}`" v-model="document.name_en" class="form-input" :disabled="!editable" :aria-describedby="describedBy"></template></FormField>
                                <FormField :id="`document-description-fr-${index}`" :label="t('catalog.editor.descriptionFr')" :error="fieldError('documents', index, 'description_fr')"><template #default="{ describedBy }"><textarea :id="`document-description-fr-${index}`" v-model="document.description_fr" class="form-input min-h-20" :disabled="!editable" :aria-describedby="describedBy" /></template></FormField>
                                <FormField :id="`document-description-en-${index}`" :label="t('catalog.editor.descriptionEn')" :error="fieldError('documents', index, 'description_en')"><template #default="{ describedBy }"><textarea :id="`document-description-en-${index}`" v-model="document.description_en" class="form-input min-h-20" :disabled="!editable" :aria-describedby="describedBy" /></template></FormField>
                            </div>
                            <div v-if="editable" class="mt-4 flex items-center justify-between gap-4"><label class="flex items-center gap-2 font-semibold"><input v-model="document.is_required" type="checkbox" class="size-5 rounded border-slate-300 text-brand-blue-600"> {{ t('catalog.editor.isRequired') }}</label><button type="button" class="inline-flex min-h-11 items-center gap-2 rounded-md px-3 font-semibold text-brand-red-700 hover:bg-brand-red-50" @click="form.documents.splice(index, 1)"><TrashIcon class="size-4" aria-hidden="true" /> {{ t('common.remove') }}</button></div>
                        </fieldset>
                        <button v-if="editable" type="button" class="button-secondary" @click="addDocument"><PlusIcon class="size-5" aria-hidden="true" /> {{ t('catalog.editor.addDocument') }}</button>
                    </div>
                </details>

                <details class="group border border-slate-300 bg-white">
                    <summary class="flex min-h-16 cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 sm:px-6">
                        <span><span class="font-display text-xl font-semibold text-ink-900">{{ t('catalog.editor.rules') }}</span><span class="ml-2 font-mono text-sm text-slate-500">{{ form.rules.length }}</span></span>
                        <ChevronDownIcon class="size-5 transition group-open:rotate-180" aria-hidden="true" />
                    </summary>
                    <div class="space-y-4 border-t border-slate-200 p-5 sm:p-6">
                        <fieldset v-for="(rule, index) in form.rules" :key="rule.public_id ?? index" class="border border-slate-200 p-4">
                            <legend class="px-2 font-mono text-sm font-semibold text-brand-blue-900">{{ t('catalog.editor.item', { number: index + 1 }) }}</legend>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <FormField :id="`rule-code-${index}`" :label="t('catalog.editor.ruleCode')" :error="fieldError('rules', index, 'code')" required><template #default="{ describedBy }"><input :id="`rule-code-${index}`" v-model="rule.code" class="form-input font-mono uppercase" :disabled="!editable" :aria-describedby="describedBy"></template></FormField>
                                <FormField :id="`rule-type-${index}`" :label="t('catalog.editor.type')" :error="fieldError('rules', index, 'rule_type')" required><template #default="{ describedBy }"><select :id="`rule-type-${index}`" v-model="rule.rule_type" class="form-input" :disabled="!editable" :aria-describedby="describedBy"><option v-for="type in options.ruleTypes" :key="type" :value="type">{{ t(`catalog.ruleTypes.${type}`) }}</option></select></template></FormField>
                                <FormField :id="`rule-name-fr-${index}`" :label="t('catalog.editor.nameFr')" :error="fieldError('rules', index, 'name_fr')" required><template #default="{ describedBy }"><input :id="`rule-name-fr-${index}`" v-model="rule.name_fr" class="form-input" :disabled="!editable" :aria-describedby="describedBy"></template></FormField>
                                <FormField :id="`rule-name-en-${index}`" :label="t('catalog.editor.nameEn')" :error="fieldError('rules', index, 'name_en')" required><template #default="{ describedBy }"><input :id="`rule-name-en-${index}`" v-model="rule.name_en" class="form-input" :disabled="!editable" :aria-describedby="describedBy"></template></FormField>
                                <FormField :id="`rule-description-fr-${index}`" :label="t('catalog.editor.descriptionFr')" :error="fieldError('rules', index, 'description_fr')" required><template #default="{ describedBy }"><textarea :id="`rule-description-fr-${index}`" v-model="rule.description_fr" class="form-input min-h-24" :disabled="!editable" :aria-describedby="describedBy" /></template></FormField>
                                <FormField :id="`rule-description-en-${index}`" :label="t('catalog.editor.descriptionEn')" :error="fieldError('rules', index, 'description_en')" required><template #default="{ describedBy }"><textarea :id="`rule-description-en-${index}`" v-model="rule.description_en" class="form-input min-h-24" :disabled="!editable" :aria-describedby="describedBy" /></template></FormField>
                            </div>
                            <button v-if="editable" type="button" class="mt-4 inline-flex min-h-11 items-center gap-2 rounded-md px-3 font-semibold text-brand-red-700 hover:bg-brand-red-50" @click="form.rules.splice(index, 1)"><TrashIcon class="size-4" aria-hidden="true" /> {{ t('common.remove') }}</button>
                        </fieldset>
                        <button v-if="editable" type="button" class="button-secondary" @click="addRule"><PlusIcon class="size-5" aria-hidden="true" /> {{ t('catalog.editor.addRule') }}</button>
                    </div>
                </details>

                <details class="group border border-slate-300 bg-white">
                    <summary class="flex min-h-16 cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 sm:px-6">
                        <span><span class="font-display text-xl font-semibold text-ink-900">{{ t('catalog.editor.fees') }}</span><span class="ml-2 font-mono text-sm text-slate-500">{{ form.fees.length }}</span></span>
                        <ChevronDownIcon class="size-5 transition group-open:rotate-180" aria-hidden="true" />
                    </summary>
                    <div class="space-y-4 border-t border-slate-200 p-5 sm:p-6">
                        <fieldset v-for="(fee, index) in form.fees" :key="fee.public_id ?? index" class="border border-slate-200 p-4">
                            <legend class="px-2 font-mono text-sm font-semibold text-brand-blue-900">{{ t('catalog.editor.item', { number: index + 1 }) }}</legend>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <FormField :id="`fee-code-${index}`" :label="t('catalog.editor.feeCode')" :error="fieldError('fees', index, 'code')" required><template #default="{ describedBy }"><input :id="`fee-code-${index}`" v-model="fee.code" class="form-input font-mono uppercase" :disabled="!editable" :aria-describedby="describedBy"></template></FormField>
                                <FormField :id="`fee-step-${index}`" :label="t('catalog.editor.stepCode')" :error="fieldError('fees', index, 'step_code')"><template #default="{ describedBy }"><select :id="`fee-step-${index}`" v-model="fee.step_code" class="form-input" :disabled="!editable" :aria-describedby="describedBy"><option :value="null">—</option><option v-for="step in form.steps" :key="step.code" :value="step.code">{{ step.code }}</option></select></template></FormField>
                                <FormField :id="`fee-label-fr-${index}`" :label="t('catalog.editor.nameFr')" :error="fieldError('fees', index, 'label_fr')" required><template #default="{ describedBy }"><input :id="`fee-label-fr-${index}`" v-model="fee.label_fr" class="form-input" :disabled="!editable" :aria-describedby="describedBy"></template></FormField>
                                <FormField :id="`fee-label-en-${index}`" :label="t('catalog.editor.nameEn')" :error="fieldError('fees', index, 'label_en')" required><template #default="{ describedBy }"><input :id="`fee-label-en-${index}`" v-model="fee.label_en" class="form-input" :disabled="!editable" :aria-describedby="describedBy"></template></FormField>
                                <FormField :id="`fee-description-fr-${index}`" :label="t('catalog.editor.descriptionFr')" :error="fieldError('fees', index, 'description_fr')"><template #default="{ describedBy }"><textarea :id="`fee-description-fr-${index}`" v-model="fee.description_fr" class="form-input min-h-20" :disabled="!editable" :aria-describedby="describedBy" /></template></FormField>
                                <FormField :id="`fee-description-en-${index}`" :label="t('catalog.editor.descriptionEn')" :error="fieldError('fees', index, 'description_en')"><template #default="{ describedBy }"><textarea :id="`fee-description-en-${index}`" v-model="fee.description_en" class="form-input min-h-20" :disabled="!editable" :aria-describedby="describedBy" /></template></FormField>
                                <FormField :id="`fee-amount-${index}`" :label="t('catalog.editor.amountMinor')" :error="fieldError('fees', index, 'amount_minor')" required><template #default="{ describedBy }"><input :id="`fee-amount-${index}`" v-model.number="fee.amount_minor" type="number" min="0" step="1" class="form-input font-mono" :disabled="!editable" :aria-describedby="describedBy"></template></FormField>
                                <FormField :id="`fee-currency-${index}`" :label="t('catalog.editor.currency')" :error="fieldError('fees', index, 'currency')" required><template #default="{ describedBy }"><input :id="`fee-currency-${index}`" v-model="fee.currency" maxlength="3" class="form-input font-mono uppercase" :disabled="!editable" :aria-describedby="describedBy"></template></FormField>
                                <FormField :id="`fee-exponent-${index}`" :label="t('catalog.editor.minorUnitExponent')" :error="fieldError('fees', index, 'minor_unit_exponent')" required><template #default="{ describedBy }"><input :id="`fee-exponent-${index}`" v-model.number="fee.minor_unit_exponent" type="number" min="0" max="3" class="form-input font-mono" :disabled="!editable" :aria-describedby="describedBy"></template></FormField>
                                <div class="hidden sm:block" />
                                <FormField :id="`fee-when-fr-${index}`" :label="t('catalog.editor.dueWhenFr')" :error="fieldError('fees', index, 'due_when_fr')"><template #default="{ describedBy }"><input :id="`fee-when-fr-${index}`" v-model="fee.due_when_fr" class="form-input" :disabled="!editable" :aria-describedby="describedBy"></template></FormField>
                                <FormField :id="`fee-when-en-${index}`" :label="t('catalog.editor.dueWhenEn')" :error="fieldError('fees', index, 'due_when_en')"><template #default="{ describedBy }"><input :id="`fee-when-en-${index}`" v-model="fee.due_when_en" class="form-input" :disabled="!editable" :aria-describedby="describedBy"></template></FormField>
                                <FormField :id="`fee-basis-fr-${index}`" :label="t('catalog.editor.legalBasisFeeFr')" :error="fieldError('fees', index, 'legal_basis_fr')"><template #default="{ describedBy }"><textarea :id="`fee-basis-fr-${index}`" v-model="fee.legal_basis_fr" class="form-input min-h-20" :disabled="!editable" :aria-describedby="describedBy" /></template></FormField>
                                <FormField :id="`fee-basis-en-${index}`" :label="t('catalog.editor.legalBasisFeeEn')" :error="fieldError('fees', index, 'legal_basis_en')"><template #default="{ describedBy }"><textarea :id="`fee-basis-en-${index}`" v-model="fee.legal_basis_en" class="form-input min-h-20" :disabled="!editable" :aria-describedby="describedBy" /></template></FormField>
                            </div>
                            <div v-if="editable" class="mt-4 flex items-center justify-between gap-4"><label class="flex items-center gap-2 font-semibold"><input v-model="fee.is_mandatory" type="checkbox" class="size-5 rounded border-slate-300 text-brand-blue-600"> {{ t('catalog.editor.mandatoryFee') }}</label><button type="button" class="inline-flex min-h-11 items-center gap-2 rounded-md px-3 font-semibold text-brand-red-700 hover:bg-brand-red-50" @click="form.fees.splice(index, 1)"><TrashIcon class="size-4" aria-hidden="true" /> {{ t('common.remove') }}</button></div>
                        </fieldset>
                        <button v-if="editable" type="button" class="button-secondary" @click="addFee"><PlusIcon class="size-5" aria-hidden="true" /> {{ t('catalog.editor.addFee') }}</button>
                    </div>
                </details>

                <div v-if="editable" class="sticky bottom-3 z-20 flex justify-end border border-slate-300 bg-white/95 p-4 shadow-lg backdrop-blur">
                    <button type="submit" class="button-primary" :disabled="form.processing">
                        <CheckCircleIcon class="size-5" aria-hidden="true" />
                        {{ form.processing ? t('common.processing') : t('common.save') }}
                    </button>
                </div>
            </form>

            <aside class="space-y-5 xl:sticky xl:top-5 xl:self-start">
                <section class="border-t-4 border-brand-red-700 bg-brand-blue-900 p-5 text-white">
                    <p class="eyebrow text-blue-200">{{ t('catalog.editor.workflow') }}</p>
                    <div class="mt-4">
                        <StatusBadge :tone="statusTone">{{ t(`catalog.status.${version.status}`) }}</StatusBadge>
                    </div>
                    <p class="mt-4 text-sm leading-6 text-blue-100">{{ t(`catalog.statusHelp.${version.status}`) }}</p>

                    <button v-if="version.permissions.submitForReview" type="button" class="mt-5 min-h-11 w-full rounded-md bg-white px-4 font-semibold text-brand-blue-900 hover:bg-blue-50" @click="submitReview">
                        {{ t('catalog.editor.submitReview') }}
                    </button>
                    <button v-if="version.permissions.publish" type="button" class="mt-5 min-h-11 w-full rounded-md bg-white px-4 font-semibold text-brand-blue-900 hover:bg-blue-50" @click="publish">
                        {{ t('catalog.editor.publish') }}
                    </button>

                    <form v-if="version.permissions.returnToDraft" class="mt-5 border-t border-white/20 pt-5" @submit.prevent="returnToDraft">
                        <label for="return-reason" class="block text-sm font-semibold">{{ t('catalog.editor.returnReason') }}</label>
                        <p class="mt-1 text-sm text-blue-100">{{ t('catalog.editor.returnReasonHint') }}</p>
                        <textarea id="return-reason" v-model="returnForm.reason" class="form-input mt-2 min-h-24 text-ink-900" required minlength="10" />
                        <p v-if="returnForm.errors.reason" class="mt-2 text-sm font-semibold text-red-200">{{ returnForm.errors.reason }}</p>
                        <button type="submit" class="mt-3 min-h-11 w-full rounded-md border border-white/40 px-4 font-semibold hover:bg-white/10">
                            {{ t('catalog.editor.returnDraft') }}
                        </button>
                    </form>

                    <form v-if="version.permissions.retire" class="mt-5 border-t border-white/20 pt-5" @submit.prevent="retire">
                        <label for="retire-reason" class="block text-sm font-semibold">{{ t('catalog.editor.retireReason') }}</label>
                        <textarea id="retire-reason" v-model="retireForm.reason" class="form-input mt-2 min-h-24 text-ink-900" required minlength="10" />
                        <p v-if="retireForm.errors.reason" class="mt-2 text-sm font-semibold text-red-200">{{ retireForm.errors.reason }}</p>
                        <button type="submit" class="mt-3 min-h-11 w-full rounded-md bg-brand-red-700 px-4 font-semibold text-white hover:bg-red-800">
                            {{ t('catalog.editor.retire') }}
                        </button>
                    </form>

                    <button v-if="version.permissions.createVersion && ['published', 'retired'].includes(version.status)" type="button" class="mt-5 flex min-h-11 w-full items-center justify-center gap-2 rounded-md border border-white/40 px-4 font-semibold hover:bg-white/10" @click="createVersion">
                        <DocumentDuplicateIcon class="size-5" aria-hidden="true" />
                        {{ t('catalog.editor.newVersion') }}
                    </button>
                </section>

                <section class="border border-slate-300 bg-white p-5">
                    <h2 class="font-display text-lg font-semibold text-ink-900">{{ t('catalog.editor.versions') }}</h2>
                    <ol class="mt-4 space-y-2">
                        <li v-for="item in version.service.versions" :key="item.publicId">
                            <Link
                                :href="`/admin/catalog/versions/${item.publicId}`"
                                class="flex min-h-11 items-center justify-between gap-3 rounded-md px-3 text-sm font-semibold"
                                :class="item.publicId === version.publicId ? 'bg-brand-blue-50 text-brand-blue-900' : 'text-slate-600 hover:bg-slate-50'"
                            >
                                {{ t('common.version', { number: item.number }) }}
                                <ArrowPathIcon v-if="item.publicId === version.publicId" class="size-4" aria-hidden="true" />
                                <span v-else class="text-xs">{{ t(`catalog.status.${item.status}`) }}</span>
                            </Link>
                        </li>
                    </ol>
                </section>
            </aside>
        </div>
    </AgentShell>
</template>
