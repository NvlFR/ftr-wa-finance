<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TransactionForm from '@/Components/TransactionForm.vue'; // <-- Import komponen form
import { Head, useForm } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';

const $q = useQuasar();

const form = useForm({
    type: 'pengeluaran',
    amount: null,
    description: '',
    category: '',
    created_at: new Date().toISOString().slice(0, 10),
});

const submit = () => {
    form.post(route('transactions.store'), {
        onSuccess: () => {
            $q.notify({
                color: 'positive',
                message: 'Transaksi berhasil ditambahkan!',
                icon: 'check_circle',
            });
        },
    });
};
</script>

<template>
    <Head title="Tambah Transaksi" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Transaksi Baru</h2>
        </template>

        <q-page class="q-pa-md">
            <q-card>
                <TransactionForm :form="form" @submit="submit">
                    <template #actions>
                        <q-btn :href="route('transactions.index')" flat label="Batal" color="grey-8" />
                        <q-btn label="Simpan" type="submit" color="primary" :loading="form.processing" />
                    </template>
                </TransactionForm>
            </q-card>
        </q-page>
    </AuthenticatedLayout>
</template>
