<script setup>
  // Komponen ini menerima 'form' sebagai prop dari halaman induk (Create/Edit)
  defineProps({
    form: Object
  })

  // Komponen ini akan memancarkan event 'submit' saat form disubmit
  const emit = defineEmits(['submit'])
</script>

<template>
  <q-form @submit.prevent="emit('submit')">
    <q-card-section>
      <q-select
        filled
        v-model="form.type"
        :options="['pengeluaran', 'pemasukan']"
        label="Tipe Transaksi"
        :error="!!form.errors.type"
        :error-message="form.errors.type"
      />
      <q-input
        filled
        v-model.number="form.amount"
        type="number"
        label="Jumlah (Rp)"
        class="q-mt-md"
        :error="!!form.errors.amount"
        :error-message="form.errors.amount"
      />
      <q-input
        filled
        v-model="form.description"
        label="Deskripsi"
        class="q-mt-md"
        :error="!!form.errors.description"
        :error-message="form.errors.description"
      />
      <q-input
        filled
        v-model="form.category"
        label="Kategori (opsional)"
        class="q-mt-md"
        :error="!!form.errors.category"
        :error-message="form.errors.category"
      />
      <q-input filled v-model="form.created_at" mask="####-##-##" label="Tanggal" class="q-mt-md">
        <template v-slot:append>
          <q-icon name="event" class="cursor-pointer">
            <q-popup-proxy cover transition-show="scale" transition-hide="scale">
              <q-date v-model="form.created_at" mask="YYYY-MM-DD"></q-date>
            </q-popup-proxy>
          </q-icon>
        </template>
      </q-input>
    </q-card-section>
    <q-card-actions align="right">
      <slot name="actions"></slot>
    </q-card-actions>
  </q-form>
</template>
