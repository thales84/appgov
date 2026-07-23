<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { DocumentCheckIcon, ChatBubbleLeftRightIcon, CheckCircleIcon, XCircleIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
import AgentShell from '@/Layouts/AgentShell.vue';

const props = defineProps({
  application: {
    type: Object,
    required: true,
  },
});

const activeTab = ref('documents'); // 'documents', 'correction', 'decision', 'messages'

// Start review action
const startReview = () => {
  router.post(route('agent.applications.start-review', props.application.publicId));
};

// Document review form
const activeDocId = ref(null);
const docForm = useForm({
  is_valid: true,
  notes: '',
});

const submitDocReview = (docPublicId, isValid) => {
  docForm.is_valid = isValid;
  docForm.post(route('agent.applications.documents.review', [props.application.publicId, docPublicId]), {
    preserveScroll: true,
    onSuccess: () => {
      activeDocId.value = null;
      docForm.reset();
    },
  });
};

// Correction form
const correctionForm = useForm({
  reason: '',
});

const submitCorrection = () => {
  correctionForm.post(route('agent.applications.correction.store', props.application.publicId));
};

// Decision form
const decisionForm = useForm({
  decision_type: 'approved',
  reason_fr: '',
  reason_en: '',
});

const submitDecision = () => {
  if (confirm(`Confirmez-vous le prononcé de cette décision (${decisionForm.decision_type}) ?`)) {
    decisionForm.post(route('agent.applications.decisions.store', props.application.publicId));
  }
};

// Message form
const messageForm = useForm({
  message: '',
  is_internal: true,
});

const sendMessage = () => {
  messageForm.post(route('agent.applications.messages.store', props.application.publicId), {
    preserveScroll: true,
    onSuccess: () => messageForm.reset('message'),
  });
};
</script>

<template>
  <Head :title="`Instruction - ${application.reference || application.publicId}`" />

  <AgentShell title="Instruction du dossier" eyebrow="Espace Instructeur Agent">
    <!-- Header Cartouche -->
    <div class="bg-brand-blue-900 text-white rounded-lg p-6 mb-6 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-3">
          <span class="font-mono text-xl font-bold tracking-wider">{{ application.reference || 'Brouillon' }}</span>
          <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500 text-white">
            {{ application.statusLabel }}
          </span>
        </div>
        <p class="text-xs text-brand-blue-200 mt-1">
          Procédure : <strong>{{ application.procedure.title.fr }}</strong> (v{{ application.procedure.versionNumber }})
        </p>
        <p class="text-xs text-brand-blue-200">
          Organisme : {{ application.procedure.service.organization.name.fr }}
        </p>
      </div>

      <div v-if="application.status === 'submitted'">
        <button
          type="button"
          class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded shadow transition"
          @click="startReview"
        >
          Démarrer l'instruction
        </button>
      </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="grid gap-7 lg:grid-cols-[minmax(0,1fr)_340px]">
      <section class="space-y-6">
        <div class="border-b border-slate-200 flex gap-4 bg-white px-4 rounded-t-lg">
          <button
            class="py-3 px-4 text-xs font-semibold border-b-2 transition"
            :class="activeTab === 'documents' ? 'border-brand-blue-600 text-brand-blue-900' : 'border-transparent text-slate-500 hover:text-slate-700'"
            @click="activeTab = 'documents'"
          >
            Pièces & Réponses ({{ application.submittedDocuments.length }})
          </button>
          <button
            class="py-3 px-4 text-xs font-semibold border-b-2 transition"
            :class="activeTab === 'correction' ? 'border-brand-blue-600 text-brand-blue-900' : 'border-transparent text-slate-500 hover:text-slate-700'"
            @click="activeTab = 'correction'"
          >
            Demander une correction
          </button>
          <button
            class="py-3 px-4 text-xs font-semibold border-b-2 transition"
            :class="activeTab === 'decision' ? 'border-brand-blue-600 text-brand-blue-900' : 'border-transparent text-slate-500 hover:text-slate-700'"
            @click="activeTab = 'decision'"
          >
            Décision d'instruction
          </button>
          <button
            class="py-3 px-4 text-xs font-semibold border-b-2 transition"
            :class="activeTab === 'messages' ? 'border-brand-blue-600 text-brand-blue-900' : 'border-transparent text-slate-500 hover:text-slate-700'"
            @click="activeTab = 'messages'"
          >
            Notes & Echanges ({{ application.messages.length }})
          </button>
        </div>

        <!-- TAB 1: DOCUMENTS & RESPONSES -->
        <div v-if="activeTab === 'documents'" class="bg-white border border-slate-200 rounded-b-lg p-6 space-y-6">
          <h3 class="font-display text-base font-semibold text-brand-blue-900 border-b pb-2">Contrôle de conformité des pièces jointes</h3>

          <div class="space-y-4">
            <div
              v-for="doc in application.submittedDocuments"
              :key="doc.publicId"
              class="border border-slate-200 rounded-lg p-4 bg-slate-50/50 space-y-3"
            >
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-semibold text-sm text-slate-900">{{ doc.originalFilename }}</p>
                  <p class="text-xs text-slate-400">Empreinte SHA-256 : {{ doc.fileHash.substring(0, 24) }}...</p>
                </div>

                <div class="flex items-center gap-2">
                  <span
                    class="px-2.5 py-1 rounded-full text-xs font-semibold"
                    :class="{
                      'bg-emerald-100 text-emerald-800': doc.status === 'valid',
                      'bg-red-100 text-red-800': doc.status === 'invalid',
                      'bg-blue-100 text-blue-800': doc.status === 'pending',
                    }"
                  >
                    {{ doc.statusLabel }}
                  </span>

                  <a :href="doc.downloadUrl" target="_blank" class="text-xs text-brand-blue-600 hover:underline font-semibold">
                    Consulter
                  </a>
                </div>
              </div>

              <!-- Action buttons for document review -->
              <div class="pt-2 flex items-center gap-3 border-t border-slate-200">
                <button
                  type="button"
                  class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-medium flex items-center gap-1"
                  @click="submitDocReview(doc.publicId, true)"
                >
                  <CheckCircleIcon class="w-4 h-4" /> Marquer Conforme
                </button>
                <button
                  type="button"
                  class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded text-xs font-medium flex items-center gap-1"
                  @click="activeDocId = activeDocId === doc.publicId ? null : doc.publicId"
                >
                  <XCircleIcon class="w-4 h-4" /> Rejeter / Motif
                </button>
              </div>

              <div v-if="activeDocId === doc.publicId" class="p-3 bg-white border border-red-200 rounded text-xs space-y-2 mt-2">
                <label class="block font-semibold text-slate-700">Motif de non-conformité :</label>
                <input
                  v-model="docForm.notes"
                  type="text"
                  placeholder="Ex: Image floue ou document expiré"
                  class="w-full text-xs border-slate-300 rounded"
                >
                <div class="flex justify-end gap-2">
                  <button
                    type="button"
                    class="px-3 py-1 bg-red-700 text-white font-semibold rounded text-xs"
                    @click="submitDocReview(doc.publicId, false)"
                  >
                    Confirmer le rejet
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 2: CORRECTION -->
        <div v-else-if="activeTab === 'correction'" class="bg-white border border-slate-200 rounded-b-lg p-6 space-y-6">
          <h3 class="font-display text-base font-semibold text-brand-blue-900 border-b pb-2">Demander une correction à l'usager</h3>
          
          <p class="text-xs text-slate-600">
            Cette action fera passer le dossier au statut <strong>Correction demandée</strong>. L'usager sera notifié et pourra modifier les pièces ou champs concernés.
          </p>

          <form @submit.prevent="submitCorrection" class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-slate-800 mb-1">Motif détaillé de la correction requise :</label>
              <textarea
                v-model="correctionForm.reason"
                rows="4"
                placeholder="Précisez exactement les éléments à corriger..."
                class="w-full text-xs border-slate-300 rounded focus:ring-brand-blue-600"
              />
              <p v-if="correctionForm.errors.reason" class="text-xs text-red-600 mt-1">{{ correctionForm.errors.reason }}</p>
            </div>

            <div class="flex justify-end">
              <button
                type="submit"
                :disabled="correctionForm.processing"
                class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded text-xs transition"
              >
                Envoyer la demande de correction
              </button>
            </div>
          </form>
        </div>

        <!-- TAB 3: DECISION -->
        <div v-else-if="activeTab === 'decision'" class="bg-white border border-slate-200 rounded-b-lg p-6 space-y-6">
          <h3 class="font-display text-base font-semibold text-brand-blue-900 border-b pb-2">Prononcer une décision sur le dossier</h3>

          <form @submit.prevent="submitDecision" class="space-y-5">
            <div>
              <label class="block text-xs font-semibold text-slate-800 mb-2">Type de décision :</label>
              <div class="flex gap-4">
                <label class="flex items-center gap-2 text-xs font-medium cursor-pointer">
                  <input v-model="decisionForm.decision_type" type="radio" value="approved" class="text-emerald-600 focus:ring-emerald-500">
                  <span class="text-emerald-800 font-bold">Approuver le dossier</span>
                </label>
                <label class="flex items-center gap-2 text-xs font-medium cursor-pointer">
                  <input v-model="decisionForm.decision_type" type="radio" value="rejected" class="text-red-600 focus:ring-red-500">
                  <span class="text-red-800 font-bold">Rejeter le dossier</span>
                </label>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-800 mb-1">Motif / Justification (Français) :</label>
                <textarea
                  v-model="decisionForm.reason_fr"
                  rows="3"
                  class="w-full text-xs border-slate-300 rounded focus:ring-brand-blue-600"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-800 mb-1">Motif / Justification (Anglais) :</label>
                <textarea
                  v-model="decisionForm.reason_en"
                  rows="3"
                  class="w-full text-xs border-slate-300 rounded focus:ring-brand-blue-600"
                />
              </div>
            </div>

            <div class="flex justify-end">
              <button
                type="submit"
                :disabled="decisionForm.processing"
                class="px-5 py-2.5 bg-brand-blue-900 hover:bg-brand-blue-800 text-white font-bold rounded text-xs transition"
              >
                Enregistrer la décision formelle
              </button>
            </div>
          </form>
        </div>

        <!-- TAB 4: MESSAGES -->
        <div v-else-if="activeTab === 'messages'" class="bg-white border border-slate-200 rounded-b-lg p-6 space-y-6">
          <h3 class="font-display text-base font-semibold text-brand-blue-900 border-b pb-2">Fil d'échanges & Notes internes</h3>

          <div class="space-y-3 max-h-80 overflow-y-auto p-2 border rounded bg-slate-50">
            <div
              v-for="msg in application.messages"
              :key="msg.publicId"
              class="p-3 rounded text-xs space-y-1"
              :class="msg.isInternal ? 'bg-amber-50 border border-amber-200 text-amber-900' : 'bg-white border border-slate-200 text-slate-800'"
            >
              <div class="flex justify-between items-center text-[11px] font-semibold text-slate-500">
                <span>{{ msg.senderName }} ({{ msg.isAgent ? 'Agent' : 'Usager' }})</span>
                <span>{{ new Date(msg.createdAt).toLocaleString('fr-FR') }}</span>
              </div>
              <p class="whitespace-pre-wrap">{{ msg.message }}</p>
            </div>
            <div v-if="application.messages.length === 0" class="text-xs text-slate-400 text-center py-4">
              Aucun message pour l'instant.
            </div>
          </div>

          <form @submit.prevent="sendMessage" class="space-y-3">
            <div>
              <textarea
                v-model="messageForm.message"
                rows="2"
                placeholder="Rédiger un message ou une note..."
                class="w-full text-xs border-slate-300 rounded focus:ring-brand-blue-600"
              />
            </div>
            <div class="flex items-center justify-between">
              <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                <input v-model="messageForm.is_internal" type="checkbox" class="rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                <span>Note interne (visible uniquement par les agents)</span>
              </label>

              <button
                type="submit"
                :disabled="messageForm.processing"
                class="px-4 py-2 bg-brand-blue-600 hover:bg-brand-blue-900 text-white font-semibold rounded text-xs"
              >
                Envoyer
              </button>
            </div>
          </form>
        </div>
      </section>

      <!-- Sidebar Metadata -->
      <aside class="space-y-6">
        <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm space-y-4">
          <h4 class="text-xs font-semibold uppercase text-slate-500 tracking-wider">Informations usager</h4>
          <div class="text-xs space-y-2">
            <div>
              <span class="text-slate-400 block">Nom complet :</span>
              <span class="font-semibold text-slate-900">{{ application.procedure.service.organization.name.fr }}</span>
            </div>
            <div>
              <span class="text-slate-400 block">Référence :</span>
              <span class="font-mono font-bold text-brand-blue-900">{{ application.reference || '-' }}</span>
            </div>
          </div>
        </div>
      </aside>
    </div>
  </AgentShell>
</template>
