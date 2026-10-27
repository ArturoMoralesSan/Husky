<script setup lang="ts">
import type { Component } from 'vue';

withDefaults(
    defineProps<{
        type?: 'button' | 'submit' | 'reset';
        variant?: 'primary' | 'secondary' | 'danger';
        icon?: Component;
        disabled?: boolean;
        loading?: boolean;
        loadingText?: string;
        block?: boolean;
    }>(),
    {
        type: 'button',
        variant: 'primary',
        disabled: false,
        loading: false,
        loadingText: '',
        block: false,
    },
);
</script>

<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        :class="[
            'inline-flex min-h-12 items-center justify-center gap-[15px] rounded-full border px-[23px] py-0 text-[13px] font-semibold',
            'font-[DM_Sans,sans-serif]',
            'transition-colors duration-[250ms] ease-in-out',
            'motion-reduce:transition-none',
            'focus-visible:outline focus-visible:outline-[3px] focus-visible:outline-[#b6c79a] focus-visible:outline-offset-2',
            'disabled:cursor-not-allowed disabled:opacity-50',
            block ? 'w-full' : '',

            variant === 'primary'
                ? 'border-[#34463A] bg-[#34463A] text-white hover:border-[#741203] hover:bg-[#741203]'
                : '',

            variant === 'secondary'
                ? 'border-[rgba(0,90,37,0.25)] bg-transparent text-[#34463A] hover:border-[#741203] hover:bg-[#741203] hover:text-white'
                : '',

            variant === 'danger'
                ? 'border-[#741203] bg-[#741203] text-white hover:border-[#5A0E02] hover:bg-[#5A0E02]'
                : '',
        ]"
    >
        <!-- Indicador de carga -->
        <svg
            v-if="loading"
            class="h-4 w-4 shrink-0 animate-spin motion-reduce:animate-none"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            aria-hidden="true"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            />

            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
            />
        </svg>

        <!-- Icono -->
        <component
            :is="icon"
            v-else-if="icon"
            class="h-4 w-4 shrink-0"
            :size="16"
            :stroke-width="2"
            aria-hidden="true"
        />

        <!-- Texto -->
        <span>
            <template v-if="loading && loadingText">
                {{ loadingText }}
            </template>

            <slot v-else />
        </span>
    </button>
</template>