<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SavingForm from '@/Components/SavingForm.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    saving: Object,
});

const form = useForm({
    goal_name: props.saving.goal_name,
    target_amount: props.saving.target_amount,
});

const submit = () => form.put(route('savings.update', props.saving.id));
</script>

<template>
    <Head title="Edit Tujuan Tabungan" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Tujuan Tabungan</h2>
        </template>
        <q-page class="q-pa-md">
            <q-card>
                <SavingForm :form="form" @submit="submit">
                    <template #actions>
                        <q-btn :href="route('savings.index')" flat label="Batal" />
                        <q-btn label="Update" type="submit" color="primary" :loading="form.processing" />
                    </template>
                </SavingForm>
            </q-card>
        </q-page>
    </AuthenticatedLayout>
</template>
