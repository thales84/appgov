<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import FormField from '@/Components/FormField.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const { t } = useI18n();
const page = usePage();
const sent = computed(() => Boolean(page.props.flash.status));
const form = useForm({ email: '' });

function submit() {
    form.post('/forgot-password');
}
</script>

<template>
    <Head :title="t('meta.forgotPasswordTitle')" />
    <AuthLayout :eyebrow="t('auth.forgot.eyebrow')" :title="t('auth.forgot.title')" :description="t('auth.forgot.description')">
        <div v-if="sent" class="mb-6 border-l-4 border-green-700 bg-green-50 p-4 text-sm font-semibold leading-6 text-green-800" role="status">
            {{ t('auth.forgot.sent') }}
        </div>
        <form class="space-y-5" @submit.prevent="submit">
            <FormField id="email" :label="t('fields.email')" :error="form.errors.email ? t('validation.email') : ''" required>
                <template #default="{ describedBy }">
                    <input id="email" v-model="form.email" type="email" autocomplete="email" class="form-input" :aria-describedby="describedBy" required autofocus>
                </template>
            </FormField>
            <button type="submit" class="button-primary w-full" :disabled="form.processing">
                {{ form.processing ? t('common.processing') : t('auth.forgot.submit') }}
            </button>
        </form>
        <p class="mt-6 border-t border-slate-200 pt-5 text-center text-sm">
            <Link href="/login" class="rounded-sm font-semibold text-brand-blue-700 hover:underline">{{ t('auth.backToLogin') }}</Link>
        </p>
    </AuthLayout>
</template>
