<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    parties: Array, // Menerima daftar pihak dari controller
});

const form = useForm({
    type: 'hutang',
    party_id: null,
    amount: null,
    description: '',
});

const submit = () => form.post(route('debts.store'));
</script>

<template>
    <Head title="Catat Hutang/Piutang" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Catat Hutang/Piutang Baru</h2>
        </template>
        <q-page class="q-pa-md">
            <q-card>
                <q-form @submit.prevent="submit">
                    <q-card-section class="q-gutter-md">
                        <q-select
                            filled
                            v-model="form.type"
                            :options="['hutang', 'piutang']"
                            label="Tipe Transaksi"
                            hint="Hutang (Anda meminjam), Piutang (Anda memberi pinjaman)"
                        />
                        <q-select
                            filled
                            v-model="form.party_id"
                            :options="parties"
                            option-value="id"
                            option-label="name"
                            label="Pihak Terkait"
                            emit-value
                            map-options
                            :error="!!form.errors.party_id"
                            :error-message="form.errors.party_id"
                        >
                            <template v-slot:no-option>
                                <q-item>
                                    <q-item-section class="text-grey">
                                        Tidak ada pihak ditemukan. Tambah dulu di menu 'Daftar Pihak'.
                                    </q-item-section>
                                </q-item>
                            </template>
                        </q-select>
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
                            v-model="form.description"
                            label="Deskripsi"
                            :error="!!form.errors.description"
                            :error-message="form.errors.description"
                        />
                    </q-card-section>
                    <q-card-actions align="right">
                        <q-btn :href="route('debts.index')" flat label="Batal" />
                        <q-btn label="Simpan" type="submit" color="primary" :loading="form.processing" />
                    </q-card-actions>
                </q-form>
            </q-card>
        </q-page>
    </AuthenticatedLayout>
</template>
