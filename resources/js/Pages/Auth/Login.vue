<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { LockClosedIcon } from '@heroicons/vue/20/solid';
import { useI18n } from 'vue-i18n';
import FormField from '@/Components/FormField.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    portal: {
        type: String,
        default: 'citizen',
    },
});

const { t } = useI18n();
const page = usePage();
const isAgent = computed(() => props.portal === 'agent');
const form = useForm({
    email: '',
    password: '',
    remember: false,
    portal: props.portal,
});

function submit() {
    form.post(isAgent.value ? '/agent/login' : '/login', {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <Head :title="t(isAgent ? 'meta.agentLoginTitle' : 'meta.loginTitle')" />

    <AuthLayout
        :portal="portal"
        :eyebrow="t(isAgent ? 'auth.login.agentEyebrow' : 'auth.login.eyebrow')"
        :title="t(isAgent ? 'auth.login.agentTitle' : 'auth.login.title')"
        :description="t(isAgent ? 'auth.login.agentDescription' : 'auth.login.description')"
    >
        <div v-if="page.props.flash.status" class="mb-6 border-l-4 border-green-700 bg-green-50 p-4 text-sm font-semibold text-green-800" role="status">
            {{ t('auth.login.passwordResetSuccess') }}
        </div>

        <form class="space-y-5" @submit.prevent="submit">
            <FormField id="email" :label="t('fields.email')" :error="form.errors.email ? t('auth.login.invalidCredentials') : ''" required>
                <template #default="{ describedBy }">
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        class="form-input"
                        :aria-describedby="describedBy"
                        :aria-invalid="Boolean(form.errors.email)"
                        required
                        autofocus
                    >
                </template>
            </FormField>

            <FormField id="password" :label="t('fields.password')" :error="form.errors.password ? t('validation.password') : ''" required>
                <template #default="{ describedBy }">
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        class="form-input"
                        :aria-describedby="describedBy"
                        :aria-invalid="Boolean(form.errors.password)"
                        required
                    >
                </template>
            </FormField>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <label class="flex min-h-11 cursor-pointer items-center gap-2 text-sm text-slate-700">
                    <input v-model="form.remember" type="checkbox" class="size-4 rounded border-slate-300 text-brand-blue-600 focus:ring-brand-blue-600">
                    {{ t('auth.login.remember') }}
                </label>
                <Link v-if="!isAgent" href="/forgot-password" class="rounded-sm text-sm font-semibold text-brand-blue-700 hover:underline">
                    {{ t('auth.login.forgot') }}
                </Link>
            </div>

            <button type="submit" class="button-primary w-full" :disabled="form.processing">
                <LockClosedIcon class="size-5" aria-hidden="true" />
                {{ form.processing ? t('common.processing') : t('auth.login.submit') }}
            </button>
        </form>

        <p v-if="!isAgent" class="mt-6 border-t border-slate-200 pt-5 text-center text-sm text-slate-600">
            {{ t('auth.login.noAccount') }}
            <Link href="/register" class="rounded-sm font-semibold text-brand-blue-700 hover:underline">{{ t('auth.login.register') }}</Link>
        </p>
        <p v-else class="mt-6 border-t border-slate-200 pt-5 text-sm leading-6 text-slate-600">
            {{ t('auth.login.agentHelp') }}
        </p>
    </AuthLayout>
</template>
