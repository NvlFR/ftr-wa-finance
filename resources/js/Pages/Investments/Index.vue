<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    portfolio: Array,
});

// Fungsi untuk memformat angka menjadi format Rupiah
const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
};

// Fungsi untuk memformat angka desimal (untuk crypto, dll)
const formatDecimal = (value) => {
    return parseFloat(value).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 8 });
};

// Hitung total semua modal investasi
const totalCapital = computed(() => {
    return props.portfolio.reduce((sum, asset) => sum + parseFloat(asset.total_capital), 0);
});

const getEmoji = (asset_type) => {
    switch (asset_type.toLowerCase()) {
        case 'crypto': return '💎';
        case 'saham': return '📈';
        case 'emas': return '🪙';
        case 'reksadana': return '📄';
        default: return '💰';
    }
};
</script>

<template>
    <Head title="Portofolio Investasi" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Portofolio Investasi</h2>
        </template>

        <q-page class="q-pa-md">
            <q-card class="bg-secondary text-white q-mb-md">
                <q-card-section>
                    <div class="text-caption">Total Modal Terinvestasi</div>
                    <div class="text-h5 text-weight-bold">{{ formatCurrency(totalCapital) }}</div>
                </q-card-section>
            </q-card>

            <div v-if="portfolio.length > 0" class="row q-col-gutter-md">
                <div v-for="asset in portfolio" :key="asset.asset_name" class="col-12 col-sm-6 col-md-4">
                    <q-card>
                        <q-card-section>
                            <div class="text-h6">
                                {{ getEmoji(asset.asset_type) }} {{ asset.asset_name }}
                            </div>
                            <div class="text-caption text-grey">{{ asset.asset_type }}</div>
                        </q-card-section>
                        <q-separator />
                        <q-card-section class="row">
                            <div class="col">
                                <div class="text-caption">Jumlah</div>
                                <div>{{ formatDecimal(asset.total_quantity) }} unit</div>
                            </div>
                            <div class="col text-right">
                                <div class="text-caption">Modal</div>
                                <div class="text-weight-medium">{{ formatCurrency(asset.total_capital) }}</div>
                            </div>
                        </q-card-section>
                    </q-card>
                </div>
            </div>
            <div v-else class="text-center text-grey q-mt-xl">
                <q-icon name="trending_down" size="xl" />
                <p class="q-mt-md">Anda belum memiliki aset investasi.</p>
            </div>
        </q-page>
    </AuthenticatedLayout>
</template>
