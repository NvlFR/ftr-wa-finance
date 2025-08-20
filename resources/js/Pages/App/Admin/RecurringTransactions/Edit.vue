<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';

const props = defineProps({
    recurring_transaction: Object,
});

const $q = useQuasar();

const form = useForm({
    type: props.recurring_transaction.type,
    amount: props.recurring_transaction.amount,
    description: props.recurring_transaction.description,
    category: props.recurring_transaction.category,
    day_of_month: props.recurring_transaction.day_of_month,
});

const submit = () => {
    form.put(route('recurring-transactions.update', props.recurring_transaction.id), {
        onSuccess: () => {
            $q.notify({
                color: 'positive',
                message: 'Aturan berhasil diperbarui!',
                icon: 'check_circle',
            });
        },
    });
};
</script>

<template>
    <Head title="Edit Transaksi Berulang" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Transaksi Berulang</h2>
        </template>

        <q-page class="q-pa-md">
            <q-card style="max-width: 600px" class="q-mx-auto">
                 <q-form @submit.prevent="submit">
                    <q-card-section class="q-gutter-md">
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
                            v-model="form.description"
                            label="Deskripsi"
                            hint="Contoh: Gaji Bulanan, Tagihan Internet, Spotify"
                            :error="!!form.errors.description"
                            :error-message="form.errors.description"
                        />
                        <q-input
                            filled
                            v-model.number="form.amount"
                            type="number"
                            label="Jumlah (Rp)"
                            :error="!!form.errors.amount"
                            :error-message="form.errors.amount"
                        />
                         <q-input
                            filled
                            v-model="form.category"
                            label="Kategori (opsional)"
                            :error="!!form.errors.category"
                            :error-message="form.errors.category"
                        />
                        <q-input
                            filled
                            v-model.number="form.day_of_month"
                            type="number"
                            label="Dicatat Setiap Tanggal"
                            min="1"
                            max="31"
                            :error="!!form.errors.day_of_month"
                            :error-message="form.errors.day_of_month"
                        />
                    </q-card-section>
                    <q-card-actions align="right" class="q-pa-md">
                        <q-btn :href="route('recurring-transactions.index')" flat label="Batal" />
                        <q-btn label="Update" type="submit" color="primary" :loading="form.processing" />
                    </q-card-actions>
                </q-form>
            </q-card>
        </q-page>
    </AuthenticatedLayout>
</template>
