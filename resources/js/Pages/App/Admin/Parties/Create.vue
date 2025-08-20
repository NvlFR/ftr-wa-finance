<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PartyForm from '@/Components/PartyForm.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    type: 'Perorangan',
    notes: '',
});

const submit = () => form.post(route('parties.store'));
</script>

<template>
    <Head title="Tambah Pihak Baru" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Pihak Baru</h2>
        </template>
        <q-page class="q-pa-md">
            <q-card>
                <PartyForm :form="form" @submit="submit">
                    <template #actions>
                        <q-btn :href="route('parties.index')" flat label="Batal" />
                        <q-btn label="Simpan" type="submit" color="primary" :loading="form.processing" />
                    </template>
                </PartyForm>
            </q-card>
        </q-page>
    </AuthenticatedLayout>
</template>
