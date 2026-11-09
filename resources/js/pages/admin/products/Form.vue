<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProductController from '@/actions/App/Http/Controllers/Admin/ProductController';
import FormSheet from '@/components/FormSheet.vue';
import InputError from '@/components/InputError.vue';
import RequiredMark from '@/components/RequiredMark.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { Product } from '@/types';

// Edit mode when `product` is present, otherwise Create mode: one form does both
const props = defineProps<{
    product?: Product | null;
}>();

// The right-side sheet this form lives in (opened / closed by the Index page)
const open = defineModel<boolean>('open', { required: true });

const isEdit = computed(() => !!props.product);

const title = computed(() =>
    isEdit.value ? 'Edit catalog product' : 'Create catalog product',
);

const description = computed(() =>
    isEdit.value
        ? 'Update the shared catalog product.'
        : 'Add a product to the shared catalog. Shop owners can then list it in their shops.',
);

const formAction = computed(() =>
    props.product
        ? ProductController.update.form(props.product.id)
        : ProductController.store.form(),
);
</script>

<template>
    <FormSheet v-model:open="open" :title="title" :description="description">
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
                    :default-value="product?.name"
                    placeholder="Product name"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="slug">Slug <RequiredMark /></Label>
                <Input
                    id="slug"
                    name="slug"
                    :default-value="product?.slug"
                    placeholder="product-slug"
                />
                <InputError :message="errors.slug" />
            </div>

            <div class="grid gap-2">
                <Label for="sku">SKU <RequiredMark /></Label>
                <Input
                    id="sku"
                    name="sku"
                    :default-value="product?.sku"
                    placeholder="SKU-001"
                />
                <InputError :message="errors.sku" />
            </div>

            <div class="grid gap-2">
                <Label for="description">Description</Label>
                <Textarea
                    id="description"
                    name="description"
                    :default-value="product?.description ?? ''"
                    rows="4"
                />
                <InputError :message="errors.description" />
            </div>

            <div class="flex items-center gap-3">
                <Button type="submit" :disabled="processing">
                    {{ isEdit ? 'Update product' : 'Create product' }}
                </Button>
                <Button type="button" variant="outline" @click="open = false">
                    Cancel
                </Button>
            </div>
        </Form>
    </FormSheet>
</template>
