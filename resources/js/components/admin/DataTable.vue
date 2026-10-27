
<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Search, FilterX } from 'lucide-vue-next';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginationData {
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

withDefaults(
    defineProps<{
        pagination?: PaginationData;
        isEmpty?: boolean;
        emptyColspan?: number;
        search?: string;
        searchPlaceholder?: string;
        showSearch?: boolean;
        hasFilters?: boolean;
        showClearFilters?: boolean;
    }>(),
    {
        isEmpty: false,
        emptyColspan: 1,
        search: '',
        searchPlaceholder: 'Buscar registros...',
        showSearch: true,
        hasFilters: false,
        showClearFilters: true,
    },
);

const emit = defineEmits<{
    'update:search': [value: string];
    search: [];
    'clear-filters': [];
}>();
</script>

<template>
    <section
        class="overflow-hidden rounded-2xl border border-[rgba(0,90,37,0.15)] bg-white font-['DM_Sans',sans-serif] text-[#293A30]"
    >
        <!-- Búsqueda y filtros -->
        <div
            v-if="showSearch || $slots.filters"
            class="border-b border-[rgba(0,90,37,0.09)] p-4 sm:p-5"
        >
            <form
                class="grid grid-cols-1 items-center gap-3 sm:grid-cols-2 lg:grid-cols-[minmax(200px,1fr)_minmax(160px,auto)_auto]"
                @submit.prevent="emit('search')"
            >
                <!-- Búsqueda -->
                <div v-if="showSearch" class="relative min-w-0">
                    <Search
                        :size="17"
                        class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[#69766B]"
                    />

                    <input
                        :value="search"
                        type="search"
                        :placeholder="searchPlaceholder"
                        class="h-[42px] w-full rounded-xl border border-[rgba(0,90,37,0.15)] bg-white pl-10 pr-3 text-sm text-[#293A30] outline-none transition-colors duration-[250ms] ease-in-out placeholder:text-[#69766B] hover:border-[rgba(0,90,37,0.35)] focus:border-[#005A25] focus:ring-2 focus:ring-[rgba(0,90,37,0.1)]"
                        @input="
                            emit(
                                'update:search',
                                ($event.target as HTMLInputElement).value,
                            )
                        "
                    />
                </div>

                <!-- Filtros personalizados -->
                <div
                    v-if="$slots.filters"
                    class="flex min-w-0 flex-wrap items-center gap-2"
                >
                    <slot name="filters" />
                </div>

                <!-- Buscar -->
                <button
                    v-if="showSearch"
                    type="submit"
                    class="inline-flex h-[42px] w-full shrink-0 items-center justify-center gap-2 rounded-full border border-[#34463A] bg-transparent px-[23px] text-[13px] font-semibold text-[#34463A] transition-colors duration-[250ms] ease-in-out hover:border-[#34463A] hover:bg-[#34463A] hover:text-white focus-visible:outline focus-visible:outline-[3px] focus-visible:outline-[#D8DFD0] focus-visible:outline-offset-2 sm:col-span-2 lg:col-span-1 lg:w-auto motion-reduce:transition-none"
                >
                    <Search :size="16" />
                    Buscar
                </button>
            </form>

            <!-- Filtros activos -->
            <div
                v-if="hasFilters && showClearFilters"
                class="mt-4 flex flex-wrap items-center gap-2"
            >
                <span class="text-xs font-medium text-[#69766B]">
                    Filtros activos:
                </span>

                <slot name="active-filters" />

                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-2 text-xs font-semibold text-[#005A25] transition-colors duration-[250ms] ease-in-out hover:bg-[#E7E9DF] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#005A25] focus-visible:outline-offset-2 motion-reduce:transition-none"
                    @click="emit('clear-filters')"
                >
                    <FilterX :size="14" />
                    Limpiar filtros
                </button>
            </div>
        </div>

        <!-- Tabla -->
        <div class="w-full overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm">
                <thead
                    v-if="$slots['table-head']"
                    class="bg-[#f9f6ef] text-xs font-semibold uppercase tracking-wide text-[#34463A]"
                >
                    <slot name="table-head" />
                </thead>

                <tbody class="divide-y divide-[rgba(0,90,37,0.09)]">
                    <slot name="table-body" />

                    <!-- Estado vacío -->
                    <tr v-if="isEmpty">
                        <td
                            :colspan="emptyColspan"
                            class="px-6 py-14 text-center"
                        >
                            <slot name="empty">
                                <div
                                    class="mx-auto flex max-w-sm flex-col items-center"
                                >
                                    <div
                                        class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-[#E7E9DF] text-[#005A25]"
                                    >
                                        <Search :size="22" />
                                    </div>

                                    <p
                                        class="font-['Manrope',sans-serif] text-sm font-semibold text-[#293A30]"
                                    >
                                        No se encontraron registros
                                    </p>

                                    <p class="mt-1 text-sm text-[#69766B]">
                                        Intenta cambiar la búsqueda o los filtros.
                                    </p>
                                </div>
                            </slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Resumen y paginación -->
        <div
            v-if="pagination"
            class="flex flex-col gap-4 border-t border-[rgba(0,90,37,0.15)] bg-white px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5"
        >
            <p class="text-sm text-[#69766B]">
                <template v-if="pagination.total > 0">
                    Mostrando
                    <span class="font-semibold text-[#293A30]">
                        {{ pagination.from ?? 0 }}
                    </span>
                    a
                    <span class="font-semibold text-[#293A30]">
                        {{ pagination.to ?? 0 }}
                    </span>
                    de
                    <span class="font-semibold text-[#293A30]">
                        {{ pagination.total }}
                    </span>
                    registros
                </template>

                <template v-else>
                    No hay registros para mostrar
                </template>
            </p>

            <!-- Navegación -->
            <nav
                v-if="pagination.last_page > 1"
                class="flex flex-wrap items-center gap-1"
                aria-label="Paginación"
            >
                <template
                    v-for="(link, index) in pagination.links"
                    :key="`${index}-${link.label}`"
                >
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        preserve-scroll
                        preserve-state
                        :aria-current="link.active ? 'page' : undefined"
                        class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-sm transition-colors duration-[250ms] ease-in-out focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#005A25] focus-visible:outline-offset-2 motion-reduce:transition-none"
                        :class="
                            link.active
                                ? 'border-[#34463A] bg-[#34463A] font-semibold text-white'
                                : 'border-[rgba(0,90,37,0.15)] bg-white text-[#34463A] hover:border-[#005A25] hover:bg-[#E7E9DF]'
                        "
                    >
                        <span v-html="link.label" />
                    </Link>

                    <span
                        v-else
                        class="inline-flex min-h-9 min-w-9 cursor-not-allowed items-center justify-center rounded-lg border border-[rgba(0,90,37,0.09)] px-3 text-sm text-[#69766B] opacity-60"
                    >
                        <span v-html="link.label" />
                    </span>
                </template>
            </nav>
        </div>
    </section>
</template>
