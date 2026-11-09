<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import CatalogController from '@/actions/App/Http/Controllers/ShopOwner/CatalogController';
import ShopProductController from '@/actions/App/Http/Controllers/ShopOwner/ShopProductController';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ShopProductForm from './Form.vue';
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
import type { OwnerShop, Paginated, ShopListing } from '@/types';

const props = defineProps<{
    shop: Pick<OwnerShop, 'id' | 'name'>;
    listings: Paginated<ShopListing>;
}>();

const { can } = usePermissions();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'My shops', href: '/owner/shops' },
            { title: 'My products', href: '#' },
        ],
    },
});

// Use the shop-level name if set, otherwise the Global Product's name
const displayName = (listing: ShopListing): string =>
    listing.name || listing.product?.name || '';

// The create / edit form slides in from the right
const sheetOpen = ref(false);
const editing = ref<ShopListing | null>(null);

const sheetTitle = computed(() =>
    editing.value ? 'Edit shop product' : 'Create new product',
);

function openCreate(): void {
    editing.value = null;
    sheetOpen.value = true;
}

function openEdit(listing: ShopListing): void {
    editing.value = listing;
    sheetOpen.value = true;
}

// The delete / remove confirmation popup
const deleteOpen = ref(false);
const deleting = ref<ShopListing | null>(null);
const deleteProcessing = ref(false);

function askDelete(listing: ShopListing): void {
    deleting.value = listing;
    deleteOpen.value = true;
}

function confirmDelete(): void {
    if (!deleting.value) {
        return;
    }

    router.delete(
        ShopProductController.destroy.url({
            shop: props.shop.id,
            shopProduct: deleting.value.id,
        }),
        {
            preserveScroll: true,
            onStart: () => (deleteProcessing.value = true),
            onFinish: () => {
                deleteProcessing.value = false;
                deleteOpen.value = false;
            },
        },
    );
}
</script>

<template>
    <Head title="My products" />

    <div class="space-y-6 p-6">
        <div class="flex items-start justify-between gap-4">
            <Heading
                :title="`My products: ${props.shop.name}`"
                description="Set the price, details and thumbnail for products in this shop."
            />
            <div class="flex gap-2">
                <Button
                    v-if="can('add_shop_products')"
                    variant="outline"
                    @click="openCreate"
                >
                    Create new product
                </Button>
                <Button v-if="can('view_shop_products')" as-child>
                    <Link :href="CatalogController.index.url(props.shop.id)">
                        Browse catalog
                    </Link>
                </Button>
            </div>
        </div>

        <p
            v-if="$page.props.errors.listing"
            class="rounded-md border border-destructive/50 p-3 text-sm text-destructive"
        >
            {{ $page.props.errors.listing }}
        </p>

        <div class="rounded-xl border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Product</TableHead>
                        <TableHead>Price</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="listing in listings.data"
                        :key="listing.id"
                    >
                        <TableCell>
                            <div class="flex items-center gap-3">
                                <img
                                    v-if="listing.thumbnail_url"
                                    :src="listing.thumbnail_url"
                                    :alt="displayName(listing)"
                                    class="size-10 rounded-md border object-cover"
                                />
                                <div>
                                    <div class="font-medium">
                                        {{ displayName(listing) }}
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        SKU: {{ listing.product?.sku }}
                                    </div>
                                </div>
                            </div>
                        </TableCell>
                        <TableCell>৳{{ listing.price }}</TableCell>
                        <TableCell class="space-x-2 text-right">
                            <Button
                                v-if="can('edit_shop_products')"
                                size="sm"
                                variant="outline"
                                @click="openEdit(listing)"
                            >
                                Edit
                            </Button>
                            <Button
                                v-if="can('remove_shop_products')"
                                size="sm"
                                variant="destructive"
                                @click="askDelete(listing)"
                            >
                                Remove
                            </Button>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="listings.data.length === 0">
                        <TableCell
                            colspan="3"
                            class="py-8 text-center text-muted-foreground"
                        >
                            No products in this shop yet. Browse the catalog to
                            add some.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <Pagination :links="listings.links" />
        <ShopProductForm
            v-model:open="sheetOpen"
            :shop="props.shop"
            :listing="editing"
        />
        <ConfirmDialog
            v-model:open="deleteOpen"
            :title="'Remove product from your shop?'"
            :description="`${deleting ? displayName(deleting) : ''} will be removed from your shop. It stays in the shared catalog.`"
            confirm-label="Remove"
            :processing="deleteProcessing"
            @confirm="confirmDelete"
        />
    </div>
</template>
