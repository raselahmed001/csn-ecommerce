<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { computed } from 'vue';
import ShopController from '@/actions/App/Http/Controllers/Admin/ShopController';
import FormSheet from '@/components/FormSheet.vue';
import InputError from '@/components/InputError.vue';
import RequiredMark from '@/components/RequiredMark.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import type { OwnerOption, Shop } from '@/types';

// Edit mode when `shop` is present, otherwise Create mode: one form does both
const props = defineProps<{
    shop?: Shop | null;
    owners: OwnerOption[];
}>();

// The right-side sheet this form lives in (opened / closed by the Index page)
const open = defineModel<boolean>('open', { required: true });

const isEdit = computed(() => !!props.shop);

const title = computed(() => (isEdit.value ? 'Edit shop' : 'Create shop'));

const description = computed(() =>
    isEdit.value
        ? 'Update shop details and owner.'
        : 'Create a shop and assign it to a shop owner.',
);

const formAction = computed(() =>
    props.shop
        ? ShopController.update.form(props.shop.id)
        : ShopController.store.form(),
);
</script>

<template>
    <FormSheet v-model:open="open" :title="title" :description="description">
        <p v-if="owners.length === 0" class="mb-4 text-sm text-amber-700">
            Create a shop owner account before creating a shop.
        </p>

        <Form
            novalidate
            v-bind="formAction"
            class="space-y-6"
            v-slot="{ errors, processing }"
            @success="open = false"
        >
            <div class="grid gap-2">
                <Label for="name">Name <RequiredMark /></Label>
                <Input
                    id="name"
                    name="name"
                    :default-value="shop?.name"
                    placeholder="Shop name"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="slug">Slug <RequiredMark /></Label>
                <Input
                    id="slug"
                    name="slug"
                    :default-value="shop?.slug"
                    placeholder="shop-slug"
                />
                <InputError :message="errors.slug" />
            </div>

            <div class="grid gap-2">
                <Label for="owner_id">Owner <RequiredMark /></Label>
                <Select
                    name="owner_id"
                    :default-value="shop ? String(shop.owner_id) : undefined"
                >
                    <SelectTrigger id="owner_id" class="w-full">
                        <SelectValue placeholder="Select an owner" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="owner in owners"
                            :key="owner.id"
                            :value="String(owner.id)"
                        >
                            {{ owner.name }} ({{ owner.email }})
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="errors.owner_id" />
            </div>

            <div class="grid gap-2">
                <Label for="description">Description</Label>
                <Textarea
                    id="description"
                    name="description"
                    :default-value="shop?.description ?? ''"
                    rows="4"
                />
                <InputError :message="errors.description" />
            </div>

            <div class="flex items-center gap-3">
                <Button type="submit" :disabled="processing">
                    {{ isEdit ? 'Update shop' : 'Create shop' }}
                </Button>
                <Button type="button" variant="outline" @click="open = false">
                    Cancel
                </Button>
            </div>
        </Form>
    </FormSheet>
</template>
