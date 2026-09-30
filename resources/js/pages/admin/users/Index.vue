<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Plus, ShieldCheck, UserCog } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';
import { create as usersCreate } from '@/routes/admin/users';

type AdminUser = {
    id: number;
    name: string;
    email: string;
    role: 'admin' | 'superadmin';
    email_verified_at: string | null;
    created_at: string | null;
};

type AdminUserStats = {
    admins: number;
    superadmins: number;
};

defineProps<{
    users: AdminUser[];
    stats: AdminUserStats;
}>();

const dateFormatter = new Intl.DateTimeFormat('id-ID', {
    dateStyle: 'medium',
});

function formatDate(value: string | null): string {
    return value ? dateFormatter.format(new Date(value)) : 'No date';
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Admin Users',
                href: '/admin/users',
            },
        ],
    },
});
</script>

<template>
    <Head title="Admin Users" />

    <div class="flex h-full flex-1 flex-col gap-5 p-4 md:p-6">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div class="grid gap-1">
                <h1 class="text-2xl font-semibold tracking-normal text-foreground">
                    Admin Users
                </h1>
                <p class="text-sm text-muted-foreground">
                    Superadmin workspace for creating dashboard admin accounts.
                </p>
            </div>
            <Button as-child>
                <Link :href="usersCreate()">
                    <Plus />
                    Create Admin
                </Link>
            </Button>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between gap-3 pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">
                        Admin Accounts
                    </CardTitle>
                    <UserCog class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-semibold tracking-normal text-foreground">
                        {{ stats.admins }}
                    </div>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Operational dashboard users created by superadmin.
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between gap-3 pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">
                        Superadmins
                    </CardTitle>
                    <ShieldCheck class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-semibold tracking-normal text-foreground">
                        {{ stats.superadmins }}
                    </div>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Seeded owners who can create admin accounts.
                    </p>
                </CardContent>
            </Card>
        </div>

        <Card>
            <CardHeader>
                <CardTitle class="text-base">Dashboard Access</CardTitle>
            </CardHeader>
            <CardContent>
                <div v-if="users.length" class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-left text-sm">
                        <thead class="border-b text-xs uppercase text-muted-foreground">
                            <tr>
                                <th class="py-3 pr-4 font-medium">Name</th>
                                <th class="px-4 py-3 font-medium">Email</th>
                                <th class="px-4 py-3 font-medium">Role</th>
                                <th class="py-3 pl-4 text-right font-medium">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="user in users" :key="user.id" class="align-middle">
                                <td class="py-3 pr-4 font-medium text-foreground">
                                    {{ user.name }}
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ user.email }}
                                </td>
                                <td class="px-4 py-3">
                                    <Badge :variant="user.role === 'superadmin' ? 'default' : 'secondary'">
                                        {{ user.role === 'superadmin' ? 'Superadmin' : 'Admin' }}
                                    </Badge>
                                </td>
                                <td class="py-3 pl-4 text-right text-muted-foreground">
                                    {{ formatDate(user.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="rounded-md border border-dashed p-8 text-center text-sm text-muted-foreground">
                    No admin users yet.
                </div>
            </CardContent>
        </Card>
    </div>
</template>