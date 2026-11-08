<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <img
        src="/images/lette/icon-rounded.png"
        alt="Lette Little"
        :class="['rounded-md object-contain', className]"
        v-bind="$attrs"
    />
</template>
