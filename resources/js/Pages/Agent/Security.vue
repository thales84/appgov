<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { CheckCircleIcon, DevicePhoneMobileIcon, KeyIcon } from '@heroicons/vue/24/outline';
import { useI18n } from 'vue-i18n';
import FormField from '@/Components/FormField.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import AgentShell from '@/Layouts/AgentShell.vue';

const props = defineProps({
    twoFactor: {
        type: Object,
        required: true,
    },
});

const { t } = useI18n();
const page = usePage();
const justConfirmed = computed(() => page.props.flash.status === 'two-factor-authentication-confirmed');
const enableForm = useForm({});
const confirmForm = useForm({ code: '' });

function enable() {
    enableForm.post('/user/two-factor-authentication', { preserveScroll: true });
}

function confirm() {
    confirmForm.post('/user/confirmed-two-factor-authentication', {
        preserveScroll: true,
        onFinish: () => confirmForm.reset(),
    });
}
</script>

<template>
    <Head :title="t('meta.agentSecurityTitle')" />
    <AgentShell :title="t('agent.security.title')" section="security">
        <div class="grid gap-6 xl:grid-cols-[1fr_360px]">
            <section class="border border-slate-200 bg-white p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-6">
                    <div>
                        <h2 class="font-display text-xl font-semibold text-ink-900">{{ t('agent.security.twoFactorTitle') }}</h2>
                        <p class="mt-2 leading-6 text-slate-600">{{ t('agent.security.twoFactorDescription') }}</p>
                    </div>
                    <StatusBadge :tone="twoFactor.confirmed ? 'success' : 'warning'">
                        {{ t(twoFactor.confirmed ? 'agent.security.active' : 'agent.security.required') }}
                    </StatusBadge>
                </div>

                <div v-if="!twoFactor.hasSecret" class="py-8">
                    <DevicePhoneMobileIcon class="size-10 text-brand-blue-600" aria-hidden="true" />
                    <h3 class="mt-5 font-display text-xl font-semibold text-ink-900">{{ t('agent.security.startTitle') }}</h3>
                    <p class="mt-2 max-w-2xl leading-6 text-slate-600">{{ t('agent.security.startDescription') }}</p>
                    <button type="button" class="button-primary mt-6" :disabled="enableForm.processing" @click="enable">
                        {{ enableForm.processing ? t('common.processing') : t('agent.security.enable') }}
                    </button>
                </div>

                <div v-else-if="!twoFactor.confirmed" class="grid gap-8 py-8 md:grid-cols-[220px_1fr]">
                    <div class="border border-slate-200 bg-white p-3">
                        <!-- SVG generated server-side by Fortify from the authenticated user's secret. -->
                        <div class="mx-auto max-w-48" v-html="twoFactor.qrCodeSvg" />
                    </div>
                    <div>
                        <p class="eyebrow text-brand-blue-600">{{ t('agent.security.step') }}</p>
                        <h3 class="mt-3 font-display text-xl font-semibold text-ink-900">{{ t('agent.security.scanTitle') }}</h3>
                        <p class="mt-2 leading-6 text-slate-600">{{ t('agent.security.scanDescription') }}</p>
                        <div class="mt-4 bg-slate-100 p-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('agent.security.manualKey') }}</p>
                            <code class="mt-1 block break-all font-mono text-sm text-ink-900">{{ twoFactor.manualKey }}</code>
                        </div>
                        <form class="mt-6 space-y-4" @submit.prevent="confirm">
                            <FormField id="code" :label="t('fields.authenticationCode')" :error="confirmForm.errors.code ? t('auth.twoFactor.invalid') : ''" required>
                                <template #default="{ describedBy }">
                                    <input id="code" v-model="confirmForm.code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" class="form-input font-mono tracking-[0.28em]" :aria-describedby="describedBy" required>
                                </template>
                            </FormField>
                            <button type="submit" class="button-primary" :disabled="confirmForm.processing">
                                {{ confirmForm.processing ? t('common.processing') : t('agent.security.confirm') }}
                            </button>
                        </form>
                    </div>
                </div>

                <div v-else class="py-8">
                    <div v-if="justConfirmed" class="mb-6 border-l-4 border-green-700 bg-green-50 p-4 text-sm font-semibold text-green-800" role="status">
                        {{ t('agent.security.confirmedMessage') }}
                    </div>
                    <div class="flex gap-4">
                        <CheckCircleIcon class="size-10 shrink-0 text-green-700" aria-hidden="true" />
                        <div>
                            <h3 class="font-display text-xl font-semibold text-ink-900">{{ t('agent.security.readyTitle') }}</h3>
                            <p class="mt-2 leading-6 text-slate-600">{{ t('agent.security.readyDescription') }}</p>
                        </div>
                    </div>
                    <Link href="/agent" class="button-primary mt-7">{{ t('agent.security.openWorkspace') }}</Link>
                </div>
            </section>

            <aside class="border-t-4 border-brand-red-700 bg-brand-blue-900 p-6 text-white xl:self-start">
                <KeyIcon class="size-7 text-blue-200" aria-hidden="true" />
                <h2 class="mt-4 font-display text-lg font-semibold">{{ t('agent.security.recoveryTitle') }}</h2>
                <p class="mt-2 text-sm leading-6 text-blue-100">{{ t('agent.security.recoveryDescription') }}</p>
                <ul v-if="twoFactor.confirmed" class="mt-5 grid gap-2 sm:grid-cols-2 xl:grid-cols-1">
                    <li v-for="code in twoFactor.recoveryCodes" :key="code" class="bg-white/10 px-3 py-2 font-mono text-sm text-white">{{ code }}</li>
                </ul>
                <p v-else class="mt-5 border-t border-white/15 pt-5 text-sm text-blue-200">{{ t('agent.security.recoveryAfter') }}</p>
            </aside>
        </div>
    </AgentShell>
</template>
