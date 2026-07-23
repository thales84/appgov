<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { TruckIcon, ShieldCheckIcon } from '@heroicons/vue/24/outline';
import AgentShell from '@/Layouts/AgentShell.vue';

defineProps({
  deliveries: {
    type: Object,
    required: true,
  },
});

const selectedDelivery = ref(null);

const deliverForm = useForm({
  recipient_name: '',
  identity_document_number: '',
  notes: 'Pièce d\'identité vérifiée au guichet.',
});

const openDeliverModal = (delivery) => {
  selectedDelivery.value = delivery;
  deliverForm.recipient_name = delivery.citizenName;
  deliverForm.identity_document_number = '';
};

const submitDelivery = () => {
  if (!selectedDelivery.value) return;
  deliverForm.post(route('agent.deliveries.deliver', selectedDelivery.value.publicId), {
    onSuccess: () => {
      selectedDelivery.value = null;
    },
  });
};
</script>

<template>
  <Head title="Guichet de Remise des Titres" />

  <AgentShell title="Remise des Titres" eyebrow="Guichet de Délivrance & Preuves">
    <div class="space-y-6 max-w-6xl mx-auto">
      <div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm">
        <h2 class="font-display text-lg font-bold text-slate-900 mb-1">Titres Disponibles au Guichet</h2>
        <p class="text-xs text-slate-500 mb-6">Effectuez le contrôle d'identité et enregistrez la preuve de remise pour clôturer le dossier.</p>

        <div v-if="deliveries.data.length === 0" class="p-8 text-center text-xs text-slate-500 bg-slate-50 rounded-lg">
          Aucun titre en attente de remise.
        </div>

        <table v-else class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50 text-slate-400 font-semibold border-b">
              <th class="py-3 px-4">Réf. Dossier</th>
              <th class="py-3 px-4">Titulaire</th>
              <th class="py-3 px-4">N° d'Acte Émis</th>
              <th class="py-3 px-4">État Expédition</th>
              <th class="py-3 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">
            <tr v-for="del in deliveries.data" :key="del.publicId">
              <td class="py-3 px-4 font-mono font-bold text-brand-blue-900">{{ del.applicationReference }}</td>
              <td class="py-3 px-4 text-slate-800 font-medium">{{ del.citizenName }}</td>
              <td class="py-3 px-4 font-mono font-bold text-emerald-800">{{ del.documentNumber }}</td>
              <td class="py-3 px-4">
                <span
                  class="px-2 py-0.5 rounded text-[11px] font-bold uppercase"
                  :class="del.status === 'delivered' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                >
                  {{ del.statusLabel }}
                </span>
              </td>
              <td class="py-3 px-4 text-right">
                <button
                  v-if="del.status !== 'delivered'"
                  type="button"
                  class="px-3 py-1.5 bg-brand-blue-600 hover:bg-brand-blue-800 text-white text-xs font-semibold rounded flex items-center gap-1 ml-auto"
                  @click="openDeliverModal(del)"
                >
                  <ShieldCheckIcon class="w-4 h-4" /> Remettre le titre
                </button>

                <div v-else class="text-[11px] text-slate-500 text-right">
                  <p class="font-bold text-slate-700">Remis à {{ del.proof?.recipientName }}</p>
                  <p class="text-[10px] text-slate-400">Pièce ID : {{ del.proof?.identityDocumentNumber }}</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Delivery Proof Modal -->
      <div v-if="selectedDelivery" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-xl space-y-4">
          <h3 class="font-bold text-sm text-slate-900">Enregistrement de la preuve de remise</h3>
          <p class="text-xs text-slate-500">
            Dossier : <strong>{{ selectedDelivery.applicationReference }}</strong> | Titre : <strong>{{ selectedDelivery.documentNumber }}</strong>
          </p>

          <div class="space-y-3 text-xs">
            <div>
              <label class="block font-semibold text-slate-700 mb-1">Nom complet du destinataire</label>
              <input v-model="deliverForm.recipient_name" type="text" class="w-full text-xs rounded border-slate-300">
            </div>

            <div>
              <label class="block font-semibold text-slate-700 mb-1">Numéro de pièce d'identité (CNI / Passeport)</label>
              <input v-model="deliverForm.identity_document_number" type="text" class="w-full text-xs rounded border-slate-300" placeholder="ex: 118273940">
            </div>

            <div>
              <label class="block font-semibold text-slate-700 mb-1">Notes complémentaires</label>
              <textarea v-model="deliverForm.notes" rows="2" class="w-full text-xs rounded border-slate-300"></textarea>
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <button
              type="button"
              class="px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-100 rounded"
              @click="selectedDelivery = null"
            >
              Annuler
            </button>
            <button
              type="button"
              :disabled="!deliverForm.identity_document_number || deliverForm.processing"
              class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-800 disabled:opacity-50 text-white text-xs font-bold rounded"
              @click="submitDelivery"
            >
              Valider la remise & Clôturer
            </button>
          </div>
        </div>
      </div>
    </div>
  </AgentShell>
</template>
