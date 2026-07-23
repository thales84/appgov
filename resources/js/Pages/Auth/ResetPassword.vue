<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FormField from '@/Components/FormField.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    email: {
        type: String,
        default: '',
    },
    token: {
        type: String,
        required: true,
    },
});

const { t } = useI18n();
const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/reset-password', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <Head :title="t('meta.resetPasswordTitle')" />
    <AuthLayout :eyebrow="t('auth.reset.eyebrow')" :title="t('auth.reset.title')" :description="t('auth.reset.description')">
        <form class="space-y-5" @submit.prevent="submit">
            <FormField id="email" :label="t('fields.email')" :error="form.errors.email ? t('validation.email') : ''" required>
                <template #default="{ describedBy }">
                    <input id="email" v-model="form.email" type="email" autocomplete="email" class="form-input" :aria-describedby="describedBy" required>
                </template>
            </FormField>
            <FormField id="password" :label="t('fields.password')" :hint="t('auth.register.passwordHint')" :error="form.errors.password ? t('validation.strongPassword') : ''" required>
                <template #default="{ describedBy }">
                    <input id="password" v-model="form.password" type="password" autocomplete="new-password" class="form-input" :aria-describedby="describedBy" required>
                </template>
            </FormField>
            <FormField id="password-confirmation" :label="t('fields.passwordConfirmation')" :error="form.errors.password_confirmation ? t('validation.passwordConfirmation') : ''" required>
                <template #default="{ describedBy }">
                    <input id="password-confirmation" v-model="form.password_confirmation" type="password" autocomplete="new-password" class="form-input" :aria-describedby="describedBy" required>
                </template>
            </FormField>
            <button type="submit" class="button-primary w-full" :disabled="form.processing">
                {{ form.processing ? t('common.processing') : t('auth.reset.submit') }}
            </button>
        </form>
    </AuthLayout>
</template>
