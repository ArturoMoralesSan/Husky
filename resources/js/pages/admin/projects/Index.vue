<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import {
    FilterX,
    Image as ImageIcon,
    FolderKanban,
    Plus,
    CheckCircle2,
    CircleOff,
    MapPin,
    Calendar,
} from 'lucide-vue-next'
import Swal from 'sweetalert2'
import { ref } from 'vue'

import DataTable from '@/components/admin/DataTable.vue'
import PageHeader from '@/components/admin/PageHeader.vue'
import TableActions from '@/components/admin/TableActions.vue'
import admin from '@/routes/admin'

interface ProjectImage {
    id: number
    file: string
    url: string
    alt: string | null
    is_primary: boolean
    sort_order: number
}

interface Project {
    id: number
    name: string
    slug: string
    category: string | null
    description: string | null
    location: string | null
    year: number | null
    is_active: boolean
    sort_order: number
    created_at: string | null
    gallery_images: ProjectImage[]
}

interface PaginationLink {
    url: string | null
    label: string
    active: boolean
}

interface ProjectsPagination {
    data: Project[]
    links: PaginationLink[]
    current_page: number
    last_page: number
    from: number | null
    to: number | null
    total: number
}

const props = defineProps<{
    projects: ProjectsPagination
    filters?: {
        search?: string
    }
}>()

const search = ref(props.filters?.search ?? '')

const hasFilters = () => Boolean(search.value.trim())

const submitSearch = () => {
    router.get(
        admin.projects.index().url,
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
        admin.projects.index().url,
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

const projectImage = (project: Project): string | null => {
    const primaryImage = project.gallery_images?.find(
        (image) => image.is_primary,
    )

    const firstImage = primaryImage ?? project.gallery_images?.[0]

    return imageUrl(firstImage?.url ?? firstImage?.file ?? null)
}

const deleteProject = (project: Project) => {
    Swal.fire({
        title: '¿Eliminar proyecto?',
        text: `Se eliminará "${project.name}" junto con su galería de imágenes. Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(admin.projects.destroy(project.id).url, {
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
                title: 'Proyectos',
                href: admin.projects.index,
            },
        ],
    },
})
</script>

<template>
    <Head title="Proyectos" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-hidden p-4 sm:p-6">
        <PageHeader
            title="Proyectos"
            description="Administra los proyectos y las aplicaciones donde se utiliza la madera."
            section="Proyectos"
            :icon="FolderKanban"
            :action-icon="Plus"
            action-label="Nuevo proyecto"
            :action-href="admin.projects.create().url"
        />

        <DataTable
            :pagination="projects"
            :is-empty="projects.data.length === 0"
            :empty-colspan="6"
            v-model:search="search"
            search-placeholder="Buscar por nombre, categoría o ubicación..."
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
                </div>
            </template>

            <template #table-head>
                <tr>
                    <th class="px-5 py-4 font-semibold sm:px-6">
                        Proyecto
                    </th>

                    <th class="px-5 py-4 font-semibold sm:px-6">
                        Categoría y ubicación
                    </th>

                    <th class="px-5 py-4 text-center font-semibold sm:px-6">
                        Año
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
                    v-for="project in projects.data"
                    :key="project.id"
                    class="transition-colors hover:bg-[#F6F9F6]"
                >
                    <td class="px-5 py-4 sm:px-6">
                        <div class="flex min-w-[240px] items-center gap-3">
                            <div
                                class="h-14 w-16 shrink-0 overflow-hidden rounded-lg border border-[#DCE7DF] bg-[#F2F7F4]"
                            >
                                <img
                                    v-if="projectImage(project)"
                                    :src="projectImage(project) ?? ''"
                                    :alt="project.name"
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
                                    {{ project.name }}
                                </p>

                                <p
                                    v-if="project.description"
                                    class="mt-0.5 max-w-xs truncate text-xs text-[#718277]"
                                >
                                    {{ project.description }}
                                </p>

                                <p class="mt-1 text-xs text-[#87958B]">
                                    ID: {{ project.id }}
                                </p>
                            </div>
                        </div>
                    </td>

                    <td class="px-5 py-4 sm:px-6">
                        <div class="min-w-[160px] space-y-1">
                            <p class="text-sm font-medium text-[#244334]">
                                {{ project.category || 'Sin categoría' }}
                            </p>

                            <p
                                v-if="project.location"
                                class="flex items-center gap-1.5 text-xs text-[#718277]"
                            >
                                <MapPin class="h-3.5 w-3.5 shrink-0" />
                                {{ project.location }}
                            </p>

                            <p
                                v-else
                                class="text-xs text-[#87958B]"
                            >
                                Sin ubicación
                            </p>
                        </div>
                    </td>

                    <td class="px-5 py-4 text-center sm:px-6">
                        <span
                            v-if="project.year"
                            class="inline-flex items-center justify-center gap-1.5 rounded-full border border-[#DCE7DF] bg-[#F6F9F6] px-3 py-1.5 text-xs font-semibold text-[#385644]"
                        >
                            <Calendar class="h-3.5 w-3.5" />
                            {{ project.year }}
                        </span>

                        <span
                            v-else
                            class="text-xs text-[#87958B]"
                        >
                            —
                        </span>
                    </td>

                    <td class="px-5 py-4 text-center sm:px-6">
                        <span
                            class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg bg-[#E4F2E8] px-2 font-semibold text-[#187744]"
                        >
                            {{ project.sort_order }}
                        </span>
                    </td>

                    <td class="whitespace-nowrap px-5 py-4 sm:px-6">
                        <span
                            v-if="project.is_active"
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
                        :edit-url="admin.projects.edit(project.id).url"
                        :show-view="false"
                        item-name="proyecto"
                        @delete="deleteProject(project)"
                    />
                </tr>
            </template>

            <template #empty>
                <div class="flex flex-col items-center justify-center gap-3 py-8">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#E4F2E8] text-[#187744]"
                    >
                        <FolderKanban class="h-6 w-6" />
                    </div>

                    <div class="text-center">
                        <p class="font-medium text-[#244334]">
                            No se encontraron proyectos
                        </p>

                        <p class="mt-1 text-sm text-[#718277]">
                            {{
                                hasFilters()
                                    ? 'Prueba con otros filtros o limpia la búsqueda.'
                                    : 'Todavía no hay proyectos registrados.'
                            }}
                        </p>
                    </div>
                </div>
            </template>
        </DataTable>
    </div>
</template>