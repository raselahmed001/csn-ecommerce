<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { computed } from 'vue';
import UserController from '@/actions/App/Http/Controllers/Admin/UserController';
import FormSheet from '@/components/FormSheet.vue';
import InputError from '@/components/InputError.vue';
import RequiredMark from '@/components/RequiredMark.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { ShopOwner } from '@/types';

// Edit mode when `user` is present, otherwise Create mode: one form does both
const props = defineProps<{
    user?: ShopOwner | null;
}>();

// The right-side sheet this form lives in (opened / closed by the Index page)
const open = defineModel<boolean>('open', { required: true });

const isEdit = computed(() => !!props.user);

const title = computed(() =>
    isEdit.value ? 'Edit shop owner' : 'Create shop owner',
);

const description = computed(() =>
    isEdit.value ? 'Update account details.' : 'Add a new shop owner account.',
);

const formAction = computed(() =>
    props.user
        ? UserController.update.form(props.user.id)
        : UserController.store.form(),
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
                    :default-value="user?.name"
                    autocomplete="off"
                    placeholder="Full name"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email address <RequiredMark /></Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    :default-value="user?.email"
                    autocomplete="off"
                    placeholder="email@example.com"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password">
                    Password <RequiredMark v-if="!isEdit" />
                    <span v-if="isEdit" class="text-muted-foreground">
                        (leave blank to keep current)
                    </span>
                </Label>
                <Input
                    id="password"
                    type="password"
                    name="password"
                    autocomplete="new-password"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation"
                    >Confirm password <RequiredMark v-if="!isEdit"
                /></Label>
                <Input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    autocomplete="new-password"
                />
            </div>

            <div class="flex items-center gap-3">
                <Button type="submit" :disabled="processing">
                    {{ isEdit ? 'Update owner' : 'Create owner' }}
                </Button>
                <Button type="button" variant="outline" @click="open = false">
                    Cancel
                </Button>
            </div>
        </Form>
    </FormSheet>
</template>
