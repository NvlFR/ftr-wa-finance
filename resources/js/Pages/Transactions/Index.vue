<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    transactions: Object, // Menerima data paginasi dari controller
});

const filter = ref(''); // Untuk fitur pencarian

// Mendefinisikan kolom untuk tabel Quasar
const columns = [
    { name: 'date', label: 'Tanggal', field: row => new Date(row.created_at).toLocaleDateString('id-ID'), align: 'left', sortable: true },
    { name: 'type', label: 'Tipe', field: 'type', align: 'left', sortable: true },
    { name: 'description', label: 'Deskripsi', field: 'description', align: 'left' },
    { name: 'category', label: 'Kategori', field: 'category', align: 'left', sortable: true },
    { name: 'amount', label: 'Jumlah', field: 'amount', align: 'right', sortable: true, format: val => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(val) },
];
</script>

<template>
    <Head title="Transaksi" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Daftar Transaksi</h2>
        </template>

        <q-page class="q-pa-md">
            <q-card>
                <q-table
                    title="Semua Transaksi"
                    :rows="transactions.data"
                    :columns="columns"
                    row-key="id"
                    :filter="filter"
                >
                    <template v-slot:top-right>
                        <q-input borderless dense debounce="300" v-model="filter" placeholder="Cari...">
                            <template v-slot:append>
                                <q-icon name="search" />
                            </template>
                        </q-input>
                    </template>

                    <template v-slot:body-cell-type="props">
                        <q-td :props="props">
                            <q-badge :color="props.row.type === 'pemasukan' ? 'positive' : 'negative'" :label="props.row.type" />
                        </q-td>
                    </template>
                </q-table>

                <div v-if="transactions.links.length > 3" class="q-pa-lg flex flex-center">
                    <q-pagination>
                        <Link v-for="(link, key) in transactions.links" :key="key"
                            :href="link.url"
                            :class="{ 'bg-primary text-white': link.active, 'text-grey': !link.url }"
                            class="q-ma-xs q-pa-md cursor-pointer"
                            v-html="link.label"
                            as="button"
                            :disabled="!link.url"
                        />
                    </q-pagination>
                </div>

            </q-card>
        </q-page>
    </AuthenticatedLayout>
</template>
