<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';

const leftDrawerOpen = ref(false);

const toggleLeftDrawer = () => {
    leftDrawerOpen.value = !leftDrawerOpen.value;
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <q-layout view="hHh LpR fFf">

        <q-header elevated class="bg-primary text-white">
            <q-toolbar>
                <q-btn dense flat round icon="menu" @click="toggleLeftDrawer" />
                <q-toolbar-title>
                    <q-avatar>
                        <img src="https://cdn.quasar.dev/logo-v2/svg/logo-mono-white.svg">
                    </q-avatar>
                    {{ $page.props.appName || 'FinBot' }}
                </q-toolbar-title>

                <q-tabs shrink stretch>
                    <q-route-tab to="/dashboard" label="Dashboard" />
                    </q-tabs>

                <q-space />

                <q-btn-dropdown stretch flat :label="$page.props.auth.user.name">
                    <q-list>
                        <q-item :href="route('profile.edit')" clickable v-close-popup tabindex="0">
                            <q-item-section>
                                <q-item-label>Profile</q-item-label>
                            </q-item-section>
                        </q-item>
                        <q-separator />
                        <q-item @click="logout" clickable v-close-popup tabindex="0">
                            <q-item-section>
                                <q-item-label>Log Out</q-item-label>
                            </q-item-section>
                        </q-item>
                    </q-list>
                </q-btn-dropdown>
            </q-toolbar>
        </q-header>

        <q-drawer show-if-above v-model="leftDrawerOpen" side="left" bordered>
            <q-list>
                <q-item-label header>Menu Navigasi</q-item-label>
                <q-item clickable :href="route('dashboard')" :active="route().current('dashboard')">
                    <q-item-section avatar><q-icon name="dashboard" /></q-item-section>
                    <q-item-section><q-item-label>Dashboard</q-item-label></q-item-section>
                </q-item>
                </q-list>
        </q-drawer>

        <q-page-container>
            <slot />
        </q-page-container>

    </q-layout>
</template>
