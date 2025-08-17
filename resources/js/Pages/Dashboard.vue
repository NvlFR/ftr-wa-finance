<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";
import { useFormatter } from '@/Composables/useFormatter'
const props = defineProps({
    summary: Object,
});

const { formatCurrency } = useFormatter(); 

const expensePercentage = computed(() => {
    if (props.summary.monthly_income === 0) {
        return 0;
    }
    return props.summary.monthly_expense / props.summary.monthly_income;
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <q-page class="q-pa-md">
            <template #header>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Dashboard Ringkasan
                </h2>
            </template>
            <div class="row q-col-gutter-md">
                <div class="col-12 col-md-4">
                    <q-card class="bg-primary text-white">
                        <q-card-section>
                            <div class="text-h6">Estimasi Kekayaan Bersih</div>
                            <div class="text-caption">
                                Total Aset - Total Hutang
                            </div>
                        </q-card-section>
                        <q-card-section class="q-pt-none">
                            <div class="text-h4 text-weight-bold">
                                {{ formatCurrency(summary.net_worth) }}
                            </div>
                        </q-card-section>
                        <q-separator dark />
                        <q-card-section class="row">
                            <div class="col">
                                <div class="text-caption">Aset</div>
                                <div class="text-subtitle1">
                                    {{ formatCurrency(summary.total_assets) }}
                                </div>
                            </div>
                            <div class="col">
                                <div class="text-caption">Hutang</div>
                                <div class="text-subtitle1">
                                    {{ formatCurrency(summary.total_debts) }}
                                </div>
                            </div>
                        </q-card-section>
                    </q-card>
                </div>

                <div class="col-12 col-md-8">
                    <q-card>
                        <q-card-section>
                            <div class="text-h6">Arus Kas Bulan Ini</div>
                            <div class="text-caption">
                                {{
                                    new Date().toLocaleString("id-ID", {
                                        month: "long",
                                        year: "numeric",
                                    })
                                }}
                            </div>
                        </q-card-section>
                        <q-card-section>
                            <div class="row items-center">
                                <div class="col">
                                    <q-icon
                                        name="arrow_downward"
                                        color="positive"
                                        size="sm"
                                    />
                                    <span class="text-caption q-ml-xs"
                                        >Pemasukan</span
                                    >
                                    <div class="text-h6 text-positive">
                                        {{
                                            formatCurrency(
                                                summary.monthly_income
                                            )
                                        }}
                                    </div>
                                </div>
                                <div class="col">
                                    <q-icon
                                        name="arrow_upward"
                                        color="negative"
                                        size="sm"
                                    />
                                    <span class="text-caption q-ml-xs"
                                        >Pengeluaran</span
                                    >
                                    <div class="text-h6 text-negative">
                                        {{
                                            formatCurrency(
                                                summary.monthly_expense
                                            )
                                        }}
                                    </div>
                                </div>
                            </div>
                            <q-linear-progress
                                rounded
                                size="20px"
                                :value="expensePercentage"
                                color="negative"
                                class="q-mt-sm"
                            >
                                <div class="absolute-full flex flex-center">
                                    <q-badge
                                        color="white"
                                        text-color="accent"
                                        :label="`${(
                                            expensePercentage * 100
                                        ).toFixed(1)}% Terpakai`"
                                    />
                                </div>
                            </q-linear-progress>
                        </q-card-section>
                    </q-card>
                </div>
            </div>
        </q-page>
    </AuthenticatedLayout>
</template>
