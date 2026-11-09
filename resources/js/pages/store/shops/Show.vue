<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StorefrontController from '@/actions/App/Http/Controllers/Customer/StorefrontController';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import { home } from '@/routes';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { Paginated, StoreListing, StoreShop } from '@/types';

defineProps<{
    shop: StoreShop;
    products: Paginated<StoreListing>;
}>();
</script>

<template>
    <Head :title="shop.name" />

    <div class="mx-auto max-w-6xl space-y-6 p-6">
        <div class="flex items-start justify-between gap-4">
            <Heading :title="shop.name" :description="shop.description ?? ''" />
            <Button as-child variant="outline">
                <Link :href="home()">All shops</Link>
            </Button>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="listing in products.data"
                :key="listing.id"
                :href="
                    StorefrontController.showProduct.url({
                        shop: shop.slug,
                        shopProduct: listing.id,
                    })
                "
            >
                <Card
                    class="h-full overflow-hidden transition hover:bg-muted/40"
                >
                    <img
                        v-if="listing.thumbnail_url"
                        :src="listing.thumbnail_url"
                        :alt="listing.name || listing.product.name"
                        class="h-48 w-full object-cover"
                    />
                    <CardHeader>
                        <CardTitle>
                            {{ listing.name || listing.product.name }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <p
                            v-if="listing.description"
                            class="line-clamp-2 text-sm text-muted-foreground"
                        >
                            {{ listing.description }}
                        </p>
                        <p class="text-lg font-semibold">
                            ৳{{ listing.price }}
                        </p>
                    </CardContent>
                </Card>
            </Link>

            <p
                v-if="products.data.length === 0"
                class="rounded-xl border p-8 text-center text-muted-foreground sm:col-span-2 lg:col-span-3"
            >
                This shop has no products available.
            </p>
        </div>

        <Pagination :links="products.links" />
    </div>
</template>
