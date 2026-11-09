<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StorefrontController from '@/actions/App/Http/Controllers/Customer/StorefrontController';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import type { Paginated, StoreShop } from '@/types';

defineProps<{
    shops: Paginated<StoreShop>;
}>();
</script>

<template>
    <Head title="Shops" />

    <div class="mx-auto max-w-6xl space-y-6 p-6">
        <Heading
            title="Browse shops"
            description="Choose a shop to see its available products."
        />

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="shop in shops.data"
                :key="shop.id"
                :href="StorefrontController.show.url(shop.slug)"
            >
                <Card
                    class="h-full overflow-hidden transition hover:bg-muted/40"
                >
                    <CardHeader>
                        <CardTitle>{{ shop.name }}</CardTitle>
                        <CardDescription
                            v-if="shop.description"
                            class="line-clamp-3"
                        >
                            {{ shop.description }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="text-sm text-muted-foreground">
                        {{ shop.shop_products_count }} products
                    </CardContent>
                </Card>
            </Link>

            <p
                v-if="shops.data.length === 0"
                class="rounded-xl border p-8 text-center text-muted-foreground sm:col-span-2 lg:col-span-3"
            >
                No shops are available yet.
            </p>
        </div>

        <Pagination :links="shops.links" />
    </div>
</template>
