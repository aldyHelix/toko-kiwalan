<script setup lang="ts">
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import type { SharedProps } from '@/types';

const page = usePage<SharedProps>();

const branches = computed(() => page.props.branch.available);
const activeId = computed(() => page.props.branch.active?.id ?? null);

const select = (event: Event): void => {
    const branchId = Number((event.target as HTMLSelectElement).value);

    if (!Number.isNaN(branchId)) {
        router.post(
            '/branch/select',
            { branch_id: branchId },
            { preserveScroll: true, preserveState: true },
        );
    }
};
</script>

<template>
    <div v-if="branches.length" class="flex items-center gap-2 text-sm">
        <label for="branch-switcher" class="font-medium text-gray-600">Cabang</label>
        <select
            id="branch-switcher"
            :value="activeId ?? ''"
            class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
            @change="select"
        >
            <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                {{ branch.name }} ({{ branch.code }})
            </option>
        </select>
    </div>
</template>
