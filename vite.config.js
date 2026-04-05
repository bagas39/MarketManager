import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/js/pembelian.js', 
                'resources/js/penjualan.js',
                'resources/js/manajemen_stok.js',
                'resources/js/kasir.js',
                'resources/js/laporan_keuangan.js',
                'resources/js/pengguna.js',
                'resources/js/prediksi_stok.js',
                'resources/js/stok_opname.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
