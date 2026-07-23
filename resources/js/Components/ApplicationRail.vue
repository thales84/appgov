<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: {
    type: String,
    required: true,
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
  delivered: { label: 'Remis', stepIndex: 5 },
};

const currentInfo = computed(() => statusMap[props.status] || { label: props.status, stepIndex: 1 });
</script>

<template>
  <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm">
    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-4">
      Rail de progression du dossier
    </h3>

    <div class="space-y-4">
      <div
        v-for="(step, index) in steps"
        :key="step.publicId || index"
        class="flex items-start gap-3 relative"
      >
        <!-- Connector line -->
        <div
          v-if="index < steps.length - 1"
          class="absolute left-[11px] top-6 bottom-0 w-[2px]"
          :class="index < currentInfo.stepIndex ? 'bg-brand-blue-600' : 'bg-slate-200'"
        />

        <!-- Step dot -->
        <div class="relative z-10 flex items-center justify-center w-6 h-6 rounded-full shrink-0 mt-0.5">
          <span
            v-if="index < currentInfo.stepIndex"
            class="w-5 h-5 rounded-full bg-brand-blue-600 text-white flex items-center justify-center text-xs font-bold"
          >
            ✓
          </span>
          <span
            v-else-if="index === currentInfo.stepIndex"
            class="w-6 h-6 rounded-full border-2 border-brand-blue-600 bg-white flex items-center justify-center"
          >
            <span class="w-2.5 h-2.5 rounded-full bg-brand-blue-600 animate-pulse" />
          </span>
          <span
            v-else
            class="w-5 h-5 rounded-full border border-slate-300 bg-slate-100"
          />
        </div>

        <!-- Step details -->
        <div class="flex-1 min-w-0 pt-0.5">
          <div
            class="text-sm font-semibold"
            :class="index === currentInfo.stepIndex ? 'text-brand-blue-900 font-bold' : (index < currentInfo.stepIndex ? 'text-slate-800' : 'text-slate-400')"
          >
            {{ step.name?.fr || step.name }}
          </div>
          <p
            v-if="step.description?.fr"
            class="text-xs text-slate-500 mt-0.5"
          >
            {{ step.description.fr }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
