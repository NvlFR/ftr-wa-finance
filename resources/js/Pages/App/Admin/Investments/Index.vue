<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, reactive, computed } from 'vue';
import { useQuasar } from 'quasar';
import { useFormatter } from '@/Composables/useFormatter';
import dayjs from "dayjs";

const title = "Portofolio Investasi";
const $q = useQuasar();
const { formatCurrency, formatDecimal } = useFormatter();

const rows = ref([]);
const loading = ref(true);
const showFilter = ref(false);

const currentYear = new Date().getFullYear();
const currentMonth = new Date().getMonth() + 1;

const filter = reactive({
  search: "",
  year: currentYear,
  month: currentMonth,
});

const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 0,
  sortBy: "transaction_date",
  descending: true,
});

const columns = [
  { name: 'date', label: 'Tanggal', field: 'transaction_date', align: 'left', sortable: true, format: (val) => dayjs(val).format('DD MMM YYYY') },
  { name: 'asset', label: 'Aset (Tipe)', field: 'asset_name', align: 'left', sortable: true },
  { name: 'type', label: 'Jenis Transaksi', field: 'type', align: 'center' },
  { name: 'quantity', label: 'Jumlah Unit', field: 'quantity', align: 'right', format: (val) => formatDecimal(val) },
  { name: 'price', label: 'Harga/Unit', field: 'price_per_unit', align: 'right', format: (val) => formatCurrency(val) },
  { name: 'total', label: 'Total Nilai', field: 'total_amount', align: 'right', sortable: true, format: (val) => formatCurrency(val) },
  { name: 'action', label: '', align: 'right' },
];

// Opsi filter tahun & bulan
const years = Array.from({ length: currentYear - 2020 }, (v, k) => currentYear - k);
const months = [
    { value: null, label: "Semua Bulan" },
    ...Array.from({length: 12}, (e, i) => ({
      value: i + 1,
      label: new Date(null, i + 1, null).toLocaleDateString("id-ID", { month: "long" })
    }))
];

const fetchItems = async (props) => {
    loading.value = true;
    const page = props?.pagination?.page || pagination.value.page;
    const rowsPerPage = props?.pagination?.rowsPerPage || pagination.value.rowsPerPage;
    const sortBy = props?.pagination?.sortBy || pagination.value.sortBy;
    const descending = props?.pagination?.descending || pagination.value.descending;

    try {
        const response = await axios.get(route('investments.data'), {
            params: {
                page: page,
                limit: rowsPerPage,
                sort: sortBy,
                order: descending ? 'desc' : 'asc',
                ...filter
            }
        });
        
        rows.value = response.data.data;
        pagination.value.page = response.data.current_page;
        pagination.value.rowsPerPage = response.data.per_page;
        pagination.value.rowsNumber = response.data.total;
        pagination.value.sortBy = sortBy;
        pagination.value.descending = descending;
    } catch (error) {
        $q.notify({ type: 'negative', message: 'Gagal memuat data.' });
    } finally {
        loading.value = false;
    }
};

const onFilterChange = () => {
  fetchItems();
};

onMounted(() => {
    fetchItems();
});
const onRowClick = (evt, row) => {
    router.get(route('app.admin.investments.detail', row.id));
};
</script>

<template>
    <Head :title="title" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ title }}</h2>
                <div>
                     <Link :href="route('investments.create')">
                        <q-btn icon="add_chart" color="primary" label="Catat Transaksi" />
                    </Link>
                    <q-btn class="q-ml-sm" :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'" color="grey-8" dense flat round @click="showFilter = !showFilter" />
                </div>
            </div>
        </template>
        
        <q-page class="q-pa-md">
            <q-card v-if="showFilter" class="q-mb-md">
                <div class="row q-col-gutter-md items-center q-pa-sm">
                    <div class="col-12 col-sm-6 col-md-3">
                        <q-select v-model="filter.year" :options="years" label="Tahun" dense outlined @update:model-value="onFilterChange" clearable />
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <q-select v-model="filter.month" :options="months" label="Bulan" dense outlined @update:model-value="onFilterChange" emit-value map-options :disable="!filter.year" />
                    </div>
                    <div class="col-12 col-md-6">
                         <q-input outlined dense debounce="300" v-model="filter.search" placeholder="Cari nama aset..." @update:model-value="onFilterChange" clearable>
                            <template v-slot:append><q-icon name="search" /></template>
                        </q-input>
                    </div>
                </div>
            </q-card>

            <q-table
                :rows="rows"
                :columns="columns"
                row-key="id"
                v-model:pagination="pagination"
                :loading="loading"
                @request="fetchItems"
                binary-state-sort
                 @row-click="onRowClick" class="cursor-pointer"
            >
                <template v-slot:body-cell-asset="props">
                    <q-td :props="props">
                        <div class="text-weight-bold">{{ props.row.asset_name }}</div>
                        <div class="text-caption text-grey">{{ props.row.asset_type }}</div>
                    </q-td>
                </template>

                <template v-slot:body-cell-type="props">
                    <q-td :props="props">
                        <q-badge :color="props.row.type === 'beli' ? 'positive' : 'negative'" :label="props.row.type" />
                    </q-td>
                </template>

                 <template v-slot:body-cell-action="props">
                    <q-td :props="props">
                        <div class="q-gutter-xs">
                            <q-btn flat round color="primary" icon="edit" size="sm" />
                            <q-btn flat round color="negative" icon="delete" size="sm" />
                        </div>
                    </q-td>
                </template>
            </q-table>
        </q-page>
    </AuthenticatedLayout>
</template>