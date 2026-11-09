<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';

type Stat = {
    label: string;
    value: number;
    href: string;
};

defineProps<{
    stats: Stat[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const page = usePage();
</script>

<template>
    <Head title="Dashboard" />

    <div class="space-y-6 p-6">
        <Heading
            :title="`Welcome back, ${page.props.auth.user.name}`"
            description="A quick summary of what you manage."
        />

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <Link v-for="stat in stats" :key="stat.label" :href="stat.href">
                <Card class="h-full transition hover:bg-muted/40">
                    <CardHeader>
                        <CardDescription>{{ stat.label }}</CardDescription>
                        <CardTitle class="text-3xl">{{ stat.value }}</CardTitle>
                    </CardHeader>
                </Card>
            </Link>
        </div>
    </div>
</template>
