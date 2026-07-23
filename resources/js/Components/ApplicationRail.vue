<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: {
    type: String,
    default: 'under_review',
  },
  steps: {
    type: Array,
    default: () => [],
  },
  currentStepCode: {
    type: String,
    default: '',
  },
});

const statusMap = {
  draft: { label: 'Brouillon', stepIndex: 0 },
  submitted: { label: 'Dossier déposé', stepIndex: 1 },
  under_review: { label: 'En instruction', stepIndex: 2 },
  correction_requested: { label: 'Correction demandée', stepIndex: 2 },
  approved: { label: 'Approuvé', stepIndex: 3 },
  rejected: { label: 'Rejeté', stepIndex: 3 },
  in_production: { label: 'En production', stepIndex: 4 },
  delivered: { label: 'Titre délivré', stepIndex: 5 },
  closed: { label: 'Dossier clôturé', stepIndex: 5 },
};

const currentInfo = computed(() => statusMap[props.status] || { label: props.status, stepIndex: 2 });

const resolvedSteps = computed(() => {
  return props.steps.map((step, idx) => {
    // 1. Explicit status provided (e.g. from Home.vue)
    if (step.status) {
      return {
        ...step,
        label: step.label || step.name_fr || step.name?.fr || step.name,
        description: step.description || step.description_fr || step.description?.fr,
        computedStatus: step.status,
      };
    }

    // 2. Infer status from application status and index
    let computedStatus = 'upcoming';
    if (idx < currentInfo.value.stepIndex) {
      computedStatus = 'complete';
    } else if (idx === currentInfo.value.stepIndex) {
      computedStatus = 'current';
    }

    return {
      ...step,
      label: step.label || step.name_fr || step.name?.fr || step.name,
      description: step.description || step.description_fr || step.description?.fr,
      computedStatus,
    };
  });
});

const getLineClass = (index, step, nextStep) => {
  if (step.computedStatus === 'complete' && (nextStep?.computedStatus === 'complete' || nextStep?.computedStatus === 'current')) {
    return 'bg-emerald-500';
  }
  if (step.computedStatus === 'complete' || step.computedStatus === 'current') {
    return 'bg-gradient-to-b from-brand-blue-600 via-brand-blue-400 to-slate-200';
  }
  return 'bg-slate-200';
};
</script>

<template>
  <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
      <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-brand-blue-600 animate-ping" />
        Rail de progression du dossier
      </h3>
      <span class="text-xs font-medium text-slate-400">Suivi temps réel</span>
    </div>

    <div class="relative space-y-8">
      <div
        v-for="(step, index) in resolvedSteps"
        :key="step.code || step.publicId || index"
        class="relative flex items-start gap-4"
      >
        <!-- Trait vertical continu reliant chaque étape -->
        <div
          v-if="index < resolvedSteps.length - 1"
          class="absolute left-[15px] top-8 bottom-0 w-[3px] -mb-8 transition-colors duration-300"
          :class="getLineClass(index, step, resolvedSteps[index + 1])"
          aria-hidden="true"
        />

        <!-- Puce/Noeud d'étape coloré -->
        <div class="relative z-10 flex items-center justify-center shrink-0">
          <!-- Étape terminée (Vert émeraude) -->
          <div
            v-if="step.computedStatus === 'complete'"
            class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center text-sm font-bold ring-4 ring-emerald-50 shadow-sm"
          >
            <svg class="w-4 h-4 stroke-[3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
          </div>

          <!-- Étape en cours (Bleu vibrant animé) -->
          <div
            v-else-if="step.computedStatus === 'current'"
            class="w-8 h-8 rounded-full bg-brand-blue-600 text-white flex items-center justify-center text-sm font-bold ring-4 ring-brand-blue-100 shadow-md relative"
          >
            <span class="absolute inset-0 rounded-full bg-brand-blue-400 animate-ping opacity-30" />
            <span class="relative z-10">{{ index + 1 }}</span>
          </div>

          <!-- Étape à venir (Slate épuré) -->
          <div
            v-else
            class="w-8 h-8 rounded-full border-2 border-slate-300 bg-white text-slate-400 flex items-center justify-center text-xs font-semibold"
          >
            {{ index + 1 }}
          </div>
        </div>

        <!-- Contenu et texte explicatif du déroulement -->
        <div class="flex-1 min-w-0 pt-0.5">
          <div class="flex flex-wrap items-center justify-between gap-2">
            <h4
              class="text-sm font-bold leading-snug"
              :class="{
                'text-emerald-900': step.computedStatus === 'complete',
                'text-brand-blue-950 font-extrabold': step.computedStatus === 'current',
                'text-slate-500 font-medium': step.computedStatus === 'upcoming'
              }"
            >
              {{ step.label }}
            </h4>

            <!-- Badge statut d'étape -->
            <span
              v-if="step.computedStatus === 'complete'"
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500" />
              Terminée
            </span>
            <span
              v-else-if="step.computedStatus === 'current'"
              class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-brand-blue-50 text-brand-blue-700 border border-brand-blue-200 animate-pulse"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-brand-blue-600" />
              {{ step.helper || 'Étape en cours' }}
            </span>
            <span
              v-else
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-500 border border-slate-200"
            >
              Étape à venir
            </span>
          </div>

          <!-- Texte explicatif du déroulement -->
          <p
            v-if="step.description"
            class="text-xs mt-1.5 leading-relaxed"
            :class="step.computedStatus === 'upcoming' ? 'text-slate-400' : 'text-slate-600'"
          >
            {{ step.description }}
          </p>

          <!-- Horodatage / Estimation -->
          <div v-if="step.date" class="mt-2 flex items-center gap-1.5 text-[11px] font-medium text-slate-500">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ step.date }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
