
<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import {
    FilterX,
    Image as ImageIcon,
    Users,
    Plus,
    ExternalLink,
} from 'lucide-vue-next'
import Swal from 'sweetalert2'
import { ref } from 'vue'

import DataTable from '@/components/admin/DataTable.vue'
import PageHeader from '@/components/admin/PageHeader.vue'
import TableActions from '@/components/admin/TableActions.vue'
import admin from '@/routes/admin'

interface Client {
    id: number
    name: string
    logo: string | null
    logo_url: string | null
    url: string | null
    created_at: string | null
}

interface PaginationLink {
    url: string | null
    label: string
    active: boolean
}

interface ClientsPagination {
    data: Client[]
    links: PaginationLink[]
    current_page: number
    last_page: number
    from: number | null
    to: number | null
    total: number
}

const props = defineProps<{
    clients: ClientsPagination
    filters?: {
        search?: string
    }
}>()

const search = ref(props.filters?.search ?? '')

const hasFilters = () => Boolean(search.value.trim())

const submitSearch = () => {
    router.get(
        admin.clients.index().url,
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
        admin.clients.index().url,
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

const deleteClient = (client: Client) => {
    Swal.fire({
        title: '¿Eliminar cliente?',
        text: `Se eliminará "${client.name}" y su logotipo. Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(admin.clients.destroy(client.id).url, {
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
                title: 'Clientes',
                href: admin.clients.index,
            },
        ],
    },
})
</script>

<template>
    <Head title="Clientes" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-hidden p-4 sm:p-6">
        <PageHeader
            title="Clientes"
            description="Administra los clientes y sus logotipos."
            section="Catálogo"
            :icon="Users"
            :action-icon="Plus"
            action-label="Nuevo cliente"
            :action-href="admin.clients.create().url"
        />

        <DataTable
            :pagination="clients"
            :is-empty="clients.data.length === 0"
            :empty-colspan="4"
            v-model:search="search"
            search-placeholder="Buscar por nombre de cliente..."
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
                        Cliente
                    </th>

                    <th class="px-5 py-4 font-semibold sm:px-6">
                        Sitio web
                    </th>

                    <th class="px-5 py-4 font-semibold sm:px-6">
                        Registro
                    </th>

                    <th class="px-5 py-4 text-right font-semibold sm:px-6">
                        Acciones
                    </th>
                </tr>
            </template>

            <template #table-body>
                <tr
                    v-for="client in clients.data"
                    :key="client.id"
                    class="transition-colors hover:bg-[#F6F9F6]"
                >
                    <td class="px-5 py-4 sm:px-6">
                        <div class="flex min-w-[220px] items-center gap-3">
                            <div
                                class="flex h-14 w-16 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-[#DCE7DF] bg-white"
                            >
                                <img
                                    v-if="imageUrl(client.logo_url)"
                                    :src="imageUrl(client.logo_url) ?? ''"
                                    :alt="client.name"
                                    class="h-full w-full object-contain p-1.5"
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
                                    {{ client.name }}
                                </p>

                                <p class="mt-1 text-xs text-[#87958B]">
                                    ID: {{ client.id }}
                                </p>
                            </div>
                        </div>
                    </td>

                    <td class="px-5 py-4 sm:px-6">
                        <a
                            v-if="client.url"
                            :href="client.url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex max-w-[260px] items-center gap-2 truncate text-sm font-medium text-[#187744] transition-colors hover:text-[#125C35] hover:underline"
                        >
                            <span class="truncate">
                                {{ client.url }}
                            </span>

                            <ExternalLink class="h-3.5 w-3.5 shrink-0" />
                        </a>

                        <span
                            v-else
                            class="text-sm text-[#87958B]"
                        >
                            Sin sitio web
                        </span>
                    </td>

                    <td class="whitespace-nowrap px-5 py-4 text-sm text-[#718277] sm:px-6">
                        {{ client.created_at || '—' }}
                    </td>

                    <TableActions
                        :edit-url="admin.clients.edit(client.id).url"
                        :show-view="false"
                        item-name="cliente"
                        @delete="deleteClient(client)"
                    />
                </tr>
            </template>

            <template #empty>
                <div class="flex flex-col items-center justify-center gap-3 py-8">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#E4F2E8] text-[#187744]"
                    >
                        <Users class="h-6 w-6" />
                    </div>

                    <div class="text-center">
                        <p class="font-medium text-[#244334]">
                            No se encontraron clientes
                        </p>

                        <p class="mt-1 text-sm text-[#718277]">
                            {{
                                hasFilters()
                                    ? 'Prueba con otro nombre o limpia la búsqueda.'
                                    : 'Todavía no hay clientes registrados.'
                            }}
                        </p>
                    </div>
                </div>
            </template>
        </DataTable>
    </div>
</template>
