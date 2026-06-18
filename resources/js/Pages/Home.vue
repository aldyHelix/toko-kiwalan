<script setup lang="ts">
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import BranchSwitcher from '@/Components/BranchSwitcher.vue';
import type { SharedProps } from '@/types';

defineProps<{ appName: string }>();

const page = usePage<SharedProps>();
const activeBranch = computed(() => page.props.branch.active);
</script>

<template>
    <Head title="Beranda" />

    <main
        class="relative flex min-h-screen flex-col items-center justify-center gap-4 p-8 text-center"
    >
        <div class="absolute right-4 top-4">
            <BranchSwitcher />
        </div>

        <h1 class="text-4xl font-bold tracking-tight">{{ appName }}</h1>
        <p class="max-w-md text-gray-600">Boilerplate e-commerce dengan 3D product viewer + AR.</p>
        <span class="rounded-full bg-indigo-50 px-3 py-1 text-sm font-medium text-indigo-700">
            Inertia 2 · Vue 3 · TypeScript · Tailwind 4
        </span>
        <p v-if="activeBranch" class="text-sm text-gray-500">
            Cabang aktif: <span class="font-medium">{{ activeBranch.name }}</span>
        </p>
    </main>
</template>
