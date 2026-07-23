<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { CreditCardIcon, CheckCircleIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';
import AppShell from '@/Layouts/AppShell.vue';

const props = defineProps({
  application: {
    type: Object,
    required: true,
  },
  invoice: {
    type: Object,
    required: true,
  },
});

const selectedProvider = ref('local_mock');

const payForm = useForm({
  provider: 'local_mock',
});

const handlePayment = () => {
  payForm.provider = selectedProvider.value;
  payForm.post(route('account.applications.payments.initiate', props.application.publicId));
};
</script>

<template>
  <Head :title="`Facture ${invoice.invoiceNumber}`" />

  <AppShell :title="`Facture ${invoice.invoiceNumber}`" eyebrow="Espace Usager — Réglement des frais">
    <div class="max-w-3xl mx-auto space-y-6">
      <div class="flex items-center justify-between">
        <Link
          :href="route('account.applications.show', application.publicId)"
          class="text-xs font-semibold text-slate-500 hover:text-slate-700 flex items-center gap-1"
        >
          <ArrowLeftIcon class="w-4 h-4" /> Retour au dossier {{ application.reference }}
        </Link>

        <span
          class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider"
          :class="invoice.status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
        >
          {{ invoice.statusLabel }}
        </span>
      </div>

      <!-- Invoice Card -->
      <div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm space-y-6">
        <div class="border-b pb-4 flex items-start justify-between">
          <div>
            <h2 class="font-display text-xl font-bold text-brand-blue-900">RÉPUBLIQUE DU CAMEROUN</h2>
            <p class="text-xs text-slate-500">Trésor Public — Démarche administrative</p>
            <p class="text-sm font-semibold text-slate-800 mt-2">{{ application.procedureTitle.fr }}</p>
          </div>

          <div class="text-right">
            <p class="text-xs font-mono text-slate-400">N° Facture</p>
            <p class="font-mono text-lg font-bold text-slate-900">{{ invoice.invoiceNumber }}</p>
          </div>
        </div>

        <!-- Line items table -->
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-slate-50 text-slate-500 font-semibold border-b">
              <th class="py-2.5 px-3">Désignation</th>
              <th class="py-2.5 px-3 text-right">Qté</th>
              <th class="py-2.5 px-3 text-right">Montant ({{ invoice.currency }})</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            <tr v-for="line in invoice.lines" :key="line.publicId">
              <td class="py-3 px-3 font-medium text-slate-800">{{ line.label.fr }}</td>
              <td class="py-3 px-3 text-right text-slate-600">{{ line.quantity }}</td>
              <td class="py-3 px-3 text-right font-mono font-semibold text-slate-900">
                {{ (line.amountMinor).toLocaleString('fr-FR') }}
              </td>
            </tr>
          </tbody>
          <tfoot>
            <tr class="border-t-2 border-brand-blue-900 text-sm font-bold text-brand-blue-900">
              <td colspan="2" class="py-3 px-3">Total à régler</td>
              <td class="py-3 px-3 text-right font-mono">
                {{ (invoice.totalAmountMinor).toLocaleString('fr-FR') }} {{ invoice.currency }}
              </td>
            </tr>
          </tfoot>
        </table>

        <!-- Paid Confirmation & PDF Receipt link -->
        <div v-if="invoice.status === 'paid'" class="bg-emerald-50 border border-emerald-200 rounded-lg p-5 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <CheckCircleIcon class="w-8 h-8 text-emerald-600 shrink-0" />
            <div>
              <p class="text-sm font-bold text-emerald-900">Facture entièrement réglée</p>
              <p class="text-xs text-emerald-700 mt-0.5">
                Réf quittance : <strong>{{ invoice.payment?.paymentReference }}</strong>
              </p>
            </div>
          </div>

          <a
            v-if="invoice.payment"
            :href="route('account.applications.payments.receipt.download', [application.publicId, invoice.payment.publicId])"
            target="_blank"
            class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs rounded transition shrink-0"
          >
            Télécharger la quittance PDF
          </a>
        </div>

        <!-- Payment provider selection if Unpaid -->
        <div v-else class="pt-4 border-t space-y-4">
          <h4 class="text-xs font-semibold text-slate-700 uppercase tracking-wider">Sélectionner un moyen de paiement</h4>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <label
              class="border rounded-lg p-3.5 flex items-center gap-3 cursor-pointer transition"
              :class="selectedProvider === 'local_mock' ? 'border-brand-blue-600 bg-brand-blue-50/50' : 'border-slate-200 bg-white'"
            >
              <input v-model="selectedProvider" type="radio" value="local_mock" class="text-brand-blue-600 focus:ring-brand-blue-600">
              <div>
                <p class="text-xs font-bold text-slate-900">Paiement de démonstration</p>
                <p class="text-[11px] text-slate-500">Validation instantanée locale</p>
              </div>
            </label>

            <label
              class="border rounded-lg p-3.5 flex items-center gap-3 cursor-pointer transition opacity-60"
              :class="selectedProvider === 'mtn_momo' ? 'border-brand-blue-600 bg-brand-blue-50/50' : 'border-slate-200 bg-white'"
            >
              <input v-model="selectedProvider" type="radio" value="mtn_momo" class="text-brand-blue-600 focus:ring-brand-blue-600">
              <div>
                <p class="text-xs font-bold text-slate-900">MTN Mobile Money</p>
                <p class="text-[11px] text-slate-500">MoMo Cameroun</p>
              </div>
            </label>
          </div>

          <div class="flex justify-end pt-2">
            <button
              type="button"
              :disabled="payForm.processing"
              class="px-6 py-3 bg-brand-blue-600 hover:bg-brand-blue-900 text-white font-bold rounded text-xs transition shadow flex items-center gap-2"
              @click="handlePayment"
            >
              <CreditCardIcon class="w-5 h-5" />
              Procéder au règlement ({{ (invoice.totalAmountMinor).toLocaleString('fr-FR') }} {{ invoice.currency }})
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppShell>
</template>
