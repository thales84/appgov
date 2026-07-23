<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { AcademicCapIcon, CheckCircleIcon, XCircleIcon } from '@heroicons/vue/24/outline';
import AgentShell from '@/Layouts/AgentShell.vue';

const props = defineProps({
  sessions: {
    type: Object,
    required: true,
  },
});

const selectedAttemptForCorrection = ref(null);

const correctionForm = useForm({
  result: 'passed',
  score: 100,
  reason: '',
});

const openCorrectionModal = (attempt) => {
  selectedAttemptForCorrection.value = attempt;
  correctionForm.result = attempt.result;
  correctionForm.score = attempt.score;
  correctionForm.reason = '';
};

const submitCorrection = () => {
  if (!selectedAttemptForCorrection.value) return;
  correctionForm.post(route('agent.examinations.attempts.correct', selectedAttemptForCorrection.value.publicId), {
    onSuccess: () => {
      selectedAttemptForCorrection.value = null;
    },
  });
};
</script>

<template>
  <Head title="Gestion des Examens & Sessions" />

  <AgentShell title="Gestion des Examens" eyebrow="Workspace Examiner & Sessions">
    <div class="space-y-6 max-w-6xl mx-auto">
      <div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm">
        <h2 class="font-display text-lg font-bold text-slate-900 mb-1">Sessions d'Épreuves & Résultats</h2>
        <p class="text-xs text-slate-500 mb-6">Consultez les sessions programmées et gérez les résultats des candidats.</p>

        <div v-if="sessions.data.length === 0" class="p-8 text-center text-xs text-slate-500 bg-slate-50 rounded-lg">
          Aucune session d'examen configurée pour votre organisme.
        </div>

        <div v-else class="space-y-8">
          <div v-for="session in sessions.data" :key="session.publicId" class="border border-slate-200 rounded-lg overflow-hidden">
            <div class="bg-slate-50 px-4 py-3 border-b flex items-center justify-between">
              <div class="flex items-center gap-3">
                <AcademicCapIcon class="w-5 h-5 text-brand-blue-700" />
                <div>
                  <h3 class="text-xs font-bold text-slate-900">{{ session.examTypeName.fr }} — {{ session.locationName.fr }}</h3>
                  <p class="text-[11px] text-slate-500">Date de session : {{ session.sessionDate }}</p>
                </div>
              </div>
            </div>

            <!-- Attempts list table -->
            <table class="w-full text-left text-xs border-collapse">
              <thead>
                <tr class="bg-white text-slate-400 font-semibold border-b">
                  <th class="py-2.5 px-4">Réf. Dossier</th>
                  <th class="py-2.5 px-4">Candidat</th>
                  <th class="py-2.5 px-4">Examinateur</th>
                  <th class="py-2.5 px-4 text-center">Score</th>
                  <th class="py-2.5 px-4">Résultat</th>
                  <th class="py-2.5 px-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 bg-white">
                <tr v-for="att in session.attempts" :key="att.publicId">
                  <td class="py-3 px-4 font-mono font-bold text-brand-blue-900">{{ att.reference }}</td>
                  <td class="py-3 px-4 text-slate-800 font-medium">{{ att.citizenName }}</td>
                  <td class="py-3 px-4 text-slate-600">{{ att.examinerName }}</td>
                  <td class="py-3 px-4 text-center font-mono font-semibold">{{ att.score ?? '-' }}/100</td>
                  <td class="py-3 px-4">
                    <span
                      class="px-2 py-0.5 rounded text-[11px] font-bold uppercase"
                      :class="att.result === 'passed' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                    >
                      {{ att.resultLabel }}
                    </span>
                    <p v-if="att.previousResult" class="text-[10px] text-amber-600 font-semibold mt-0.5">
                      Corrigé (ex: {{ att.previousResult }})
                    </p>
                  </td>
                  <td class="py-3 px-4 text-right">
                    <button
                      type="button"
                      class="text-xs text-brand-blue-600 hover:text-brand-blue-900 font-semibold underline"
                      @click="openCorrectionModal(att)"
                    >
                      Correction auditée
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Correction Modal -->
      <div v-if="selectedAttemptForCorrection" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-xl space-y-4">
          <h3 class="font-bold text-sm text-slate-900">Correction exceptionnelle de résultat</h3>
          <p class="text-xs text-slate-500">
            Dossier : <strong>{{ selectedAttemptForCorrection.reference }}</strong>
          </p>

          <div class="space-y-3 text-xs">
            <div>
              <label class="block font-semibold text-slate-700 mb-1">Nouveau résultat</label>
              <select v-model="correctionForm.result" class="w-full text-xs rounded border-slate-300">
                <option value="passed">Admis / Réussi</option>
                <option value="failed">Ajourné / Échoué</option>
                <option value="absent">Absent</option>
              </select>
            </div>

            <div>
              <label class="block font-semibold text-slate-700 mb-1">Nouveau Score (/100)</label>
              <input v-model.number="correctionForm.score" type="number" min="0" max="100" class="w-full text-xs rounded border-slate-300">
            </div>

            <div>
              <label class="block font-semibold text-slate-700 mb-1">Motif de la correction (Audit obligatoire)</label>
              <textarea v-model="correctionForm.reason" rows="3" class="w-full text-xs rounded border-slate-300" placeholder="Motif administratif..."></textarea>
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <button
              type="button"
              class="px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-100 rounded"
              @click="selectedAttemptForCorrection = null"
            >
              Annuler
            </button>
            <button
              type="button"
              :disabled="!correctionForm.reason || correctionForm.processing"
              class="px-4 py-1.5 bg-brand-blue-600 hover:bg-brand-blue-900 disabled:opacity-50 text-white text-xs font-bold rounded"
              @click="submitCorrection"
            >
              Enregistrer la correction
            </button>
          </div>
        </div>
      </div>
    </div>
  </AgentShell>
</template>
