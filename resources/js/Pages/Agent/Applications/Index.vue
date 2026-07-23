<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { MagnifyingGlassIcon, FunnelIcon } from '@heroicons/vue/24/outline';
import AgentShell from '@/Layouts/AgentShell.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({
  applications: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
});

const search = ref(props.filters.q || '');
const selectedStatus = ref(props.filters.status || '');

const applyFilters = () => {
  router.get(
    route('agent.applications.index'),
    { q: search.value, status: selectedStatus.value },
    { preserveState: true, replace: true }
  );
};

let searchTimeout;
watch(search, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(applyFilters, 300);
});
</script>

<template>
  <Head title="File de traitement des dossiers" />

  <AgentShell title="File d'instruction des dossiers" eyebrow="Gestion des démarches">
    <div class="space-y-6">
      <!-- Filter Bar -->
      <div class="bg-white border border-slate-200 rounded-lg p-4 flex flex-col sm:flex-row gap-4 items-center justify-between shadow-sm">
        <div class="relative w-full sm:w-80">
          <MagnifyingGlassIcon class="w-5 h-5 absolute left-3 top-2.5 text-slate-400" />
          <input
            v-model="search"
            type="text"
            placeholder="Rechercher par référence, nom ou e-mail..."
            class="w-full pl-9 pr-4 py-2 text-sm border-slate-300 rounded-md focus:ring-brand-blue-600 focus:border-brand-blue-600"
          >
        </div>

        <div class="flex items-center gap-3 w-full sm:w-auto">
          <FunnelIcon class="w-5 h-5 text-slate-400" />
          <select
            v-model="selectedStatus"
            class="text-sm border-slate-300 rounded-md focus:ring-brand-blue-600 focus:border-brand-blue-600 w-full sm:w-auto"
            @change="applyFilters"
          >
            <option value="">Tous les statuts actifs</option>
            <option value="submitted">Déposé</option>
            <option value="under_review">En instruction</option>
            <option value="correction_requested">Correction demandée</option>
            <option value="approved">Approuvé</option>
            <option value="rejected">Rejeté</option>
          </select>
        </div>
      </div>

      <!-- Data Table -->
      <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold uppercase text-slate-500 tracking-wider">
              <th class="py-3.5 px-4">Référence</th>
              <th class="py-3.5 px-4">Usager</th>
              <th class="py-3.5 px-4">Procédure</th>
              <th class="py-3.5 px-4">Statut</th>
              <th class="py-3.5 px-4">Date dépôt</th>
              <th class="py-3.5 px-4 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 text-xs">
            <tr v-for="app in applications.data" :key="app.publicId" class="hover:bg-slate-50 transition">
              <td class="py-3.5 px-4 font-mono font-bold text-brand-blue-900">
                {{ app.reference || 'Non attribuée' }}
              </td>
              <td class="py-3.5 px-4 font-medium text-slate-900">
                {{ app.citizenName }}
              </td>
              <td class="py-3.5 px-4 text-slate-700">
                {{ app.procedureTitle.fr }}
              </td>
              <td class="py-3.5 px-4">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                  {{ app.statusLabel }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-slate-500">
                {{ app.submittedAt ? new Date(app.submittedAt).toLocaleDateString('fr-FR') : '-' }}
              </td>
              <td class="py-3.5 px-4 text-right">
                <Link
                  :href="route('agent.applications.show', app.publicId)"
                  class="px-3 py-1.5 bg-brand-blue-600 hover:bg-brand-blue-900 text-white font-semibold rounded text-xs transition"
                >
                  Instruire
                </Link>
              </td>
            </tr>
            <tr v-if="applications.data.length === 0">
              <td colspan="6" class="p-8 text-center text-slate-500">
                Aucun dossier ne correspond à votre recherche ou à votre périmètre d'affectation.
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="applications.links" class="p-4 border-t border-slate-200">
          <Pagination :links="applications.links" />
        </div>
      </div>
    </div>
  </AgentShell>
</template>
