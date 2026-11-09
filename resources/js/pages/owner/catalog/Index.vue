<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import ShopProductForm from '../products/Form.vue';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
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
import type { CatalogProduct, OwnerShop, Paginated } from '@/types';

const props = defineProps<{
    shop: Pick<OwnerShop, 'id' | 'name'>;
    products: Paginated<CatalogProduct>;
}>();

const { can } = usePermissions();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'My shops', href: '/owner/shops' },
            { title: 'Shared catalog', href: '#' },
        ],
    },
});

// The add / create form slides in from the right.
// `selected` = a catalog product being added; null = a brand new product.
const sheetOpen = ref(false);
const selected = ref<CatalogProduct | null>(null);

function openAdd(product: CatalogProduct): void {
    selected.value = product;
    sheetOpen.value = true;
}

function openCreate(): void {
    selected.value = null;
    sheetOpen.value = true;
}
</script>

<template>
    <Head title="Shared catalog" />

    <div class="space-y-6 p-6">
        <div class="flex items-start justify-between gap-4">
            <Heading
                :title="`Shared catalog: ${props.shop.name}`"
                description="Products available to add to your shop. Products already in your shop are hidden."
            />
            <Button
                v-if="can('add_shop_products')"
                variant="outline"
                @click="openCreate"
            >
                Create new product
            </Button>
        </div>

        <div class="rounded-xl border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Product</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="product in products.data"
                        :key="product.id"
                    >
                        <TableCell>
                            <div class="font-medium">{{ product.name }}</div>
                            <div class="text-xs text-muted-foreground">
                                SKU: {{ product.sku }}
                            </div>
                        </TableCell>
                        <TableCell class="text-right">
                            <Button
                                v-if="can('add_shop_products')"
                                size="sm"
                                @click="openAdd(product)"
                            >
                                Add to shop
                            </Button>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="products.data.length === 0">
                        <TableCell
                            colspan="2"
                            class="py-8 text-center text-muted-foreground"
                        >
                            No more catalog products to add.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <Pagination :links="products.links" />
        <ShopProductForm
            v-model:open="sheetOpen"
            :shop="props.shop"
            :product="selected"
        />
    </div>
</template>
