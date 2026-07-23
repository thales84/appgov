<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  applicationPublicId: {
    type: String,
    required: true,
  },
  requirement: {
    type: Object,
    required: true,
  },
  existingDoc: {
    type: Object,
    default: null,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const fileInput = ref(null);

const form = useForm({
  file: null,
});

const handleFileChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;

  form.file = file;
  form.post(route('account.applications.documents.store', [props.applicationPublicId, props.requirement.publicId]), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
      if (fileInput.value) fileInput.value.value = '';
    },
  });
};
</script>

<template>
  <div class="border border-slate-200 rounded-lg p-4 bg-slate-50/50 space-y-3">
    <div class="flex items-start justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <span class="text-sm font-semibold text-slate-900">
            {{ requirement.name?.fr || requirement.name }}
          </span>
          <span
            v-if="requirement.isRequired"
            class="text-xs font-bold px-2 py-0.5 rounded bg-amber-100 text-amber-800"
          >
            Obligatoire
          </span>
          <span
            v-else
            class="text-xs font-normal px-2 py-0.5 rounded bg-slate-100 text-slate-600"
          >
            Optionnel
          </span>
        </div>
        <p v-if="requirement.description?.fr" class="text-xs text-slate-500 mt-1">
          {{ requirement.description.fr }}
        </p>
        <p class="text-[11px] text-slate-400 mt-0.5">
          Formats autorisés : PDF, JPEG, PNG (max {{ requirement.maxFileSizeKb ? (requirement.maxFileSizeKb / 1024).toFixed(0) : 10 }} Mo)
        </p>
      </div>

      <!-- Document Status Badge -->
      <div v-if="existingDoc" class="shrink-0">
        <span
          class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full"
          :class="{
            'bg-emerald-100 text-emerald-800': existingDoc.status === 'valid',
            'bg-blue-100 text-blue-800': existingDoc.status === 'pending',
            'bg-red-100 text-red-800': ['invalid', 'quarantined'].includes(existingDoc.status),
          }"
        >
          ✓ {{ existingDoc.statusLabel || existingDoc.status }}
        </span>
      </div>
    </div>

    <!-- Existing File Details -->
    <div v-if="existingDoc" class="flex items-center justify-between bg-white border border-slate-200 rounded p-2.5 text-xs text-slate-700">
      <div class="truncate mr-2">
        <span class="font-medium text-slate-900">{{ existingDoc.originalFilename }}</span>
        <span class="text-slate-400 ml-2">({{ (existingDoc.fileSizeBytes / 1024).toFixed(0) }} Ko)</span>
      </div>
      <a
        :href="existingDoc.downloadUrl"
        target="_blank"
        class="text-brand-blue-600 hover:text-brand-blue-900 font-semibold shrink-0"
      >
        Télécharger
      </a>
    </div>

    <!-- File Input -->
    <div v-if="!disabled">
      <input
        ref="fileInput"
        type="file"
        accept=".pdf,.jpg,.jpeg,.png"
        class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand-blue-600 file:text-white hover:file:bg-brand-blue-900 cursor-pointer"
        :disabled="form.processing"
        @change="handleFileChange"
      >
      <div v-if="form.progress" class="w-full bg-slate-200 rounded-full h-1.5 mt-2">
        <div class="bg-brand-blue-600 h-1.5 rounded-full" :style="{ width: `${form.progress.percentage}%` }" />
      </div>
      <p v-if="form.errors.file" class="text-xs text-brand-red-700 font-semibold mt-1">
        {{ form.errors.file }}
      </p>
    </div>
  </div>
</template>
