<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{ title: string }>();
</script>

<template>
    <main class="flex min-h-screen flex-col items-center justify-center bg-gray-50 p-6">
        <Link href="/" class="mb-6 text-xl font-bold tracking-tight text-gray-900">
            Toko Kiwalan
        </Link>

        <div class="w-full max-w-md rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-200">
            <h1 class="mb-6 text-2xl font-semibold text-gray-900">{{ title }}</h1>
            <slot />
        </div>
    </main>
</template>
