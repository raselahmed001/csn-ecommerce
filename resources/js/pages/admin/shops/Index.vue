<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ShopController from '@/actions/App/Http/Controllers/Admin/ShopController';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import ShopForm from './Form.vue';
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
import type { OwnerOption, Paginated, Shop } from '@/types';

defineProps<{
    shops: Paginated<Shop>;
    owners: OwnerOption[];
}>();

const { can } = usePermissions();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Shops', href: '/admin/shops' }],
    },
});

// The create / edit form slides in from the right
const sheetOpen = ref(false);
const editing = ref<Shop | null>(null);

function openCreate(): void {
    editing.value = null;
    sheetOpen.value = true;
}

function openEdit(shop: Shop): void {
    editing.value = shop;
    sheetOpen.value = true;
}

// The delete / remove confirmation popup
const deleteOpen = ref(false);
const deleting = ref<Shop | null>(null);
const deleteProcessing = ref(false);

function askDelete(shop: Shop): void {
    deleting.value = shop;
    deleteOpen.value = true;
}

function confirmDelete(): void {
    if (!deleting.value) {
        return;
    }

    router.delete(ShopController.destroy.url(deleting.value.id), {
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
    <Head title="Shops" />

    <div class="space-y-6 p-6">
        <div class="flex items-start justify-between gap-4">
            <Heading
                title="Shops"
                description="Create shops and assign them to shop owner accounts."
            />
            <Button v-if="can('create_shops')" @click="openCreate">
                Create shop
            </Button>
        </div>

        <p
            v-if="$page.props.errors.shop"
            class="rounded-md border border-destructive/50 p-3 text-sm text-destructive"
        >
            {{ $page.props.errors.shop }}
        </p>

        <div class="rounded-xl border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Shop</TableHead>
                        <TableHead>Owner</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="shop in shops.data" :key="shop.id">
                        <TableCell>
                            <div class="font-medium">{{ shop.name }}</div>
                            <div class="text-xs text-muted-foreground">
                                {{ shop.slug }}
                            </div>
                        </TableCell>
                        <TableCell>
                            <div>{{ shop.owner?.name }}</div>
                            <div class="text-xs text-muted-foreground">
                                {{ shop.owner?.email }}
                            </div>
                        </TableCell>
                        <TableCell class="space-x-2 text-right">
                            <Button
                                v-if="can('edit_shops')"
                                size="sm"
                                variant="outline"
                                @click="openEdit(shop)"
                            >
                                Edit
                            </Button>
                            <Button
                                v-if="can('delete_shops')"
                                size="sm"
                                variant="destructive"
                                @click="askDelete(shop)"
                            >
                                Delete
                            </Button>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="shops.data.length === 0">
                        <TableCell
                            colspan="3"
                            class="py-8 text-center text-muted-foreground"
                        >
                            No shops found.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <Pagination :links="shops.links" />
        <ShopForm v-model:open="sheetOpen" :shop="editing" :owners="owners" />
        <ConfirmDialog
            v-model:open="deleteOpen"
            :title="'Delete shop?'"
            :description="`${deleting?.name} and all of its product listings will be deleted. This cannot be undone.`"
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="confirmDelete"
        />
    </div>
</template>
