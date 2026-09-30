<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import InfoPage from '@/components/lette/InfoPage.vue';
import { careGuide, shippingReturns, sizeGuide } from '@/routes';
</script>

<template>
    <InfoPage eyebrow="HELP" title="FAQ">
        <h2>How do I choose a size?</h2>
        <p>
            Use the
            <Link :href="sizeGuide.url()">size guide</Link>
            and your baby's current height and weight. When you are between
            sizes, pick the larger one.
        </p>
        <h2>How should I wash the clothes?</h2>
        <p>
            Cold gentle wash, mild detergent, and line dry in the shade. See
            the
            <Link :href="careGuide.url()">care guide</Link>
            for the full notes.
        </p>
        <h2>When do I pay shipping?</h2>
        <p>
            The product total is paid at checkout. Courier shipping is quoted
            after we have your address. Read
            <Link :href="shippingReturns.url()">shipping & returns</Link>
            for timing and return rules.
        </p>
        <h2>What payment methods do you accept?</h2>
        <p>
            You can pay instantly through Midtrans, or by bank transfer to our
            account. Bank transfer is useful when you want to skip the payment
            gateway fee, or when Midtrans is temporarily unavailable.
        </p>
        <h2>How do I contact you?</h2>
        <p>
            Email
            <a href="mailto:hello@lettelittle.id">hello@lettelittle.id</a>
            or send a message on Instagram
            <a href="https://instagram.com/lettelittle.id">@lettelittle.id</a>.
        </p>
    </InfoPage>
</template>
