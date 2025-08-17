<script setup>
defineProps({
    form: Object,
});
const emit = defineEmits(['submit']);
</script>

<template>
    <q-form @submit.prevent="emit('submit')">
        <q-card-section class="q-gutter-md">
            <q-input
                filled
                v-model="form.goal_name"
                label="Nama Tujuan Tabungan"
                hint="Contoh: Liburan ke Jepang, Dana Pendidikan"
                :error="!!form.errors.goal_name"
                :error-message="form.errors.goal_name"
            />
            <q-input
                filled
                v-model.number="form.target_amount"
                type="number"
                label="Target Jumlah (Rp)"
                :error="!!form.errors.target_amount"
                :error-message="form.errors.target_amount"
            />
        </q-card-section>
        <q-card-actions align="right">
            <slot name="actions"></slot>
        </q-card-actions>
    </q-form>
</template>
