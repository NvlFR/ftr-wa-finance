<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";

const props = defineProps({
    transaction: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
    }).format(value);
};

const formatDate = (value) => {
    return new Date(value).toLocaleDateString("id-ID", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>

<template>
    <Head :title="'Detail Transaksi #' + transaction.id" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <q-btn
                    flat
                    round
                    dense
                    icon="arrow_back"
                    @click="$inertia.visit(route('transactions.index'))"
                />
                <h2
                    class="font-semibold text-xl text-gray-800 leading-tight ml-4"
                >
                    Detail Transaksi
                </h2>
            </div>
        </template>

        <q-page class="q-pa-md">
            <q-card flat bordered>
                <q-card-section>
                    <div class="text-h6">
                        {{ transaction.description }}
                        <q-badge
                            :color="
                                transaction.type === 'pemasukan'
                                    ? 'positive'
                                    : 'negative'
                            "
                            class="q-ml-sm"
                        >
                            {{ transaction.type }}
                        </q-badge>
                    </div>
                    <div
                        class="text-subtitle1 text-weight-bold"
                        :class="
                            transaction.type === 'pemasukan'
                                ? 'text-positive'
                                : 'text-negative'
                        "
                    >
                        {{ formatCurrency(transaction.amount) }}
                    </div>
                </q-card-section>
                <q-separator />
                <q-card-section>
                    <q-list separator>
                        <q-item>
                            <q-item-section>
                                <q-item-label caption>Kategori</q-item-label>
                                <q-item-label>{{
                                    transaction.category || "-"
                                }}</q-item-label>
                            </q-item-section>
                        </q-item>
                        <q-item>
                            <q-item-section>
                                <q-item-label caption
                                    >Tanggal Transaksi</q-item-label
                                >
                                <q-item-label>{{
                                    formatDate(transaction.created_at)
                                }}</q-item-label>
                            </q-item-section>
                        </q-item>
                        <q-item>
                            <q-item-section>
                                <q-item-label caption
                                    >Terakhir Diperbarui</q-item-label
                                >
                                <q-item-label>{{
                                    formatDate(transaction.updated_at)
                                }}</q-item-label>
                            </q-item-section>
                        </q-item>
                    </q-list>
                </q-card-section>
                <q-separator />
                <q-card-actions>
                    <q-btn flat color="primary">Edit</q-btn>
                    <!-- <q-btn
                        @click="deleteTransaction"
                        flat
                        label="Hapus"
                        color="negative"
                        icon="delete"
                    />
                    <q-space />
                    <q-btn
                        :href="route('transactions.index')"
                        flat
                        label="Batal"
                        color="grey-8"
                    />
                    <q-btn
                        label="Update"
                        type="submit"
                        color="primary"
                    /> -->
                </q-card-actions>
            </q-card>
        </q-page>
    </AuthenticatedLayout>
</template>
