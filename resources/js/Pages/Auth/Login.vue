<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = (): void => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk" />

    <GuestLayout title="Masuk">
        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-gray-700"
                    >Email</label
                >
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    autocomplete="username"
                    required
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                />
                <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                    {{ form.errors.email }}
                </p>
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-gray-700">
                    Kata Sandi
                </label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    autocomplete="current-password"
                    required
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                />
                <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                    {{ form.errors.password }}
                </p>
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input
                    v-model="form.remember"
                    type="checkbox"
                    class="rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                />
                Ingat saya
            </label>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full rounded-lg bg-amber-500 px-4 py-2.5 font-medium text-white transition hover:bg-amber-600 disabled:opacity-50"
            >
                Masuk
            </button>

            <p class="text-center text-sm text-gray-600">
                Belum punya akun?
                <Link href="/register" class="font-medium text-amber-600 hover:underline"
                    >Daftar</Link
                >
            </p>
        </form>
    </GuestLayout>
</template>
