<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import type { PaginationLink } from '@/types';

defineProps<{
    links: PaginationLink[];
}>();

const cleanLabel = (label: string): string =>
    label.replace(/&laquo;|&raquo;/g, '').trim();
</script>

<template>
    <nav
        v-if="links.length > 3"
        class="flex flex-wrap gap-1"
        aria-label="Pagination"
    >
        <template v-for="link in links" :key="link.label">
            <Button
                v-if="link.url"
                as-child
                size="sm"
                :variant="link.active ? 'default' : 'outline'"
            >
                <Link :href="link.url" preserve-scroll>
                    {{ cleanLabel(link.label) }}
                </Link>
            </Button>
            <Button v-else size="sm" variant="outline" disabled>
                {{ cleanLabel(link.label) }}
            </Button>
        </template>
    </nav>
</template>
