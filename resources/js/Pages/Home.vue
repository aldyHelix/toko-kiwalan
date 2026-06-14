<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineProps<{ appName: string }>();
</script>

<template>
    <Head title="Beranda" />

    <main class="flex min-h-screen flex-col items-center justify-center gap-4 p-8 text-center">
        <h1 class="text-4xl font-bold tracking-tight">{{ appName }}</h1>
        <p class="max-w-md text-gray-600">
            Boilerplate e-commerce dengan 3D product viewer + AR. Scaffold Fase 0 aktif.
        </p>
        <span class="rounded-full bg-indigo-50 px-3 py-1 text-sm font-medium text-indigo-700">
            Inertia 2 · Vue 3 · TypeScript · Tailwind 4
        </span>
    </main>
</template>
