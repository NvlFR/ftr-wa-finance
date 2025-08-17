<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    savings: Array,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
};

const getProgress = (current, target) => {
    if (target <= 0) return 1; // Jika target 0, anggap 100%
    const progress = current / target;
    return progress > 1 ? 1 : progress; // Progress tidak boleh lebih dari 100% (nilai 1)
};
</script>

<template>
    <Head title="Tabungan & Dana Darurat" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tabungan & Dana Darurat</h2>
        </template>

        <q-page class="q-pa-md">
            <div v-if="savings.length > 0" class="row q-col-gutter-md">
                <div v-for="saving in savings" :key="saving.id" class="col-12 col-sm-6 col-md-4">
                    <q-card>
                        <q-card-section>
                            <div class="text-h6">
                                <q-icon :name="saving.is_emergency_fund ? 'emergency' : 'savings'" class="q-mr-sm" />
                                {{ saving.goal_name }}
                            </div>
                            <q-badge v-if="saving.is_emergency_fund" color="red-6" class="q-mt-xs">
                                Dana Darurat
                            </q-badge>
                        </q-card-section>

                        <q-card-section class="q-pt-none">
                            <div class="text-subtitle2 text-grey-8">
                                {{ formatCurrency(saving.current_amount) }} / {{ formatCurrency(saving.target_amount) }}
                            </div>
                            <q-linear-progress
                                rounded
                                size="25px"
                                :value="getProgress(saving.current_amount, saving.target_amount)"
                                :color="saving.is_emergency_fund ? 'red-6' : 'primary'"
                                class="q-mt-sm"
                            >
                                <div class="absolute-full flex flex-center">
                                    <q-badge color="white" text-color="black">
                                        {{ (getProgress(saving.current_amount, saving.target_amount) * 100).toFixed(1) }}%
                                    </q-badge>
                                </div>
                            </q-linear-progress>
                        </q-card-section>
                    </q-card>
                </div>
            </div>
            <div v-else class="text-center text-grey q-mt-xl">
                <q-icon name="account_balance_wallet" size="xl" />
                <p class="q-mt-md">Anda belum memiliki tujuan tabungan atau dana darurat.</p>
            </div>
        </q-page>
    </AuthenticatedLayout>
</template>
