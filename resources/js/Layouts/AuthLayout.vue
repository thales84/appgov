<script setup>
import { Link } from '@inertiajs/vue3';
import { CheckCircleIcon, ShieldCheckIcon } from '@heroicons/vue/24/outline';
import { useI18n } from 'vue-i18n';
import BrandMark from '@/Components/BrandMark.vue';
import LanguageSwitch from '@/Components/LanguageSwitch.vue';

defineProps({
    portal: {
        type: String,
        default: 'citizen',
    },
    eyebrow: {
        type: String,
        required: true,
    },
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        required: true,
    },
});

const { t } = useI18n();
</script>

<template>
    <div class="min-h-screen bg-cloud-50 lg:grid lg:grid-cols-[minmax(320px,0.82fr)_minmax(520px,1.18fr)]">
        <a
            href="#main-content"
            class="fixed left-4 top-3 z-50 -translate-y-24 rounded-md bg-white px-4 py-2 font-semibold text-brand-blue-900 shadow-lg transition focus:translate-y-0"
        >
            {{ t('common.skipToContent') }}
        </a>

        <aside class="relative overflow-hidden bg-brand-blue-900 px-6 py-7 text-white sm:px-10 lg:flex lg:min-h-screen lg:flex-col lg:justify-between lg:px-12 lg:py-10">
            <div class="absolute inset-y-0 right-0 hidden w-20 border-l border-white/10 lg:block" aria-hidden="true">
                <div class="absolute inset-y-0 left-1/2 w-px bg-white/10" />
                <div class="absolute right-0 top-[18%] h-28 w-8 bg-brand-red-700" />
            </div>

            <div class="relative flex items-center justify-between gap-5 lg:pr-20">
                <Link href="/" class="rounded-md">
                    <BrandMark />
                </Link>
                <LanguageSwitch inverted />
            </div>

            <div class="relative mt-14 hidden max-w-md lg:block lg:pr-20">
                <p class="eyebrow text-blue-200">{{ t(`authLayout.${portal}.eyebrow`) }}</p>
                <p class="mt-5 font-display text-4xl font-semibold leading-tight">
                    {{ t(`authLayout.${portal}.title`) }}
                </p>
                <ul class="mt-10 space-y-5 text-blue-50">
                    <li v-for="item in 3" :key="item" class="flex gap-3">
                        <CheckCircleIcon class="mt-0.5 size-5 shrink-0 text-blue-200" aria-hidden="true" />
                        <span>{{ t(`authLayout.${portal}.points.${item - 1}`) }}</span>
                    </li>
                </ul>
            </div>

            <div class="relative mt-8 hidden items-center gap-3 text-sm text-blue-200 lg:flex lg:pr-20">
                <ShieldCheckIcon class="size-5" aria-hidden="true" />
                <span>{{ t('authLayout.secureSession') }}</span>
            </div>
        </aside>

        <main id="main-content" class="flex min-h-[calc(100vh-108px)] items-start justify-center px-4 py-10 sm:px-8 sm:py-14 lg:min-h-screen lg:items-center lg:px-14">
            <div class="w-full max-w-xl">
                <p class="eyebrow text-brand-blue-600">{{ eyebrow }}</p>
                <h1 class="mt-4 font-display text-3xl font-semibold tracking-tight text-ink-900 sm:text-4xl">{{ title }}</h1>
                <p class="mt-4 max-w-lg text-lg leading-7 text-slate-600">{{ description }}</p>
                <div class="mt-8 border-t-4 border-brand-red-700 bg-white p-6 shadow-sm sm:p-8">
                    <slot />
                </div>
            </div>
        </main>
    </div>
</template>
