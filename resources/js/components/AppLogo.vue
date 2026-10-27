<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';

const name = usePage().props.name;
</script>

<template>
    <div class="flex w-full items-center justify-center">
        <img
            src="/images/husky_blanco.webp"
            alt="Industrias Husky"
            class="h-auto w-[129px] object-contain"
        />
    </div>
</template>