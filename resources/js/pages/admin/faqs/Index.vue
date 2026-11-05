
<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import {
    FilterX,
    CircleHelp,
    Plus,
    CheckCircle2,
    CircleOff,
    Tags,
} from 'lucide-vue-next'
import Swal from 'sweetalert2'
import { ref } from 'vue'

import DataTable from '@/components/admin/DataTable.vue'
import PageHeader from '@/components/admin/PageHeader.vue'
import TableActions from '@/components/admin/TableActions.vue'
import admin from '@/routes/admin'

interface Faq {
    id: number
    question: string
    answer: string
    category: string | null
    is_active: boolean
    sort_order: number
    created_at?: string
}

interface PaginationLink {
    url: string | null
    label: string
    active: boolean
}

interface FaqsPagination {
    data: Faq[]
    links: PaginationLink[]
    current_page: number
    last_page: number
    from: number | null
    to: number | null
    total: number
}

const props = defineProps<{
    faqs: FaqsPagination
    categories: string[]
    filters?: {
        search?: string
        category?: string
        status?: string
    }
}>()

const search = ref(props.filters?.search ?? '')
const category = ref(props.filters?.category ?? '')
const status = ref(props.filters?.status ?? '')

const hasFilters = () =>
    Boolean(
        search.value.trim() ||
        category.value !== '' ||
        status.value !== '',
    )

const submitSearch = () => {
    router.get(
        admin.faqs.index().url,
        {
            search: search.value.trim() || undefined,
            category: category.value || undefined,
            status: status.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    )
}

const clearFilters = () => {
    search.value = ''
    category.value = ''
    status.value = ''

    router.get(
        admin.faqs.index().url,
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    )
}

const answerPreview = (answer: string): string => {
    const plainText = answer
        .replace(/<[^>]*>/g, ' ')
        .replace(/\s+/g, ' ')
        .trim()

    return plainText.length > 120
        ? `${plainText.substring(0, 120)}...`
        : plainText
}

const deleteFaq = (faq: Faq) => {
    Swal.fire({
        title: '¿Eliminar pregunta frecuente?',
        text: `Se eliminará "${faq.question}". Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(admin.faqs.destroy(faq.id).url, {
                preserveScroll: true,
            })
        }
    })
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Panel',
                href: admin.dashboard(),
            },
            {
                title: 'Preguntas frecuentes',
                href: admin.faqs.index,
            },
        ],
    },
})
</script>

<template>
    <Head title="Preguntas frecuentes" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-hidden p-4 sm:p-6">
        <PageHeader
            title="Preguntas frecuentes"
            description="Administra las preguntas y respuestas que ayudan a tus clientes a conocer mejor nuestros productos y servicios."
            section="Contenido"
            :icon="CircleHelp"
            :action-icon="Plus"
            action-label="Nueva pregunta"
            :action-href="admin.faqs.create().url"
        />

        <DataTable
            :pagination="faqs"
            :is-empty="faqs.data.length === 0"
            :empty-colspan="5"
            v-model:search="search"
            search-placeholder="Buscar preguntas o respuestas..."
            :has-filters="hasFilters()"
            @search="submitSearch"
            @clear-filters="clearFilters"
        >
            <template #active-filters>
                <div
                    v-if="hasFilters()"
                    class="flex flex-wrap items-center gap-2"
                >
                    <span
                        v-if="search.trim()"
                        class="inline-flex items-center gap-2 rounded-full border border-[#DCE7DF] bg-[#EDF4EF] px-3 py-1.5 text-xs font-medium text-[#244334]"
                    >
                        <FilterX class="h-3.5 w-3.5" />
                        Búsqueda: {{ search }}
                    </span>

                    <span
                        v-if="category"
                        class="inline-flex items-center gap-2 rounded-full border border-[#DCE7DF] bg-[#EDF4EF] px-3 py-1.5 text-xs font-medium text-[#244334]"
                    >
                        <Tags class="h-3.5 w-3.5" />
                        Categoría: {{ category }}
                    </span>

                    <span
                        v-if="status"
                        class="rounded-full border border-[#DCE7DF] bg-[#EDF4EF] px-3 py-1.5 text-xs font-medium text-[#244334]"
                    >
                        Estado: {{ status === 'active' ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
            </template>

            <template #table-head>
                <tr>
                    <th class="px-5 py-4 font-semibold sm:px-6">
                        Pregunta
                    </th>

                    <th class="px-5 py-4 font-semibold sm:px-6">
                        Categoría
                    </th>

                    <th class="px-5 py-4 text-center font-semibold sm:px-6">
                        Orden
                    </th>

                    <th class="px-5 py-4 font-semibold sm:px-6">
                        Estado
                    </th>

                    <th class="px-5 py-4 text-right font-semibold sm:px-6">
                        Acciones
                    </th>
                </tr>
            </template>

            <template #table-body>
                <tr
                    v-for="faq in faqs.data"
                    :key="faq.id"
                    class="transition-colors hover:bg-[#F6F9F6]"
                >
                    <td class="px-5 py-4 sm:px-6">
                        <div class="flex min-w-[260px] items-start gap-3">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-[#DCE7DF] bg-[#E4F2E8] text-[#187744]"
                            >
                                <CircleHelp class="h-5 w-5" />
                            </div>

                            <div class="min-w-0">
                                <p class="font-semibold text-[#244334]">
                                    {{ faq.question }}
                                </p>

                                <p
                                    class="mt-1 max-w-xl text-xs leading-relaxed text-[#718277]"
                                >
                                    {{ answerPreview(faq.answer) }}
                                </p>

                                <p class="mt-1 text-xs text-[#87958B]">
                                    ID: {{ faq.id }}
                                </p>
                            </div>
                        </div>
                    </td>

                    <td class="px-5 py-4 sm:px-6">
                        <span
                            v-if="faq.category"
                            class="inline-flex items-center gap-1.5 rounded-full border border-[#DCE7DF] bg-[#F6F9F6] px-3 py-1.5 text-xs font-medium text-[#385644]"
                        >
                            <Tags class="h-3.5 w-3.5" />
                            {{ faq.category }}
                        </span>

                        <span
                            v-else
                            class="text-sm text-[#87958B]"
                        >
                            Sin categoría
                        </span>
                    </td>

                    <td class="px-5 py-4 text-center sm:px-6">
                        <span
                            class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg bg-[#E4F2E8] px-2 font-semibold text-[#187744]"
                        >
                            {{ faq.sort_order }}
                        </span>
                    </td>

                    <td class="whitespace-nowrap px-5 py-4 sm:px-6">
                        <span
                            v-if="faq.is_active"
                            class="inline-flex items-center gap-1.5 rounded-full border border-[#C8E5D1] bg-[#E9F4EC] px-2.5 py-1 text-xs font-semibold text-[#187744]"
                        >
                            <CheckCircle2 class="h-3.5 w-3.5" />
                            Activa
                        </span>

                        <span
                            v-else
                            class="inline-flex items-center gap-1.5 rounded-full border border-[#DCE7DF] bg-[#F6F9F6] px-2.5 py-1 text-xs font-medium text-[#84958A]"
                        >
                            <CircleOff class="h-3.5 w-3.5" />
                            Inactiva
                        </span>
                    </td>

                    <TableActions
                        :edit-url="admin.faqs.edit(faq.id).url"
                        :show-view="false"
                        item-name="pregunta frecuente"
                        @delete="deleteFaq(faq)"
                    />
                </tr>
            </template>

            <template #empty>
                <div class="flex flex-col items-center justify-center gap-3 py-8">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#E4F2E8] text-[#187744]"
                    >
                        <CircleHelp class="h-6 w-6" />
                    </div>

                    <div class="text-center">
                        <p class="font-medium text-[#244334]">
                            No se encontraron preguntas
                        </p>

                        <p class="mt-1 text-sm text-[#718277]">
                            {{
                                hasFilters()
                                    ? 'Prueba con otros filtros o limpia la búsqueda.'
                                    : 'Todavía no hay preguntas frecuentes registradas.'
                            }}
                        </p>
                    </div>
                </div>
            </template>
        </DataTable>

        <div
            v-if="categories.length"
            class="rounded-xl border border-[#DCE7DF] bg-white p-4 sm:p-5"
        >
            <div class="mb-3 flex items-center gap-2">
                <Tags class="h-4 w-4 text-[#187744]" />
                <h3 class="text-sm font-semibold text-[#244334]">
                    Filtrar por categoría
                </h3>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <select
                    v-model="category"
                    @change="submitSearch"
                    class="w-full rounded-lg border border-[#DCE7DF] bg-[#F6F9F6] px-3 py-2.5 text-sm text-[#244334] outline-none transition focus:border-[#187744] focus:ring-2 focus:ring-[#187744]/10 sm:max-w-xs"
                >
                    <option value="">
                        Todas las categorías
                    </option>

                    <option
                        v-for="item in categories"
                        :key="item"
                        :value="item"
                    >
                        {{ item }}
                    </option>
                </select>

                <select
                    v-model="status"
                    @change="submitSearch"
                    class="w-full rounded-lg border border-[#DCE7DF] bg-[#F6F9F6] px-3 py-2.5 text-sm text-[#244334] outline-none transition focus:border-[#187744] focus:ring-2 focus:ring-[#187744]/10 sm:max-w-xs"
                >
                    <option value="">
                        Todos los estados
                    </option>

                    <option value="active">
                        Activas
                    </option>

                    <option value="inactive">
                        Inactivas
                    </option>
                </select>

                <button
                    v-if="hasFilters()"
                    type="button"
                    @click="clearFilters"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-[#DCE7DF] px-4 py-2.5 text-sm font-medium text-[#52665A] transition hover:bg-[#F2F7F4]"
                >
                    <FilterX class="h-4 w-4" />
                    Limpiar filtros
                </button>
            </div>
        </div>
    </div>
</template>
