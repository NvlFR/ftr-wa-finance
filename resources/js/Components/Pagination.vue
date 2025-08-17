<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: Array,
});
</script>

<template>
    <div v-if="links.length > 3">
        <div class="q-pa-lg flex flex-center">
            <div class="q-gutter-sm">
                <Link
                    v-for="(link, key) in links"
                    :key="key"
                    :href="link.url"
                    :class="{ 'bg-primary text-white': link.active, 'text-grey-7': !link.url }"
                    class="q-btn q-btn-item non-selectable no-outline q-btn--flat q-btn--rectangle q-btn--actionable q-focusable q-hoverable q-px-sm"
                    v-html="link.label"
                    as="button"
                    :disabled="!link.url"
                />
            </div>
        </div>
    </div>
</template>
