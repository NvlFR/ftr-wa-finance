<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head } from '@inertiajs/vue3';
import { useFormatter } from '@/Composables/useFormatter';

const props = defineProps({
    asset: Object,
    history: Object,
});

const { formatCurrency, formatDecimal } = useFormatter();

const historyColumns = [
    { name: 'date', label: 'Tanggal', field: row => new Date(row.transaction_date).toLocaleDateString('id-ID'), align: 'left' },
    { name: 'type', label: 'Tipe', field: 'type', align: 'left' },
    { name: 'quantity', label: 'Jumlah Unit', field: 'quantity', align: 'right', format: val => formatDecimal(val) },
    { name: 'price', label: 'Harga /Unit', field: 'price_per_unit', align: 'right', format: val => formatCurrency(val) },
    { name: 'total', label: 'Total Nilai', field: 'total_amount', align: 'right', format: val => formatCurrency(val) },
];
</script>

<template>
    <Head :title="'Detail Aset: ' + asset.asset_name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <q-btn flat round dense icon="arrow_back" @click="$inertia.visit(route('investments.index'))" />
                <h2 class="font-semibold text-xl text-gray-800 leading-tight ml-4">
                    Detail Aset: {{ asset.asset_name }}
                </h2>
            </div>
        </template>

        <q-page class="q-pa-md">
            <q-card class="q-mb-md">
                 <q-card-section>
                    <div class="text-h6">{{ asset.asset_name }}</div>
                    <div class="text-caption text-grey">{{ asset.asset_type }}</div>
                </q-card-section>
                <q-separator />
                <q-card-section class="row text-center">
                    <div class="col">
                        <div class="text-caption">Jumlah Dimiliki</div>
                        <div class="text-subtitle1 text-weight-medium">{{ formatDecimal(asset.total_quantity) }} unit</div>
                    </div>
                    <div class="col">
                        <div class="text-caption">Total Modal</div>
                        <div class="text-subtitle1 text-weight-bold">{{ formatCurrency(asset.total_capital) }}</div>
                    </div>
                </q-card-section>
            </q-card>

            <q-card>
                 <q-table
                    title="Riwayat Transaksi"
                    :rows="history.data"
                    :columns="historyColumns"
                    row-key="id"
                    flat
                 >
                    <template v-slot:body-cell-type="props">
                        <q-td :props="props">
                            <q-badge :color="props.row.type === 'beli' ? 'positive' : 'negative'" :label="props.row.type" />
                        </q-td>
                    </template>
                 </q-table>
                 <Pagination :links="history.links" />
            </q-card>

        </q-page>
    </AuthenticatedLayout>
</template>
