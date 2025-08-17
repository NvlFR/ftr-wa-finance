<script setup>
import { defineComponent, onMounted, ref, watch, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { useQuasar } from "quasar";
import { usePageStorage } from "@/Composables/usePageStorage";

defineComponent({
  name: "AuthenticatedLayout",
});

const storage = usePageStorage("auth-layout");
const $q = useQuasar();
const page = usePage();

const leftDrawerOpen = ref(storage.get("left-drawer-open", $q.screen.gt.sm));

watch(leftDrawerOpen, (newValue) => {
  storage.set("left-drawer-open", newValue);
});

onMounted(() => {
  if ($q.screen.lt.md) {
    leftDrawerOpen.value = false;
  }
});
</script>

<template>
  <q-layout view="hHh LpR fFf" class="bg-grey-2">
    <q-header elevated class="bg-white text-grey-8">
      <q-toolbar>
        <q-btn
          dense
          flat
          round
          icon="menu"
          @click="leftDrawerOpen = !leftDrawerOpen"
        />

        <q-toolbar-title>
          <slot name="title">{{ page.props.appName }}</slot>
        </q-toolbar-title>

        <slot name="right-button"></slot>
      </q-toolbar>
      <slot name="header"></slot>
    </q-header>

    <q-drawer v-model="leftDrawerOpen" show-if-above bordered :width="280">
      <q-scroll-area class="fit">
        <q-list>
          <q-item class="q-py-md">
            <q-item-section avatar>
              <q-avatar color="primary" text-color="white" icon="person" />
            </q-item-section>
            <q-item-section>
              <q-item-label class="text-bold">{{ page.props.auth.user.name }}</q-item-label>
              <q-item-label caption>{{ page.props.auth.user.email }}</q-item-label>
            </q-item-section>
          </q-item>

          <q-separator />

          <q-item
            clickable
            v-ripple
            :active="route().current('dashboard')"
            @click="router.get(route('dashboard'))"
          >
            <q-item-section avatar>
              <q-icon name="dashboard" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Dashboard</q-item-label>
            </q-item-section>
          </q-item>

          <q-item
            clickable
            v-ripple
            :active="route().current('transactions.index')"
            @click="router.get(route('transactions.index'))"
          >
            <q-item-section avatar>
              <q-icon name="request_quote" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Transaksi</q-item-label>
            </q-item-section>
          </q-item>

          <q-separator class="q-my-sm" />

          <q-item
            clickable
            v-ripple
            :active="route().current('investments.index')"
            @click="router.get(route('investments.index'))"
          >
            <q-item-section avatar>
              <q-icon name="trending_up" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Investasi</q-item-label>
            </q-item-section>
          </q-item>

          <q-item
            clickable
            v-ripple
            :active="route().current('savings.index')"
            @click="router.get(route('savings.index'))"
          >
            <q-item-section avatar>
              <q-icon name="savings" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Tabungan</q-item-label>
            </q-item-section>
          </q-item>

          <q-item
            clickable
            v-ripple
            :active="route().current('debts.index')"
            @click="router.get(route('debts.index'))"
          >
            <q-item-section avatar>
              <q-icon name="sync_alt" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Hutang Piutang</q-item-label>
            </q-item-section>
          </q-item>

          <q-separator class="q-my-sm" />

          <q-item
            clickable
            v-ripple
            :active="route().current('profile.edit')"
            @click="router.get(route('profile.edit'))"
          >
            <q-item-section avatar>
              <q-icon name="manage_accounts" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Profil Saya</q-item-label>
            </q-item-section>
          </q-item>

        </q-list>

        <div class="absolute-bottom q-pa-md text-grey-6">
            <q-separator class="q-mb-md" />
             <q-item
                clickable
                v-ripple
                @click="router.post(route('logout'))"
              >
                <q-item-section avatar>
                  <q-icon name="logout" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>Logout</q-item-label>
                </q-item-section>
              </q-item>
          <div class="text-center q-mt-md">
             &copy; 2025 - {{ page.props.appName }} v{{ page.props.appVersion }}
          </div>
        </div>
      </q-scroll-area>
    </q-drawer>

    <q-page-container>
      <q-page padding>
        <slot></slot>
      </q-page>
    </q-page-container>

    <slot name="footer"></slot>
  </q-layout>
</template>

<style scoped>
.q-item.q-router-link--active,
.q-item--active {
  background-color: #eef2ff;
  color: #4f46e5;
}
</style>
