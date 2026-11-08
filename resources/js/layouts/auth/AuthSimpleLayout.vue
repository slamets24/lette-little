<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { home } from '@/routes';

const logoSrc = '/images/lette/LOGO/Artboard%201.png';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-[var(--lette-canvas)] p-6 md:p-10 dark:bg-background"
    >
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <Link
                        :href="home()"
                        class="flex flex-col items-center gap-3 font-medium"
                    >
                        <img
                            :src="logoSrc"
                            alt="Lette Little"
                            class="h-auto max-h-20 w-44 object-contain"
                        />
                        <span class="sr-only">Lette Little</span>
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="text-xl font-medium tracking-normal text-foreground">
                            {{ title }}
                        </h1>
                        <p class="text-center text-sm text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>