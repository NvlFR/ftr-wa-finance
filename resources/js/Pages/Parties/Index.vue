<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    parties: Object,
});

const filter = ref('');
const columns = [
    { name: 'name', label: 'Nama', field: 'name', align: 'left', sortable: true },
    { name: 'type', label: 'Tipe', field: 'type', align: 'left', sortable: true },
    { name: 'notes', label: 'Catatan', field: 'notes', align: 'left' },
    { name: 'actions', label: 'Aksi', field: 'id', align: 'center' },
];
</script>

<template>
    <Head title="Daftar Pihak" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Daftar Pihak</h2>
        </template>

        <q-page class="q-pa-md">
            <q-card>
                <q-table
                    title="Semua Pihak"
                    :rows="parties.data"
                    :columns="columns"
                    row-key="id"
                    :filter="filter"
                >
                    <template v-slot:top>
                        <Link :href="route('parties.create')">
                            <q-btn color="primary" icon="add" label="Tambah Pihak" />
                        </Link>
                        <q-space />
                        <q-input borderless dense debounce="300" v-model="filter" placeholder="Cari...">
                            <template v-slot:append><q-icon name="search" /></template>
                        </q-input>
                    </template>

                    <template v-slot:body-cell-actions="props">
                        <q-td :props="props">
                            <Link :href="route('parties.edit', props.row.id)">
                                <q-btn flat round color="primary" icon="edit" size="sm" />
                            </Link>
                        </q-td>
                    </template>
                </q-table>
                <Pagination :links="parties.links" />
            </q-card>
        </q-page>
    </AuthenticatedLayout>
</template>
