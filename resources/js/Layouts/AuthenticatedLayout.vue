<script setup>
import { defineComponent, onMounted, ref, watch, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { useQuasar } from "quasar";

defineComponent({
    name: "AuthenticatedLayout",
});

const LEFT_DRAWER_STORAGE_KEY = "ftr.layout.left-drawer-open";
const $q = useQuasar();
const page = usePage();
const leftDrawerOpen = ref(
    JSON.parse(localStorage.getItem(LEFT_DRAWER_STORAGE_KEY))
);
const isDropdownOpen = ref(false);
const toggleLeftDrawer = () => (leftDrawerOpen.value = !leftDrawerOpen.value);

onMounted(() => {
    if ($q.screen.lt.md) {
        leftDrawerOpen.value = false;
    }
});
</script>

<template>
    <q-layout view="lHh LpR lFf">
        <q-header>
            <q-toolbar class="bg-grey-1 text-black toolbar-scrolled">
                <q-btn
                    v-if="!leftDrawerOpen"
                    flat
                    dense
                    aria-label="Menu"
                    @click="toggleLeftDrawer"
                >
                    <q-icon class="material-symbols-outlined"
                        >dock_to_right</q-icon
                    >
                </q-btn>
                <slot name="left-button"></slot>
                <q-toolbar-title
                    :class="{ 'q-ml-sm': leftDrawerOpen }"
                    style="font-size: 18px"
                >
                    <slot name="title">{{ page.props.appName }}</slot>
                </q-toolbar-title>
                <slot name="right-button"></slot>
            </q-toolbar>
            <slot name="header"></slot>
        </q-header>
        <q-drawer
            :breakpoint="768"
            v-model="leftDrawerOpen"
            bordered
            class="bg-grey-2"
            style="color: #444"
        >
            <div
                class="absolute-top"
                style="
                    height: 50px;
                    border-bottom: 1px solid #ddd;
                    align-items: center;
                    justify-content: center;
                "
            >
                <div
                    style="
                        width: 100%;
                        padding: 8px;
                        display: flex;
                        justify-content: space-between;
                    "
                >
                    <q-btn-dropdown
                        v-model="isDropdownOpen"
                        class="profile-btn text-bold"
                        flat
                        :label="page.props.auth.user.name"
                        style="
                            justify-content: space-between;
                            flex-grow: 1;
                            overflow: hidden;
                        "
                        :class="{ 'profile-btn-active': isDropdownOpen }"
                    >
                        <q-list id="profile-btn-popup" style="color: #444">
                            <q-item>
                                <q-avatar style="margin-left: -15px"
                                    ><q-icon name="person"
                                /></q-avatar>
                                <q-item-section>
                                    <q-item-label>
                                        <div class="text-bold">
                                            {{ page.props.auth.user.name }}
                                        </div>
                                        <div class="text-grey-8 text-caption">
                                            {{
                                                page.props.auth.user.username
                                            }}@{{ page.props.auth.user.email }}
                                        </div>
                                    </q-item-label>
                                </q-item-section>
                            </q-item>
                            <q-separator />

                            <q-item
                                clickable
                                v-close-popup
                                v-ripple
                                style="color: inherit"
                                :href="route('logout')"
                            >
                                <q-item-section>
                                    <q-item-label
                                        ><q-icon
                                            name="logout"
                                            class="q-mr-sm"
                                        />
                                        Logout</q-item-label
                                    >
                                </q-item-section>
                            </q-item>
                        </q-list>
                    </q-btn-dropdown>
                    <q-btn
                        v-if="leftDrawerOpen"
                        flat
                        dense
                        aria-label="Menu"
                        @click="toggleLeftDrawer"
                    >
                        <q-icon name="dock_to_left" />
                    </q-btn>
                </div>
            </div>
            <q-scroll-area style="height: calc(100% - 50px); margin-top: 50px">
                <q-list id="main-nav" style="margin-bottom: 50px">
                    <q-item
                        clickable
                        v-ripple
                        :active="$page.url.startsWith('/dashboard')"
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
                        :active="$page.url.startsWith('/investments')"
                        @click="router.get(route('investments.index'))"
                    >
                        <q-item-section avatar>
                            <q-icon name="trending_up" />
                        </q-item-section>
                        <q-item-section>
                            <q-item-label>Investasi</q-item-label>
                        </q-item-section>
                    </q-item>

                    <q-separator />
                    <q-expansion-item
                        icon="credit_score"
                        label="Bugeting"
                        :default-opened="
                            $page.url.startsWith('/transaction') ||
                            $page.url.startsWith('/savings') ||
                            $page.url.startsWith('/budgets')
                        "
                    >
                        <q-item
                            class="subnav"
                            clickable
                            v-ripple
                            :active="$page.url.startsWith('/transaction')"
                            @click="router.get(route('transaction.index'))"
                        >
                            <q-item-section avatar>
                                <q-icon name="request_quote" />
                            </q-item-section>
                            <q-item-section>
                                <q-item-label>Transaksi</q-item-label>
                            </q-item-section>
                        </q-item>
                        <q-item
                            class="subnav"
                            clickable
                            v-ripple
                            :active="$page.url.startsWith('/savings')"
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
                            class="subnav"
                            clickable
                            v-ripple
                            :active="$page.url.startsWith('/budgets')"
                            @click="router.get(route('budgets.index'))"
                        >
                            <q-item-section avatar>
                                <q-icon name="assessment" />
                            </q-item-section>
                            <q-item-section>
                                <q-item-label>Budget</q-item-label>
                            </q-item-section>
                        </q-item>

                        <q-item
                            class="subnav"
                            clickable
                            v-ripple
                            :active="
                                $page.url.startsWith('/recuring-transactions')
                            "
                            @click="
                                router.get(route('recuring-transactions.index'))
                            "
                        >
                            <q-item-section avatar>
                                <q-icon name="event_repeat" />
                            </q-item-section>
                            <q-item-section>
                                <q-item-label>Transaksi Berulang</q-item-label>
                            </q-item-section>
                        </q-item>
                    </q-expansion-item>
                    <q-expansion-item
                        icon="sync_alt"
                        label="Tagihan"
                        :default-opened="
                            $page.url.startsWith('/debts') ||
                            $page.url.startsWith('/parties')
                        "
                    >
                        <q-item
                            class="subnav"
                            clickable
                            v-ripple
                            :active="$page.url.startsWith('/debts')"
                            @click="router.get(route('debts.index'))"
                        >
                            <q-item-section avatar>
                                <q-icon name="receipt_long" />
                            </q-item-section>
                            <q-item-section>
                                <q-item-label>Transaksi</q-item-label>
                            </q-item-section>
                        </q-item>
                        <q-item
                            class="subnav"
                            clickable
                            v-ripple
                            :active="$page.url.startsWith('/parties')"
                            @click="router.get(route('parties.index'))"
                        >
                            <q-item-section avatar>
                                <q-icon name="groups" />
                            </q-item-section>
                            <q-item-section>
                                <q-item-label>Pihak</q-item-label>
                            </q-item-section>
                        </q-item>
                    </q-expansion-item>
                    <q-separator />
                    <div class="absolute-bottom text-grey-6 q-pa-md">
                        &copy; 2025 -
                        {{ page.props.appName + " v" + page.props.appVersion }}
                    </div>
                </q-list>
            </q-scroll-area>
        </q-drawer>
        <q-page-container class="bg-grey-1">
            <q-page>
                <slot></slot>
            </q-page>
        </q-page-container>
        <slot name="footer"></slot>
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
