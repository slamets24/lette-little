<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Cloud, Gift, Heart, Leaf, Package } from '@lucide/vue';
import ProductCard from '@/components/lette/ProductCard.vue';
import StorefrontLayout from '@/components/lette/StorefrontLayout.vue';
import { asset, benefits, categories, products } from '@/data/lette';

const benefitIcons = [Leaf, Cloud, Heart, Gift];
const instagramImages = [
    asset('stitch-dresses.jpg'),
    asset('stitch-rompers.jpg'),
    asset('stitch-sets.jpg'),
    asset('stitch-product-luna.jpg'),
    asset('stitch-story.jpg'),
    asset('stitch-insta-clouds.jpg'),
];
</script>

<template>
    <StorefrontLayout active="home">
        <Head title="Lette Little" />

        <section class="home-hero">
            <img
                :src="asset('stitch-hero.jpg')"
                alt="Lette Little soft baby essentials"
            />
            <div class="hero-content">
                <p>Lette Little</p>
                <h1>Made Light.<br />Made Gentle.</h1>
                <span>Soft essentials for little beginnings.</span>
                <Link href="/shop">SHOP COLLECTION</Link>
            </div>
        </section>

        <section class="collection-section section-shell">
            <div class="section-title">
                <Cloud />
                <h2>Cloud Beginning Collection</h2>
            </div>
            <div class="category-grid">
                <article
                    v-for="category in categories"
                    :key="category.slug"
                    class="category-card"
                >
                    <Link href="/collection" class="category-image">
                        <img :src="category.image" :alt="category.name" />
                    </Link>
                    <h3>{{ category.name }}</h3>
                    <Link href="/collection">DISCOVER</Link>
                </article>
            </div>
        </section>

        <section class="section-shell best-seller-section">
            <h2>Best Sellers</h2>
            <div class="product-grid">
                <ProductCard
                    v-for="product in products"
                    :key="product.name"
                    :product="product"
                />
            </div>
            <Link class="outline-link" href="/shop">VIEW ALL PRODUCTS</Link>
        </section>

        <section class="story-band">
            <div class="story-copy">
                <p>OUR STORY</p>
                <h2>As light as love,<br />as gentle as a touch.</h2>
                <Cloud />
                <span>
                    Lette Little was born from a simple idea. To create clothing
                    that feels as gentle as a mother's touch and as light as a
                    cloud. For every little moment in your little one's world.
                </span>
                <Link href="/about">OUR STORY</Link>
            </div>
            <img
                :src="asset('stitch-story.jpg')"
                alt="Lette Little sketches and soft packaging"
            />
        </section>

        <section class="benefit-strip">
            <article v-for="(benefit, index) in benefits" :key="benefit.title">
                <component :is="benefitIcons[index] || Package" />
                <h3>{{ benefit.title }}</h3>
                <p>{{ benefit.body }}</p>
            </article>
        </section>

        <section class="instagram-section section-shell">
            <h2>@LETTELITTLE.ID</h2>
            <div class="instagram-grid">
                <img
                    v-for="(image, index) in instagramImages"
                    :key="`${image}-${index}`"
                    :src="image"
                    alt="Lette Little Instagram moment"
                    loading="lazy"
                />
            </div>
            <a href="https://instagram.com/lettelittle.id"
                >FOLLOW US ON INSTAGRAM</a
            >
        </section>
    </StorefrontLayout>
</template>

<style scoped>
.home-hero {
    position: relative;
    min-height: clamp(430px, 45vw, 560px);
    overflow: hidden;
    background: linear-gradient(90deg, #9a8b76 0%, #b5a68e 42%, #d6cbb7 100%);
}

.home-hero img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center right;
}

.home-hero::after {
    position: absolute;
    inset: 0;
    content: '';
    background: linear-gradient(
        90deg,
        rgba(64, 50, 38, 0.46),
        rgba(64, 50, 38, 0.16) 46%,
        rgba(64, 50, 38, 0.02)
    );
}

.hero-content {
    position: relative;
    z-index: 1;
    display: flex;
    width: min(100% - 48px, 1220px);
    min-height: inherit;
    flex-direction: column;
    justify-content: center;
    margin: 0 auto;
    color: #fffaf1;
}

.hero-content p {
    margin-bottom: 18px;
    font-size: 13px;
    letter-spacing: 0.02em;
}

.hero-content h1 {
    max-width: 560px;
    color: #fffaf1;
    font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
    font-size: clamp(42px, 5vw, 72px);
    font-weight: 400;
    line-height: 1.14;
}

.hero-content span {
    margin-top: 18px;
    font-size: 17px;
}

.hero-content a,
.story-copy a,
.outline-link {
    display: inline-flex;
    width: fit-content;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(108, 91, 78, 0.34);
    background: #fffdf8;
    color: #4e453f;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.14em;
    transition:
        background 180ms ease,
        border-color 180ms ease,
        transform 180ms ease;
}

.hero-content a {
    margin-top: 32px;
    min-width: 164px;
    min-height: 45px;
}

.hero-content a:hover,
.story-copy a:hover,
.outline-link:hover {
    border-color: #6c5b4e;
    background: #f6f3ed;
    transform: translateY(-1px);
}

.section-shell {
    width: min(100% - 48px, 1220px);
    margin: 0 auto;
}

.collection-section {
    padding: 36px 0 22px;
}

.section-title {
    display: grid;
    justify-items: center;
    gap: 6px;
    margin-bottom: 26px;
    color: #6c5b4e;
    text-align: center;
}

.section-title svg {
    width: 28px;
    height: 20px;
    stroke-width: 1.2;
}

.section-title h2,
.best-seller-section h2,
.instagram-section h2 {
    color: #6c5b4e;
    font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
    font-size: clamp(24px, 2.35vw, 32px);
    font-weight: 400;
    line-height: 1.25;
    text-align: center;
}

.category-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
}

.category-card {
    display: grid;
    justify-items: center;
    text-align: center;
}

.category-image {
    display: block;
    aspect-ratio: 1;
    width: 100%;
    overflow: hidden;
    background: #f0eee8;
}

.category-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 420ms ease;
}

.category-card:hover img {
    transform: scale(1.04);
}

.category-card h3 {
    margin-top: 13px;
    color: #6c5b4e;
    font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
    font-size: 17px;
    font-weight: 400;
}

.category-card > a:last-child,
.instagram-section > a {
    margin-top: 3px;
    color: #4e453f;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
}

.category-card > a:last-child::after {
    display: block;
    width: 35px;
    height: 1px;
    margin: 7px auto 0;
    background: #6c5b4e;
    content: '';
}

.best-seller-section {
    display: grid;
    justify-items: center;
    padding: 28px 0 44px;
}

.product-grid {
    display: grid;
    width: 100%;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 16px;
    margin-top: 24px;
}

.outline-link {
    min-width: 198px;
    min-height: 42px;
    margin-top: 34px;
}

.story-band {
    display: grid;
    grid-template-columns: minmax(320px, 43%) minmax(0, 1fr);
    background: #ebe4dc;
}

.story-copy {
    position: relative;
    display: grid;
    align-content: center;
    justify-items: start;
    min-height: 365px;
    padding: 64px clamp(28px, 7vw, 96px);
}

.story-copy > p {
    color: #4e453f;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.15em;
}

.story-copy h2 {
    margin-top: 20px;
    color: #6c5b4e;
    font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
    font-size: clamp(28px, 3vw, 40px);
    font-weight: 400;
    line-height: 1.22;
}

.story-copy svg {
    position: absolute;
    right: 20%;
    top: 42%;
    width: 34px;
    height: 24px;
    color: #a89485;
    stroke-width: 1.25;
}

.story-copy span {
    max-width: 330px;
    margin-top: 22px;
    color: #4e453f;
    font-size: 13px;
    line-height: 1.9;
}

.story-copy a {
    min-width: 136px;
    min-height: 42px;
    margin-top: 28px;
    background: transparent;
}

.story-band > img {
    width: 100%;
    height: 100%;
    min-height: 365px;
    object-fit: cover;
}

.benefit-strip {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    border-bottom: 1px solid #e5ded6;
    background: #fcf9f3;
}

.benefit-strip article {
    display: grid;
    justify-items: center;
    min-height: 165px;
    align-content: center;
    border-right: 1px solid #e5ded6;
    padding: 24px;
    text-align: center;
}

.benefit-strip article:last-child {
    border-right: 0;
}

.benefit-strip svg {
    width: 30px;
    height: 30px;
    margin-bottom: 11px;
    color: #6c5b4e;
    stroke-width: 1.2;
}

.benefit-strip h3 {
    color: #6c5b4e;
    font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
    font-size: 15px;
    font-weight: 400;
}

.benefit-strip p {
    max-width: 170px;
    margin-top: 5px;
    color: #4e453f;
    font-size: 12px;
    line-height: 1.55;
}

.instagram-section {
    display: grid;
    justify-items: center;
    padding: 38px 0 48px;
}

.instagram-grid {
    display: grid;
    width: 100%;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 10px;
    margin-top: 24px;
}

.instagram-grid img {
    aspect-ratio: 1;
    width: 100%;
    object-fit: cover;
}

.instagram-section > a {
    margin-top: 22px;
}

@media (max-width: 680px) {
    .section-shell,
    .hero-content {
        width: min(100% - 32px, 1220px);
    }

    .category-grid,
    .benefit-strip {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .product-grid,
    .instagram-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .story-band {
        grid-template-columns: 1fr;
    }

    .story-band > img {
        min-height: 280px;
    }

    .benefit-strip article:nth-child(2) {
        border-right: 0;
    }

    .benefit-strip article:nth-child(-n + 2) {
        border-bottom: 1px solid #e5ded6;
    }
}

@media (max-width: 560px) {
    .home-hero {
        min-height: 520px;
    }

    .home-hero::after {
        background: linear-gradient(
            180deg,
            rgba(57, 46, 38, 0.05),
            rgba(57, 46, 38, 0.54)
        );
    }

    .home-hero img {
        width: 100%;
    }

    .hero-content {
        justify-content: end;
        padding-bottom: 54px;
    }

    .hero-content h1 {
        font-size: 42px;
    }

    .category-grid,
    .product-grid,
    .instagram-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .benefit-strip {
        grid-template-columns: 1fr;
    }

    .benefit-strip article,
    .benefit-strip article:nth-child(2) {
        border-right: 0;
    }

    .benefit-strip article:not(:last-child) {
        border-bottom: 1px solid #e5ded6;
    }
}
</style>
