<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { dashboard } from '@/routes';
import { index as usersIndex, store as usersStore } from '@/routes/admin/users';

defineProps<{
    passwordRules: string;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Admin Users',
                href: usersIndex(),
            },
            {
                title: 'Create',
                href: '/admin/users/create',
            },
        ],
    },
});
</script>

<template>
    <Head title="Create Admin" />

    <div class="flex h-full flex-1 flex-col gap-5 p-4 md:p-6">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div class="grid gap-1">
                <h1 class="text-2xl font-semibold tracking-normal text-foreground">
                    Create Admin
                </h1>
                <p class="text-sm text-muted-foreground">
                    Create dashboard-only admin accounts. Customer accounts still use signup.
                </p>
            </div>
            <Button variant="outline" as-child>
                <Link :href="usersIndex()">
                    <ArrowLeft />
                    Back
                </Link>
            </Button>
        </div>

        <Card class="max-w-2xl">
            <CardHeader>
                <CardTitle class="text-base">Admin Credentials</CardTitle>
                <CardDescription>
                    This account can access admin dashboard modules, but cannot create other admins.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="usersStore.form()"
                    :reset-on-success="['password', 'password_confirmation']"
                    v-slot="{ errors, processing }"
                    class="grid gap-5"
                >
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            name="name"
                            type="text"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Admin name"
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            name="email"
                            type="email"
                            required
                            autocomplete="email"
                            placeholder="admin@example.com"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">Password</Label>
                        <PasswordInput
                            id="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            placeholder="Password"
                            :passwordrules="passwordRules"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">Confirm password</Label>
                        <PasswordInput
                            id="password_confirmation"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Confirm password"
                            :passwordrules="passwordRules"
                        />
                        <InputError :message="errors.password_confirmation" />
                    </div>

                    <Button type="submit" class="w-full sm:w-fit" :disabled="processing">
                        <Spinner v-if="processing" />
                        Create Admin
                    </Button>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>