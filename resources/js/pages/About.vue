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
                width="512"
                height="512"
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
    color: var(--lette-muted);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.18em;
}

.about-hero h1,
.principles h2 {
    color: var(--lette-brand);
    font-family: var(--lette-heading-font);
    font-size: clamp(36px, 5vw, 62px);
    font-weight: 400;
    line-height: 1.16;
}

.about-hero h1 {
    margin-top: 18px;
}

.about-hero span {
    margin-top: 22px;
    color: var(--lette-text);
    font-size: 14px;
    line-height: 1.9;
}

.about-hero a {
    display: inline-flex;
    min-height: var(--lette-cta-height);
    align-items: center;
    justify-content: center;
    margin-top: 30px;
    border: 1px solid var(--lette-border);
    background: var(--lette-surface);
    padding: 0 var(--lette-cta-padding);
    color: var(--lette-text);
    font-size: var(--lette-cta-size);
    font-weight: 700;
    letter-spacing: var(--lette-cta-tracking);
    text-transform: uppercase;
}

.about-hero img {
    width: 100%;
    height: auto;
    aspect-ratio: 1.15;
    object-fit: cover;
}

.principles {
    display: grid;
    justify-items: center;
    border-top: 1px solid var(--lette-line);
    padding: 58px 0 78px;
    text-align: center;
}

.principles > svg {
    width: 38px;
    height: 26px;
    color: var(--lette-brand);
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
    background: var(--lette-sand);
    padding: 28px 22px;
}

.principles h3 {
    color: var(--lette-brand);
    font-family: var(--lette-heading-font);
    font-size: 18px;
    font-weight: 400;
}

.principles p {
    margin-top: 10px;
    color: var(--lette-text);
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
