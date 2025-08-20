<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { useQuasar } from "quasar";
import dayjs from "dayjs";
import { formatDateTime, formatNumber } from "@/helpers/formatter";
import { createMonthOptions, createYearOptions } from "@/helpers/options";

const title = "Biaya Operasional";
const page = usePage();
const $q = useQuasar();
const showFilter = ref(false);
const rows = ref([]);
const loading = ref(true);

const currentYear = new Date().getFullYear();
const currentMonth = new Date().getMonth() + 1;

const years = createYearOptions(2024, currentYear);

const months = [{ value: null, label: "Semua Bulan" }, ...createMonthOptions()];

const filter = reactive({
  search: "",
  category_id: "all",
  company_id: "all",
  year: currentYear,
  month: currentMonth,
  // ...
});

const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
  sortBy: "datetime",
  descending: true,
});

const columns = [
  {
    name: "datetime",
    label: "Tanggal & Waktu",
    field: "datetime",
    align: "left",
    sortable: true,
  },
  {
    name: "category",
    label: "Kategori",
    field: (row) => row.category?.name,
    align: "left",
    sortable: false,
  },
  {
    name: "description",
    label: "Deskripsi",
    field: "description",
    align: "right",
    sortable: true,
  },

  {
    name: "amount",
    label: "Jumlah (Rp.)",
    field: "amount",
    align: "right",
    sortable: true,
  },
  {
    name: "notes",
    label: "Catatan",
    field: "notes",
    align: "right",
    sortable: true,
  },
  {
    name: "action",
    label: "",
    align: "right",
  },
];

const categories = ref([
  { value: "all", label: "Semua Kategori" },
  ...(page.props.categories || []).map((c) => ({ label: c.name, value: c.id })),
]);

onMounted(() => {
  fetchItems();
});

const deleteItem = (row) =>
  handleDelete({
    message: `Hapus biaya sebesar ${formatNumber(
      row.amount
    )} pada tanggal ${formatDateTime(row.datetime)}?`,
    url: route("app.cost.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

const fetchItems = (props = null) => {
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("app.cost.data"),
    loading,
  });
};

const onFilterChange = () => {
  fetchItems();
};
const onRowClicked = (row) =>
  router.get(route("app.cost.detail", { id: row.id }));

const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter(
    (col) => col.name === "datetime" || col.name === "action"
  );
});

watch(
  () => filter.year,
  (newVal) => {
    if (newVal === null) {
      filter.month = null;
    }
  }
);
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <q-btn
        icon="add"
        dense
        color="primary"
        @click="router.get(route('app.cost.add'))"
      />
      <q-btn
        class="q-ml-sm"
        :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'"
        color="grey"
        dense
        @click="showFilter = !showFilter"
      />
    </template>

    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
          <q-select
            v-model="filter.year"
            :options="years"
            label="Tahun"
            dense
            outlined
            class="col-xs-6 col-sm-2"
            emit-value
            map-options
            @update:model-value="onFilterChange"
          />
          <q-select
            v-model="filter.month"
            :options="months"
            label="Bulan"
            dense
            outlined
            class="col-xs-6 col-sm-2"
            emit-value
            map-options
            :disable="filter.year === null"
            @update:model-value="onFilterChange"
          />

          <q-select
            v-model="filter.category_id"
            :options="categories"
            label="Kategori"
            dense
            class="col-xs-12 col-sm-3"
            map-options
            emit-value
            outlined
            @update:model-value="onFilterChange"
          />
          <q-input
            class="col"
            outlined
            dense
            debounce="300"
            v-model="filter.search"
            placeholder="Cari di catatan..."
            clearable
            @update:model-value="onFilterChange"
          >
            <template v-slot:append>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
      </q-toolbar>
    </template>

    <div class="q-pa-sm">
      <q-table
        class="full-height-table"
        flat
        bordered
        square
        color="primary"
        row-key="id"
        virtual-scroll
        v-model:pagination="pagination"
        :loading="loading"
        :columns="computedColumns"
        :rows="rows"
        :rows-per-page-options="[10, 25, 50]"
        @request="fetchItems"
        binary-state-sort
      >
        <template v-slot:body="props">
          <q-tr
            :props="props"
            class="cursor-pointer"
            @click="onRowClicked(props.row)"
          >
            <q-td key="datetime" :props="props" class="wrap-column">
              <div class="flex items-center q-gutter-xs">
                <q-icon name="event" />
                <div>
                  {{ formatDateTime(props.row.datetime) }}
                </div>
              </div>
              <template v-if="!$q.screen.gt.sm">
                <div v-if="props.row.category" class="text-caption text-grey-8">
                  <q-icon name="category" size="xs" />
                  {{ props.row.category.name }}
                </div>
                <div
                  v-if="props.row.description"
                  class="text-caption text-grey-8"
                >
                  <q-icon name="clarify" size="xs" />
                  {{ props.row.description }}
                </div>
                <div class="text-caption text-grey-8">
                  <q-icon name="paid" size="xs" /> Rp.
                  {{ formatNumber(props.row.amount) }}
                </div>
                <div v-if="props.row.notes" class="text-caption text-grey-8">
                  <q-icon name="notes" size="xs" /> {{ props.row.notes }}
                </div>
              </template>
            </q-td>

            <q-td key="category" :props="props">
              {{ props.row.category?.name || "-" }}
            </q-td>
            <q-td key="description" :props="props">
              <div class="wrap-column">{{ props.row.description || "-" }}</div>
            </q-td>
            <q-td key="amount" :props="props" style="text-align: right">
              {{ formatNumber(props.row.amount) }}
            </q-td>

            <q-td key="notes" :props="props">
              <div class="wrap-column">{{ props.row.notes || "-" }}</div>
            </q-td>

            <q-td key="action" :props="props">
              <div class="flex justify-end">
                <q-btn
                  icon="more_vert"
                  dense
                  flat
                  style="height: 40px; width: 30px"
                  @click.stop
                >
                  <q-menu
                    anchor="bottom right"
                    self="top right"
                    transition-show="scale"
                    transition-hide="scale"
                  >
                    <q-list style="width: 200px">
                      <q-item
                        clickable
                        v-ripple
                        v-close-popup
                        @click.stop="
                          router.get(route('app.cost.duplicate', props.row.id))
                        "
                      >
                        <q-item-section avatar>
                          <q-icon name="file_copy" />
                        </q-item-section>
                        <q-item-section> Duplikat </q-item-section>
                      </q-item>
                      <q-item
                        clickable
                        v-ripple
                        v-close-popup
                        @click.stop="
                          router.get(route('app.cost.edit', props.row.id))
                        "
                      >
                        <q-item-section avatar>
                          <q-icon name="edit" />
                        </q-item-section>
                        <q-item-section>Edit</q-item-section>
                      </q-item>
                      <q-item
                        @click.stop="deleteItem(props.row)"
                        clickable
                        v-ripple
                        v-close-popup
                        class="text-negative"
                      >
                        <q-item-section avatar>
                          <q-icon name="delete_forever" />
                        </q-item-section>
                        <q-item-section>Hapus</q-item-section>
                      </q-item>
                    </q-list>
                  </q-menu>
                </q-btn>
              </div>
            </q-td>
          </q-tr>
        </template>
      </q-table>
    </div>
  </authenticated-layout>
</template>
