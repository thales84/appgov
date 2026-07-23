<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FormField from '@/Components/FormField.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const { locale, t } = useI18n();
const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
    locale: locale.value,
});

function fieldError(field, messageKey = 'validation.required') {
    return form.errors[field] ? t(messageKey) : '';
}

function submit() {
    form.locale = locale.value;
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <Head :title="t('meta.registerTitle')" />

    <AuthLayout
        :eyebrow="t('auth.register.eyebrow')"
        :title="t('auth.register.title')"
        :description="t('auth.register.description')"
    >
        <form class="space-y-5" @submit.prevent="submit">
            <div class="grid gap-5 sm:grid-cols-2">
                <FormField id="first-name" :label="t('fields.firstName')" :error="fieldError('first_name')" required>
                    <template #default="{ describedBy }">
                        <input id="first-name" v-model="form.first_name" type="text" autocomplete="given-name" class="form-input" :aria-describedby="describedBy" required autofocus>
                    </template>
                </FormField>
                <FormField id="last-name" :label="t('fields.lastName')" :error="fieldError('last_name')" required>
                    <template #default="{ describedBy }">
                        <input id="last-name" v-model="form.last_name" type="text" autocomplete="family-name" class="form-input" :aria-describedby="describedBy" required>
                    </template>
                </FormField>
            </div>

            <FormField id="email" :label="t('fields.email')" :error="fieldError('email', 'validation.email')" required>
                <template #default="{ describedBy }">
                    <input id="email" v-model="form.email" type="email" autocomplete="email" class="form-input" :aria-describedby="describedBy" required>
                </template>
            </FormField>

            <FormField id="password" :label="t('fields.password')" :hint="t('auth.register.passwordHint')" :error="fieldError('password', 'validation.strongPassword')" required>
                <template #default="{ describedBy }">
                    <input id="password" v-model="form.password" type="password" autocomplete="new-password" class="form-input" :aria-describedby="describedBy" required>
                </template>
            </FormField>

            <FormField id="password-confirmation" :label="t('fields.passwordConfirmation')" :error="fieldError('password_confirmation', 'validation.passwordConfirmation')" required>
                <template #default="{ describedBy }">
                    <input id="password-confirmation" v-model="form.password_confirmation" type="password" autocomplete="new-password" class="form-input" :aria-describedby="describedBy" required>
                </template>
            </FormField>

            <div>
                <label class="flex cursor-pointer items-start gap-3 text-sm leading-6 text-slate-700">
                    <input v-model="form.terms" type="checkbox" class="mt-1 size-4 rounded border-slate-300 text-brand-blue-600 focus:ring-brand-blue-600" required>
                    <span>{{ t('auth.register.terms') }}</span>
                </label>
                <p v-if="form.errors.terms" class="mt-2 text-sm font-semibold text-brand-red-700">{{ t('validation.terms') }}</p>
            </div>

            <button type="submit" class="button-primary w-full" :disabled="form.processing">
                {{ form.processing ? t('common.processing') : t('auth.register.submit') }}
            </button>
        </form>

        <p class="mt-6 border-t border-slate-200 pt-5 text-center text-sm text-slate-600">
            {{ t('auth.register.hasAccount') }}
            <Link href="/login" class="rounded-sm font-semibold text-brand-blue-700 hover:underline">{{ t('auth.register.login') }}</Link>
        </p>
    </AuthLayout>
</template>
