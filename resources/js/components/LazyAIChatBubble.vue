<script setup lang="ts">
import { defineAsyncComponent, onMounted, onUnmounted, ref } from 'vue';

const ready = ref(false);
const startOpen = ref(false);
const AIChatBubble = defineAsyncComponent(() => import('./AIChatBubble.vue'));

function wake() {
    startOpen.value = true;
    ready.value = true;
}

onMounted(() => {
    window.addEventListener('lette:open-chat', wake);
    if (typeof window !== 'undefined' && 'requestIdleCallback' in window) {
        window.requestIdleCallback(
            () => {
                ready.value = true;
            },
            { timeout: 3000 },
        );
    } else {
        setTimeout(() => {
            ready.value = true;
        }, 1500);
    }
});

onUnmounted(() => {
    window.removeEventListener('lette:open-chat', wake);
});
</script>

<template>
    <AIChatBubble v-if="ready" :start-open="startOpen" />
</template>
