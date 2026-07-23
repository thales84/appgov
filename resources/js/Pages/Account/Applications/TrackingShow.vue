<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircleIcon, ClockIcon, ArrowLeftIcon, DocumentCheckIcon } from '@heroicons/vue/24/outline';
import AppShell from '@/Layouts/AppShell.vue';

defineProps({
  application: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <Head :title="`Suivi du Dossier ${application.reference}`" />

  <AppShell :title="`Suivi de la Demande`" eyebrow="Espace Usager — Parcours & Titre Officiel">
    <div class="max-w-4xl mx-auto space-y-6">
      <Link
        :href="route('account.applications.show', application.publicId)"
        class="text-xs font-semibold text-slate-500 hover:text-slate-700 flex items-center gap-1"
      >
        <ArrowLeftIcon class="w-4 h-4" /> Retour au détail {{ application.reference }}
      </Link>

      <!-- Issued Document Info Card if produced -->
      <div v-if="application.issuedDocument" class="bg-brand-blue-900 text-white rounded-lg p-6 shadow-md space-y-3">
        <div class="flex items-center justify-between border-b border-brand-blue-700 pb-3">
          <div class="flex items-center gap-3">
            <DocumentCheckIcon class="w-8 h-8 text-brand-blue-200" />
            <div>
              <p class="text-xs font-mono uppercase text-brand-blue-200">Acte / Titre Officiel Émis</p>
              <h2 class="text-lg font-bold font-mono tracking-wider">{{ application.issuedDocument.documentNumber }}</h2>
            </div>
          </div>
          <span class="px-3 py-1 bg-brand-blue-800 rounded-full text-xs font-semibold">
            {{ application.delivery?.statusLabel ?? 'Disponible au guichet' }}
          </span>
        </div>

        <p class="text-xs text-brand-blue-100">
          Document : <strong>{{ application.issuedDocument.documentType }}</strong> | Émis le {{ new Date(application.issuedDocument.issuedAt).toLocaleDateString('fr-FR') }}
        </p>
      </div>

      <!-- Events Timeline -->
      <div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm space-y-6">
        <h3 class="font-display text-base font-bold text-slate-900">Historique d'avancement du dossier</h3>

        <div class="relative border-l-2 border-slate-200 ml-4 space-y-6">
          <div v-for="evt in application.events" :key="evt.publicId" class="relative pl-6">
            <div class="absolute -left-[9px] top-0 bg-white p-0.5 rounded-full">
              <CheckCircleIcon v-if="evt.eventType.startsWith('status.')" class="w-4 h-4 text-emerald-600" />
              <ClockIcon v-else class="w-4 h-4 text-slate-400" />
            </div>

            <div class="flex items-center justify-between">
              <p class="text-xs font-bold text-slate-900">{{ evt.label.fr }}</p>
              <span class="text-[11px] font-mono text-slate-400">
                {{ new Date(evt.createdAt).toLocaleString('fr-FR') }}
              </span>
            </div>
            <p class="text-[11px] font-mono text-slate-500 mt-0.5">{{ evt.eventType }}</p>
          </div>
        </div>
      </div>
    </div>
  </AppShell>
</template>
