
<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import {
    FilterX,
    Image as ImageIcon,
    Package,
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

interface Product {
    id: number
    name: string
    slug: string
    short_description: string | null
    material: string | null
    treatment: string | null
    retention: string | number | null
    retention_unit: string | null
    image: string | null
    image_alt: string | null
    is_active: boolean
    sort_order: number
    variants_count: number
}

interface PaginationLink {
    url: string | null
    label: string
    active: boolean
}

interface ProductsPagination {
    data: Product[]
    links: PaginationLink[]
    current_page: number
    last_page: number
    from: number | null
    to: number | null
    total: number
}

const props = defineProps<{
    products: ProductsPagination
    filters: {
        search?: string
        status?: string | number | boolean
    }
}>()

const search = ref(props.filters?.search ?? '')
const status = ref(String(props.filters?.status ?? ''))

const hasFilters = () =>
    Boolean(search.value.trim() || status.value !== '')

const submitSearch = () => {
    router.get(
        admin.products.index().url,
        {
            search: search.value.trim() || undefined,
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
    status.value = ''

    router.get(
        admin.products.index().url,
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

const deleteProduct = (product: Product) => {
    Swal.fire({
        title: '¿Eliminar producto?',
        text: `Se eliminará "${product.name}" y sus medidas registradas. Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(admin.products.destroy(product.id).url, {
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
                title: 'Productos',
                href: admin.products.index,
            },
        ],
    },
})
</script>

<template>
    <Head title="Productos" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-hidden p-4 sm:p-6">
        <PageHeader
            title="Productos"
            description="Administra el catálogo de productos, sus características y medidas."
            section="Catálogo"
            :icon="Package"
            :action-icon="Plus"
            action-label="Nuevo producto"
            :action-href="admin.products.create().url"
        />

        <DataTable
            :pagination="products"
            :is-empty="products.data.length === 0"
            :empty-colspan="6"
            v-model:search="search"
            search-placeholder="Buscar por nombre, material o tratamiento..."
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
                        Producto
                    </th>

                    <th class="px-5 py-4 font-semibold sm:px-6">
                        Material y tratamiento
                    </th>

                    <th class="px-5 py-4 text-center font-semibold sm:px-6">
                        Medidas
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
                    v-for="product in products.data"
                    :key="product.id"
                    class="transition-colors hover:bg-[#F6F9F6]"
                >
                    <td class="px-5 py-4 sm:px-6">
                        <div class="flex min-w-[240px] items-center gap-3">
                            <div
                                class="h-14 w-16 shrink-0 overflow-hidden rounded-lg border border-[#DCE7DF] bg-[#F2F7F4]"
                            >
                                <img
                                    v-if="imageUrl(product.image)"
                                    :src="imageUrl(product.image) ?? ''"
                                    :alt="product.image_alt || product.name"
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
                                    {{ product.name }}
                                </p>

                                <p
                                    v-if="product.short_description"
                                    class="mt-0.5 max-w-xs truncate text-xs text-[#718277]"
                                >
                                    {{ product.short_description }}
                                </p>

                                <p class="mt-1 text-xs text-[#87958B]">
                                    ID: {{ product.id }}
                                </p>
                            </div>
                        </div>
                    </td>

                    <td class="px-5 py-4 sm:px-6">
                        <div class="min-w-[160px] space-y-1 text-sm">
                            <p class="font-medium text-[#244334]">
                                {{ product.material || 'Sin especificar' }}
                            </p>

                            <p class="text-xs text-[#718277]">
                                {{ product.treatment || 'Sin tratamiento especificado' }}
                            </p>

                            <p
                                v-if="product.retention !== null && product.retention !== ''"
                                class="text-xs text-[#52665A]"
                            >
                                Retención:
                                {{ product.retention }}
                                {{ product.retention_unit || '' }}
                            </p>
                        </div>
                    </td>

                    <td class="px-5 py-4 text-center sm:px-6">
                        <span
                            class="inline-flex items-center justify-center gap-1.5 rounded-full border border-[#DCE7DF] bg-[#F6F9F6] px-3 py-1.5 text-xs font-semibold text-[#385644]"
                        >
                            <Package class="h-3.5 w-3.5" />
                            {{ product.variants_count }}
                        </span>
                    </td>

                    <td class="px-5 py-4 text-center sm:px-6">
                        <span
                            class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg bg-[#E4F2E8] px-2 font-semibold text-[#187744]"
                        >
                            {{ product.sort_order }}
                        </span>
                    </td>

                    <td class="whitespace-nowrap px-5 py-4 sm:px-6">
                        <span
                            v-if="product.is_active"
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
                        :edit-url="admin.products.edit(product.id).url"
                        :show-view="false"
                        item-name="producto"
                        @delete="deleteProduct(product)"
                    />
                </tr>
            </template>

            <template #empty>
                <div class="flex flex-col items-center justify-center gap-3 py-8">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#E4F2E8] text-[#187744]"
                    >
                        <Package class="h-6 w-6" />
                    </div>

                    <div class="text-center">
                        <p class="font-medium text-[#244334]">
                            No se encontraron productos
                        </p>

                        <p class="mt-1 text-sm text-[#718277]">
                            {{
                                hasFilters()
                                    ? 'Prueba con otros filtros o limpia la búsqueda.'
                                    : 'Todavía no hay productos registrados.'
                            }}
                        </p>
                    </div>
                </div>
            </template>
        </DataTable>
    </div>
</template>
