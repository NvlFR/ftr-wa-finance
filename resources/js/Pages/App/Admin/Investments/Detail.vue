<script setup>
  import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
  import { Head, Link } from '@inertiajs/vue3'
  import { useFormatter } from '@/Composables/useFormatter'

  const props = defineProps({
    investment: Object
  })

  const { formatCurrency, formatDecimal } = useFormatter()

  const formatDate = value => {
    return new Date(value).toLocaleDateString('id-ID', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    })
  }
</script>

<template>
  <Head :title="'Detail Transaksi Investasi'" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center">
        <q-btn
          flat
          round
          dense
          icon="arrow_back"
          @click="$inertia.visit(route('investments.index'))"
        />
        <h2 class="font-semibold text-xl text-gray-800 leading-tight ml-4">
          Detail Transaksi Investasi
        </h2>
      </div>
    </template>

    <q-page class="q-pa-md">
      <q-card flat bordered style="max-width: 600px" class="q-mx-auto">
        <q-card-section>
          <div class="text-h6">
            {{ investment.asset_name }}
            <q-badge :color="investment.type === 'beli' ? 'positive' : 'negative'" class="q-ml-sm">
              Transaksi {{ investment.type }}
            </q-badge>
          </div>
          <div
            class="text-subtitle1 text-weight-bold"
            :class="investment.type === 'beli' ? 'text-positive' : 'text-negative'"
          >
            {{ formatCurrency(investment.total_amount) }}
          </div>
        </q-card-section>
        <q-separator />
        <q-card-section>
          <q-list separator>
            <q-item>
              <q-item-section>
                <q-item-label caption>Tipe Aset</q-item-label>
                <q-item-label>{{ investment.asset_type }}</q-item-label>
              </q-item-section>
            </q-item>
            <q-item>
              <q-item-section>
                <q-item-label caption>Jumlah Unit</q-item-label>
                <q-item-label>{{ formatDecimal(investment.quantity) }}</q-item-label>
              </q-item-section>
            </q-item>
            <q-item>
              <q-item-section>
                <q-item-label caption>Harga per Unit</q-item-label>
                <q-item-label>{{ formatCurrency(investment.price_per_unit) }}</q-item-label>
              </q-item-section>
            </q-item>
            <q-item>
              <q-item-section>
                <q-item-label caption>Tanggal Transaksi</q-item-label>
                <q-item-label>{{ formatDate(investment.transaction_date) }}</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
        <q-separator />
        <q-card-actions>
          <Link :href="route('investments.edit', investment.id)">
            <q-btn flat color="primary">Edit</q-btn>
          </Link>
        </q-card-actions>
      </q-card>
    </q-page>
  </AuthenticatedLayout>
</template>
