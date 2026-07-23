<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { CalendarDaysIcon, DocumentCheckIcon, DocumentTextIcon, ArrowRightIcon } from '@heroicons/vue/24/outline';
import { useI18n } from 'vue-i18n';
import AppShell from '@/Layouts/AppShell.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import ApplicationRail from '@/Components/ApplicationRail.vue';
import DynamicFormField from '@/Components/DynamicFormField.vue';
import DocumentUploader from '@/Components/DocumentUploader.vue';

const props = defineProps({
  application: {
    type: Object,
    required: true,
  },
});

const { t, locale } = useI18n();
const text = (value) => value?.[locale.value] ?? value?.fr ?? '';

const activeTab = ref('form'); // 'form', 'documents', 'summary'

const isDraft = computed(() => ['draft', 'correction_requested'].includes(props.application.status));

// Form responses state
const draftForm = useForm({
  responses: { ...props.application.formResponses },
});

const saveDraft = () => {
  draftForm.put(route('account.applications.update', props.application.publicId), {
    preserveScroll: true,
  });
};

const submitForm = useForm({});

const submitApplication = () => {
  if (confirm('Êtes-vous sûr de vouloir déposer ce dossier ? Cette action est irréversible.')) {
    submitForm.post(route('account.applications.submit', props.application.publicId));
  }
};

const existingDocForRequirement = (reqId) => {
  return props.application.submittedDocuments.find(d => d.requirementPublicId === reqId && d.status !== 'replaced');
};
</script>

<template>
  <Head :title="application.reference ? `Dossier ${application.reference}` : 'Brouillon de dossier'" />

  <AppShell :title="text(application.procedure.title)" :eyebrow="`Version ${application.procedure.versionNumber}`">
    <!-- Reference Cartouche for Submitted Applications -->
    <div v-if="application.reference" class="mb-6 bg-brand-blue-900 text-white rounded-lg p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
      <div>
        <p class="text-xs uppercase tracking-widest text-brand-blue-200 font-mono">Référence de suivi publique</p>
        <p class="font-mono text-2xl font-bold tracking-wider text-white mt-1">{{ application.reference }}</p>
        <p class="text-xs text-brand-blue-200 mt-1">Organisme : {{ text(application.procedure.service.organization.name) }}</p>
      </div>

      <div class="flex items-center gap-3">
        <span class="px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-500 text-white">
          {{ application.statusLabel }}
        </span>
      </div>
    </div>

    <div class="grid gap-7 lg:grid-cols-[minmax(0,1fr)_340px]">
      <section class="space-y-6">
        <!-- Tabs for Draft Mode -->
        <div v-if="isDraft" class="border-b border-slate-200 flex gap-4">
          <button
            class="py-3 px-4 text-sm font-semibold border-b-2 transition"
            :class="activeTab === 'form' ? 'border-brand-blue-600 text-brand-blue-900' : 'border-transparent text-slate-500 hover:text-slate-700'"
            @click="activeTab = 'form'"
          >
            1. Formulaire
          </button>
          <button
            class="py-3 px-4 text-sm font-semibold border-b-2 transition"
            :class="activeTab === 'documents' ? 'border-brand-blue-600 text-brand-blue-900' : 'border-transparent text-slate-500 hover:text-slate-700'"
            @click="activeTab = 'documents'"
          >
            2. Pièces à fournir ({{ application.submittedDocuments.length }}/{{ application.procedure.documentRequirements.length }})
          </button>
          <button
            class="py-3 px-4 text-sm font-semibold border-b-2 transition"
            :class="activeTab === 'summary' ? 'border-brand-blue-600 text-brand-blue-900' : 'border-transparent text-slate-500 hover:text-slate-700'"
            @click="activeTab = 'summary'"
          >
            3. Récapitulatif & Dépôt
          </button>
        </div>

        <!-- TAB 1: FORM -->
        <div v-if="activeTab === 'form' && isDraft" class="bg-white border border-slate-200 rounded-lg p-6 space-y-6">
          <h3 class="font-display text-lg font-semibold text-brand-blue-900 border-b pb-3">Renseignements de la demande</h3>
          
          <form @submit.prevent="saveDraft" class="space-y-5">
            <div v-if="application.procedure.formFields.length === 0" class="text-sm text-slate-500 italic">
              Aucun champ supplémentaire requis pour cette procédure.
            </div>

            <DynamicFormField
              v-for="field in application.procedure.formFields"
              :key="field.publicId"
              :field="field"
              v-model="draftForm.responses[field.code]"
              :error="draftForm.errors[`responses.${field.code}`]"
            />

            <div class="pt-4 flex justify-end">
              <button
                type="submit"
                :disabled="draftForm.processing"
                class="px-5 py-2.5 bg-brand-blue-600 hover:bg-brand-blue-900 text-white font-semibold rounded-md text-sm transition shadow-sm"
              >
                {{ draftForm.processing ? 'Enregistrement...' : 'Enregistrer le brouillon' }}
              </button>
            </div>
          </form>
        </div>

        <!-- TAB 2: DOCUMENTS -->
        <div v-else-if="activeTab === 'documents' && isDraft" class="bg-white border border-slate-200 rounded-lg p-6 space-y-6">
          <h3 class="font-display text-lg font-semibold text-brand-blue-900 border-b pb-3">Téléversement des pièces requises</h3>
          
          <div v-if="application.procedure.documentRequirements.length === 0" class="text-sm text-slate-500 italic">
            Aucun document requis pour cette procédure.
          </div>

          <div class="space-y-4">
            <DocumentUploader
              v-for="req in application.procedure.documentRequirements"
              :key="req.publicId"
              :application-public-id="application.publicId"
              :requirement="req"
              :existing-doc="existingDocForRequirement(req.publicId)"
            />
          </div>
        </div>

        <!-- TAB 3: SUMMARY & SUBMIT -->
        <div v-else-if="activeTab === 'summary' && isDraft" class="bg-white border border-slate-200 rounded-lg p-6 space-y-6">
          <h3 class="font-display text-lg font-semibold text-brand-blue-900 border-b pb-3">Récapitulatif de la demande</h3>
          
          <!-- Summary of form responses -->
          <div class="space-y-3">
            <h4 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Réponses aux questions</h4>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-md border text-xs">
              <div v-for="field in application.procedure.formFields" :key="field.publicId">
                <dt class="text-slate-500 font-medium">{{ text(field.label) }}</dt>
                <dd class="text-slate-900 font-semibold mt-0.5">{{ draftForm.responses[field.code] || '-' }}</dd>
              </div>
            </dl>
          </div>

          <!-- Summary of documents -->
          <div class="space-y-3">
            <h4 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Pièces jointes téléversées</h4>
            <ul class="divide-y divide-slate-200 border rounded-md text-xs">
              <li v-for="req in application.procedure.documentRequirements" :key="req.publicId" class="p-3 flex justify-between items-center">
                <span>{{ text(req.name) }}</span>
                <span v-if="existingDocForRequirement(req.publicId)" class="text-emerald-700 font-semibold">✓ Présent</span>
                <span v-else-if="req.isRequired" class="text-brand-red-700 font-semibold">✗ Manquant</span>
                <span v-else class="text-slate-400">Non fourni</span>
              </li>
            </ul>
          </div>

          <div v-if="submitForm.errors.documents" class="p-4 bg-red-50 border border-red-200 rounded text-xs text-red-700 font-medium">
            {{ submitForm.errors.documents }}
          </div>

          <div class="pt-4 border-t flex justify-end">
            <button
              type="button"
              :disabled="submitForm.processing"
              class="px-6 py-3 bg-brand-blue-600 hover:bg-brand-blue-900 text-white font-bold rounded-md text-sm transition shadow-md flex items-center gap-2"
              @click="submitApplication"
            >
              <DocumentCheckIcon class="w-5 h-5" />
              {{ submitForm.processing ? 'Dépôt en cours...' : 'Déposer définitivement le dossier' }}
            </button>
          </div>
        </div>

        <!-- READONLY SUBMITTED VIEW -->
        <div v-else-if="!isDraft" class="bg-white border border-slate-200 rounded-lg p-6 space-y-6">
          <div class="flex items-center justify-between border-b pb-4">
            <h3 class="font-display text-lg font-semibold text-brand-blue-900">Synthèse du dossier déposé</h3>
            <span class="text-xs text-slate-500">Déposé le {{ new Date(application.submittedAt).toLocaleDateString('fr-FR') }}</span>
          </div>

          <div class="space-y-4">
            <h4 class="text-sm font-semibold text-slate-800">Pièces déposées</h4>
            <div class="space-y-2">
              <div v-for="doc in application.submittedDocuments" :key="doc.publicId" class="flex items-center justify-between p-3 border rounded bg-slate-50 text-xs">
                <div>
                  <p class="font-semibold text-slate-900">{{ doc.originalFilename }}</p>
                  <p class="text-slate-400">Empreinte SHA-256 : {{ doc.fileHash.substring(0, 16) }}...</p>
                </div>
                <a :href="doc.downloadUrl" target="_blank" class="text-brand-blue-600 font-semibold hover:underline">
                  Télécharger
                </a>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ASIDE: RAIL DE PROGRESSION -->
      <aside class="space-y-6">
        <ApplicationRail
          :status="application.status"
          :steps="application.procedure.steps"
        />

        <div v-if="application.procedure.isDemo" class="border border-brand-red-700/25 bg-brand-red-50 p-4 rounded-lg font-semibold text-xs text-brand-red-700">
          {{ t('catalog.detail.demoNotice') }}
        </div>
      </aside>
    </div>
  </AppShell>
</template>
