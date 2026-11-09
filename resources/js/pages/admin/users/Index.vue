<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import UserController from '@/actions/App/Http/Controllers/Admin/UserController';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import UserForm from './Form.vue';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { usePermissions } from '@/composables/usePermissions';
import type { Paginated, ShopOwner } from '@/types';

const props = defineProps<{
    users: Paginated<ShopOwner>;
    filters: { search: string };
}>();

const { can } = usePermissions();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Shop owners', href: '/admin/users' }],
    },
});

const search = ref(props.filters.search);

function applySearch(): void {
    router.get(
        UserController.index.url(),
        { search: search.value || undefined },
        { preserveState: true, replace: true },
    );
}

// The create / edit form slides in from the right
const sheetOpen = ref(false);
const editing = ref<ShopOwner | null>(null);

function openCreate(): void {
    editing.value = null;
    sheetOpen.value = true;
}

function openEdit(owner: ShopOwner): void {
    editing.value = owner;
    sheetOpen.value = true;
}

// The delete / remove confirmation popup
const deleteOpen = ref(false);
const deleting = ref<ShopOwner | null>(null);
const deleteProcessing = ref(false);

function askDelete(owner: ShopOwner): void {
    deleting.value = owner;
    deleteOpen.value = true;
}

function confirmDelete(): void {
    if (!deleting.value) {
        return;
    }

    router.delete(UserController.destroy.url(deleting.value.id), {
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
    <Head title="Shop owners" />

    <div class="space-y-6 p-6">
        <div class="flex items-start justify-between gap-4">
            <Heading
                title="Shop owners"
                description="Create and manage shop owner accounts."
            />
            <Button v-if="can('create_users')" @click="openCreate">
                Create owner
            </Button>
        </div>

        <form class="flex max-w-md gap-2" @submit.prevent="applySearch">
            <Input
                v-model="search"
                placeholder="Search name or email"
                aria-label="Search"
            />
            <Button type="submit" variant="outline">Search</Button>
            <Button v-if="filters.search" as-child variant="ghost">
                <Link :href="UserController.index.url()">Clear</Link>
            </Button>
        </form>

        <p
            v-if="$page.props.errors.user"
            class="rounded-md border border-destructive/50 p-3 text-sm text-destructive"
        >
            {{ $page.props.errors.user }}
        </p>

        <div class="rounded-xl border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Owner</TableHead>
                        <TableHead>Shops</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="owner in users.data" :key="owner.id">
                        <TableCell>
                            <div class="font-medium">{{ owner.name }}</div>
                            <div class="text-xs text-muted-foreground">
                                {{ owner.email }}
                            </div>
                        </TableCell>
                        <TableCell>{{ owner.shops_count }}</TableCell>
                        <TableCell class="space-x-2 text-right">
                            <Button
                                v-if="can('edit_users')"
                                size="sm"
                                variant="outline"
                                @click="openEdit(owner)"
                            >
                                Edit
                            </Button>
                            <Button
                                v-if="can('delete_users')"
                                size="sm"
                                variant="destructive"
                                :disabled="(owner.shops_count ?? 0) > 0"
                                :title="
                                    (owner.shops_count ?? 0) > 0
                                        ? 'Delete or reassign this owner’s shops first'
                                        : 'Delete owner'
                                "
                                @click="askDelete(owner)"
                            >
                                Delete
                            </Button>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="users.data.length === 0">
                        <TableCell
                            colspan="3"
                            class="py-8 text-center text-muted-foreground"
                        >
                            No shop owners found.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <Pagination :links="users.links" />
        <UserForm v-model:open="sheetOpen" :user="editing" />
        <ConfirmDialog
            v-model:open="deleteOpen"
            :title="'Delete shop owner?'"
            :description="`${deleting?.name} will be permanently deleted. This cannot be undone.`"
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="confirmDelete"
        />
    </div>
</template>
