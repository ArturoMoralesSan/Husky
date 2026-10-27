
<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import {
    FilterX,
    Image as ImageIcon,
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

interface Banner {
    id: number
    title: string
    subtitle: string | null
    description: string | null
    image: string
    alt_text: string | null
    link_url: string | null
    button_text: string | null
    position: string
    sort_order: number
    is_active: boolean
    starts_at: string | null
    ends_at: string | null
}

interface PaginationLink {
    url: string | null
    label: string
    active: boolean
}

interface BannersPagination {
    data: Banner[]
    links: PaginationLink[]
    current_page: number
    last_page: number
    from: number | null
    to: number | null
    total: number
}

const props = defineProps<{
    banners: BannersPagination
    filters: {
        search?: string
        position?: string
        status?: string | number | boolean
    }
    positions: string[]
}>()

const search = ref(props.filters?.search ?? '')
const position = ref(props.filters?.position ?? '')
const status = ref(String(props.filters?.status ?? ''))

const positionLabels: Record<string, string> = {
    home: 'Inicio',
    products: 'Productos',
    services: 'Servicios',
}

const positionLabel = (value: string) =>
    positionLabels[value] ?? value

const hasFilters = () =>
    Boolean(search.value.trim() || position.value || status.value !== '')

const submitSearch = () => {
    router.get(
        admin.banners.index().url,
        {
            search: search.value.trim() || undefined,
            position: position.value || undefined,
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
    position.value = ''
    status.value = ''

    router.get(
        admin.banners.index().url,
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    )
}

const imageUrl = (path: string | null) => {
    if (!path) return null

    if (
        path.startsWith('http://') ||
        path.startsWith('https://') ||
        path.startsWith('/')
    ) {
        return path
    }

    return `/storage/${path}`
}

const deleteBanner = (banner: Banner) => {
    Swal.fire({
        title: '¿Eliminar banner?',
        text: `Se eliminará "${banner.title}" y su imagen. Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(admin.banners.destroy(banner.id).url, {
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
                title: 'Banners',
                href: admin.banners.index,
            },
        ],
    },
})
</script>

<template>
    <Head title="Banners" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-hidden p-4 sm:p-6">
        <PageHeader
            title="Banners"
            description="Administra los banners y su orden de aparición en el sitio."
            section="Contenido"
            :icon="ImageIcon"
            :action-icon="Plus"
            action-label="Nuevo banner"
            :action-href="admin.banners.create().url"
        />

        <DataTable
            :pagination="banners"
            :is-empty="banners.data.length === 0"
            :empty-colspan="6"
            v-model:search="search"
            search-placeholder="Buscar banner por título..."
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
                        v-if="position"
                        class="rounded-full border border-[#DCE7DF] bg-[#EDF4EF] px-3 py-1.5 text-xs font-medium text-[#244334]"
                    >
                        Posición: {{ positionLabel(position) }}
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
                        Banner
                    </th>
                    <th class="px-5 py-4 font-semibold sm:px-6">
                        Posición
                    </th>
                    <th class="px-5 py-4 text-center font-semibold sm:px-6">
                        Orden
                    </th>
                    <th class="px-5 py-4 font-semibold sm:px-6">
                        Estado
                    </th>
                    <th class="px-5 py-4 font-semibold sm:px-6">
                        Publicación
                    </th>
                    <th class="px-5 py-4 text-right font-semibold sm:px-6">
                        Acciones
                    </th>
                </tr>
            </template>

            <template #table-body>
                <tr
                    v-for="banner in banners.data"
                    :key="banner.id"
                    class="transition-colors hover:bg-[#F6F9F6]"
                >
                    <td class="px-5 py-4 sm:px-6">
                        <div class="flex min-w-[220px] items-center gap-3">
                            <div
                                class="h-14 w-24 shrink-0 overflow-hidden rounded-lg border border-[#DCE7DF] bg-[#F2F7F4]"
                            >
                                <img
                                    v-if="imageUrl(banner.image)"
                                    :src="imageUrl(banner.image) ?? ''"
                                    :alt="banner.alt_text || banner.title"
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
                                    {{ banner.title }}
                                </p>
                                <p
                                    v-if="banner.subtitle"
                                    class="mt-0.5 max-w-xs truncate text-xs text-[#718277]"
                                >
                                    {{ banner.subtitle }}
                                </p>
                                <p class="mt-1 text-xs text-[#87958B]">
                                    ID: {{ banner.id }}
                                </p>
                            </div>
                        </div>
                    </td>

                    <td class="px-5 py-4 sm:px-6">
                        <span
                            class="inline-flex rounded-full border border-[#DCE7DF] bg-[#F6F9F6] px-2.5 py-1 text-xs font-medium text-[#385644]"
                        >
                            {{ positionLabel(banner.position) }}
                        </span>
                    </td>

                    <td class="px-5 py-4 text-center sm:px-6">
                        <span
                            class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg bg-[#E4F2E8] px-2 font-semibold text-[#187744]"
                        >
                            {{ banner.sort_order }}
                        </span>
                    </td>

                    <td class="whitespace-nowrap px-5 py-4 sm:px-6">
                        <span
                            v-if="banner.is_active"
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

                    <td class="px-5 py-4 sm:px-6">
                        <div class="min-w-[130px] text-xs text-[#52665A]">
                            <p>
                                Inicio:
                                {{ banner.starts_at || 'Sin programar' }}
                            </p>
                            <p class="mt-1">
                                Fin:
                                {{ banner.ends_at || 'Sin vencimiento' }}
                            </p>
                        </div>
                    </td>

                    <TableActions
                        :edit-url="admin.banners.edit(banner.id).url"
                        :show-view="false"
                        item-name="banner"
                        @delete="deleteBanner(banner)"
                    />
                </tr>
            </template>

            <template #empty>
                <div class="flex flex-col items-center justify-center gap-3 py-8">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#E4F2E8] text-[#187744]"
                    >
                        <ImageIcon class="h-6 w-6" />
                    </div>

                    <div class="text-center">
                        <p class="font-medium text-[#244334]">
                            No se encontraron banners
                        </p>
                        <p class="mt-1 text-sm text-[#718277]">
                            {{
                                hasFilters()
                                    ? 'Prueba con otros filtros o limpia la búsqueda.'
                                    : 'Todavía no hay banners registrados.'
                            }}
                        </p>
                    </div>
                </div>
            </template>
        </DataTable>
    </div>
</template>
