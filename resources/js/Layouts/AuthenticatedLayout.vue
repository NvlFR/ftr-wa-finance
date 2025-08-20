<script setup>
  import { ref, computed, defineComponent, onMounted, watch } from 'vue'
  import { useQuasar } from 'quasar'
  import { router, usePage } from '@inertiajs/vue3'

  defineComponent({
    name: 'AuthenticatedLayout'
  })

  const LEFT_DRAWER_STORAGE_KEY = 'ftr.layout.left-drawer-open'
  const $q = useQuasar()
  const page = usePage()
  const leftDrawerOpen = ref(JSON.parse(localStorage.getItem(LEFT_DRAWER_STORAGE_KEY)))
  const isDropdownOpen = ref(false)
  const toggleLeftDrawer = () => (leftDrawerOpen.value = !leftDrawerOpen.value)

  onMounted(() => {
    if ($q.screen.lt.md) {
      leftDrawerOpen.value = false
    }
  })

  const dropdownIcon = computed(() => {
    return isDropdownOpen.value === true ? 'close' : 'menu'
  })
</script>

<template>
  <q-layout view="lHh LpR lFf">
    <q-header>
      <q-toolbar class="bg-grey-1 text-black toolbar-scrolled">
        <q-btn-dropdown
          v-model="isDropdownOpen"
          dense
          flat
          push
          no-caps
          content-style="max-height: 100vh"
          :dropdown-icon="dropdownIcon"
        >
          <q-list id="main-nav">
            <q-item
              clickable
              v-ripple
              :active="$page.url === '/dashboard'"
              @click="router.get(route('dashboard'))"
            >
              <q-item-section avatar><q-icon name="dashboard" /></q-item-section>
              <q-item-section><q-item-label>Dashboard</q-item-label></q-item-section>
            </q-item>
            <q-item
              clickable
              v-ripple
              :active="$page.url.startsWith('/investments')"
              @click="router.get(route('investments.index'))"
            >
              <q-item-section avatar><q-icon name="trending_up" /></q-item-section>
              <q-item-section><q-item-label>Investasi</q-item-label></q-item-section>
            </q-item>
            <q-separator />

            <q-expansion-item icon="credit_score" label="Budgeting" :header-inset-level="0.1" group="nav-group">
              <q-item
                class="subnav"
                clickable
                v-ripple
                :active="$page.url.startsWith('/transactions')"
                @click="router.get(route('transactions.index'))"
              >
                <q-item-section avatar><q-icon name="request_quote" /></q-item-section>
                <q-item-section><q-item-label>Transaksi</q-item-label></q-item-section>
              </q-item>
              <q-item
                class="subnav"
                clickable
                v-ripple
                :active="$page.url.startsWith('/savings')"
                @click="router.get(route('savings.index'))"
              >
                <q-item-section avatar><q-icon name="savings" /></q-item-section>
                <q-item-section><q-item-label>Tabungan</q-item-label></q-item-section>
              </q-item>
              <q-item
                class="subnav"
                clickable
                v-ripple
                :active="$page.url.startsWith('/budgets')"
                @click="router.get(route('budgets.index'))"
              >
                <q-item-section avatar><q-icon name="assessment" /></q-item-section>
                <q-item-section><q-item-label>Budget</q-item-label></q-item-section>
              </q-item>
              <q-item
                class="subnav"
                clickable
                v-ripple
                :active="$page.url.startsWith('/recurring-transactions')"
                @click="router.get(route('recurring-transactions.index'))"
              >
                <q-item-section avatar><q-icon name="event_repeat" /></q-item-section>
                <q-item-section><q-item-label>Transaksi Berulang</q-item-label></q-item-section>
              </q-item>
            </q-expansion-item>

            <q-expansion-item icon="sync_alt" label="Tagihan" :header-inset-level="0.1" group="nav-group">
              <q-item
                class="subnav"
                clickable
                v-ripple
                :active="$page.url.startsWith('/debts')"
                @click="router.get(route('debts.index'))"
              >
                <q-item-section avatar><q-icon name="receipt_long" /></q-item-section>
                <q-item-section><q-item-label>Transaksi</q-item-label></q-item-section>
              </q-item>
              <q-item
                class="subnav"
                clickable
                v-ripple
                :active="$page.url.startsWith('/parties')"
                @click="router.get(route('parties.index'))"
              >
                <q-item-section avatar><q-icon name="groups" /></q-item-section>
                <q-item-section><q-item-label>Pihak</q-item-label></q-item-section>
              </q-item>
            </q-expansion-item>
            <q-separator />

            <div class="text-grey-6 q-pa-sm">&copy; 2025 - {{ page.props.appName }}</div>
          </q-list>
        </q-btn-dropdown>

        <q-toolbar-title>
          <slot name="header"></slot>
        </q-toolbar-title>

        <q-space />

        <q-btn-dropdown stretch flat no-caps>
          <template v-slot:label>
            <div class="row items-center no-wrap">
              <q-avatar size="28px" class="q-mr-sm">
                <img src="https://cdn.quasar.dev/img/boy-avatar.png" />
              </q-avatar>
              <div class="text-weight-bold">
                {{ $page.props.auth.user.name }}
              </div>
            </div>
          </template>
          <q-list style="min-width: 200px">
            <q-item :href="route('profile.edit')" clickable v-close-popup>
              <q-item-section avatar><q-icon name="person" /></q-item-section>
              <q-item-section><q-item-label>Profil</q-item-label></q-item-section>
            </q-item>
            <q-separator />
            <q-item @click="router.post(route('logout'))" clickable v-close-popup>
              <q-item-section avatar><q-icon name="logout" /></q-item-section>
              <q-item-section><q-item-label>Logout</q-item-label></q-item-section>
            </q-item>
          </q-list>
        </q-btn-dropdown>
      </q-toolbar>
    </q-header>

    <q-page-container class="bg-grey-2">
      <slot></slot>
    </q-page-container>
  </q-layout>
</template>
<style>
  .profile-btn span.block {
    text-align: left !important;
    width: 100% !important;
    margin-left: 10px !important;
  }
</style>
<style scoped>
  .q-toolbar {
    border-bottom: 1px solid transparent;
    /* Optional border line */
  }

  .toolbar-scrolled {
    box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);
    /* Add shadow */
    border-bottom: 1px solid #ddd;
    /* Optional border line */
  }

  .profile-btn-active {
    background-color: #ddd !important;
  }

  #profile-btn-popup .q-item--active {
    color: inherit !important;
  }
  #main-nav .q-item:hover {
    background-color: #e9e9e9;
  }
</style>
