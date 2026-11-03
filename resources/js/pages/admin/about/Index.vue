
<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import {
    BookOpen,
    Building2,
    Compass,
    Eye,
    HeartHandshake,
    Plus,
    Target,
    Pencil,
    Info,
    ImagePlus,
    Star,
    Images,
} from 'lucide-vue-next'

import PageHeader from '@/components/admin/PageHeader.vue'
import admin from '@/routes/admin'

interface AboutValue {
    id: number
    name: string
    description: string | null
    icon: string | null
    sort_order: number
}

interface GalleryImage {
    id: number
    file: string
    url: string
    alt: string | null
    is_primary: boolean
    sort_order: number
}

interface About {
    id: number
    who_we_are: string | null
    history: string | null
    mission: string | null
    vision: string | null
    objectives: string | null
    values: AboutValue[]
    gallery_images?: GalleryImage[]
    gallery?: {
        images?: GalleryImage[]
    } | null
}

const props = defineProps<{
    about: About | null
}>()

const sections = [
    {
        title: 'Quiénes somos',
        description: 'Presentación de la empresa.',
        field: 'who_we_are',
        icon: Building2,
    },
    {
        title: 'Historia',
        description: 'Trayectoria de Industrias Husky.',
        field: 'history',
        icon: BookOpen,
    },
    {
        title: 'Misión',
        description: 'Propósito de la empresa.',
        field: 'mission',
        icon: Compass,
    },
    {
        title: 'Visión',
        description: 'Proyección y futuro de la empresa.',
        field: 'vision',
        icon: Eye,
    },
    {
        title: 'Objetivos',
        description: 'Metas de la empresa.',
        field: 'objectives',
        icon: Target,
    },
] as const

const hasContent = (value: string | null): boolean =>
    Boolean(value?.trim())

const galleryImages = props.about
    ? (props.about.gallery_images ?? props.about.gallery?.images ?? [])
        .slice()
        .sort((a, b) => a.sort_order - b.sort_order)
    : []

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Panel',
                href: admin.dashboard(),
            },
            {
                title: 'Acerca de nosotros',
                href: admin.about.index,
            },
        ],
    },
})
</script>

<template>
    <Head title="Acerca de nosotros" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-hidden p-4 sm:p-6">
        <PageHeader
            title="Acerca de nosotros"
            description="Administra la información institucional de Industrias Husky."
            section="Contenido"
            :icon="Building2"
            :action-icon="about ? Pencil : Plus"
            :action-label="about ? 'Editar información' : 'Agregar información'"
            :action-href="about ? admin.about.edit().url : admin.about.create().url"
        />

        <!-- Sin información registrada -->
        <div
            v-if="!about"
            class="rounded-xl border border-[#DCE7DF] bg-white px-5 py-12"
        >
            <div class="flex flex-col items-center justify-center text-center">
                <div
                    class="flex h-14 w-14 items-center justify-center rounded-xl bg-[#E4F2E8] text-[#187744]"
                >
                    <Info class="h-7 w-7" />
                </div>

                <h3 class="mt-4 font-semibold text-[#244334]">
                    No hay información registrada
                </h3>

                <p class="mt-1 max-w-md text-sm leading-6 text-[#718277]">
                    Todavía no has agregado la información institucional
                    de Industrias Husky.
                </p>
            </div>
        </div>

        <template v-else>
            <!-- Información institucional -->
            <div class="overflow-hidden rounded-xl border border-[#DCE7DF] bg-white">
                <div class="border-b border-[#DCE7DF] px-5 py-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#E4F2E8] text-[#187744]"
                        >
                            <Building2 class="h-5 w-5" />
                        </div>

                        <div>
                            <h2 class="font-semibold text-[#244334]">
                                Información institucional
                            </h2>

                            <p class="mt-0.5 text-xs text-[#718277]">
                                Contenido general de la empresa.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-[#DCE7DF]">
                    <div
                        v-for="section in sections"
                        :key="section.field"
                        class="flex gap-4 px-5 py-5 sm:px-6"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-[#DCE7DF] bg-[#F2F7F4] text-[#187744]"
                        >
                            <component
                                :is="section.icon"
                                class="h-5 w-5"
                            />
                        </div>

                        <div class="min-w-0 flex-1">
                            <div
                                class="flex flex-wrap items-center justify-between gap-2"
                            >
                                <div>
                                    <h3 class="font-semibold text-[#244334]">
                                        {{ section.title }}
                                    </h3>

                                    <p class="mt-0.5 text-xs text-[#87958B]">
                                        {{ section.description }}
                                    </p>
                                </div>

                                <span
                                    v-if="hasContent(about[section.field])"
                                    class="rounded-full border border-[#DCE7DF] bg-[#EDF4EF] px-2.5 py-1 text-xs font-medium text-[#187744]"
                                >
                                    Registrado
                                </span>

                                <span
                                    v-else
                                    class="rounded-full border border-[#E8E8E8] bg-[#F7F7F7] px-2.5 py-1 text-xs font-medium text-[#87958B]"
                                >
                                    Sin contenido
                                </span>
                            </div>

                            <p
                                v-if="hasContent(about[section.field])"
                                class="mt-3 whitespace-pre-line text-sm leading-6 text-[#526B5C]"
                            >
                                {{ about[section.field] }}
                            </p>

                            <p
                                v-else
                                class="mt-3 text-sm italic text-[#A0ACA4]"
                            >
                                No se ha agregado información.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Valores -->
            <div class="overflow-hidden rounded-xl border border-[#DCE7DF] bg-white">
                <div
                    class="flex flex-col gap-3 border-b border-[#DCE7DF] px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#E4F2E8] text-[#187744]"
                        >
                            <HeartHandshake class="h-5 w-5" />
                        </div>

                        <div>
                            <h2 class="font-semibold text-[#244334]">
                                Valores
                            </h2>

                            <p class="mt-0.5 text-xs text-[#718277]">
                                Principios que representan a la empresa.
                            </p>
                        </div>
                    </div>

                    <span
                        class="w-fit rounded-full border border-[#DCE7DF] bg-[#EDF4EF] px-3 py-1 text-xs font-medium text-[#187744]"
                    >
                        {{ about.values?.length ?? 0 }}
                        {{ about.values?.length === 1 ? 'valor' : 'valores' }}
                    </span>
                </div>

                <div
                    v-if="about.values?.length"
                    class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6 lg:grid-cols-3"
                >
                    <div
                        v-for="value in about.values"
                        :key="value.id"
                        class="rounded-lg border border-[#DCE7DF] bg-white p-4 transition-colors hover:bg-[#F6F9F6]"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#E4F2E8] text-[#187744]"
                            >
                                <span
                                    v-if="value.icon"
                                    class="text-lg"
                                >
                                    {{ value.icon }}
                                </span>

                                <HeartHandshake
                                    v-else
                                    class="h-5 w-5"
                                />
                            </div>

                            <div class="min-w-0">
                                <h3 class="font-semibold text-[#244334]">
                                    {{ value.name }}
                                </h3>

                                <p class="mt-0.5 text-xs text-[#87958B]">
                                    Orden: {{ value.sort_order }}
                                </p>
                            </div>
                        </div>

                        <p
                            v-if="value.description"
                            class="mt-3 text-sm leading-6 text-[#718277]"
                        >
                            {{ value.description }}
                        </p>

                        <p
                            v-else
                            class="mt-3 text-sm italic text-[#A0ACA4]"
                        >
                            Sin descripción.
                        </p>
                    </div>
                </div>

                <div
                    v-else
                    class="flex flex-col items-center justify-center px-5 py-10 text-center"
                >
                    <HeartHandshake class="h-8 w-8 text-[#A0B4A7]" />

                    <p class="mt-3 font-medium text-[#244334]">
                        No hay valores registrados
                    </p>

                    <p class="mt-1 text-sm text-[#718277]">
                        Puedes agregarlos desde la edición de la información.
                    </p>
                </div>
            </div>

            <!-- Galería de imágenes -->
            <div class="overflow-hidden rounded-xl border border-[#DCE7DF] bg-white">
                <div
                    class="flex flex-col gap-3 border-b border-[#DCE7DF] px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#E4F2E8] text-[#187744]"
                        >
                            <Images class="h-5 w-5" />
                        </div>

                        <div>
                            <h2 class="font-semibold text-[#244334]">
                                Galería de imágenes
                            </h2>

                            <p class="mt-0.5 text-xs text-[#718277]">
                                Fotografías de Industrias Husky.
                            </p>
                        </div>
                    </div>

                    <span
                        class="w-fit rounded-full border border-[#DCE7DF] bg-[#EDF4EF] px-3 py-1 text-xs font-medium text-[#187744]"
                    >
                        {{ galleryImages.length }}
                        {{ galleryImages.length === 1 ? 'imagen' : 'imágenes' }}
                    </span>
                </div>

                <div
                    v-if="galleryImages.length"
                    class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 sm:p-6 lg:grid-cols-3 xl:grid-cols-4"
                >
                    <div
                        v-for="image in galleryImages"
                        :key="image.id"
                        class="overflow-hidden rounded-xl border border-[#DCE7DF] bg-white"
                    >
                        <div class="relative aspect-[4/3] bg-[#F2F7F4]">
                            <img
                                :src="image.url"
                                :alt="image.alt || 'Imagen de Industrias Husky'"
                                class="h-full w-full object-cover"
                                loading="lazy"
                            />

                            <span
                                v-if="image.is_primary"
                                class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full border border-[#C8DCCF] bg-white/95 px-2.5 py-1 text-xs font-semibold text-[#187744] shadow-sm"
                            >
                                <Star class="h-3.5 w-3.5 fill-current" />
                                Principal
                            </span>
                        </div>

                        <div class="p-3">
                            <p
                                class="truncate text-sm font-medium text-[#244334]"
                                :title="image.alt || 'Sin texto alternativo'"
                            >
                                {{ image.alt || 'Sin texto alternativo' }}
                            </p>

                            <p class="mt-1 text-xs text-[#87958B]">
                                Orden: {{ image.sort_order }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="flex flex-col items-center justify-center px-5 py-10 text-center"
                >
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#F2F7F4] text-[#A0B4A7]"
                    >
                        <ImagePlus class="h-6 w-6" />
                    </div>

                    <p class="mt-3 font-medium text-[#244334]">
                        No hay imágenes registradas
                    </p>

                    <p class="mt-1 text-sm text-[#718277]">
                        Puedes agregar fotografías desde la edición de la información.
                    </p>
                </div>
            </div>
        </template>
    </div>
</template>
