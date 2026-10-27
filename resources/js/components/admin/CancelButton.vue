<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Component } from 'vue';

withDefaults(
    defineProps<{
        href: string;
        label?: string;
        icon?: Component;
        block?: boolean;
    }>(),
    {
        label: 'Cancelar',
        block: false,
    },
);
</script>

<template>
    <Link
        :href="href"
        :class="[
            'inline-flex min-h-12 items-center justify-center gap-[15px] rounded-full border border-[rgba(0,90,37,0.25)] bg-transparent px-[23px] py-0 font-[DM_Sans,sans-serif] text-[13px] font-semibold text-[#34463A]',
            'transition-colors duration-[250ms] ease-in-out motion-reduce:transition-none',
            'hover:border-[#741203] hover:bg-[#741203] hover:text-white',
            'focus-visible:outline focus-visible:outline-[3px] focus-visible:outline-[#b6c79a] focus-visible:outline-offset-2',
            block ? 'w-full' : '',
        ]"
    >
        <component
            :is="icon"
            v-if="icon"
            class="h-4 w-4 shrink-0"
            :size="16"
            :stroke-width="2"
            aria-hidden="true"
        />

        <slot>{{ label }}</slot>
    </Link>
</template>