<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StorefrontLayout from '@/components/lette/StorefrontLayout.vue';
import { journalArticles } from '@/data/lette';
</script>

<template>
    <StorefrontLayout active="journal">
        <Head title="Journal" />

        <section class="journal-hero">
            <p>JOURNAL</p>
            <h1>
                Notes on soft beginnings, tiny rituals, and thoughtful dressing.
            </h1>
        </section>

        <section class="journal-grid">
            <article v-for="article in journalArticles" :key="article.title">
                <img :src="article.image" :alt="article.title" />
                <div>
                    <time>{{ article.date }}</time>
                    <h2>{{ article.title }}</h2>
                    <p>{{ article.excerpt }}</p>
                    <Link href="/journal">READ MORE</Link>
                </div>
            </article>
        </section>
    </StorefrontLayout>
</template>

<style scoped>
.journal-hero,
.journal-grid {
    width: min(100% - 48px, 1220px);
    margin: 0 auto;
}

.journal-hero {
    display: grid;
    justify-items: center;
    padding: 74px 0 44px;
    text-align: center;
}

.journal-hero p {
    color: #80756e;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.18em;
}

.journal-hero h1 {
    max-width: 780px;
    margin-top: 18px;
    color: #6c5b4e;
    font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
    font-size: clamp(36px, 5vw, 62px);
    font-weight: 400;
    line-height: 1.16;
}

.journal-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px;
    padding-bottom: 74px;
}

.journal-grid article {
    background: #f6f3ed;
}

.journal-grid img {
    aspect-ratio: 1.05;
    width: 100%;
    object-fit: cover;
}

.journal-grid div {
    display: grid;
    padding: 24px;
}

time {
    color: #80756e;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.14em;
}

h2 {
    margin-top: 12px;
    color: #6c5b4e;
    font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
    font-size: 24px;
    font-weight: 400;
    line-height: 1.25;
}

p {
    margin-top: 14px;
    color: #4e453f;
    font-size: 13px;
    line-height: 1.8;
}

a {
    width: fit-content;
    margin-top: 22px;
    border-bottom: 1px solid #6c5b4e;
    color: #4e453f;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.14em;
}

@media (max-width: 900px) {
    .journal-hero,
    .journal-grid {
        width: min(100% - 32px, 1220px);
    }

    .journal-grid {
        grid-template-columns: 1fr;
    }
}
</style>
