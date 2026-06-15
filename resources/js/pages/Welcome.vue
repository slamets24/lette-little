<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ArrowLeft, Check, ChevronLeft, ChevronRight, Clock3, Cloud, CreditCard, Heart, Home, LayoutDashboard, Leaf, MapPin, Minus, Package, Plus, Search, ShieldCheck, ShoppingBag, Sparkles, Star, Store, Trash2, UserRound } from '@lucide/vue';
import type { Component } from 'vue';
import { computed, ref } from 'vue';

type Screen = 'home' | 'product' | 'search' | 'wishlist' | 'cart' | 'checkout' | 'payment' | 'address' | 'confirmation' | 'orders' | 'profile' | 'dashboard';
type Product = { id: string; name: string; tag: string; price: number; image: string; color: string };
type NavItem = { key: Screen; label: string; icon: Component };

const heroImage = 'https://lh3.googleusercontent.com/aida-public/AB6AXuBozw4nzZBNtKFF3mHYx2mr2Hki0ray98K1YqcLsU4up6rP2rEEcG8ksyh5Il-rHPhhh2QWahTfhDHxfFYOltQpO918oUJ7CGNHiqBxaXyjYOrHKDwQ81QVdCxSMPIQ7lEMvzOAz_BcO2rQaTy0WnD9BILjkYmTaHEo59OcFYNtKFoP6xP9QTiTW4TnUf34e9L93jnVuogv3-bBGAmwSRRAZ6vhFyl14JAmdaAFvpbW2m3ERrA2hgqcUWrvbVsE59AshTx2GevhyxXk';
const productDetailImage = 'https://lh3.googleusercontent.com/aida-public/AB6AXuBuMle4IUYcKbrZyByWQIzhOuNyv7n1uX9XPAazUqdjnFLxV_gUE978QPQ62VP9z4Gt38-_7fyvCVc-QqNrzGPOZSgK6_Ed9hRmNKs71CeN4lkFSmWR4yD2M2OSpTgwOPUO_1GGm1rlQ3QvdqIYqeIM9Jf2JbdICQIa-3oJOqswIRUiuf1Rzll9wQ8XWSp9f9opY3hLLE6HqdZWawVjJGrvNQ1UemkdEQg-EJUa8sLJNL_LpH_z9RmxOBOh3k-IHZ9CRw8ZTG8GnLID';

const products: Product[] = [
    { id: 'onesie', name: 'Cloud-Soft Onesie', tag: 'Organic Cotton', price: 24, color: 'Soft Cream', image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuC0gn0Xrllkq8qzm6nd8qy8S6E-kcRv97sYsH-ohUINg_fq0o_2xWF5krbYUNK5g7evKdY0adcT4PqfQALo4hFpgnDY8Lq0Mz18UxjX55HYEEYZCBUNHkb7YD1j-Lsg8fOC52mFt5UNxC2iIetN2mYt6FQWdqOXDHqxeRA7YHkm0hWyp_pLGCT9TK0u9KvPIDGH_iE4awyCvAnQvsDHkCpKd6aa0RK73ArIyp27mHEqZL6TumopHL2ZOSTcxGc_zXyxohp8ZwrbKTqz' },
    { id: 'swaddle', name: 'Gentle Touch Swaddle', tag: 'Muslin Fabric', price: 18, color: 'Sage', image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuC7pVcKoxiXV0Wl39qxxpt-NVNE5g7wUH_MgKojGcNETEd7FaRIcPo6l4BMNcsx8IIEFvcYQeNJBKF2ypwkqpCq8q3PLpN30d2F8JGrZAFIiOMg9iaqMjH76dndAiQmsPdFjs7cwvPncdKoGaz_pDCHmcQgnoMO0tFrFtdFRt6-vp7Ciu5xLDH3pORgeFfE3LeQiGHa2Q72tbaCEHIbB7J-xVtmrnysK_XDrQUtuYtTatWkrs3BHAjsho_7AForhiwjhUUDEC44ox7S' },
    { id: 'socks', name: 'Tiny Comfort Socks', tag: 'Multipack', price: 12, color: 'Sand', image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuC6gup6wgnI2am5iIM6hIcGnABmGt_G2K9hj6__xrbvPtdaZ0l1A8jbiahTAwxi0XahuEx02rOOHLXsQeZa5p3Z2qkBsrSSPEQWb78prr2WINjkUOlECWl1wPTtJk7TrIqfT7mCsVd-hkKZRYXc5dt5sXjp43qc-QRBjASU5taTx07noSYaHx56SB3TzYaM5r2oFPGVSLxA28Xe0cmx5v_bW_PKohYTUCDKk4uyXLDoxiOWRsUnw8ezPKmoGAok1A0UBjbpeI5Vp2Np' },
    { id: 'pillow', name: 'Dreamy Cloud Pillow', tag: 'Nursery Decor', price: 22, color: 'Ivory', image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuB3Y04jAnYtHiIEdCR0DNnhngwCKMbyL2qkqucjKzsCXeALiPQkNG38zqZpoobAnS58SDDOZN6PT7QBJgVVpgY6yQ7PT2TT7kCfQ0oqrO_lyh8dUK5gVjs9ktvPg3lmQ7yL0zsqsyyHSfWCBqMlupEHpS_I561RaBvPSO7SymzCzUv98kdWdcjmkZXWYAnC5SM7UXfai_lmUHn7apx0mK6Sc7hS9pJMsQalhuAFWaQ3-gBnYpM0qrZgRAou1gXWWSDcWTRfiOrmSomb' },
];

const categories = [
    { label: 'Onesies', icon: Sparkles, tone: 'peach' },
    { label: 'Swaddles', icon: Leaf, tone: 'sage' },
    { label: 'Accessories', icon: Store, tone: 'cream' },
    { label: 'Soft Toys', icon: Heart, tone: 'sand' },
    { label: 'Nursery', icon: Cloud, tone: 'stone' },
];
const navItems: NavItem[] = [
    { key: 'home', label: 'Home', icon: Home },
    { key: 'search', label: 'Shop', icon: Search },
    { key: 'wishlist', label: 'Favorit', icon: Heart },
    { key: 'profile', label: 'Akun', icon: UserRound },
];

const activeScreen = ref<Screen>('home');
const selectedSize = ref('3-6M');
const selectedColor = ref('Soft Cream');
const searchQuery = ref('onesie');
const wishlistIds = ref(new Set(['onesie', 'pillow']));
const cartItems = ref([{ productId: 'onesie', size: '3-6M', qty: 1 }, { productId: 'swaddle', size: 'One Size', qty: 1 }]);
const currentProduct = computed(() => products[0]);
const cartProducts = computed(() => cartItems.value.map((item) => ({ ...item, product: products.find((product) => product.id === item.productId) })).filter((item): item is typeof item & { product: Product } => Boolean(item.product)));
const subtotal = computed(() => cartProducts.value.reduce((total, item) => total + item.product.price * item.qty, 0));
const shipping = computed(() => (subtotal.value >= 50 ? 0 : 5));
const total = computed(() => subtotal.value + shipping.value);
const wishlistProducts = computed(() => products.filter((product) => wishlistIds.value.has(product.id)));
const filteredProducts = computed(() => products.filter((product) => `${product.name} ${product.tag}`.toLowerCase().includes(searchQuery.value.toLowerCase())));

function money(value: number) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
}
function goTo(screen: Screen) {
    activeScreen.value = screen;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
function toggleFavorite(productId: string) {
    const next = new Set(wishlistIds.value);

    if (next.has(productId)) {
        next.delete(productId);
    } else {
        next.add(productId);
    }

    wishlistIds.value = next;
}
function addToCart(productId = currentProduct.value.id) {
    const existing = cartItems.value.find((item) => item.productId === productId);

    if (existing) {
        existing.qty += 1;
        cartItems.value = [...cartItems.value];

        return;
    }

    cartItems.value = [...cartItems.value, { productId, size: productId === 'onesie' ? selectedSize.value : 'One Size', qty: 1 }];
}
function updateQty(productId: string, delta: number) {
    cartItems.value = cartItems.value.map((item) => item.productId === productId ? { ...item, qty: Math.max(0, item.qty + delta) } : item).filter((item) => item.qty > 0);
}
</script>

<template>
    <Head title="Lette Little">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Be+Vietnam+Pro:wght@400;500;600&display=swap" rel="stylesheet" />
    </Head>

    <div class="lette-app min-h-screen bg-[#fcf9f3] text-[#1c1c18] antialiased">
        <header class="lette-header">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 md:px-8">
                <button v-if="activeScreen !== 'home'" type="button" class="icon-button" aria-label="Kembali" @click="goTo('home')"><ArrowLeft /></button>
                <button v-else type="button" class="icon-button" aria-label="Beranda" @click="goTo('home')"><Cloud /></button>
                <button type="button" class="brand-lockup" @click="goTo('home')">Lette Little</button>
                <div class="flex items-center gap-2">
                    <button type="button" class="icon-button" aria-label="Cari" @click="goTo('search')"><Search /></button>
                    <button type="button" class="icon-button relative" aria-label="Keranjang" @click="goTo('cart')"><ShoppingBag /><span v-if="cartItems.length" class="cart-dot">{{ cartItems.length }}</span></button>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 pb-28 pt-20 md:px-8 md:pb-12">
            <section v-if="activeScreen === 'home'" class="flex flex-col gap-16">
                <div class="hero-shell">
                    <img :src="heroImage" alt="Bayi memakai onesie lembut Lette Little" class="hero-image" />
                    <div class="hero-copy">
                        <h1>Made Light.<br />Made Gentle.</h1>
                        <p>Curated essentials for your little ones, crafted with soft organic textiles and quiet everyday comfort.</p>
                        <button type="button" class="primary-action" @click="goTo('search')">Explore Collection</button>
                    </div>
                </div>

                <section class="section-stack">
                    <div class="section-heading"><h2>Categories</h2><button type="button" @click="goTo('search')">View All</button></div>
                    <div class="category-rail">
                        <button v-for="category in categories" :key="category.label" type="button" class="category-item" :data-tone="category.tone" @click="goTo('search')"><span><component :is="category.icon" /></span>{{ category.label }}</button>
                    </div>
                </section>

                <section class="section-stack">
                    <div class="section-heading"><h2>Featured Favorites</h2><div class="hidden gap-2 md:flex"><button type="button" class="round-control" aria-label="Sebelumnya"><ChevronLeft /></button><button type="button" class="round-control" aria-label="Berikutnya"><ChevronRight /></button></div></div>
                    <div class="product-grid">
                        <article v-for="product in products" :key="product.id" class="product-card">
                            <button type="button" class="product-media" @click="goTo('product')"><img :src="product.image" :alt="product.name" /></button>
                            <button type="button" class="favorite-button" :class="{ active: wishlistIds.has(product.id) }" aria-label="Favoritkan produk" @click="toggleFavorite(product.id)"><Heart /></button>
                            <div class="product-copy"><span>{{ product.tag }}</span><button type="button" @click="goTo('product')">{{ product.name }}</button><strong>{{ money(product.price) }}</strong></div>
                            <button type="button" class="mini-cart" aria-label="Tambah ke keranjang" @click="addToCart(product.id)"><Plus /></button>
                        </article>
                    </div>
                </section>

                <section class="club-panel">
                    <div><h2>Join the Little Club</h2><p>Receive gentle updates, parenting tips, and early access to our new collections.</p></div>
                    <form class="club-form" @submit.prevent="goTo('profile')"><input type="email" value="hello@lette.test" aria-label="Email" /><button type="submit">Subscribe</button></form>
                </section>
            </section>

            <section v-else-if="activeScreen === 'product'" class="detail-layout">
                <div class="detail-media"><img :src="productDetailImage" alt="Cloud-Soft Onesie" /><button type="button" class="favorite-button detail-favorite" :class="{ active: wishlistIds.has(currentProduct.id) }" aria-label="Favoritkan produk" @click="toggleFavorite(currentProduct.id)"><Heart /></button></div>
                <div class="detail-copy">
                    <div class="rating-line"><Star v-for="index in 5" :key="index" /><span>4.9 dari 128 ulasan</span></div>
                    <h1>Cloud-Soft Onesie</h1><strong>{{ money(currentProduct.price) }}</strong>
                    <p>A breathable organic cotton essential with a cloud-soft handfeel, nickel-free snaps, and a relaxed fit for naps, cuddles, and slow mornings.</p>
                    <div class="option-block"><div class="option-label"><span>COLOR</span><em>{{ selectedColor }}</em></div><div class="swatch-row"><button v-for="color in ['Soft Cream', 'Blush Sand', 'Warm Stone']" :key="color" type="button" :class="['swatch', { selected: selectedColor === color }]" :aria-label="color" @click="selectedColor = color" /></div></div>
                    <div class="option-block"><div class="option-label"><span>SIZE</span><button type="button">Size Guide</button></div><div class="size-grid"><button v-for="size in ['0-3M', '3-6M', '6-9M', '9-12M']" :key="size" type="button" :class="{ selected: selectedSize === size }" @click="selectedSize = size">{{ size }}</button></div></div>
                    <div class="action-stack"><button type="button" class="primary-action" @click="addToCart(); goTo('cart')">Add to Cart</button><button type="button" class="secondary-action" @click="goTo('checkout')">Buy Now</button></div>
                    <div class="soft-accordion"><article><div><Package /><span>Shipping & Returns</span></div><p>Free standard shipping over $50. Returns accepted within 30 days for unworn items.</p></article><article><div><Leaf /><span>Sustainability</span></div><p>GOTS certified organic cotton and recyclable, plastic-free packaging.</p></article></div>
                </div>
            </section>

            <section v-else-if="activeScreen === 'search'" class="screen-stack"><div class="screen-title"><h1>Pencarian Produk</h1><p>Temukan essentials yang ringan, lembut, dan siap jadi hadiah kecil.</p></div><label class="search-field"><Search /><input v-model="searchQuery" type="search" placeholder="Cari onesie, swaddle, nursery..." /></label><div class="chip-row"><button type="button">Organic</button><button type="button">0-6M</button><button type="button">Gift Ready</button></div><div class="product-grid"><article v-for="product in filteredProducts" :key="product.id" class="product-card compact"><button type="button" class="product-media" @click="goTo('product')"><img :src="product.image" :alt="product.name" /></button><div class="product-copy"><span>{{ product.tag }}</span><button type="button" @click="goTo('product')">{{ product.name }}</button><strong>{{ money(product.price) }}</strong></div></article></div></section>

            <section v-else-if="activeScreen === 'wishlist'" class="screen-stack"><div class="screen-title"><h1>Daftar Keinginan</h1><p>Produk yang disimpan untuk nanti, saat momen gifting terasa pas.</p></div><div class="product-grid"><article v-for="product in wishlistProducts" :key="product.id" class="product-card compact"><button type="button" class="product-media" @click="goTo('product')"><img :src="product.image" :alt="product.name" /></button><button type="button" class="favorite-button active" @click="toggleFavorite(product.id)"><Heart /></button><div class="product-copy"><span>{{ product.tag }}</span><button type="button" @click="goTo('product')">{{ product.name }}</button><strong>{{ money(product.price) }}</strong></div></article></div></section>

            <section v-else-if="activeScreen === 'cart'" class="screen-stack cart-layout"><div class="screen-title"><h1>Shopping Cart</h1><p>Items selected with care for your little one.</p></div><div class="cart-list"><article v-for="item in cartProducts" :key="item.productId" class="cart-row"><img :src="item.product.image" :alt="item.product.name" /><div><h2>{{ item.product.name }}</h2><p>Size: {{ item.size }} | Color: {{ item.product.color }}</p><strong>{{ money(item.product.price) }}</strong></div><div class="qty-control"><button type="button" @click="updateQty(item.productId, -1)"><Minus /></button><span>{{ item.qty }}</span><button type="button" @click="updateQty(item.productId, 1)"><Plus /></button></div><button type="button" class="trash-button" aria-label="Hapus item" @click="updateQty(item.productId, -99)"><Trash2 /></button></article></div><aside class="summary-panel"><h2>Order Summary</h2><dl><div><dt>Subtotal</dt><dd>{{ money(subtotal) }}</dd></div><div><dt>Shipping</dt><dd>{{ shipping === 0 ? 'Free' : money(shipping) }}</dd></div><div><dt>Total</dt><dd>{{ money(total) }}</dd></div></dl><button type="button" class="primary-action" @click="goTo('checkout')">Secure Checkout</button><p><ShieldCheck /> Secure Checkout Guaranteed</p></aside></section>

            <section v-else-if="activeScreen === 'checkout'" class="screen-stack checkout-grid"><div class="screen-title"><h1>Checkout</h1><p>Review alamat, pembayaran, dan ringkasan sebelum menyelesaikan pesanan.</p></div><div class="checkout-cards"><button type="button" class="info-card" @click="goTo('address')"><MapPin /><span>Alamat Pengiriman</span><strong>Jl. Anggrek No. 18, Jakarta Selatan</strong></button><button type="button" class="info-card" @click="goTo('payment')"><CreditCard /><span>Metode Pembayaran</span><strong>Visa ending 4242</strong></button></div><aside class="summary-panel"><h2>Ringkasan</h2><dl><div><dt>Subtotal</dt><dd>{{ money(subtotal) }}</dd></div><div><dt>Shipping</dt><dd>{{ shipping === 0 ? 'Free' : money(shipping) }}</dd></div><div><dt>Total</dt><dd>{{ money(total) }}</dd></div></dl><button type="button" class="primary-action" @click="goTo('confirmation')">Place Order</button></aside></section>

            <section v-else-if="activeScreen === 'address'" class="screen-stack narrow-screen"><div class="screen-title"><h1>Alamat Pengiriman</h1><p>Pilih alamat utama untuk pengiriman lembut berikutnya.</p></div><article class="address-card selected"><Check /><h2>Rumah</h2><p>Amelia Putri<br />Jl. Anggrek No. 18, Cilandak, Jakarta Selatan 12430</p><span>Instruksi: titip di resepsionis jika tidak di rumah.</span></article><article class="address-card"><MapPin /><h2>Kantor</h2><p>Lette Studio<br />Jl. Kemang Raya No. 9, Jakarta Selatan</p></article><button type="button" class="primary-action" @click="goTo('checkout')">Use This Address</button></section>
            <section v-else-if="activeScreen === 'payment'" class="screen-stack narrow-screen"><div class="screen-title"><h1>Metode Pembayaran</h1><p>Pembayaran aman dengan kartu, e-wallet, atau transfer bank.</p></div><article class="payment-card selected"><CreditCard /><div><h2>Visa ending 4242</h2><p>Expires 08/28</p></div><Check /></article><article class="payment-card"><Sparkles /><div><h2>Little Wallet</h2><p>Balance $64.00</p></div></article><article class="payment-card"><Package /><div><h2>Bank Transfer</h2><p>Manual confirmation</p></div></article><button type="button" class="primary-action" @click="goTo('checkout')">Use Payment Method</button></section>
            <section v-else-if="activeScreen === 'confirmation'" class="confirmation-screen"><div class="confirm-mark"><Check /></div><h1>Order Confirmed</h1><p>Pesanan LL-2406 sudah diterima. Kami sedang menyiapkan paket yang ringan dan lembut untuk si kecil.</p><div class="summary-panel"><h2>Delivery Estimate</h2><p class="estimate"><Clock3 /> 3-5 business days</p><dl><div><dt>Total Paid</dt><dd>{{ money(total) }}</dd></div></dl></div><button type="button" class="primary-action" @click="goTo('orders')">View Order History</button></section>
            <section v-else-if="activeScreen === 'orders'" class="screen-stack narrow-screen"><div class="screen-title"><h1>Riwayat Pesanan</h1><p>Semua order Lette Little tersimpan rapi di sini.</p></div><article class="order-card"><Package /><div><h2>LL-2406</h2><p>Cloud-Soft Onesie, Gentle Touch Swaddle</p><span>Preparing shipment</span></div><strong>{{ money(total) }}</strong></article><article class="order-card"><Check /><div><h2>LL-2318</h2><p>Dreamy Cloud Pillow</p><span>Delivered</span></div><strong>$22.00</strong></article></section>
            <section v-else-if="activeScreen === 'profile'" class="screen-stack profile-layout"><div class="profile-hero"><div class="avatar">AP</div><h1>Amelia Putri</h1><p>Member Little Club sejak 2024</p></div><div class="profile-actions"><button type="button" @click="goTo('dashboard')"><LayoutDashboard /><span>Dashboard</span></button><button type="button" @click="goTo('orders')"><Package /><span>Riwayat Pesanan</span></button><button type="button" @click="goTo('address')"><MapPin /><span>Alamat</span></button><button type="button" @click="goTo('payment')"><CreditCard /><span>Pembayaran</span></button></div></section>
            <section v-else class="screen-stack dashboard-screen"><div class="screen-title"><h1>Dashboard</h1><p>Ringkasan aktivitas belanja dan preferensi essentials si kecil.</p></div><div class="metric-grid"><article><span>Open Orders</span><strong>1</strong></article><article><span>Wishlist</span><strong>{{ wishlistProducts.length }}</strong></article><article><span>Little Points</span><strong>840</strong></article></div><div class="care-panel"><h2>Gentle Reminder</h2><p>Ukuran 3-6M biasanya nyaman sampai tinggi 67 cm. Cek size guide sebelum repeat order.</p><button type="button" class="secondary-action" @click="goTo('product')">Review Onesie</button></div></section>
        </main>

        <nav class="bottom-nav" aria-label="Navigasi utama"><button v-for="item in navItems" :key="item.key" type="button" :class="{ active: activeScreen === item.key }" @click="goTo(item.key)"><component :is="item.icon" /><span>{{ item.label }}</span></button></nav>
    </div>
</template>



