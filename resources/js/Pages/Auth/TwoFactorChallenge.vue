<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FormField from '@/Components/FormField.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';

defineProps({
    portal: {
        type: String,
        default: 'agent',
    },
});

const { t } = useI18n();
const useRecoveryCode = ref(false);
const form = useForm({
    code: '',
    recovery_code: '',
});

function toggleMode() {
    useRecoveryCode.value = !useRecoveryCode.value;
    form.clearErrors();
    form.reset();
}

function submit() {
    form.post('/two-factor-challenge', {
        onFinish: () => form.reset(),
    });
}
</script>

<template>
    <Head :title="t('meta.twoFactorTitle')" />
    <AuthLayout
        :portal="portal"
        :eyebrow="t('auth.twoFactor.eyebrow')"
        :title="t('auth.twoFactor.title')"
        :description="t(useRecoveryCode ? 'auth.twoFactor.recoveryDescription' : 'auth.twoFactor.description')"
    >
        <form class="space-y-5" @submit.prevent="submit">
            <FormField
                v-if="!useRecoveryCode"
                id="code"
                :label="t('fields.authenticationCode')"
                :hint="t('auth.twoFactor.codeHint')"
                :error="form.errors.code ? t('auth.twoFactor.invalid') : ''"
                required
            >
                <template #default="{ describedBy }">
                    <input id="code" v-model="form.code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" class="form-input font-mono tracking-[0.28em]" :aria-describedby="describedBy" required autofocus>
                </template>
            </FormField>
            <FormField
                v-else
                id="recovery-code"
                :label="t('fields.recoveryCode')"
                :error="form.errors.recovery_code ? t('auth.twoFactor.invalidRecovery') : ''"
                required
            >
                <template #default="{ describedBy }">
                    <input id="recovery-code" v-model="form.recovery_code" type="text" autocomplete="one-time-code" class="form-input font-mono" :aria-describedby="describedBy" required autofocus>
                </template>
            </FormField>
            <button type="submit" class="button-primary w-full" :disabled="form.processing">
                {{ form.processing ? t('common.processing') : t('auth.twoFactor.submit') }}
            </button>
        </form>
        <button type="button" class="mt-5 min-h-11 w-full rounded-sm text-sm font-semibold text-brand-blue-700 hover:underline" @click="toggleMode">
            {{ t(useRecoveryCode ? 'auth.twoFactor.useCode' : 'auth.twoFactor.useRecovery') }}
        </button>
    </AuthLayout>
</template>
