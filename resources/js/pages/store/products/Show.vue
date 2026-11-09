<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StorefrontController from '@/actions/App/Http/Controllers/Customer/StorefrontController';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import type { StoreListing, StoreShop } from '@/types';

const props = defineProps<{
    shop: Pick<StoreShop, 'id' | 'name' | 'slug'>;
    listing: StoreListing;
}>();

// Use the shop-level name/description if set, otherwise the Global Product's
const name = props.listing.name || props.listing.product.name;
const description =
    props.listing.description || props.listing.product.description;
</script>

<template>
    <Head :title="name" />

    <div class="mx-auto max-w-5xl space-y-6 p-6">
        <Button as-child variant="outline" size="sm">
            <Link :href="StorefrontController.show.url(shop.slug)">
                Back to {{ shop.name }}
            </Link>
        </Button>

        <Card>
            <CardContent class="grid gap-8 md:grid-cols-2">
                <img
                    v-if="listing.thumbnail_url"
                    :src="listing.thumbnail_url"
                    :alt="name"
                    class="max-h-[32rem] w-full rounded-lg object-cover"
                />
                <div class="space-y-4">
                    <p class="text-sm text-muted-foreground">
                        SKU {{ listing.product.sku }}
                    </p>
                    <h1 class="text-3xl font-semibold">{{ name }}</h1>
                    <p
                        v-if="description"
                        class="whitespace-pre-line text-muted-foreground"
                    >
                        {{ description }}
                    </p>
                    <p class="text-2xl font-semibold">৳{{ listing.price }}</p>
                    <p class="text-sm text-muted-foreground">
                        Sold by {{ shop.name }}
                    </p>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
