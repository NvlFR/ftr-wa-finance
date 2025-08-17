<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import { Head, Link } from "@inertiajs/vue3";
import { useFormatter } from "@/Composables/useFormatter";

const props = defineProps({
    debts: Object, // Di controller, ini berisi semua data paginasi
});

const { formatCurrency } = useFormatter();

// Memisahkan data dari paginator untuk ditampilkan
const receivables = props.debts.data.filter((d) => d.type === "piutang");
const payables = props.debts.data.filter((d) => d.type === "hutang");

const columns = [
    {
        name: "party",
        label: "Pihak",
        field: (row) => (row.party ? row.party.name : "[Pihak Dihapus]"),
        align: "left",
        sortable: true,
    },
    {
        name: "description",
        label: "Deskripsi",
        field: "description",
        align: "left",
    },
    {
        name: "amount",
        label: "Jumlah",
        field: "amount",
        align: "right",
        sortable: true,
        format: (val) => formatCurrency(val),
    },
    // Aksi bisa ditambahkan di sini nanti (Edit, Lunas, Hapus)
];
</script>

<template>
    <Head title="Hutang & Piutang" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Hutang & Piutang
            </h2>
        </template>

        <q-page class="q-pa-md">
            <div class="q-mb-md">
                <Link :href="route('debts.create')">
                    <q-btn
                        color="primary"
                        icon="add"
                        label="Catat Hutang/Piutang"
                    />
                </Link>
            </div>

            <div class="row q-col-gutter-md">
                <div class="col-12 col-md-6">
                    <q-table
                        title="Piutang (Uang Anda di Luar)"
                        :rows="receivables"
                        :columns="columns"
                        row-key="id"
                        flat
                        bordered
                    />
                </div>
                <div class="col-12 col-md-6">
                    <q-table
                        title="Hutang (Kewajiban Anda)"
                        :rows="payables"
                        :columns="columns"
                        row-key="id"
                        flat
                        bordered
                    />
                </div>
            </div>
            <Pagination :links="debts.links" />
        </q-page>
    </AuthenticatedLayout>
</template>
