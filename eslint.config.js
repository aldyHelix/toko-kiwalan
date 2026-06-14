import pluginVue from 'eslint-plugin-vue';
import { defineConfigWithVueTs, vueTsConfigs } from '@vue/eslint-config-typescript';
import skipFormatting from '@vue/eslint-config-prettier/skip-formatting';

export default defineConfigWithVueTs(
    {
        name: 'app/files-to-lint',
        files: ['**/*.{ts,mts,tsx,vue}'],
    },
    {
        name: 'app/files-to-ignore',
        ignores: [
            '**/dist/**',
            '**/public/**',
            '**/vendor/**',
            '**/node_modules/**',
            '**/bootstrap/ssr/**',
        ],
    },
    pluginVue.configs['flat/essential'],
    vueTsConfigs.recommended,
    skipFormatting,
    {
        // Inertia page components are route-named and inherently single-word
        // (Home, Cart, Checkout) — the multi-word rule does not apply to them.
        name: 'app/inertia-pages',
        files: ['resources/js/Pages/**/*.vue'],
        rules: {
            'vue/multi-word-component-names': 'off',
        },
    },
);
