<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";

const form = useForm({
    category: "",
    amount: null,
});

const submit = () => form.post(route("budgets.store"));
</script>

<template>
    <Head title="Atur Budget Baru" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Atur Budget Baru
            </h2>
        </template>
        <q-page class="q-pa-md">
            <q-card style="max-width: 600px" class="q-mx-auto">
                <q-form @submit.prevent="submit">
                    <q-card-section class="q-gutter-md">
                        <q-input
                            filled
                            v-model="form.category"
                            label="Nama Kategori"
                            :error="!!form.errors.category"
                            :error-message="form.errors.category"
                        />
                        <q-input
                            filled
                            v-model.number="form.amount"
                            type="number"
                            label="Jumlah Budget (Rp)"
                            :error="!!form.errors.amount"
                            :error-message="form.errors.amount"
                        />
                    </q-card-section>
                    <q-card-actions align="right" class="q-pa-md">
                        <q-btn
                            :href="route('budgets.index')"
                            flat
                            label="Batal"
                        />
                        <q-btn
                            label="Simpan"
                            type="submit"
                            color="primary"
                            :loading="form.processing"
                        />
                    </q-card-actions>
                </q-form>
            </q-card>
        </q-page>
    </AuthenticatedLayout>
</template>
