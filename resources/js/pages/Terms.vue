<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import InfoPage from '@/components/lette/InfoPage.vue';
import { shippingReturns } from '@/routes';
</script>

<template>
    <InfoPage eyebrow="LEGAL" title="Terms & Conditions">
        <p>
            By placing an order on Lette Little, you agree to the terms
            below. They describe how we sell, pack, and support orders today.
        </p>
        <h2>Orders and stock</h2>
        <p>
            Products are offered while stock lasts. Prices are in Indonesian
            Rupiah. We may cancel an order if an item cannot be fulfilled and
            will contact you at the email you gave at checkout.
        </p>
        <h2>Payment</h2>
        <p>
            Payment is completed through Midtrans. An order stays unpaid until
            Midtrans confirms a successful payment.
        </p>
        <h2>Shipping and returns</h2>
        <p>
            Delivery cost, packing, and returns follow our
            <Link :href="shippingReturns.url()">shipping & returns</Link>
            page.
        </p>
        <h2>Contact</h2>
        <p>
            Questions about these terms can be sent to
            <a href="mailto:hello@lettelittle.id">hello@lettelittle.id</a>.
        </p>
    </InfoPage>
</template>
