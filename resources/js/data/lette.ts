export type Category = {
    name: string;
    slug: string;
    image: string;
    intro: string;
};

export type Product = {
    name: string;
    category: string;
    price: string;
    image: string;
    swatches: string[];
};

export type JournalArticle = {
    title: string;
    excerpt: string;
    date: string;
    image: string;
};

export const asset = (path: string) => `/images/lette/${path}`;

export const categories: Category[] = [
    {
        name: 'Dresses',
        slug: 'dresses',
        image: asset('stitch-dresses.jpg'),
        intro: 'Airy silhouettes for birthdays, family photos, and soft everyday rituals.',
    },
    {
        name: 'Rompers',
        slug: 'rompers',
        image: asset('stitch-rompers.jpg'),
        intro: 'Easy one-piece comfort with breathable fabric and gentle movement.',
    },
    {
        name: 'Sets',
        slug: 'sets',
        image: asset('stitch-sets.jpg'),
        intro: 'Coordinated essentials that make dressing simple and gift-ready.',
    },
    {
        name: 'Sleepwear',
        slug: 'sleepwear',
        image: asset('stitch-sleepwear.jpg'),
        intro: 'Quiet bedtime layers made for warm cuddles and slow mornings.',
    },
];

export const products: Product[] = [
    {
        name: 'Cloud Dress',
        category: 'Dresses',
        price: 'IDR 249.000',
        image: asset('stitch-product-dress.jpg'),
        swatches: ['#f5eadc', '#d8c2a9'],
    },
    {
        name: 'Little Bloom Romper',
        category: 'Rompers',
        price: 'IDR 229.000',
        image: asset('stitch-product-romper.jpg'),
        swatches: ['#efe2d2', '#b8b098'],
    },
    {
        name: 'Morning Set',
        category: 'Sets',
        price: 'IDR 279.000',
        image: asset('stitch-product-set.jpg'),
        swatches: ['#f0dfca', '#c8ad8d'],
    },
    {
        name: 'Luna Dress',
        category: 'Dresses',
        price: 'IDR 259.000',
        image: asset('stitch-product-luna.jpg'),
        swatches: ['#f7efe4', '#d6c9b7'],
    },
    {
        name: 'Cloud Sleep Set',
        category: 'Sleepwear',
        price: 'IDR 279.000',
        image: asset('stitch-product-sleep.jpg'),
        swatches: ['#f8ecdd', '#a89485'],
    },
];

export const benefits = [
    {
        title: 'Soft & Breathable',
        body: 'Premium fabrics that are gentle on delicate skin.',
    },
    {
        title: 'Thoughtful Design',
        body: 'Made for comfort, movement, and play.',
    },
    {
        title: 'Made with Love',
        body: 'Every detail is crafted with care.',
    },
    {
        title: 'Beautifully Packaged',
        body: 'Packed with love, delivered to you.',
    },
];

export const journalArticles: JournalArticle[] = [
    {
        title: 'How to Choose Gentle First Layers',
        excerpt:
            'A simple guide to breathable fabrics, relaxed fits, and tiny details that help babies feel at ease.',
        date: 'June 18, 2026',
        image: asset('stitch-rompers.jpg'),
    },
    {
        title: 'Behind the Cloud Beginning Collection',
        excerpt:
            'Inside the moodboards, fabric tests, and quiet color palette that shaped our newest essentials.',
        date: 'June 12, 2026',
        image: asset('stitch-story.jpg'),
    },
    {
        title: 'Packing a Thoughtful Newborn Gift',
        excerpt:
            'Our favorite combinations for a soft, useful, and beautiful welcome-home package.',
        date: 'June 5, 2026',
        image: asset('stitch-sets.jpg'),
    },
];
