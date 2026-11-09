<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';
import ShopProductController from '@/actions/App/Http/Controllers/ShopOwner/ShopProductController';
import FormSheet from '@/components/FormSheet.vue';
import InputError from '@/components/InputError.vue';
import RequiredMark from '@/components/RequiredMark.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { CatalogProduct, OwnerShop, ShopListing } from '@/types';

/**
 * A single form works in three modes:
 *  - edit : `listing` is present, changes the listing in the owner's own shop
 *  - add  : `product` is present but `listing` is not, adds a Shared Catalog product to the shop
 *  - new  : neither is present, creates a new Global Product and lists it in the shop
 */
const props = defineProps<{
    shop: Pick<OwnerShop, 'id' | 'name'>;
    listing?: ShopListing | null;
    product?: CatalogProduct | null;
}>();

// The right-side sheet this form lives in (opened / closed by the Index page)
const open = defineModel<boolean>('open', { required: true });

const mode = computed<'edit' | 'add' | 'new'>(() => {
    if (props.listing) {
        return 'edit';
    }

    return props.product ? 'add' : 'new';
});

const title = computed(
    () =>
        ({
            edit: 'Edit shop product',
            add: 'Add product to shop',
            new: 'Create new product',
        })[mode.value],
);

const description = computed(
    () =>
        ({
            edit: 'Update the price, details and thumbnail for this shop only.',
            add: 'Set how this catalog product is sold in your shop.',
            new: 'The product is also added to the shared catalog for other shop owners.',
        })[mode.value],
);

const formAction = computed(() =>
    props.listing
        ? ShopProductController.update.form({
              shop: props.shop.id,
              shopProduct: props.listing.id,
          })
        : ShopProductController.store.form(props.shop.id),
);

// Small preview of the file the user just picked (shown below the file input)
const previewUrl = ref<string | null>(null);

function revokePreview(): void {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }
}

function onThumbnailChange(event: Event): void {
    const file = (event.target as HTMLInputElement).files?.[0];

    revokePreview();

    if (file) {
        previewUrl.value = URL.createObjectURL(file);
    }
}

onBeforeUnmount(revokePreview);
</script>

<template>
    <FormSheet v-model:open="open" :title="title" :description="description">
        <div
            v-if="product"
            class="mb-6 rounded-xl border bg-muted/40 p-4 text-sm"
        >
            <p class="font-medium">{{ product.name }}</p>
            <p class="text-muted-foreground">SKU: {{ product.sku }}</p>
        </div>

        <Form
            novalidate
            v-bind="formAction"
            class="space-y-6"
            v-slot="{ errors, processing }"
            @success="open = false"
        >
            <!-- Add mode: which Global Product is being added -->
            <input
                v-if="mode === 'add'"
                type="hidden"
                name="product_id"
                :value="product?.id"
            />
            <InputError :message="errors.product_id" />

            <!-- New mode: Global Product details -->
            <template v-if="mode === 'new'">
                <div class="grid gap-2">
                    <Label for="slug">Slug <RequiredMark /></Label>
                    <Input id="slug" name="slug" placeholder="product-slug" />
                    <InputError :message="errors.slug" />
                </div>

                <div class="grid gap-2">
                    <Label for="sku">SKU <RequiredMark /></Label>
                    <Input id="sku" name="sku" placeholder="SKU-001" />
                    <InputError :message="errors.sku" />
                </div>
            </template>

            <div class="grid gap-2">
                <Label for="name">
                    Name <RequiredMark v-if="mode === 'new'" />
                    <span v-if="mode !== 'new'" class="text-muted-foreground">
                        (optional, leave blank to use the catalog name)
                    </span>
                </Label>
                <Input
                    id="name"
                    name="name"
                    :default-value="listing?.name ?? ''"
                    placeholder="Product name"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="price">Price (৳) <RequiredMark /></Label>
                <Input
                    id="price"
                    name="price"
                    type="number"
                    step="0.01"
                    min="0.01"
                    :default-value="listing?.price"
                />
                <InputError :message="errors.price" />
            </div>

            <div class="grid gap-2">
                <Label for="thumbnail">Thumbnail</Label>
                <Input
                    id="thumbnail"
                    name="thumbnail"
                    type="file"
                    accept="image/png,image/jpeg,image/webp"
                    @change="onThumbnailChange"
                />
                <p class="text-xs text-muted-foreground">
                    JPG, PNG or WebP, up to 2 MB.
                </p>
                <InputError :message="errors.thumbnail" />

                <!-- Preview: the newly picked file, otherwise the saved thumbnail -->
                <div
                    v-if="previewUrl || listing?.thumbnail_url"
                    class="flex items-center gap-3"
                >
                    <img
                        :src="previewUrl ?? listing?.thumbnail_url ?? ''"
                        :alt="
                            previewUrl
                                ? 'Selected thumbnail preview'
                                : 'Current thumbnail'
                        "
                        class="size-20 rounded-md border object-cover"
                        data-test="thumbnail-preview"
                    />
                    <span class="text-xs text-muted-foreground">
                        {{
                            previewUrl
                                ? 'New thumbnail (preview)'
                                : 'Current thumbnail'
                        }}
                    </span>
                </div>

                <div
                    v-if="listing?.thumbnail_url && !previewUrl"
                    class="flex items-center gap-2"
                >
                    <Checkbox
                        id="remove_thumbnail"
                        name="remove_thumbnail"
                        value="1"
                    />
                    <Label for="remove_thumbnail" class="font-normal">
                        Remove current thumbnail
                    </Label>
                </div>
            </div>

            <div class="grid gap-2">
                <Label for="description">Description</Label>
                <Textarea
                    id="description"
                    name="description"
                    :default-value="listing?.description ?? ''"
                    rows="4"
                />
                <InputError :message="errors.description" />
            </div>

            <div class="flex items-center gap-3">
                <Button type="submit" :disabled="processing">
                    {{ mode === 'edit' ? 'Update product' : 'Add to shop' }}
                </Button>
                <Button type="button" variant="outline" @click="open = false">
                    Cancel
                </Button>
            </div>
        </Form>
    </FormSheet>
</template>
