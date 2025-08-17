<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SavingForm from '@/Components/SavingForm.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    goal_name: '',
    target_amount: null,
});

const submit = () => form.post(route('savings.store'));
</script>

<template>
    <Head title="Buat Tujuan Tabungan" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Buat Tujuan Tabungan Baru</h2>
        </template>
        <q-page class="q-pa-md">
            <q-card>
                <SavingForm :form="form" @submit="submit">
                    <template #actions>
                        <q-btn :href="route('savings.index')" flat label="Batal" />
                        <q-btn label="Simpan" type="submit" color="primary" :loading="form.processing" />
                    </template>
                </SavingForm>
            </q-card>
        </q-page>
    </AuthenticatedLayout>
</template>
