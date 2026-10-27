
<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import type { Component } from 'vue';

withDefaults(
    defineProps<{
        title: string;
        description?: string;
        section?: string;
        icon?: Component;
        actionIcon?: Component;
        actionLabel?: string;
        actionHref?: string;
    }>(),
    {
        description: '',
        section: '',
        actionIcon: () => Plus,
        actionLabel: '',
        actionHref: '',
    },
);

const emit = defineEmits<{
    action: [];
}>();
</script>

<template>
    <header
        class="rounded-2xl border border-[rgba(0,90,37,0.15)] bg-white px-4 py-4 font-['DM_Sans',sans-serif] sm:px-5"
    >
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <!-- Título, icono y descripción -->
            <div class="flex min-w-0 items-start gap-3">
                <div
                    v-if="icon"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#E7E9DF] text-[#005A25]"
                >
                    <component
                        :is="icon"
                        :size="21"
                        :stroke-width="1.8"
                        aria-hidden="true"
                    />
                </div>

                <div class="min-w-0">
                    <h1
                        class="font-['Manrope',sans-serif] text-xl font-bold leading-tight tracking-tight text-[#293A30] sm:text-[21px]"
                    >
                        {{ title }}
                    </h1>

                    <p
                        v-if="description"
                        class="mt-1 text-xs leading-5 text-[#69766B] sm:text-[13px]"
                    >
                        {{ description }}
                    </p>

                    <slot name="details" />
                </div>
            </div>

            <!-- Acciones -->
            <div
                v-if="actionHref || actionLabel || $slots.actions"
                class="flex w-full shrink-0 flex-col gap-2 sm:w-auto sm:flex-row sm:items-center sm:justify-end"
            >
                <slot name="actions" />

                <Link
                    v-if="actionHref && actionLabel"
                    :href="actionHref"
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-full border border-[#34463A] bg-[#34463A] px-[23px] py-0 text-[13px] font-semibold text-white transition-colors duration-[250ms] ease-in-out hover:border-[#741203] hover:bg-[#741203] focus-visible:outline focus-visible:outline-[3px] focus-visible:outline-[#D8DFD0] focus-visible:outline-offset-2 motion-reduce:transition-none"
                >
                    <component
                        :is="actionIcon"
                        :size="16"
                        :stroke-width="2"
                        aria-hidden="true"
                    />
                    <span>{{ actionLabel }}</span>
                </Link>

                <button
                    v-else-if="actionLabel"
                    type="button"
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-full border border-[#34463A] bg-[#34463A] px-[23px] py-0 text-[13px] font-semibold text-white transition-colors duration-[250ms] ease-in-out hover:border-[#741203] hover:bg-[#741203] focus-visible:outline focus-visible:outline-[3px] focus-visible:outline-[#D8DFD0] focus-visible:outline-offset-2 motion-reduce:transition-none"
                    @click="emit('action')"
                >
                    <component
                        :is="actionIcon"
                        :size="16"
                        :stroke-width="2"
                        aria-hidden="true"
                    />
                    <span>{{ actionLabel }}</span>
                </button>
            </div>
        </div>

        <slot />
    </header>
</template>
