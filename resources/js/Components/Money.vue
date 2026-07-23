<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    amountMinor: {
        type: Number,
        required: true,
    },
    currency: {
        type: String,
        required: true,
    },
    minorUnitExponent: {
        type: Number,
        default: 0,
    },
});

const { locale } = useI18n();

const formatted = computed(() => new Intl.NumberFormat(
    locale.value === 'fr' ? 'fr-CM' : 'en-CM',
    {
        style: 'currency',
        currency: props.currency,
        currencyDisplay: 'code',
        minimumFractionDigits: props.minorUnitExponent,
        maximumFractionDigits: props.minorUnitExponent,
    },
).format(props.amountMinor / (10 ** props.minorUnitExponent)));
</script>

<template>
    <span class="font-mono font-medium tabular-nums">{{ formatted }}</span>
</template>
