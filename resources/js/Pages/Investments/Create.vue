<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';

const $q = useQuasar();

const form = useForm({
    type: 'beli',
    asset_name: '',
    asset_type: 'saham',
    quantity: null,
    price_per_unit: null,
    transaction_date: new Date().toISOString().slice(0, 10),
});

const submit = () => {
    form.post(route('investments.store'), {
        onSuccess: () => {
            $q.notify({
                color: 'positive',
                message: 'Transaksi investasi berhasil dicatat!',
                icon: 'check_circle',
            });
        },
    });
};
</script>

<template>
    <Head title="Catat Transaksi Investasi" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Catat Transaksi Investasi Baru</h2>
        </template>

        <q-page class="q-pa-md">
            <q-card>
                <q-form @submit.prevent="submit">
                    <q-card-section class="q-gutter-md">
                        <q-select filled v-model="form.type" :options="['beli', 'jual']" label="Tipe Transaksi" />
                        <q-input filled v-model="form.asset_name" label="Nama Aset (Contoh: Saham BBCA, Bitcoin)" :error="!!form.errors.asset_name" :error-message="form.errors.asset_name" />
                        <q-select filled v-model="form.asset_type" :options="['saham', 'crypto', 'reksadana', 'emas', 'lainnya']" label="Tipe Aset" />
                        <q-input filled v-model.number="form.quantity" type="number" step="any" label="Jumlah Unit/Lembar/Gram" :error="!!form.errors.quantity" :error-message="form.errors.quantity" />
                        <q-input filled v-model.number="form.price_per_unit" type="number" step="any" label="Harga per Unit (Rp)" :error="!!form.errors.price_per_unit" :error-message="form.errors.price_per_unit" />
                        <q-input filled v-model="form.transaction_date" mask="####-##-##" label="Tanggal Transaksi">
                            <template v-slot:append>
                                <q-icon name="event" class="cursor-pointer">
                                    <q-popup-proxy cover><q-date v-model="form.transaction_date" mask="YYYY-MM-DD" /></q-popup-proxy>
                                </q-icon>
                            </template>
                        </q-input>
                    </q-card-section>
                    <q-card-actions align="right">
                        <q-btn :href="route('investments.index')" flat label="Batal" color="grey-8" />
                        <q-btn label="Simpan" type="submit" color="primary" :loading="form.processing" />
                    </q-card-actions>
                </q-form>
            </q-card>
        </q-page>
    </AuthenticatedLayout>
</template>
