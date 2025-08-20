<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import { useFormatter } from '@/Composables/useFormatter';

const props = defineProps({
    budgets: Array,
    spendings: Object,
});

const $q = useQuasar();
const { formatCurrency } = useFormatter();

const deleteBudget = (budget) => {
    $q.dialog({
        title: 'Konfirmasi',
        message: `Hapus budget untuk kategori '${budget.category}'?`,
        cancel: true,
    }).onOk(() => {
        router.delete(route('budgets.destroy', budget.id), { preserveScroll: true });
    });
};
</script>

<template>
    <Head title="Budget Bulanan" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Budget Bulan Ini</h2>
        </template>

        <q-page class="q-pa-md">
            <div class="q-mb-md">
                <Link :href="route('budgets.create')">
                    <q-btn color="primary" icon="add" label="Atur Budget Baru" />
                </Link>
            </div>

            <q-card>
                <q-list separator>
                    <q-item v-for="budget in budgets" :key="budget.id">
                        <q-item-section>
                            <q-item-label class="text-weight-medium text-capitalize">{{ budget.category }}</q-item-label>
                            <q-item-label caption>
                                Terpakai {{ formatCurrency(spendings[budget.category.toLowerCase()] || 0) }} dari {{ formatCurrency(budget.amount) }}
                            </q-item-label>
                            <q-linear-progress
                                :value="(spendings[budget.category.toLowerCase()] || 0) / budget.amount"
                                color="primary"
                                class="q-mt-sm"
                                rounded
                            />
                        </q-item-section>

                        <q-item-section side>
                           <div class="q-gutter-xs">
                                <Link :href="route('budgets.edit', budget.id)">
                                    <q-btn flat round color="primary" icon="edit" size="sm" />
                                </Link>
                                <q-btn flat round color="negative" icon="delete" size="sm" @click="deleteBudget(budget)" />
                           </div>
                        </q-item-section>
                    </q-item>

                    <q-item v-if="!budgets.length">
                        <q-item-section class="text-center text-grey q-py-lg">
                             <q-icon name="assessment" size="lg" />
                             <div>Belum ada budget yang diatur.</div>
                        </q-item-section>
                    </q-item>
                </q-list>
            </q-card>
        </q-page>
    </AuthenticatedLayout>
</template>
