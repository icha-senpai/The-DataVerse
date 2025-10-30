import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',

                // TeamTeaTime forum assets
                'resources/forum/livewire-tailwind/css/forum.css',
                'resources/forum/livewire-tailwind/js/forum.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
})
