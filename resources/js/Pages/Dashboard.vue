<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { useFormatter } from "@/Composables/useFormatter";
import { computed } from "vue";
import VueApexCharts from "vue3-apexcharts"; // <-- Import ApexCharts

const props = defineProps({
    summary: Object,
});

const { formatCurrency } = useFormatter();

// Data & Konfigurasi untuk Grafik Donat Komposisi Aset
const assetChartSeries = computed(() => {
    const totalSavings = props.summary.savingsGoals.reduce(
        (sum, goal) => sum + parseFloat(goal.current_amount),
        0
    );
    return [totalSavings, props.summary.totalInvestment];
});
const assetChartOptions = {
    chart: { type: "donut" },
    labels: ["Tabungan & Dana Darurat", "Investasi"],
    legend: { position: "bottom" },
    colors: ["#008FFB", "#00E396"],
};

// Data & Konfigurasi untuk Grafik Batang Arus Kas
const cashFlowChartSeries = [
    {
        name: "Jumlah",
        data: [props.summary.monthlyIncome, props.summary.monthlyExpense],
    },
];
const cashFlowChartOptions = {
    chart: { type: "bar", toolbar: { show: false } },
    plotOptions: {
        bar: { columnWidth: "45%", distributed: true, borderRadius: 4 },
    },
    dataLabels: { enabled: false },
    xaxis: { categories: ["Pemasukan", "Pengeluaran"] },
    yaxis: {
        labels: { formatter: (val) => `Rp ${val.toLocaleString("id-ID")}` },
    },
    legend: { show: false },
    colors: ["#33b2df", "#f44336"],
};
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard Ringkasan
            </h2>
        </template>

        <q-page class="q-pa-md">
            <div class="row q-col-gutter-md q-mb-md">
                <div class="col-12 col-sm-6 col-md-3">
                    <q-card
                        ><q-item
                            ><q-item-section
                                ><q-item-label caption
                                    >Kekayaan Bersih</q-item-label
                                ><q-item-label
                                    class="text-h6 text-weight-bold"
                                    >{{
                                        formatCurrency(summary.netWorth)
                                    }}</q-item-label
                                ></q-item-section
                            ></q-item
                        ></q-card
                    >
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <q-card
                        ><q-item
                            ><q-item-section
                                ><q-item-label caption>Total Aset</q-item-label
                                ><q-item-label class="text-h6 text-positive">{{
                                    formatCurrency(summary.totalAssets)
                                }}</q-item-label></q-item-section
                            ></q-item
                        ></q-card
                    >
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <q-card
                        ><q-item
                            ><q-item-section
                                ><q-item-label caption
                                    >Total Hutang</q-item-label
                                ><q-item-label class="text-h6 text-negative">{{
                                    formatCurrency(summary.totalDebts)
                                }}</q-item-label></q-item-section
                            ></q-item
                        ></q-card
                    >
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <q-card
                        ><q-item
                            ><q-item-section
                                ><q-item-label caption
                                    >Selisih Bulan Ini</q-item-label
                                ><q-item-label
                                    class="text-h6"
                                    :class="
                                        summary.monthlyIncome >
                                        summary.monthlyExpense
                                            ? 'text-positive'
                                            : 'text-negative'
                                    "
                                    >{{
                                        formatCurrency(
                                            summary.monthlyIncome -
                                                summary.monthlyExpense
                                        )
                                    }}</q-item-label
                                ></q-item-section
                            ></q-item
                        ></q-card
                    >
                </div>
            </div>

            <div class="row q-col-gutter-md q-mb-md">
                <div class="col-12 col-md-5">
                    <q-card
                        ><q-card-section
                            ><div class="text-h6">
                                Komposisi Aset
                            </div></q-card-section
                        ><VueApexCharts
                            v-if="
                                assetChartSeries.length > 0 &&
                                (assetChartSeries[0] > 0 ||
                                    assetChartSeries[1] > 0)
                            "
                            type="donut"
                            :options="assetChartOptions"
                            :series="assetChartSeries"
                    /></q-card>
                </div>
                <div class="col-12 col-md-7">
                    <q-card
                        ><q-card-section
                            ><div class="text-h6">
                                Arus Kas Bulan Ini
                            </div></q-card-section
                        ><VueApexCharts
                            v-if="
                                summary.monthlyIncome > 0 ||
                                summary.monthlyExpense > 0
                            "
                            type="bar"
                            height="300"
                            :options="cashFlowChartOptions"
                            :series="cashFlowChartSeries"
                    /></q-card>
                </div>

                <div v-if="summary.totalBudget > 0">
                    <div class="text-subtitle2 text-grey-8 q-mt-sm">
                        Terpakai {{ formatCurrency(summary.totalSpending) }} dari {{ formatCurrency(summary.totalBudget) }}
                    </div>
                    <q-linear-progress
                        rounded
                        size="20px"
                        :value="summary.totalSpending / summary.totalBudget"
                        color="primary"
                        class="q-mt-xs"
                    />
                </div>
                <div v-else class="text-grey q-mt-sm">
                    Anda belum mengatur budget untuk bulan ini.
                </div>
            </div>


            <div class="row q-col-gutter-md">
                <div class="col-12 col-md-6">
                    <q-card
                        ><q-card-section
                            ><div class="text-h6">
                                Piutang Aktif
                            </div></q-card-section
                        ><q-list separator
                            ><q-item
                                v-for="item in summary.activeReceivables"
                                :key="item.id"
                                ><q-item-section>{{
                                    item.party.name
                                }}</q-item-section
                                ><q-item-section
                                    side
                                    class="text-positive text-weight-medium"
                                    >{{
                                        formatCurrency(item.amount)
                                    }}</q-item-section
                                ></q-item
                            ></q-list
                        ></q-card
                    >
                </div>
                <div class="col-12 col-md-6">
                    <q-card
                        ><q-card-section
                            ><div class="text-h6">
                                Progres Tabungan
                            </div></q-card-section
                        ><q-list separator
                            ><q-item
                                v-for="item in summary.savingsGoals"
                                :key="item.id"
                                ><q-item-section
                                    ><q-item-label>{{
                                        item.goal_name
                                    }}</q-item-label
                                    ><q-linear-progress
                                        rounded
                                        :value="
                                            item.current_amount /
                                            item.target_amount
                                        "
                                        color="primary"
                                        class="q-mt-xs" /></q-item-section
                                ><q-item-section side class="text-grey"
                                    >{{
                                        (
                                            (item.current_amount /
                                                item.target_amount) *
                                            100
                                        ).toFixed(0)
                                    }}%</q-item-section
                                ></q-item
                            ></q-list
                        ></q-card
                    >
                </div>
            </div>
        </q-page>
    </AuthenticatedLayout>
</template>
