<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Cloud } from '@lucide/vue';
import StorefrontLayout from '@/components/lette/StorefrontLayout.vue';
import { asset, benefits } from '@/data/lette';
</script>

<template>
    <StorefrontLayout active="about">
        <Head title="About" />

        <section class="about-hero">
            <div>
                <p>OUR STORY</p>
                <h1>As light as love, as gentle as a touch.</h1>
                <span>
                    Lette Little was created for parents who want baby
                    essentials that feel calm, refined, and deeply practical.
                    Every piece starts with soft fabric, easy movement, and a
                    promise to keep little beginnings beautifully simple.
                </span>
                <Link href="/collection">EXPLORE COLLECTION</Link>
            </div>
            <img
                :src="asset('story.jpg')"
                alt="Lette Little design notes and packaging"
            />
        </section>

        <section class="principles">
            <Cloud />
            <h2>Made light. Made gentle.</h2>
            <div>
                <article v-for="benefit in benefits" :key="benefit.title">
                    <h3>{{ benefit.title }}</h3>
                    <p>{{ benefit.body }}</p>
                </article>
            </div>
        </section>
    </StorefrontLayout>
</template>

<style scoped>
.about-hero,
.principles {
    width: min(100% - 48px, 1220px);
    margin: 0 auto;
}

.about-hero {
    display: grid;
    grid-template-columns: 0.92fr 1.08fr;
    gap: 52px;
    align-items: center;
    padding: 72px 0 62px;
}

.about-hero > div {
    display: grid;
    justify-items: start;
}

.about-hero p {
    color: #80756e;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.18em;
}

.about-hero h1,
.principles h2 {
    color: #6c5b4e;
    font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
    font-size: clamp(36px, 5vw, 62px);
    font-weight: 400;
    line-height: 1.16;
}

.about-hero h1 {
    margin-top: 18px;
}

.about-hero span {
    margin-top: 22px;
    color: #4e453f;
    font-size: 14px;
    line-height: 1.9;
}

.about-hero a {
    min-height: 42px;
    margin-top: 30px;
    border: 1px solid #d1c4bc;
    padding: 0 22px;
    color: #4e453f;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.14em;
}

.about-hero img {
    width: 100%;
    aspect-ratio: 1.15;
    object-fit: cover;
}

.principles {
    display: grid;
    justify-items: center;
    border-top: 1px solid #e5ded6;
    padding: 58px 0 78px;
    text-align: center;
}

.principles > svg {
    width: 38px;
    height: 26px;
    color: #6c5b4e;
    stroke-width: 1.2;
}

.principles h2 {
    max-width: 640px;
    margin-top: 16px;
}

.principles > div {
    display: grid;
    width: 100%;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
    margin-top: 38px;
}

.principles article {
    background: #f6f3ed;
    padding: 28px 22px;
}

.principles h3 {
    color: #6c5b4e;
    font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
    font-size: 18px;
    font-weight: 400;
}

.principles p {
    margin-top: 10px;
    color: #4e453f;
    font-size: 12px;
    line-height: 1.7;
}

@media (max-width: 900px) {
    .about-hero,
    .principles {
        width: min(100% - 32px, 1220px);
    }

    .about-hero,
    .principles > div {
        grid-template-columns: 1fr;
    }
}
</style>
