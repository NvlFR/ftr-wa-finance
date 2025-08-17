<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TransactionForm from '@/Components/TransactionForm.vue'; // <-- Import komponen form
import { Head, useForm, router } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';

const props = defineProps({
    transaction: Object, // Menerima data transaksi dari controller
});

const $q = useQuasar();

// Inisialisasi form dengan data yang sudah ada
const form = useForm({
    type: props.transaction.type,
    amount: props.transaction.amount,
    description: props.transaction.description,
    category: props.transaction.category,
    created_at: props.transaction.created_at.slice(0, 10),
});

const submit = () => {
    form.put(route('transactions.update', props.transaction.id), {
        onSuccess: () => {
            $q.notify({
                color: 'positive',
                message: 'Transaksi berhasil diperbarui!',
                icon: 'check_circle',
            });
        },
    });
};

const deleteTransaction = () => {
    $q.dialog({
        title: 'Konfirmasi',
        message: 'Apakah Anda yakin ingin menghapus transaksi ini? Aksi ini tidak bisa dibatalkan.',
        cancel: true,
        persistent: true,
        ok: {
            label: 'Hapus',
            color: 'negative',
            flat: true,
        },
        cancel: {
            label: 'Batal',
            flat: true,
        }
    }).onOk(() => {
        // Jika user klik "Hapus", kirim request DELETE
        router.delete(route('transactions.destroy', props.transaction.id), {
            onSuccess: () => {
                $q.notify({
                    color: 'positive',
                    message: 'Transaksi berhasil dihapus!',
                    icon: 'check_circle',
                });
            }
        });
    });
};
</script>

<template>
    <Head title="Edit Transaksi" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Transaksi</h2>
        </template>

        <q-page class="q-pa-md">
            <q-card>
                <TransactionForm :form="form" @submit="submit">
                    <template #actions>
                        <q-btn :href="route('transactions.index')" flat label="Batal" color="grey-8" />
                        <q-btn label="Update" type="submit" color="primary" :loading="form.processing" />
                    </template>
                </TransactionForm>
            </q-card>
        </q-page>
    </AuthenticatedLayout>
</template>
