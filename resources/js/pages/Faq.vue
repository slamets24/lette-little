<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import InfoPage from '@/components/lette/InfoPage.vue';

type FaqAnswerPart = {
    text: string;
    href?: string;
    internal?: boolean;
};

type FaqItem = {
    question: string;
    answer: FaqAnswerPart[];
};

defineProps<{
    faqs: FaqItem[];
}>();
</script>

<template>
    <InfoPage eyebrow="HELP" title="FAQ">
        <template v-for="faq in faqs" :key="faq.question">
            <h2>{{ faq.question }}</h2>
            <p>
                <template
                    v-for="(part, index) in faq.answer"
                    :key="`${faq.question}-${index}`"
                >
                    <Link v-if="part.href && part.internal" :href="part.href">{{
                        part.text
                    }}</Link>
                    <a v-else-if="part.href" :href="part.href">{{
                        part.text
                    }}</a>
                    <template v-else>{{ part.text }}</template>
                </template>
            </p>
        </template>
    </InfoPage>
</template>
