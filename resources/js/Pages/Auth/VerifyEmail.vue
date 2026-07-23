<script setup>
import { computed } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { EnvelopeIcon } from '@heroicons/vue/24/outline';
import { useI18n } from 'vue-i18n';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const { t } = useI18n();
const page = usePage();
const sent = computed(() => page.props.flash.status === 'verification-link-sent');
const form = useForm({});

function resend() {
    form.post('/email/verification-notification');
}

function logout() {
    router.post('/logout');
}
</script>

<template>
    <Head :title="t('meta.verifyEmailTitle')" />
    <AuthLayout :eyebrow="t('auth.verify.eyebrow')" :title="t('auth.verify.title')" :description="t('auth.verify.description')">
        <div class="flex gap-4">
            <EnvelopeIcon class="mt-1 size-8 shrink-0 text-brand-blue-600" aria-hidden="true" />
            <div>
                <p class="font-semibold text-ink-900">{{ page.props.auth.user.email }}</p>
                <p class="mt-2 leading-6 text-slate-600">{{ t('auth.verify.instructions') }}</p>
            </div>
        </div>
        <div v-if="sent" class="mt-6 border-l-4 border-green-700 bg-green-50 p-4 text-sm font-semibold text-green-800" role="status">
            {{ t('auth.verify.sent') }}
        </div>
        <div class="mt-7 flex flex-col gap-3 sm:flex-row">
            <button type="button" class="button-primary" :disabled="form.processing" @click="resend">
                {{ form.processing ? t('common.processing') : t('auth.verify.resend') }}
            </button>
            <button type="button" class="button-secondary" @click="logout">{{ t('auth.logout') }}</button>
        </div>
    </AuthLayout>
</template>
