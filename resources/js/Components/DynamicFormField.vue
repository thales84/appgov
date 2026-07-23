<script setup>
import { computed } from 'vue';

const props = defineProps({
  field: {
    type: Object,
    required: true,
  },
  modelValue: {
    type: [String, Number, Boolean, Array, Object],
    default: '',
  },
  error: {
    type: String,
    default: '',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue']);

const options = computed(() => {
  return props.field.configuration?.options || [];
});

const updateValue = (e) => {
  const val = props.field.fieldType === 'boolean' ? e.target.checked : e.target.value;
  emit('update:modelValue', val);
};
</script>

<template>
  <div class="space-y-1.5">
    <label
      :for="`field-${field.code}`"
      class="block text-sm font-semibold text-slate-800"
    >
      {{ field.label?.fr || field.label }}
      <span v-if="field.isRequired" class="text-brand-red-700 font-bold ml-0.5">*</span>
    </label>

    <p v-if="field.help?.fr" class="text-xs text-slate-500">
      {{ field.help.fr }}
    </p>

    <!-- Text / Email / Phone / Number / Date -->
    <div v-if="['text', 'email', 'phone', 'number', 'integer', 'date'].includes(field.fieldType)">
      <input
        :id="`field-${field.code}`"
        :type="field.fieldType === 'date' ? 'date' : (field.fieldType === 'email' ? 'email' : (['number', 'integer'].includes(field.fieldType) ? 'number' : 'text'))"
        :value="modelValue"
        :disabled="disabled"
        class="w-full rounded-md border-slate-300 shadow-sm focus:border-brand-blue-600 focus:ring-brand-blue-600 text-sm disabled:bg-slate-100"
        @input="updateValue"
      >
    </div>

    <!-- Textarea -->
    <div v-else-if="field.fieldType === 'textarea'">
      <textarea
        :id="`field-${field.code}`"
        :value="modelValue"
        :disabled="disabled"
        rows="3"
        class="w-full rounded-md border-slate-300 shadow-sm focus:border-brand-blue-600 focus:ring-brand-blue-600 text-sm disabled:bg-slate-100"
        @input="updateValue"
      />
    </div>

    <!-- Select -->
    <div v-else-if="field.fieldType === 'select'">
      <select
        :id="`field-${field.code}`"
        :value="modelValue"
        :disabled="disabled"
        class="w-full rounded-md border-slate-300 shadow-sm focus:border-brand-blue-600 focus:ring-brand-blue-600 text-sm disabled:bg-slate-100"
        @change="updateValue"
      >
        <option value="">-- Sélectionner --</option>
        <option
          v-for="opt in options"
          :key="opt.value"
          :value="opt.value"
        >
          {{ opt.label?.fr || opt.label || opt.value }}
        </option>
      </select>
    </div>

    <!-- Boolean / Checkbox -->
    <div v-else-if="field.fieldType === 'boolean'" class="flex items-center gap-2 pt-1">
      <input
        :id="`field-${field.code}`"
        type="checkbox"
        :checked="Boolean(modelValue)"
        :disabled="disabled"
        class="rounded border-slate-300 text-brand-blue-600 focus:ring-brand-blue-600 h-4 w-4"
        @change="updateValue"
      >
      <span class="text-sm text-slate-700">Oui</span>
    </div>

    <!-- Error message -->
    <p v-if="error" class="text-xs text-brand-red-700 font-medium mt-1">
      {{ error }}
    </p>
  </div>
</template>
