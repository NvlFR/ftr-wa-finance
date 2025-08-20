<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue'; // Gunakan komponen pagination kita
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    saving: Object,
    history: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const getProgress = (current, target) => {
    if (target <= 0) return 1;
    return current / target > 1 ? 1 : current / target;
};

// Kolom untuk tabel riwayat
const historyColumns = [
    { name: 'date', label: 'Tanggal', field: row => new Date(row.created_at).toLocaleDateString('id-ID'), align: 'left' },
    { name: 'description', label: 'Deskripsi', field: 'description', align: 'left' },
    { name: 'amount', label: 'Jumlah', field: 'amount', align: 'right', format: val => formatCurrency(val) },
];
</script>

<template>
    <Head :title="'Detail Tabungan: ' + saving.goal_name" />

    <AuthenticatedLayout>
        <template #header>
             <div class="flex items-center">
                <q-btn flat round dense icon="arrow_back" @click="$inertia.visit(route('savings.index'))" />
                <h2 class="font-semibold text-xl text-gray-800 leading-tight ml-4">
                    Detail Tabungan
                </h2>
            </div>
        </template>

        <q-page class="q-pa-md">
            <q-card class="q-mb-md">
                <q-card-section>
                    <div class="text-h6">
                        <q-icon :name="saving.is_emergency_fund ? 'emergency' : 'savings'" class="q-mr-sm" />
                        {{ saving.goal_name }}
                    </div>
                    <div class="text-subtitle2 text-grey-8 q-mt-sm">
                        {{ formatCurrency(saving.current_amount) }} / {{ formatCurrency(saving.target_amount) }}
                    </div>
                    <q-linear-progress rounded size="25px" :value="getProgress(saving.current_amount, saving.target_amount)" :color="saving.is_emergency_fund ? 'red-6' : 'primary'" class="q-mt-sm">
                        <div class="absolute-full flex flex-center">
                            <q-badge color="white" text-color="black">
                                {{ (getProgress(saving.current_amount, saving.target_amount) * 100).toFixed(1) }}%
                            </q-badge>
                        </div>
                    </q-linear-progress>
                </q-card-section>
            </q-card>

            <q-card>
                 <q-table
                    title="Riwayat Dana Masuk/Keluar"
                    :rows="history.data"
                    :columns="historyColumns"
                    row-key="id"
                    flat
                 >
                     <template v-slot:body-cell-amount="props">
                        <q-td :props="props" :class="props.row.type === 'pemasukan' ? 'text-positive' : 'text-negative'">
                           {{ props.row.type === 'pemasukan' ? '+' : '-' }} {{ formatCurrency(props.row.amount) }}
                        </q-td>
                    </template>
                     <template v-slot:no-data>
                           <div class="full-width row flex-center text-grey q-gutter-sm q-pa-md">
                               <q-icon size="2em" name="history" />
                               <span>
                                   Belum ada riwayat transaksi untuk tabungan ini.
                               </span>
                           </div>
                       </template>
                 </q-table>
                 <Pagination :links="history.links" />
            </q-card>

        </q-page>
    </AuthenticatedLayout>
</template>
