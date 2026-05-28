import { defineConfig } from 'vite';
import { fileURLToPath, URL } from 'node:url';
import laravel from 'laravel-vite-plugin';
import vuetify from 'vite-plugin-vuetify';
import vue from '@vitejs/plugin-vue';
//import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
server: {
    host: '127.0.0.1',
    port: 5173,
},
plugins: [
laravel({ input: ['resources/js/app.js'],
 refresh: true,
 }), vue({ template: {
 transformAssetUrls: {
 base: null,
 includeAbsolute: false,
 }, }, }), vuetify({ autoImport: true, // Автоматический импорт компонентов Vuetify
 }), ], resolve: {
 alias: {
 '@': fileURLToPath(new URL('resources/js', import.meta.url)),
 }, }, });