<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { dashboard, home, login } from '@/routes';

// Layout for the public customer pages: header on top (name on the left, Login on the right)
const page = usePage();
</script>

<template>
    <div class="flex min-h-screen flex-col bg-background text-foreground">
        <header
            class="sticky top-0 z-10 border-b bg-background/95 backdrop-blur"
        >
            <div
                class="mx-auto flex h-16 max-w-6xl items-center justify-between px-6"
            >
                <Link
                    :href="home()"
                    class="flex items-center gap-2 font-semibold"
                >
                    <span
                        class="flex size-8 items-center justify-center rounded-md bg-primary text-primary-foreground"
                    >
                        <AppLogoIcon class="size-5 fill-current" />
                    </span>
                    {{ page.props.name }}
                </Link>

                <nav>
                    <Button v-if="page.props.auth.user" as-child>
                        <Link :href="dashboard()">Dashboard</Link>
                    </Button>
                    <Button v-else as-child>
                        <Link :href="login()">Log in</Link>
                    </Button>
                </nav>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>
    </div>
</template>
