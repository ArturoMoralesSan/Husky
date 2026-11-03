
<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import {
    FilterX,
    Image as ImageIcon,
    BriefcaseBusiness,
    Plus,
    CheckCircle2,
    CircleOff,
} from 'lucide-vue-next'
import Swal from 'sweetalert2'
import { ref } from 'vue'

import DataTable from '@/components/admin/DataTable.vue'
import PageHeader from '@/components/admin/PageHeader.vue'
import TableActions from '@/components/admin/TableActions.vue'
import admin from '@/routes/admin'

interface Service {
    id: number
    name: string
    slug: string
    category: string
    short_description: string | null
    description: string | null
    image_url: string | null
    is_active: boolean
    sort_order: number
    images_count: number
}

interface PaginationLink {
    url: string | null
    label: string
    active: boolean
}

interface ServicesPagination {
    data: Service[]
    links: PaginationLink[]
    current_page: number
    last_page: number
    from: number | null
    to: number | null
    total: number
}

const props = defineProps<{
    services: ServicesPagination
    filters?: {
        search?: string
        category?: string
        status?: string | number | boolean
    }
}>()

const search = ref(props.filters?.search ?? '')
const category = ref(props.filters?.category ?? '')
const status = ref(String(props.filters?.status ?? ''))

const hasFilters = () =>
    Boolean(search.value.trim() || category.value !== '' || status.value !== '')

const submitSearch = () => {
    router.get(
        admin.services.index().url,
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
        admin.services.index().url,
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    )
}

const imageUrl = (url: string | null): string | null => {
    if (!url) return null

    if (
        url.startsWith('http://') ||
        url.startsWith('https://') ||
        url.startsWith('/')
    ) {
        return url
    }

    return `/storage/${url}`
}

const categoryLabel = (value: string): string => {
    switch (value) {
        case 'proyectos':
            return 'Proyectos'
        case 'maquila':
            return 'Maquila'
        default:
            return value || 'Sin categoría'
    }
}

const deleteService = (service: Service) => {
    Swal.fire({
        title: '¿Eliminar servicio?',
        text: `Se eliminará "${service.name}" y sus imágenes registradas. Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(admin.services.destroy(service.id).url, {
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
                title: 'Servicios',
                href: admin.services.index,
            },
        ],
    },
})
</script>

<template>
    <Head title="Servicios" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-hidden p-4 sm:p-6">
        <PageHeader
            title="Servicios"
            description="Administra los servicios de proyectos y maquila."
            section="Catálogo"
            :icon="BriefcaseBusiness"
            :action-icon="Plus"
            action-label="Nuevo servicio"
            :action-href="admin.services.create().url"
        />

        <DataTable
            :pagination="services"
            :is-empty="services.data.length === 0"
            :empty-colspan="6"
            v-model:search="search"
            search-placeholder="Buscar por nombre o descripción..."
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
                        v-if="category !== ''"
                        class="rounded-full border border-[#DCE7DF] bg-[#EDF4EF] px-3 py-1.5 text-xs font-medium text-[#244334]"
                    >
                        Categoría: {{ categoryLabel(category) }}
                    </span>

                    <span
                        v-if="status !== ''"
                        class="rounded-full border border-[#DCE7DF] bg-[#EDF4EF] px-3 py-1.5 text-xs font-medium text-[#244334]"
                    >
                        Estado: {{ status === '1' ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
            </template>

            <template #table-head>
                <tr>
                    <th class="px-5 py-4 font-semibold sm:px-6">
                        Servicio
                    </th>

                    <th class="px-5 py-4 font-semibold sm:px-6">
                        Categoría
                    </th>

                    <th class="px-5 py-4 text-center font-semibold sm:px-6">
                        Imágenes
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
                    v-for="service in services.data"
                    :key="service.id"
                    class="transition-colors hover:bg-[#F6F9F6]"
                >
                    <td class="px-5 py-4 sm:px-6">
                        <div class="flex min-w-[240px] items-center gap-3">
                            <div
                                class="h-14 w-16 shrink-0 overflow-hidden rounded-lg border border-[#DCE7DF] bg-[#F2F7F4]"
                            >
                                <img
                                    v-if="imageUrl(service.image_url)"
                                    :src="imageUrl(service.image_url) ?? ''"
                                    :alt="service.name"
                                    class="h-full w-full object-cover"
                                />

                                <div
                                    v-else
                                    class="flex h-full items-center justify-center text-[#718277]"
                                >
                                    <ImageIcon class="h-5 w-5" />
                                </div>
                            </div>

                            <div class="min-w-0">
                                <p class="font-semibold text-[#244334]">
                                    {{ service.name }}
                                </p>

                                <p
                                    v-if="service.short_description"
                                    class="mt-0.5 max-w-xs truncate text-xs text-[#718277]"
                                >
                                    {{ service.short_description }}
                                </p>

                                <p class="mt-1 text-xs text-[#87958B]">
                                    ID: {{ service.id }}
                                </p>
                            </div>
                        </div>
                    </td>

                    <td class="px-5 py-4 sm:px-6">
                        <span
                            class="inline-flex rounded-full border border-[#DCE7DF] bg-[#F6F9F6] px-3 py-1.5 text-xs font-semibold text-[#385644]"
                        >
                            {{ categoryLabel(service.category) }}
                        </span>
                    </td>

                    <td class="px-5 py-4 text-center sm:px-6">
                        <span
                            class="inline-flex items-center justify-center gap-1.5 rounded-full border border-[#DCE7DF] bg-[#F6F9F6] px-3 py-1.5 text-xs font-semibold text-[#385644]"
                        >
                            <ImageIcon class="h-3.5 w-3.5" />
                            {{ service.images_count }}
                        </span>
                    </td>

                    <td class="px-5 py-4 text-center sm:px-6">
                        <span
                            class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg bg-[#E4F2E8] px-2 font-semibold text-[#187744]"
                        >
                            {{ service.sort_order }}
                        </span>
                    </td>

                    <td class="whitespace-nowrap px-5 py-4 sm:px-6">
                        <span
                            v-if="service.is_active"
                            class="inline-flex items-center gap-1.5 rounded-full border border-[#C8E5D1] bg-[#E9F4EC] px-2.5 py-1 text-xs font-semibold text-[#187744]"
                        >
                            <CheckCircle2 class="h-3.5 w-3.5" />
                            Activo
                        </span>

                        <span
                            v-else
                            class="inline-flex items-center gap-1.5 rounded-full border border-[#DCE7DF] bg-[#F6F9F6] px-2.5 py-1 text-xs font-medium text-[#84958A]"
                        >
                            <CircleOff class="h-3.5 w-3.5" />
                            Inactivo
                        </span>
                    </td>

                    <TableActions
                        :edit-url="admin.services.edit(service.id).url"
                        :show-view="false"
                        item-name="servicio"
                        @delete="deleteService(service)"
                    />
                </tr>
            </template>

            <template #empty>
                <div class="flex flex-col items-center justify-center gap-3 py-8">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#E4F2E8] text-[#187744]"
                    >
                        <BriefcaseBusiness class="h-6 w-6" />
                    </div>

                    <div class="text-center">
                        <p class="font-medium text-[#244334]">
                            No se encontraron servicios
                        </p>

                        <p class="mt-1 text-sm text-[#718277]">
                            {{
                                hasFilters()
                                    ? 'Prueba con otros filtros o limpia la búsqueda.'
                                    : 'Todavía no hay servicios registrados.'
                            }}
                        </p>
                    </div>
                </div>
            </template>
        </DataTable>
    </div>
</template>
