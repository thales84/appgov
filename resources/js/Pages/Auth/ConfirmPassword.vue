<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FormField from '@/Components/FormField.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const { t } = useI18n();
const form = useForm({ password: '' });

function submit() {
    form.post('/user/confirm-password', {
        onFinish: () => form.reset(),
    });
}
</script>

<template>
    <Head :title="t('meta.confirmPasswordTitle')" />
    <AuthLayout :eyebrow="t('auth.confirm.eyebrow')" :title="t('auth.confirm.title')" :description="t('auth.confirm.description')">
        <form class="space-y-5" @submit.prevent="submit">
            <FormField id="password" :label="t('fields.password')" :error="form.errors.password ? t('auth.confirm.invalid') : ''" required>
                <template #default="{ describedBy }">
                    <input id="password" v-model="form.password" type="password" autocomplete="current-password" class="form-input" :aria-describedby="describedBy" required autofocus>
                </template>
            </FormField>
            <button type="submit" class="button-primary w-full" :disabled="form.processing">
                {{ form.processing ? t('common.processing') : t('auth.confirm.submit') }}
            </button>
        </form>
    </AuthLayout>
</template>
