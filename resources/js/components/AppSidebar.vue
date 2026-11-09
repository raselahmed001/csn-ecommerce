<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { LayoutGrid, Package, Store, Users } from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
        permission: 'view_dashboard',
    },
    {
        title: 'Shop owners',
        href: '/admin/users',
        icon: Users,
        permission: 'view_users',
        adminOnly: true,
    },
    {
        title: 'Shops',
        href: '/admin/shops',
        icon: Store,
        permission: 'view_shops',
        adminOnly: true,
    },
    {
        title: 'Catalog products',
        href: '/admin/products',
        icon: Package,
        permission: 'view_catalog_products',
        adminOnly: true,
    },
    {
        title: 'My shops',
        href: '/owner/shops',
        icon: Store,
        permission: 'view_shop_products',
        ownerOnly: true,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
