<script setup>
defineProps({
    form: Object,
});
const emit = defineEmits(['submit']);
const partyTypes = ['Perorangan', 'Toko', 'Perusahaan', 'Lainnya'];
</script>

<template>
    <q-form @submit.prevent="emit('submit')">
        <q-card-section class="q-gutter-md">
            <q-input
                filled
                v-model="form.name"
                label="Nama Pihak"
                hint="Nama orang atau nama toko/perusahaan"
                :error="!!form.errors.name"
                :error-message="form.errors.name"
            />
            <q-select
                filled
                v-model="form.type"
                :options="partyTypes"
                label="Tipe Pihak"
                :error="!!form.errors.type"
                :error-message="form.errors.type"
            />
            <q-input
                filled
                v-model="form.notes"
                type="textarea"
                label="Catatan (Opsional)"
                :error="!!form.errors.notes"
                :error-message="form.errors.notes"
            />
        </q-card-section>
        <q-card-actions align="right">
            <slot name="actions"></slot>
        </q-card-actions>
    </q-form>
</template>
