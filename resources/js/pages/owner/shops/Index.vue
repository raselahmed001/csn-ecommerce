<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import CatalogController from '@/actions/App/Http/Controllers/ShopOwner/CatalogController';
import ShopProductController from '@/actions/App/Http/Controllers/ShopOwner/ShopProductController';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { usePermissions } from '@/composables/usePermissions';
import type { OwnerShop } from '@/types';

defineProps<{
    shops: OwnerShop[];
}>();

const { can } = usePermissions();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'My shops', href: '/owner/shops' }],
    },
});
</script>

<template>
    <Head title="My shops" />

    <div class="space-y-6 p-6">
        <Heading
            title="My shops"
            description="Manage the products listed in each of your shops."
        />

        <div class="rounded-xl border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Shop</TableHead>
                        <TableHead>Listed products</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="shop in shops" :key="shop.id">
                        <TableCell>
                            <div class="font-medium">{{ shop.name }}</div>
                            <div class="text-xs text-muted-foreground">
                                {{ shop.slug }}
                            </div>
                        </TableCell>
                        <TableCell>{{ shop.shop_products_count }}</TableCell>
                        <TableCell class="space-x-2 text-right">
                            <Button
                                v-if="can('view_shop_products')"
                                as-child
                                size="sm"
                                variant="outline"
                            >
                                <Link
                                    :href="
                                        ShopProductController.index.url(shop.id)
                                    "
                                >
                                    My products
                                </Link>
                            </Button>
                            <Button
                                v-if="can('view_shop_products')"
                                as-child
                                size="sm"
                            >
                                <Link
                                    :href="CatalogController.index.url(shop.id)"
                                >
                                    Browse catalog
                                </Link>
                            </Button>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="shops.length === 0">
                        <TableCell
                            colspan="3"
                            class="py-8 text-center text-muted-foreground"
                        >
                            No shop has been assigned to you yet. Please contact
                            the administrator.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
