<script setup>
import { computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FormField from '@/Components/FormField.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import AppShell from '@/Layouts/AppShell.vue';

const props = defineProps({
    profile: {
        type: Object,
        required: true,
    },
});

const { t } = useI18n();
const page = usePage();
const saved = computed(() => page.props.flash.status === 'citizen-profile-updated');
const form = useForm({
    first_name: props.profile.firstName,
    last_name: props.profile.lastName,
    email: props.profile.email,
    phone: props.profile.phone || '',
    preferred_locale: props.profile.preferredLocale,
});

function submit() {
    form.put('/account/profile', { preserveScroll: true });
}
</script>

<template>
    <Head :title="t('meta.profileTitle')" />
    <AppShell :title="t('account.profile.title')" :eyebrow="t('account.profile.eyebrow')">
        <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
            <section class="border border-slate-200 bg-white p-6 sm:p-8">
                <div v-if="saved" class="mb-6 border-l-4 border-green-700 bg-green-50 p-4 text-sm font-semibold text-green-800" role="status">
                    {{ t('account.profile.saved') }}
                </div>
                <form class="space-y-6" @submit.prevent="submit">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <FormField id="first-name" :label="t('fields.firstName')" :error="form.errors.first_name ? t('validation.required') : ''" required>
                            <template #default="{ describedBy }">
                                <input id="first-name" v-model="form.first_name" type="text" autocomplete="given-name" class="form-input" :aria-describedby="describedBy" required>
                            </template>
                        </FormField>
                        <FormField id="last-name" :label="t('fields.lastName')" :error="form.errors.last_name ? t('validation.required') : ''" required>
                            <template #default="{ describedBy }">
                                <input id="last-name" v-model="form.last_name" type="text" autocomplete="family-name" class="form-input" :aria-describedby="describedBy" required>
                            </template>
                        </FormField>
                    </div>
                    <FormField id="email" :label="t('fields.email')" :hint="t('account.profile.emailHint')" :error="form.errors.email ? t('validation.email') : ''" required>
                        <template #default="{ describedBy }">
                            <input id="email" v-model="form.email" type="email" autocomplete="email" class="form-input" :aria-describedby="describedBy" required>
                        </template>
                    </FormField>
                    <FormField id="phone" :label="t('fields.phone')" :hint="t('account.profile.phoneHint')" :error="form.errors.phone ? t('validation.phone') : ''">
                        <template #default="{ describedBy }">
                            <input id="phone" v-model="form.phone" type="tel" autocomplete="tel" class="form-input" :aria-describedby="describedBy">
                        </template>
                    </FormField>
                    <FormField id="locale" :label="t('fields.preferredLanguage')" required>
                        <template #default="{ describedBy }">
                            <select id="locale" v-model="form.preferred_locale" class="form-input" :aria-describedby="describedBy">
                                <option value="fr">{{ t('languages.fr') }}</option>
                                <option value="en">{{ t('languages.en') }}</option>
                            </select>
                        </template>
                    </FormField>
                    <button type="submit" class="button-primary" :disabled="form.processing">
                        {{ form.processing ? t('common.processing') : t('account.profile.save') }}
                    </button>
                </form>
            </section>

            <aside class="border-t-4 border-brand-red-700 bg-brand-blue-900 p-6 text-white lg:self-start">
                <p class="eyebrow text-blue-200">{{ t('identity.cardEyebrow') }}</p>
                <StatusBadge class="mt-4" tone="info">{{ t(`identity.levels.${profile.identityLevel}`) }}</StatusBadge>
                <p class="mt-4 leading-6 text-blue-100">{{ t(`identity.explanations.${profile.identityLevel}`) }}</p>
                <p class="mt-5 border-t border-white/15 pt-5 text-sm leading-6 text-blue-100">{{ t('identity.noOfficialCheck') }}</p>
            </aside>
        </div>
    </AppShell>
</template>
