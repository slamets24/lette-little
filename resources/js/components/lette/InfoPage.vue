<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StorefrontLayout from '@/components/lette/StorefrontLayout.vue';

defineProps<{
    eyebrow: string;
    title: string;
}>();
</script>

<template>
    <StorefrontLayout active="info">
        <Head :title="title" />

        <article class="info-page">
            <header>
                <p>{{ eyebrow }}</p>
                <h1>{{ title }}</h1>
            </header>
            <div class="info-body">
                <slot />
            </div>
        </article>
    </StorefrontLayout>
</template>

<style scoped>
.info-page {
    width: min(100% - 48px, 760px);
    margin: 0 auto;
    padding: 74px 0 88px;
}

header {
    display: grid;
    justify-items: start;
    margin-bottom: 36px;
}

p {
    color: var(--lette-muted);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.18em;
}

h1 {
    margin-top: 18px;
    color: var(--lette-brand);
    font-family: var(--lette-heading-font);
    font-size: clamp(36px, 5vw, 52px);
    font-weight: 400;
    line-height: 1.16;
}

.info-body {
    display: grid;
    gap: 22px;
    color: var(--lette-text);
    font-size: 15px;
    line-height: 1.8;
}

.info-body :deep(h2) {
    color: var(--lette-brand);
    font-family: var(--lette-heading-font);
    font-size: 22px;
    font-weight: 400;
}

.info-body :deep(ul),
.info-body :deep(ol) {
    display: grid;
    gap: 8px;
    padding-left: 18px;
}

.info-body :deep(table) {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.info-body :deep(th),
.info-body :deep(td) {
    border-bottom: 1px solid var(--lette-line);
    padding: 12px 10px 12px 0;
    text-align: left;
}

.info-body :deep(th) {
    color: var(--lette-muted);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.12em;
}

.info-body :deep(a) {
    color: var(--lette-brand);
    text-decoration: underline;
    text-underline-offset: 3px;
}

@media (max-width: 900px) {
    .info-page {
        width: min(100% - 32px, 760px);
        padding: 52px 0 64px;
    }
}
</style>
