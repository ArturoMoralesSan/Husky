<script setup lang="ts">
import { Check } from 'lucide-vue-next';

withDefaults(
    defineProps<{
        id?: string;
        label?: string;
        description?: string;
        error?: string;
        disabled?: boolean;
    }>(),
    {
        id: 'is_active',
        label: 'Registro activo',
        description:
            'Determina si este registro estará disponible para su uso en el sistema.',
        error: '',
        disabled: false,
    },
);

const model = defineModel<boolean>({ required: true });
</script>

<template>
    <div class="form-field font-['DM_Sans',sans-serif]">
        <label
            class="form-label mb-2 block text-sm font-semibold text-[#34463A]"
            :for="id"
        >
            Estado
        </label>

        <label
            :for="id"
            class="flex items-center justify-between gap-4 rounded-xl border border-[rgba(0,90,37,0.15)] bg-[#F9F8F6] p-4 transition-[border-color,background-color] duration-200 ease-in-out hover:border-[rgba(0,90,37,0.4)]"
            :class="{
                'cursor-pointer': !disabled,
                'cursor-not-allowed opacity-60': disabled,
            }"
        >
            <div class="flex min-w-0 items-start gap-3">
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#E7E9DF] text-[#005A25]"
                >
                    <Check class="h-5 w-5" />
                </div>

                <div class="min-w-0">
                    <p class="text-sm font-semibold text-[#293A30]">
                        {{ label }}
                    </p>

                    <p class="mt-1 text-xs leading-5 text-[#69766B]">
                        {{ description }}
                    </p>
                </div>
            </div>

            <input
                :id="id"
                v-model="model"
                type="checkbox"
                class="form-checkbox h-4 w-4 shrink-0 cursor-pointer rounded border-[rgba(0,90,37,0.3)] text-[#005A25] accent-[#005A25] focus:ring-2 focus:ring-[rgba(0,90,37,0.2)] focus:ring-offset-2 disabled:cursor-not-allowed"
                :disabled="disabled"
            />
        </label>

        <p v-if="error" class="form-error mt-2 text-sm text-[#741203]">
            {{ error }}
        </p>
    </div>
</template>