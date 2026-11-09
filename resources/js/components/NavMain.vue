<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { Auth, NavItem } from '@/types';

const props = defineProps<{
    items: NavItem[];
}>();

const page = usePage<{ auth: Auth }>();
const filteredItems = computed(() => {
    return props.items.filter((item) => {
        if (!item.permission) {
            return (
                (!item.adminOnly || page.props.auth.is_admin) &&
                (!item.ownerOnly || page.props.auth.is_shop_owner)
            );
        }

        return (
            page.props.auth.permissions.includes(item.permission) &&
            (!item.adminOnly || page.props.auth.is_admin) &&
            (!item.ownerOnly || page.props.auth.is_shop_owner)
        );
    });
});

const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in filteredItems" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="isCurrentUrl(item.href)"
                    :tooltip="item.title"
                >
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
