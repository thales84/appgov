<script setup>
import { useI18n } from 'vue-i18n';

defineProps({
    inverted: {
        type: Boolean,
        default: false,
    },
});

const { locale, t } = useI18n();

function setLocale(value) {
    locale.value = value;
    document.documentElement.lang = value;
    window.localStorage.setItem('appgov-locale', value);
}
</script>

<template>
    <div
        class="flex rounded-md border p-0.5"
        :class="inverted ? 'border-white/25' : 'border-slate-300'"
        :aria-label="t('common.changeLanguage')"
    >
        <button
            v-for="language in ['fr', 'en']"
            :key="language"
            type="button"
            class="min-h-10 min-w-10 rounded px-2 text-xs font-semibold uppercase"
            :class="[
                locale === language
                    ? inverted
                        ? 'bg-white text-brand-blue-900'
                        : 'bg-brand-blue-900 text-white'
                    : inverted
                        ? 'text-blue-100 hover:bg-white/10'
                        : 'text-slate-600 hover:bg-slate-100',
            ]"
            :aria-pressed="locale === language"
            @click="setLocale(language)"
        >
            {{ language }}
        </button>
    </div>
</template>
