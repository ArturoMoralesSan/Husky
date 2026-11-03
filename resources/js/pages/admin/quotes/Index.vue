
<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import {
    CalendarDays,
    CheckCircle2,
    ClipboardList,
    Clock,
    FilterX,
    Package,
    Wrench,
} from 'lucide-vue-next'
import Swal from 'sweetalert2'
import { ref } from 'vue'

import DataTable from '@/components/admin/DataTable.vue'
import PageHeader from '@/components/admin/PageHeader.vue'
import admin from '@/routes/admin'

interface Quote {
    id: number
    name: string
    company: string | null
    email: string
    phone: string
    product_id: number | null
    service_id: number | null
    item_name: string
    description: string | null
    status: string
    created_at: string
    product?: {
        id: number
        name: string
    } | null
    service?: {
        id: number
        name: string
    } | null
}

interface PaginationLink {
    url: string | null
    label: string
    active: boolean
}

interface QuotesPagination {
    data: Quote[]
    links: PaginationLink[]
    current_page: number
    last_page: number
    from: number | null
    to: number | null
    total: number
}

const props = defineProps<{
    quotes: QuotesPagination
    statuses: string[]
}>()

const statusLabels: Record<string, string> = {
    pendiente: 'Pendiente',
    en_revision: 'En revisión',
    enviada: 'Enviada',
    aceptada: 'Aceptada',
    rechazada: 'Rechazada',
}

const statusStyles: Record<string, string> = {
    pendiente: 'border-[#F1DFC0] bg-[#FCF5E8] text-[#A4772D]',
    en_revision: 'border-[#C9DDF0] bg-[#EDF5FC] text-[#356B9A]',
    enviada: 'border-[#D8D5EF] bg-[#F1F0FA] text-[#6860A5]',
    aceptada: 'border-[#C8E5D1] bg-[#E9F4EC] text-[#187744]',
    rechazada: 'border-[#F0CECB] bg-[#FFF1EF] text-[#C62828]',
}

const search = ref('')

const hasFilters = () => Boolean(search.value.trim())

const submitSearch = () => {
    router.get(
        admin.quotes.index().url,
        {
            search: search.value.trim() || undefined,
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

    router.get(
        admin.quotes.index().url,
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    )
}

const formatDate = (date: string) => {
    return new Intl.DateTimeFormat('es-MX', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(date))
}

const updateStatus = (quote: Quote, status: string) => {
    if (quote.status === status) return

    const previousStatus = quote.status

    Swal.fire({
        title: '¿Actualizar cotización?',
        text: `El estado cambiará de "${statusLabels[previousStatus] || previousStatus}" a "${statusLabels[status] || status}".`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, actualizar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            router.patch(
                admin.quotes.updateStatus(quote.id).url,
                { status },
                {
                    preserveScroll: true,
                    onError: () => {
                        Swal.fire({
                            title: 'No se pudo actualizar',
                            text: 'Ocurrió un problema al actualizar el estado.',
                            icon: 'error',
                        })
                    },
                },
            )
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
                title: 'Cotizaciones',
                href: admin.quotes.index,
            },
        ],
    },
})
</script>

<template>
    <Head title="Cotizaciones" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-hidden p-4 sm:p-6">
        <PageHeader
            title="Cotizaciones"
            description="Consulta y administra las solicitudes de cotización recibidas desde el sitio web."
            section="Comercial"
            :icon="ClipboardList"
        />

        <DataTable
            :pagination="quotes"
            :is-empty="quotes.data.length === 0"
            :empty-colspan="5"
            v-model:search="search"
            search-placeholder="Buscar por cliente, empresa, correo o producto..."
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
                        class="inline-flex items-center gap-2 rounded-full border border-[#DCE7DF] bg-[#EDF4EF] px-3 py-1.5 text-xs font-medium text-[#244334]"
                    >
                        <FilterX class="h-3.5 w-3.5" />
                        Búsqueda: {{ search }}
                    </span>
                </div>
            </template>

            <template #table-head>
                <tr>
                    <th class="px-5 py-4 font-semibold sm:px-6">
                        Solicitante
                    </th>

                    <th class="px-5 py-4 font-semibold sm:px-6">
                        Producto o servicio
                    </th>

                    <th class="px-5 py-4 font-semibold sm:px-6">
                        Fecha
                    </th>

                    <th class="px-5 py-4 font-semibold sm:px-6">
                        Estado
                    </th>

                    <th class="px-5 py-4 text-right font-semibold sm:px-6">
                        Seguimiento
                    </th>
                </tr>
            </template>

            <template #table-body>
                <tr
                    v-for="quote in quotes.data"
                    :key="quote.id"
                    class="transition-colors hover:bg-[#F6F9F6]"
                >
                    <td class="px-5 py-4 sm:px-6">
                        <div class="min-w-[210px]">
                            <p class="font-semibold text-[#244334]">
                                {{ quote.name }}
                            </p>

                            <p
                                v-if="quote.company"
                                class="mt-0.5 text-xs text-[#718277]"
                            >
                                {{ quote.company }}
                            </p>

                            <p class="mt-1 text-xs text-[#52665A]">
                                {{ quote.email }}
                            </p>

                            <p class="mt-0.5 text-xs text-[#87958B]">
                                {{ quote.phone }}
                            </p>

                            <p class="mt-1 text-xs text-[#87958B]">
                                ID: {{ quote.id }}
                            </p>
                        </div>
                    </td>

                    <td class="px-5 py-4 sm:px-6">
                        <div class="flex min-w-[190px] items-start gap-2.5">
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#E4F2E8] text-[#187744]"
                            >
                                <Package
                                    v-if="quote.product_id"
                                    class="h-4 w-4"
                                />

                                <Wrench
                                    v-else-if="quote.service_id"
                                    class="h-4 w-4"
                                />

                                <ClipboardList
                                    v-else
                                    class="h-4 w-4"
                                />
                            </div>

                            <div>
                                <p class="font-medium text-[#244334]">
                                    {{ quote.item_name }}
                                </p>

                                <p class="mt-1 text-xs text-[#718277]">
                                    {{
                                        quote.product_id
                                            ? 'Producto'
                                            : quote.service_id
                                              ? 'Servicio'
                                              : 'Otro proyecto'
                                    }}
                                </p>

                                <p
                                    v-if="quote.description"
                                    class="mt-1 max-w-xs truncate text-xs text-[#87958B]"
                                    :title="quote.description"
                                >
                                    {{ quote.description }}
                                </p>
                            </div>
                        </div>
                    </td>

                    <td class="whitespace-nowrap px-5 py-4 sm:px-6">
                        <div class="flex items-center gap-2 text-sm text-[#52665A]">
                            <CalendarDays class="h-4 w-4 text-[#87958B]" />
                            {{ formatDate(quote.created_at) }}
                        </div>
                    </td>

                    <td class="whitespace-nowrap px-5 py-4 sm:px-6">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold"
                            :class="statusStyles[quote.status] || 'border-[#DCE7DF] bg-[#F6F9F6] text-[#718277]'"
                        >
                            <CheckCircle2
                                v-if="quote.status === 'aceptada'"
                                class="h-3.5 w-3.5"
                            />

                            <Clock
                                v-else
                                class="h-3.5 w-3.5"
                            />

                            {{ statusLabels[quote.status] || quote.status }}
                        </span>
                    </td>

                    <td class="px-5 py-4 text-right sm:px-6">
                        <select
                            :value="quote.status"
                            class="w-full min-w-[145px] rounded-lg border border-[#DCE7DF] bg-white px-3 py-2 text-xs font-medium text-[#244334] outline-none transition focus:border-[#187744] focus:ring-2 focus:ring-[#187744]/10"
                            @change="updateStatus(
                                quote,
                                ($event.target as HTMLSelectElement).value
                            )"
                        >
                            <option
                                v-for="status in statuses"
                                :key="status"
                                :value="status"
                            >
                                {{ statusLabels[status] || status }}
                            </option>
                        </select>
                    </td>
                </tr>
            </template>

            <template #empty>
                <div class="flex flex-col items-center justify-center gap-3 py-8">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#E4F2E8] text-[#187744]"
                    >
                        <ClipboardList class="h-6 w-6" />
                    </div>

                    <div class="text-center">
                        <p class="font-medium text-[#244334]">
                            No se encontraron cotizaciones
                        </p>

                        <p class="mt-1 text-sm text-[#718277]">
                            {{
                                hasFilters()
                                    ? 'Prueba con otra búsqueda o limpia los filtros.'
                                    : 'Todavía no has recibido solicitudes de cotización.'
                            }}
                        </p>
                    </div>
                </div>
            </template>
        </DataTable>
    </div>
</template>
