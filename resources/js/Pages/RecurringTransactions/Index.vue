<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import { useFormatter } from '@/Composables/useFormatter';

const props = defineProps({
    recurring_transactions: Object,
});

const $q = useQuasar();
const { formatCurrency } = useFormatter();

const columns = [
    { name: 'description', label: 'Deskripsi', field: 'description', align: 'left' },
    { name: 'type', label: 'Tipe', field: 'type', align: 'left' },
    { name: 'amount', label: 'Jumlah', field: 'amount', align: 'right', format: val => formatCurrency(val) },
    { name: 'day', label: 'Tgl Dicatat', field: 'day_of_month', align: 'center' },
    { name: 'actions', label: 'Aksi', align: 'center' },
];
</script>

<template>
    <Head title="Transaksi Berulang" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Transaksi Berulang</h2>
        </template>

        <q-page class="q-pa-md">
            <div class="q-mb-md">
                <Link :href="route('recurring-transactions.create')">
                    <q-btn color="primary" icon="add" label="Atur Baru" />
                </Link>
            </div>

            <q-card>
                <q-table
                    title="Aturan Transaksi Otomatis"
                    :rows="recurring_transactions.data"
                    :columns="columns"
                    row-key="id"
                >
                    <template v-slot:body-cell-type="props">
                        <q-td :props="props">
                            <q-badge :color="props.row.type === 'pemasukan' ? 'positive' : 'negative'" :label="props.row.type" />
                        </q-td>
                    </template>

                    <template v-slot:body-cell-actions="props">
                        <q-td :props="props" class="q-gutter-xs">
                            <Link :href="route('recurring-transactions.edit', props.row.id)">
                                <q-btn flat round color="primary" icon="edit" size="sm" />
                            </Link>
                            </q-td>
                    </template>
                </q-table>
                <Pagination :links="recurring_transactions.links" />
            </q-card>
        </q-page>
    </AuthenticatedLayout>
</template>
