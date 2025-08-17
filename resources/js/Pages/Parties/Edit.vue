<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PartyForm from '@/Components/PartyForm.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    party: Object,
});

const form = useForm({
    name: props.party.name,
    type: props.party.type,
    notes: props.party.notes,
});

const submit = () => form.put(route('parties.update', props.party.id));
</script>

<template>
    <Head title="Edit Pihak" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Pihak</h2>
        </template>
        <q-page class="q-pa-md">
            <q-card>
                <PartyForm :form="form" @submit="submit">
                    <template #actions>
                        <q-btn :href="route('parties.index')" flat label="Batal" />
                        <q-btn label="Update" type="submit" color="primary" :loading="form.processing" />
                    </template>
                </PartyForm>
            </q-card>
        </q-page>
    </AuthenticatedLayout>
</template>
