<script setup lang="ts">
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';

// A form that slides in from the right side of the screen
defineProps<{
    title: string;
    description?: string;
}>();

const open = defineModel<boolean>('open', { required: true });
</script>

<template>
    <Sheet v-model:open="open">
        <SheetContent side="right" class="gap-0 overflow-y-auto sm:max-w-lg">
            <SheetHeader>
                <SheetTitle>{{ title }}</SheetTitle>
                <SheetDescription v-if="description">
                    {{ description }}
                </SheetDescription>
            </SheetHeader>
            <div class="px-4 pb-6">
                <slot />
            </div>
        </SheetContent>
    </Sheet>
</template>
