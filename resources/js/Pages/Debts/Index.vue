<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    receivables: Array, // Piutang
    payables: Array,    // Hutang
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
};

// Kolom untuk tabel piutang
const receivablesColumns = [
    { name: 'person_name', label: 'Nama Orang', field: 'person_name', align: 'left' },
    { name: 'description', label: 'Deskripsi', field: 'description', align: 'left' },
    { name: 'amount', label: 'Jumlah', field: 'amount', align: 'right', format: val => formatCurrency(val) },
];

// Kolom untuk tabel hutang
const payablesColumns = [
    { name: 'person_name', label: 'Kepada', field: 'person_name', align: 'left' },
    { name: 'description', label: 'Deskripsi', field: 'description', align: 'left' },
    { name: 'amount', label: 'Jumlah', field: 'amount', align: 'right', format: val => formatCurrency(val) },
];
</script>

<template>
    <Head title="Hutang & Piutang" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Hutang & Piutang</h2>
        </template>

        <q-page class="q-pa-md">
            <div class="row q-col-gutter-md">
                <div class="col-12 col-md-6">
                    <q-table
                        title="Piutang (Uang Anda di Luar)"
                        :rows="receivables"
                        :columns="receivablesColumns"
                        row-key="id"
                        flat
                        bordered
                    >
                       <template v-slot:no-data>
                           <div class="full-width row flex-center text-positive q-gutter-sm">
                               <q-icon size="2em" name="check_circle" />
                               <span>
                                   Bagus! Tidak ada yang berhutang kepada Anda.
                               </span>
                           </div>
                       </template>
                    </q-table>
                </div>

                <div class="col-12 col-md-6">
                    <q-table
                        title="Hutang (Kewajiban Anda)"
                        :rows="payables"
                        :columns="payablesColumns"
                        row-key="id"
                        flat
                        bordered
                    >
                        <template v-slot:no-data>
                           <div class="full-width row flex-center text-positive q-gutter-sm">
                               <q-icon size="2em" name="check_circle" />
                               <span>
                                   Luar biasa! Anda tidak memiliki hutang.
                               </span>
                           </div>
                       </template>
                    </q-table>
                </div>
            </div>
        </q-page>
    </AuthenticatedLayout>
</template>
