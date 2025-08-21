<script setup>
  import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
  import { Head, useForm } from '@inertiajs/vue3'
  import { computed } from 'vue'

  const props = defineProps({
    investment: {
      type: Object,
      default: null // Jika null, berarti mode 'Create'
    }
  })

  const isEditMode = computed(() => !!props.investment)
  const title = computed(() =>
    isEditMode.value ? 'Edit Transaksi Investasi' : 'Catat Transaksi Investasi Baru'
  )

  const form = useForm({
    type: props.investment?.type || 'beli',
    asset_name: props.investment?.asset_name || '',
    asset_type: props.investment?.asset_type || 'saham',
    quantity: props.investment?.quantity || null,
    price_per_unit: props.investment?.price_per_unit || null,
    transaction_date:
      props.investment?.transaction_date.slice(0, 10) || new Date().toISOString().slice(0, 10)
  })

  const submit = () => {
    if (isEditMode.value) {
      form.put(route('investments.update', props.investment.id))
    } else {
      form.post(route('investments.store'))
    }
  }
</script>

<template>
  <Head :title="title" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ title }}</h2>
    </template>

    <q-page class="q-pa-md">
      <q-card style="max-width: 600px" class="q-mx-auto">
        <q-form @submit.prevent="submit">
          <q-card-section class="q-gutter-md">
            <q-select
              filled
              v-model="form.type"
              :options="['beli', 'jual']"
              label="Tipe Transaksi"
            />
            <q-input
              filled
              v-model="form.asset_name"
              label="Nama Aset (Contoh: Saham BBCA, Bitcoin)"
              :error="!!form.errors.asset_name"
              :error-message="form.errors.asset_name"
            />
            <q-select
              filled
              v-model="form.asset_type"
              :options="['saham', 'crypto', 'reksadana', 'emas', 'lainnya']"
              label="Tipe Aset"
            />
            <q-input
              filled
              v-model.number="form.quantity"
              type="number"
              step="any"
              label="Jumlah Unit/Lembar/Gram"
              :error="!!form.errors.quantity"
              :error-message="form.errors.quantity"
            />
            <q-input
              filled
              v-model.number="form.price_per_unit"
              type="number"
              step="any"
              label="Harga per Unit (Rp)"
              :error="!!form.errors.price_per_unit"
              :error-message="form.errors.price_per_unit"
            />
            <q-input
              filled
              v-model="form.transaction_date"
              mask="####-##-##"
              label="Tanggal Transaksi"
            >
              <template v-slot:append>
                <q-icon name="event" class="cursor-pointer">
                  <q-popup-proxy cover>
                    <q-date v-model="form.transaction_date" mask="YYYY-MM-DD" />
                  </q-popup-proxy>
                </q-icon>
              </template>
            </q-input>
          </q-card-section>
          <q-card-actions align="right" class="q-pa-md">
            <q-btn :href="route('investments.index')" flat label="Batal" />
            <q-btn
              :label="isEditMode ? 'Update' : 'Simpan'"
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
