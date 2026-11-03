
<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import {
    ArrowLeft,
    CheckCircle2,
    Hash,
    ImagePlus,
    Info,
    MapPin,
    Calendar,
    FolderKanban,
    Save,
    Trash2,
    Type,
} from 'lucide-vue-next'

import AdminButton from '@/components/admin/AdminButton.vue'
import AdminCard from '@/components/admin/AdminCard.vue'
import CancelButton from '@/components/admin/CancelButton.vue'
import PageHeader from '@/components/admin/PageHeader.vue'
import GalleryDropzone, {
    type GalleryImageItem,
} from '@/components/admin/GalleryDropzone.vue'
import admin from '@/routes/admin'

interface ExistingImage {
    id?: number
    url?: string | null
    file?: string | null
    path?: string | null
    preview?: string
    alt?: string | null
    name?: string
    is_primary?: boolean
    sort_order?: number
}

interface Project {
    id: number
    name: string
    slug: string | null
    category: string | null
    description: string | null
    location: string | null
    year: number | null
    sort_order: number
    is_active: boolean
    gallery_images?: ExistingImage[]
    gallery?: {
        images?: ExistingImage[]
    } | null
}

const props = defineProps<{
    project: Project
}>()

function getImageUrl(path: string | null | undefined): string | null {
    if (!path) return null

    const value = path.trim()

    if (!value) return null

    if (value.startsWith('data:') || value.startsWith('blob:')) {
        return value
    }

    if (/^https?:\/\//i.test(value)) {
        try {
            const parsed = new URL(value)

            const storageIndex = parsed.pathname.indexOf('/storage/')

            if (storageIndex !== -1) {
                return `${parsed.pathname.substring(storageIndex)}${parsed.search}${parsed.hash}`
            }

            const projectsIndex = parsed.pathname.indexOf('/projects/')

            if (projectsIndex !== -1) {
                return `/storage${parsed.pathname.substring(projectsIndex)}${parsed.search}${parsed.hash}`
            }

            return value
        } catch {
            return value
        }
    }

    let normalized = value.replace(/\\/g, '/')

    normalized = normalized.replace(/^\/+/, '')
    normalized = normalized.replace(/^storage\//, '')

    return `/storage/${normalized}`
}

const form = useForm({
    name: props.project.name ?? '',
    slug: props.project.slug ?? '',
    category: props.project.category ?? '',
    description: props.project.description ?? '',
    location: props.project.location ?? '',
    year: props.project.year ?? null as number | null,
    sort_order: props.project.sort_order ?? 0,
    is_active: Boolean(props.project.is_active),
})

const originalGalleryImages = (
    props.project.gallery_images ??
    props.project.gallery?.images ??
    []
).map((image, index) => ({
    ...image,
    sort_order: image.sort_order ?? index,
}))

const galleryImages = ref<GalleryImageItem[]>(
    originalGalleryImages
        .map((image, index) => {
            const preview = getImageUrl(
                image.url ?? image.file ?? image.path ?? image.preview,
            )

            if (!preview) return null

            return {
                id: image.id,
                file: null,
                preview,
                alt: image.alt ?? '',
                is_primary: Boolean(image.is_primary),
                sort_order: image.sort_order ?? index,
            }
        })
        .filter((image): image is GalleryImageItem => image !== null),
)

const removedGalleryImageIds = computed(() => {
    const remainingIds = new Set(
        galleryImages.value
            .map((image) => image.id)
            .filter((id): id is number => typeof id === 'number'),
    )

    return originalGalleryImages
        .filter(
            (image) =>
                typeof image.id === 'number' &&
                !remainingIds.has(image.id),
        )
        .map((image) => image.id as number)
})

function submit(): void {
    const newGalleryImages = galleryImages.value
        .filter((item) => item.file instanceof File)
        .map((item, index) => ({
            file: item.file as File,
            alt: item.alt?.trim() || null,
            is_primary: Boolean(item.is_primary),
            sort_order: item.sort_order ?? index,
        }))

    // Si se agregaron imágenes y ninguna está marcada como principal,
    // se selecciona la primera nueva solamente si no existe una principal.
    const hasExistingPrimary = galleryImages.value.some(
        (item) => typeof item.id === 'number' && item.is_primary,
    )

    const hasNewPrimary = newGalleryImages.some(
        (item) => item.is_primary,
    )

    if (
        newGalleryImages.length > 0 &&
        !hasExistingPrimary &&
        !hasNewPrimary
    ) {
        newGalleryImages[0].is_primary = true
    }

    // Garantiza que solo una imagen nueva sea principal.
    let primaryAssigned = hasExistingPrimary

    newGalleryImages.forEach((item) => {
        if (item.is_primary && !primaryAssigned) {
            primaryAssigned = true
        } else if (item.is_primary) {
            item.is_primary = false
        }
    })

    form
        .transform((data) => ({
            ...data,

            _method: 'put',

            slug: data.slug.trim() || null,
            category: data.category.trim() || null,
            description: data.description.trim() || null,
            location: data.location.trim() || null,

            year:
                data.year === null || data.year === ('' as unknown as number)
                    ? null
                    : Number(data.year),

            sort_order: Number(data.sort_order),

            gallery_images: newGalleryImages.map((item) => item.file),

            gallery_metadata: newGalleryImages.map((item, index) => ({
                alt: item.alt,
                is_primary: item.is_primary,
                sort_order: index,
            })),

            remove_gallery_images: removedGalleryImageIds.value,

            existing_gallery_metadata: galleryImages.value
                .filter(
                    (item): item is GalleryImageItem & { id: number } =>
                        typeof item.id === 'number',
                )
                .map((item, index) => ({
                    id: item.id,
                    alt: item.alt?.trim() || null,
                    is_primary: Boolean(item.is_primary),
                    sort_order: index,
                })),
        }))
        .post(admin.projects.update(props.project.id).url, {
            forceFormData: true,
            preserveScroll: true,
        })
}
</script>

<template>
    <Head :title="`Editar ${project.name}`" />

    <div
        class="flex h-full flex-1 flex-col gap-5 overflow-x-auto bg-[#F2F7F4] p-4 sm:p-5"
    >
        <PageHeader
            title="Editar proyecto"
            description="Actualiza la información del proyecto de Industrias Husky."
            section="Proyectos"
            :icon="FolderKanban"
            :action-icon="ArrowLeft"
            action-label="Volver a proyectos"
            :action-href="admin.projects.index().url"
        />

        <form class="flex flex-col gap-5" @submit.prevent="submit">
            <AdminCard
                title="Información del proyecto"
                description="Actualiza el nombre, la categoría y los detalles del proyecto."
                :icon="Info"
            >
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="form-field md:col-span-2">
                        <label for="name" class="form-label">
                            Nombre del proyecto
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <FolderKanban
                                class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#718477]"
                            />

                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                maxlength="255"
                                required
                                placeholder="Ej. Infraestructura eléctrica"
                                :class="[
                                    'form-input pl-11',
                                    form.errors.name && 'form-input-error',
                                ]"
                            />
                        </div>

                        <p v-if="form.errors.name" class="form-error">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div class="form-field md:col-span-2">
                        <label for="slug" class="form-label">
                            URL amigable (slug)
                        </label>

                        <div class="relative">
                            <Type
                                class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#718477]"
                            />

                            <input
                                id="slug"
                                v-model="form.slug"
                                type="text"
                                maxlength="255"
                                placeholder="infraestructura-electrica"
                                :class="[
                                    'form-input pl-11',
                                    form.errors.slug && 'form-input-error',
                                ]"
                            />
                        </div>

                        <p class="form-help">
                            Puedes dejarlo vacío para que Laravel lo genere a partir del nombre.
                        </p>

                        <p v-if="form.errors.slug" class="form-error">
                            {{ form.errors.slug }}
                        </p>
                    </div>

                    <div class="form-field md:col-span-2">
                        <label for="category" class="form-label">
                            Categoría
                        </label>

                        <div class="relative">
                            <Type
                                class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#718477]"
                            />

                            <input
                                id="category"
                                v-model="form.category"
                                type="text"
                                maxlength="255"
                                placeholder="Ej. Construcción, infraestructura eléctrica..."
                                :class="[
                                    'form-input pl-11',
                                    form.errors.category && 'form-input-error',
                                ]"
                            />
                        </div>

                        <p class="form-help">
                            Indica el sector o la aplicación de la madera en este proyecto.
                        </p>

                        <p v-if="form.errors.category" class="form-error">
                            {{ form.errors.category }}
                        </p>
                    </div>

                    <div class="form-field md:col-span-2">
                        <label for="description" class="form-label">
                            Descripción
                        </label>

                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="6"
                            placeholder="Describe el proyecto, sus características y cómo se utiliza la madera."
                            :class="[
                                'form-input min-h-36 resize-y',
                                form.errors.description && 'form-input-error',
                            ]"
                        />

                        <p v-if="form.errors.description" class="form-error">
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <div class="form-field">
                        <label for="location" class="form-label">
                            Ubicación
                        </label>

                        <div class="relative">
                            <MapPin
                                class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#718477]"
                            />

                            <input
                                id="location"
                                v-model="form.location"
                                type="text"
                                maxlength="255"
                                placeholder="Ej. Durango, México"
                                :class="[
                                    'form-input pl-11',
                                    form.errors.location && 'form-input-error',
                                ]"
                            />
                        </div>

                        <p v-if="form.errors.location" class="form-error">
                            {{ form.errors.location }}
                        </p>
                    </div>

                    <div class="form-field">
                        <label for="year" class="form-label">
                            Año del proyecto
                        </label>

                        <div class="relative">
                            <Calendar
                                class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#718477]"
                            />

                            <input
                                id="year"
                                v-model.number="form.year"
                                type="number"
                                min="1900"
                                max="2100"
                                step="1"
                                placeholder="Ej. 2026"
                                :class="[
                                    'form-input pl-11',
                                    form.errors.year && 'form-input-error',
                                ]"
                            />
                        </div>

                        <p v-if="form.errors.year" class="form-error">
                            {{ form.errors.year }}
                        </p>
                    </div>
                </div>
            </AdminCard>

            <AdminCard
                title="Galería de imágenes"
                description="Administra las fotografías, selecciona una destacada y organiza su orden."
                :icon="ImagePlus"
            >
                <GalleryDropzone
                    v-model="galleryImages"
                    :max-files="20"
                    :max-size-mb="5"
                    :disabled="form.processing"
                />

                <p class="form-help mt-4">
                    Las imágenes existentes se conservan al guardar. Puedes agregar imágenes nuevas,
                    cambiar sus textos ALT, elegir la imagen destacada o eliminar las que ya no necesites.
                </p>

                <p
                    v-if="form.errors.gallery_images"
                    class="form-error mt-2"
                >
                    {{ form.errors.gallery_images }}
                </p>

                <p
                    v-if="form.errors.gallery_metadata"
                    class="form-error mt-2"
                >
                    {{ form.errors.gallery_metadata }}
                </p>

                <div
                    v-for="(error, key) in form.errors"
                    :key="key"
                    class="text-sm text-red-600"
                >
                    <template
                        v-if="
                            key.startsWith('gallery_images.') ||
                            key.startsWith('gallery_metadata.') ||
                            key.startsWith('existing_gallery_metadata.')
                        "
                    >
                        {{ error }}
                    </template>
                </div>
            </AdminCard>

            <AdminCard
                title="Publicación"
                description="Define el orden en que aparecerá el proyecto y su estado."
                :icon="CheckCircle2"
            >
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="form-field">
                        <label for="sort_order" class="form-label">
                            Orden de visualización
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <Hash
                                class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#718477]"
                            />

                            <input
                                id="sort_order"
                                v-model.number="form.sort_order"
                                type="number"
                                min="0"
                                step="1"
                                required
                                :class="[
                                    'form-input pl-11',
                                    form.errors.sort_order && 'form-input-error',
                                ]"
                            />
                        </div>

                        <p class="form-help">
                            Los valores menores aparecen primero.
                        </p>

                        <p v-if="form.errors.sort_order" class="form-error">
                            {{ form.errors.sort_order }}
                        </p>
                    </div>

                    <label
                        for="is_active"
                        class="flex cursor-pointer items-start gap-3 rounded-xl border border-[#DCE7DF] bg-white p-4"
                    >
                        <input
                            id="is_active"
                            v-model="form.is_active"
                            type="checkbox"
                            class="mt-1 h-4 w-4 rounded border-[#A6C1B3] accent-[#187744]"
                        />

                        <span>
                            <span class="block text-sm font-semibold text-[#244334]">
                                Proyecto activo
                            </span>

                            <span class="mt-1 block text-sm text-[#718477]">
                                El proyecto estará disponible para mostrarse en el sitio web.
                            </span>
                        </span>
                    </label>
                </div>
            </AdminCard>

            <div
                class="flex flex-col-reverse gap-3 border-t border-[#DCE7DF] pt-5 sm:flex-row sm:items-center sm:justify-end"
            >
                <CancelButton
                    :href="admin.projects.index().url"
                    :icon="ArrowLeft"
                    label="Cancelar"
                />

                <AdminButton
                    type="submit"
                    variant="primary"
                    :icon="Save"
                    :loading="form.processing"
                    loading-text="Guardando..."
                >
                    Guardar cambios
                </AdminButton>
            </div>
        </form>
    </div>
</template>
