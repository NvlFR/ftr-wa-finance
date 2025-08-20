<script setup>
  import { ref } from 'vue'
  import { router, useForm } from '@inertiajs/vue3'
  import { handleSubmit } from '@/helpers/client-req-handler'
  import { formatDateTimeForEditing } from '@/helpers/formatter'
  import LocaleNumberInput from '@/components/LocaleNumberInput.vue'
  import DateTimePicker from '@/components/DateTimePicker.vue'

  const title = `${data.id ? 'Edit' : 'Tambah'} Investasi`

  const form = useForm({
    type: 'beli',
    asset_name: '',
    asset_type: 'saham',
    quantity: null,
    price_per_unit: null,
    transaction_date: new Date().toISOString().slice(0, 10)
  })

  const submit = () => handleSubmit({ form, url: route('app.admin.investments.save') })
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>

    <template #left-button>
      <q-btn
        icon="arrow_back"
        dense
        color="grey-7"
        flat
        rounded
        @click="router.get(route('app.admin.invesments.index'))"
      />
    </template>

    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <q-form class="row" @submit.prevent="submit">
          <q-card square flat bordered class="col">
            <q-card-section class="q-pt-none">
              <DateTimePicker
                v-model="form.datetime"
                label="Tanggal"
                :error="!!form.errors.datetime"
                :disable="form.processing"
              />
              <q-select
                v-model="form.type"
                label="Tipe Transaksi"
                :options="['Beli', 'Jual']"
                emit-value
                :error="!!form.errors.type"
                :disable="form.processing"
              />
              <q-input
                v-model.trim="form.asset_name"
                label="Nama Aset "
                :disable="form.processing"
                :error="!!form.errors.asset_name"
                hide-bottom-space
              />
              <q-select
                v-model="form.asset_type"
                label="Tipe Aset"
                :options="['Saham', 'Crypto', 'Reksadana', 'Emas', 'Lainnya']"
                emit-value
                :error="!!form.errors.asset_type"
                :disable="form.processing"
              />
              <q-input
                v-model.trim="form.quantity"
                type="number"
                step="any"
                label="Jumlah Unit"
                :disable="form.processing"
                :error="!!form.errors.quantity"
                hide-bottom-space
              />

              <LocaleNumberInput
                v-model:modelValue="form.price_per_unit"
                label="Harga per Unit (Rp)"
                :disable="form.processing"
                :error="!!form.errors.price_per_unit"
                :errorMessage="form.errors.price_per_unit"
                hide-bottom-space
              />
            </q-card-section>

            <q-card-section class="q-gutter-sm">
              <q-btn
                icon="save"
                type="submit"
                label="Simpan"
                color="primary"
                :disable="form.processing"
              />
              <q-btn
                icon="cancel"
                label="Batal"
                :disable="form.processing"
                @click="router.get(route('app.admin.investments.index'))"
              />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>
  </authenticated-layout>
</template>
