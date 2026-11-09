<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ProductController from '@/actions/App/Http/Controllers/Admin/ProductController';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ProductForm from './Form.vue';
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
import type { Paginated, Product } from '@/types';

defineProps<{
    products: Paginated<Product>;
}>();

const { can } = usePermissions();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Catalog products', href: '/admin/products' }],
    },
});

// The create / edit form slides in from the right
const sheetOpen = ref(false);
const editing = ref<Product | null>(null);

function openCreate(): void {
    editing.value = null;
    sheetOpen.value = true;
}

function openEdit(product: Product): void {
    editing.value = product;
    sheetOpen.value = true;
}

// The delete / remove confirmation popup
const deleteOpen = ref(false);
const deleting = ref<Product | null>(null);
const deleteProcessing = ref(false);

function askDelete(product: Product): void {
    deleting.value = product;
    deleteOpen.value = true;
}

function confirmDelete(): void {
    if (!deleting.value) {
        return;
    }

    router.delete(ProductController.destroy.url(deleting.value.id), {
        preserveScroll: true,
        onStart: () => (deleteProcessing.value = true),
        onFinish: () => {
            deleteProcessing.value = false;
            deleteOpen.value = false;
        },
    });
}
</script>

<template>
    <Head title="Catalog products" />

    <div class="space-y-6 p-6">
        <div class="flex items-start justify-between gap-4">
            <Heading
                title="Catalog products"
                description="Manage the shared product catalog. Removed products can no longer be added to shops."
            />
            <Button v-if="can('create_catalog_products')" @click="openCreate">
                Create product
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
                        <TableCell class="space-x-2 text-right">
                            <Button
                                v-if="can('edit_catalog_products')"
                                size="sm"
                                variant="outline"
                                @click="openEdit(product)"
                            >
                                Edit
                            </Button>
                            <Button
                                v-if="can('delete_catalog_products')"
                                size="sm"
                                variant="destructive"
                                @click="askDelete(product)"
                            >
                                Delete
                            </Button>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="products.data.length === 0">
                        <TableCell
                            colspan="2"
                            class="py-8 text-center text-muted-foreground"
                        >
                            No catalog products found.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <Pagination :links="products.links" />
        <ProductForm v-model:open="sheetOpen" :product="editing" />
        <ConfirmDialog
            v-model:open="deleteOpen"
            :title="'Remove product from the catalog?'"
            :description="`${deleting?.name} will be removed from the shared catalog and can no longer be added to shops.`"
            confirm-label="Remove"
            :processing="deleteProcessing"
            @confirm="confirmDelete"
        />
    </div>
</template>
