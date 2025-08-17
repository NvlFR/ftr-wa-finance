<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import { ref } from 'vue';

const props = defineProps({
    savings: Array,
});

const $q = useQuasar();
const showAddFundsModal = ref(false);
const selectedSaving = ref(null);

const fundsForm = useForm({
    amount: null,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const getProgress = (current, target) => {
    if (target <= 0) return 1;
    const progress = current / target;
    return progress > 1 ? 1 : progress;
};

const openAddFundsModal = (saving) => {
    selectedSaving.value = saving;
    fundsForm.reset();
    showAddFundsModal.value = true;
};

const submitAddFunds = () => {
    fundsForm.post(route('savings.addFunds', selectedSaving.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showAddFundsModal.value = false;
            $q.notify({ color: 'positive', message: 'Dana berhasil ditambahkan!' });
        },
    });
};

const deleteGoal = (saving) => {
    $q.dialog({
        title: 'Konfirmasi Hapus',
        message: `Apakah Anda yakin ingin menghapus tujuan tabungan '${saving.goal_name}'?`,
        cancel: true,
        persistent: true,
        ok: { label: 'Hapus', color: 'negative' }
    }).onOk(() => {
        router.delete(route('savings.destroy', saving.id), {
             preserveScroll: true,
        });
    });
};

const viewDetails = (saving) => {
        router.get(route('savings.show', saving.id));
    };
</script>

<template>
    <Head title="Tabungan & Dana Darurat" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tabungan & Dana Darurat</h2>
        </template>

        <q-page class="q-pa-md">
            <div class="q-mb-md">
                <Link :href="route('savings.create')">
                    <q-btn color="primary" icon="add" label="Buat Tujuan Baru" />
                </Link>
            </div>

            <div v-if="savings.length > 0" class="row q-col-gutter-md">
                <div v-for="saving in savings" :key="saving.id" class="col-12 col-sm-6 col-md-4">
                    <q-card class="cursor-pointer" @click="viewDetails(saving)">
                        <q-card-section>
                            <div class="row items-center no-wrap">
                                <div class="col">
                                    <div class="text-h6">
                                        <q-icon :name="saving.is_emergency_fund ? 'emergency' : 'savings'" class="q-mr-sm" />
                                        {{ saving.goal_name }}
                                    </div>
                                    <q-badge v-if="saving.is_emergency_fund" color="red-6" class="q-mt-xs">
                                        Dana Darurat
                                    </q-badge>
                                </div>
                                <div class="col-auto">
                                    <q-btn color="grey-7" round flat icon="more_vert">
                                        <q-menu cover auto-close>
                                            <q-list>
                                                <q-item clickable @click="openAddFundsModal(saving)">
                                                    <q-item-section avatar><q-icon name="add_card" /></q-item-section>
                                                    <q-item-section>Tambah Dana</q-item-section>
                                                </q-item>
                                                <q-item clickable v-if="!saving.is_emergency_fund" :href="route('savings.edit', saving.id)">
                                                    <q-item-section avatar><q-icon name="edit" /></q-item-section>
                                                    <q-item-section>Edit</q-item-section>
                                                </q-item>
                                                <q-item clickable v-if="!saving.is_emergency_fund" @click="deleteGoal(saving)">
                                                    <q-item-section avatar><q-icon name="delete" /></q-item-section>
                                                    <q-item-section>Hapus</q-item-section>
                                                </q-item>
                                            </q-list>
                                        </q-menu>
                                    </q-btn>
                                </div>
                            </div>
                        </q-card-section>

                        <q-card-section class="q-pt-none">
                            <div class="text-subtitle2 text-grey-8">
                                {{ formatCurrency(saving.current_amount) }} / {{ formatCurrency(saving.target_amount) }}
                            </div>
                            <q-linear-progress rounded size="25px" :value="getProgress(saving.current_amount, saving.target_amount)" :color="saving.is_emergency_fund ? 'red-6' : 'primary'" class="q-mt-sm">
                                <div class="absolute-full flex flex-center">
                                    <q-badge color="white" text-color="black">
                                        {{ (getProgress(saving.current_amount, saving.target_amount) * 100).toFixed(1) }}%
                                    </q-badge>
                                </div>
                            </q-linear-progress>
                        </q-card-section>
                    </q-card>
                </div>
            </div>
            <div v-else class="text-center text-grey q-mt-xl">
                 <q-icon name="account_balance_wallet" size="xl" />
                <p class="q-mt-md">Anda belum memiliki tujuan tabungan.</p>
            </div>
        </q-page>
    </AuthenticatedLayout>

    <q-dialog v-model="showAddFundsModal">
        <q-card style="width: 400px">
            <q-card-section>
                <div class="text-h6">Tambah Dana ke '{{ selectedSaving?.goal_name }}'</div>
            </q-card-section>
            <q-form @submit.prevent="submitAddFunds">
                <q-card-section class="q-pt-none">
                    <q-input
                        v-model.number="fundsForm.amount"
                        type="number"
                        label="Jumlah (Rp)"
                        autofocus
                        :error="!!fundsForm.errors.amount"
                        :error-message="fundsForm.errors.amount"
                    />
                </q-card-section>
                <q-card-actions align="right">
                    <q-btn flat label="Batal" v-close-popup />
                    <q-btn label="Tambah" color="primary" type="submit" :loading="fundsForm.processing" />
                </q-card-actions>
            </q-form>
        </q-card>
    </q-dialog>

</template>
