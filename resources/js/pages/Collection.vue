<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StorefrontLayout from '@/components/lette/StorefrontLayout.vue';
import { categories } from '@/data/lette';
</script>

<template>
    <StorefrontLayout active="collection">
        <Head title="Collection" />

        <section class="collection-hero">
            <p>CLOUD BEGINNING COLLECTION</p>
            <h1>A calm wardrobe built around softness, movement, and care.</h1>
        </section>

        <section class="collection-grid">
            <article
                v-for="category in categories"
                :key="category.slug"
                class="collection-card"
            >
                <img :src="category.image" :alt="category.name" />
                <div>
                    <span>{{ category.name }}</span>
                    <p>{{ category.intro }}</p>
                    <Link href="/shop"
                        >SHOP {{ category.name.toUpperCase() }}</Link
                    >
                </div>
            </article>
        </section>
    </StorefrontLayout>
</template>

<style scoped>
.collection-hero,
.collection-grid {
    width: min(100% - 48px, 1220px);
    margin: 0 auto;
}

.collection-hero {
    display: grid;
    justify-items: center;
    padding: 74px 0 42px;
    text-align: center;
}

.collection-hero p {
    color: #80756e;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.18em;
}

.collection-hero h1 {
    max-width: 780px;
    margin-top: 18px;
    color: #6c5b4e;
    font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
    font-size: clamp(36px, 5vw, 62px);
    font-weight: 400;
    line-height: 1.16;
}

.collection-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
    padding-bottom: 74px;
}

.collection-card {
    display: grid;
    grid-template-columns: minmax(170px, 42%) 1fr;
    min-height: 290px;
    background: #f6f3ed;
}

.collection-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.collection-card div {
    display: grid;
    align-content: center;
    padding: 34px;
}

.collection-card span {
    color: #6c5b4e;
    font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
    font-size: 30px;
    font-weight: 400;
}

.collection-card p {
    margin-top: 14px;
    color: #4e453f;
    font-size: 13px;
    line-height: 1.8;
}

.collection-card a {
    width: fit-content;
    margin-top: 24px;
    border-bottom: 1px solid #6c5b4e;
    color: #4e453f;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.14em;
}

@media (max-width: 900px) {
    .collection-hero,
    .collection-grid {
        width: min(100% - 32px, 1220px);
    }

    .collection-grid,
    .collection-card {
        grid-template-columns: 1fr;
    }

    .collection-card img {
        aspect-ratio: 4 / 3;
    }
}
</style>
