<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { ref } from "vue";
import { useQuasar } from "quasar";

const props = defineProps({
    transactions: Object,
});

const $q = useQuasar();
const showCreateModal = ref(false);
const filter = ref("");

// Inertia Form Helper
const form = useForm({
    type: "pengeluaran",
    amount: null,
    description: "",
    category: "",
    created_at: new Date().toISOString().slice(0, 10), // Tanggal hari ini
});

const submit = () => {
    form.post(route("transactions.store"), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
            $q.notify({
                color: "positive",
                message: "Transaksi berhasil ditambahkan!",
                icon: "check_circle",
            });
        },
    });
};

const columns = [
    // ... (kolom yang sudah ada, tidak perlu diubah)
    {
        name: "date",
        label: "Tanggal",
        field: (row) => new Date(row.created_at).toLocaleDateString("id-ID"),
        align: "left",
        sortable: true,
    },
    {
        name: "type",
        label: "Tipe",
        field: "type",
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
        name: "category",
        label: "Kategori",
        field: "category",
        align: "left",
        sortable: true,
    },
    {
        name: "amount",
        label: "Jumlah",
        field: "amount",
        align: "right",
        sortable: true,
        format: (val) =>
            new Intl.NumberFormat("id-ID", {
                style: "currency",
                currency: "IDR",
            }).format(val),
    },
    { name: "actions", label: "Aksi", field: "id", align: "center" },
];

const onRowClick = (evt, row) => {
    router.get(route("transactions.show", row.id));
};
</script>

<template>
    <Head title="Transaksi" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Daftar Transaksi
            </h2>
        </template>

        <q-page class="q-pa-md">
            <q-card>
                <q-table
                    title="Semua Transaksi"
                    :rows="transactions.data"
                    :columns="columns"
                    row-key="id"
                    :filter="filter"
                    @row-click="onRowClick"
                >
                    <template v-slot:body-cell-actions="props">
                        <q-td :props="props">
                            <Link
                                :href="route('transactions.edit', props.row.id)"
                            >
                                <q-btn
                                    flat
                                    round
                                    color="primary"
                                    icon="edit"
                                    size="sm"
                                />
                            </Link>
                        </q-td>
                    </template>
                    <template v-slot:top>
                        <Link :href="route('transactions.create')">
                            <q-btn
                                color="primary"
                                icon="add"
                                label="Tambah Transaksi"
                            />
                        </Link>
                        <q-space />
                        <q-input
                            borderless
                            dense
                            debounce="300"
                            v-model="filter"
                            placeholder="Cari..."
                        >
                            <template v-slot:append
                                ><q-icon name="search"
                            /></template>
                        </q-input>
                    </template>

                    <template v-slot:body-cell-type="props">
                        <q-td :props="props">
                            <q-badge
                                :color="
                                    props.row.type === 'pemasukan'
                                        ? 'positive'
                                        : 'negative'
                                "
                                :label="props.row.type"
                            />
                        </q-td>
                    </template>
                </q-table>

                <div
                    v-if="transactions.links.length > 3"
                    class="q-pa-lg flex flex-center"
                >
                    <q-pagination>
                        <Link
                            v-for="(link, key) in transactions.links"
                            :key="key"
                            :href="link.url"
                            :class="{
                                'bg-primary text-white': link.active,
                                'text-grey': !link.url,
                            }"
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

    <!-- <q-dialog v-model="showCreateModal">
        <q-card style="width: 500px; max-width: 90vw">
            <q-card-section class="row items-center q-pb-none">
                <div class="text-h6">Tambah Transaksi Baru</div>
                <q-space />
                <q-btn icon="close" flat round dense v-close-popup />
            </q-card-section>

            <q-form @submit.prevent="submit">
                <q-card-section>
                    <q-select
                        filled
                        v-model="form.type"
                        :options="['pengeluaran', 'pemasukan']"
                        label="Tipe Transaksi"
                        :error="!!form.errors.type"
                        :error-message="form.errors.type"
                    />
                    <q-input
                        filled
                        v-model.number="form.amount"
                        type="number"
                        label="Jumlah (Rp)"
                        class="q-mt-md"
                        :error="!!form.errors.amount"
                        :error-message="form.errors.amount"
                    />
                    <q-input
                        filled
                        v-model="form.description"
                        label="Deskripsi"
                        class="q-mt-md"
                        :error="!!form.errors.description"
                        :error-message="form.errors.description"
                    />
                    <q-input
                        filled
                        v-model="form.category"
                        label="Kategori (opsional)"
                        class="q-mt-md"
                        :error="!!form.errors.category"
                        :error-message="form.errors.category"
                    />
                    <q-input
                        filled
                        v-model="form.created_at"
                        mask="date"
                        label="Tanggal"
                        class="q-mt-md"
                    >
                        <template v-slot:append>
                            <q-icon name="event" class="cursor-pointer">
                                <q-popup-proxy
                                    cover
                                    transition-show="scale"
                                    transition-hide="scale"
                                >
                                    <q-date v-model="form.created_at"></q-date>
                                </q-popup-proxy>
                            </q-icon>
                        </template>
                    </q-input>
                </q-card-section>
                <q-card-actions align="right">
                    <q-btn label="Batal" color="grey-8" flat v-close-popup />
                    <q-btn
                        label="Simpan"
                        type="submit"
                        color="primary"
                        :loading="form.processing"
                    />
                </q-card-actions>
            </q-form>
        </q-card>
    </q-dialog> -->
</template>
