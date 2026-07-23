<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { CogIcon, CheckCircleIcon, DocumentCheckIcon } from '@heroicons/vue/24/outline';
import AgentShell from '@/Layouts/AgentShell.vue';

defineProps({
  applications: {
    type: Object,
    required: true,
  },
});

const startForm = useForm({});
const completeForm = useForm({
  quality_notes: 'Contrôle qualité conforme.',
});

const handleStartProduction = (publicId) => {
  startForm.post(route('agent.applications.production.start', publicId));
};

const handleCompleteProduction = (publicId) => {
  completeForm.post(route('agent.applications.production.complete', publicId));
};
</script>

<template>
  <Head title="Confection & Production des Titres" />

  <AgentShell title="Confection des Titres" eyebrow="Workspace Confection & Contrôle Qualité">
    <div class="space-y-6 max-w-6xl mx-auto">
      <div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm">
        <h2 class="font-display text-lg font-bold text-slate-900 mb-1">Dossiers en File de Fabrication</h2>
        <p class="text-xs text-slate-500 mb-6">Lancez l'impression et validez le contrôle qualité pour délivrer le numéro d'acte opaque.</p>

        <div v-if="applications.data.length === 0" class="p-8 text-center text-xs text-slate-500 bg-slate-50 rounded-lg">
          Aucun dossier en attente de fabrication.
        </div>

        <table v-else class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50 text-slate-400 font-semibold border-b">
              <th class="py-3 px-4">Réf. Dossier</th>
              <th class="py-3 px-4">Titulaire</th>
              <th class="py-3 px-4">État Dossier</th>
              <th class="py-3 px-4">Numéro d'Acte Émis</th>
              <th class="py-3 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">
            <tr v-for="app in applications.data" :key="app.publicId">
              <td class="py-3 px-4 font-mono font-bold text-brand-blue-900">{{ app.reference }}</td>
              <td class="py-3 px-4 text-slate-800 font-medium">{{ app.citizenName }}</td>
              <td class="py-3 px-4">
                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700">
                  {{ app.statusLabel }}
                </span>
              </td>
              <td class="py-3 px-4 font-mono font-bold text-emerald-800">
                {{ app.documentNumber ?? '-' }}
              </td>
              <td class="py-3 px-4 text-right">
                <button
                  v-if="app.status === 'approved'"
                  type="button"
                  :disabled="startForm.processing"
                  class="px-3 py-1.5 bg-brand-blue-600 hover:bg-brand-blue-800 text-white text-xs font-semibold rounded flex items-center gap-1 ml-auto"
                  @click="handleStartProduction(app.publicId)"
                >
                  <CogIcon class="w-4 h-4" /> Lancer l'impression
                </button>

                <button
                  v-else-if="app.status === 'in_production'"
                  type="button"
                  :disabled="completeForm.processing"
                  class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-800 text-white text-xs font-semibold rounded flex items-center gap-1 ml-auto"
                  @click="handleCompleteProduction(app.publicId)"
                >
                  <CheckCircleIcon class="w-4 h-4" /> Valider Contrôle Qualité
                </button>

                <span v-else class="text-xs text-slate-400 italic">Fabriqué & Disponible</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AgentShell>
</template>
