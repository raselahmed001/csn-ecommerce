<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

// A confirmation popup shown before a destructive action (delete / remove)
withDefaults(
    defineProps<{
        title: string;
        description: string;
        confirmLabel?: string;
        processing?: boolean;
    }>(),
    {
        confirmLabel: 'Delete',
        processing: false,
    },
);

const open = defineModel<boolean>('open', { required: true });

const emit = defineEmits<{
    confirm: [];
}>();
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription>{{ description }}</DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button
                    type="button"
                    variant="outline"
                    :disabled="processing"
                    @click="open = false"
                >
                    Cancel
                </Button>
                <Button
                    type="button"
                    variant="destructive"
                    :disabled="processing"
                    data-test="confirm-delete"
                    @click="emit('confirm')"
                >
                    {{ confirmLabel }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
