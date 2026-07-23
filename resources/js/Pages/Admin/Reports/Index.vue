<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ChartBarIcon, ArrowDownTrayIcon, BanknotesIcon, DocumentCheckIcon, FolderIcon } from '@heroicons/vue/24/outline';
import AgentShell from '@/Layouts/AgentShell.vue';

defineProps({
  reports: {
    type: Object,
    required: true,
  },
});

const exportForm = useForm({});

const handleExport = () => {
  exportForm.post(route('admin.reports.export'));
};
</script>

<template>
  <Head title="Tableau de Bord & Rapports Opérationnels" />

  <AgentShell title="Tableau de Bord & Exploitation" eyebrow="Administration — KPIs & Audit Exports">
    <div class="space-y-6 max-w-6xl mx-auto">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="font-display text-lg font-bold text-slate-900">Indicateurs de Performance (KPIs)</h2>
          <p class="text-xs text-slate-500">Rapport consolidé généré le {{ new Date(reports.generatedAt).toLocaleString('fr-FR') }}</p>
        </div>

        <button
          type="button"
          :disabled="exportForm.processing"
          class="px-4 py-2 bg-brand-blue-600 hover:bg-brand-blue-800 text-white font-semibold text-xs rounded shadow flex items-center gap-2 transition"
          @click="handleExport"
        >
          <ArrowDownTrayIcon class="w-4 h-4" /> Exporter le rapport (Journalisé)
        </button>
      </div>

      <!-- Metric Cards Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm flex items-center gap-4">
          <div class="p-3 bg-brand-blue-50 text-brand-blue-700 rounded-lg">
            <FolderIcon class="w-6 h-6" />
          </div>
          <div>
            <p class="text-xs font-semibold text-slate-500 uppercase">Total Dossiers</p>
            <p class="text-2xl font-bold font-mono text-slate-900">{{ reports.totalApplications }}</p>
          </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm flex items-center gap-4">
          <div class="p-3 bg-emerald-50 text-emerald-700 rounded-lg">
            <BanknotesIcon class="w-6 h-6" />
          </div>
          <div>
            <p class="text-xs font-semibold text-slate-500 uppercase">Recettes Encaissées</p>
            <p class="text-xl font-bold font-mono text-slate-900">
              {{ (reports.totalRevenueMinor).toLocaleString('fr-FR') }} {{ reports.currency }}
            </p>
          </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm flex items-center gap-4">
          <div class="p-3 bg-indigo-50 text-indigo-700 rounded-lg">
            <DocumentCheckIcon class="w-6 h-6" />
          </div>
          <div>
            <p class="text-xs font-semibold text-slate-500 uppercase">Titres Officiels Émis</p>
            <p class="text-2xl font-bold font-mono text-slate-900">{{ reports.issuedDocumentsCount }}</p>
          </div>
        </div>
      </div>

      <!-- Status distribution breakdown -->
      <div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm space-y-4">
        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
          <ChartBarIcon class="w-5 h-5 text-brand-blue-700" /> Répartition des dossiers par statut
        </h3>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
          <div v-for="(count, status) in reports.statusCounts" :key="status" class="border p-3 rounded-lg bg-slate-50">
            <p class="font-mono text-slate-500 uppercase text-[10px]">{{ status }}</p>
            <p class="text-lg font-bold font-mono text-slate-900 mt-1">{{ count }}</p>
          </div>
        </div>
      </div>
    </div>
  </AgentShell>
</template>
