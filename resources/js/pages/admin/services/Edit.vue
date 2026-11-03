<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import {
    ArrowLeft,
    BriefcaseBusiness,
    CheckCircle2,
    Hash,
    ImagePlus,
    Info,
    Save,
    Type,
} from 'lucide-vue-next'
import { ref } from 'vue'

import AdminButton from '@/components/admin/AdminButton.vue'
import AdminCard from '@/components/admin/AdminCard.vue'
import CancelButton from '@/components/admin/CancelButton.vue'
import GalleryDropzone, {
    type GalleryImageItem,
} from '@/components/admin/GalleryDropzone.vue'
import PageHeader from '@/components/admin/PageHeader.vue'
import admin from '@/routes/admin'

interface CategoryOption {
    value: string
    label: string
}

interface ExistingGalleryImage {
    id: number
    url: string
    file: string
    alt: string | null
    is_primary: boolean
    sort_order: number
}

interface ServiceData {
    id: number
    name: string
    slug: string | null
    category: string
    short_description: string | null
    description: string | null
    sort_order: number
    is_active: boolean
    gallery_images: ExistingGalleryImage[]
}

interface Props {
    service: ServiceData
    categories: CategoryOption[]
}

const props = defineProps<Props>()

const form = useForm({
    name: props.service.name ?? '',
    slug: props.service.slug ?? '',
    category: props.service.category ?? '',
    short_description: props.service.short_description ?? '',
    description: props.service.description ?? '',
    sort_order: props.service.sort_order ?? 0,
    is_active: Boolean(props.service.is_active),
    gallery_images: [] as File[],
    gallery_metadata: [] as {
        alt: string | null
        is_primary: boolean
        sort_order: number
    }[],
    delete_gallery_images: [] as number[],
})

const existingImages = (props.service.gallery_images ?? []).map(
    (image) => ({
        ...image,
    }),
)

const galleryImages = ref<GalleryImageItem[]>([])

function toggleDeleteImage(imageId: number, checked: boolean): void {
    if (checked) {
        if (!form.delete_gallery_images.includes(imageId)) {
            form.delete_gallery_images.push(imageId)
        }

        return
    }

    form.delete_gallery_images = form.delete_gallery_images.filter(
        (id) => id !== imageId,
    )
}

function isImageMarkedForDeletion(imageId: number): boolean {
    return form.delete_gallery_images.includes(imageId)
}

function submit(): void {
    const newGalleryFiles = galleryImages.value
        .filter((item) => item.file instanceof File)
        .map((item) => ({
            file: item.file as File,
            alt: item.alt?.trim() || null,
            is_primary: Boolean(item.is_primary),
            sort_order: item.sort_order,
        }))

    const remainingExistingImages = existingImages.filter(
        (image) => !isImageMarkedForDeletion(image.id),
    )

    const existingPrimary = remainingExistingImages.some(
        (image) => image.is_primary,
    )

    if (
        newGalleryFiles.length > 0 &&
        !existingPrimary &&
        !newGalleryFiles.some((image) => image.is_primary)
    ) {
        newGalleryFiles[0].is_primary = true
    }

    let primaryAssigned = existingPrimary

    newGalleryFiles.forEach((image) => {
        if (image.is_primary && !primaryAssigned) {
            primaryAssigned = true
        } else if (image.is_primary) {
            image.is_primary = false
        }
    })

    form
        .transform((data) => ({
            ...data,
            _method: 'put',
            slug: data.slug.trim() || null,
            gallery_images: newGalleryFiles.map((image) => image.file),
            gallery_metadata: newGalleryFiles.map((image, index) => ({
                alt: image.alt,
                is_primary: image.is_primary,
                sort_order: remainingExistingImages.length + index,
            })),
            delete_gallery_images: data.delete_gallery_images,
        }))
        .post(admin.services.update(props.service.id).url, {
            forceFormData: true,
            preserveScroll: true,
        })
}
</script>

<template>
    <Head :title="`Editar ${service.name} | Industrias Husky`" />

    <div
        class="flex h-full flex-1 flex-col gap-5 overflow-x-auto bg-[#F2F7F4] p-4 sm:p-5"
    >
        <PageHeader
            title="Editar servicio"
            :description="`Modifica la información de ${service.name}.`"
            section="Servicios"
            :icon="BriefcaseBusiness"
            :action-icon="ArrowLeft"
            action-label="Volver a servicios"
            :action-href="admin.services.index().url"
        />

        <form class="flex flex-col gap-5" @submit.prevent="submit">
            <AdminCard
                title="Información del servicio"
                description="Actualiza el nombre, la categoría y el contenido del servicio."
                :icon="Info"
            >
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="form-field md:col-span-2">
                        <label for="name" class="form-label">
                            Nombre del servicio
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <BriefcaseBusiness
                                class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#718477]"
                            />

                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                maxlength="255"
                                required
                                placeholder="Ej. Servicio de maquila"
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
                                placeholder="servicio-de-maquila"
                                :class="[
                                    'form-input pl-11',
                                    form.errors.slug && 'form-input-error',
                                ]"
                            />
                        </div>

                        <p class="form-help">
                            Puedes dejarlo vacío para que Laravel genere el
                            slug a partir del nombre.
                        </p>

                        <p v-if="form.errors.slug" class="form-error">
                            {{ form.errors.slug }}
                        </p>
                    </div>

                    <div class="form-field md:col-span-2">
                        <label for="category" class="form-label">
                            Categoría
                            <span class="text-red-500">*</span>
                        </label>

                        <select
                            id="category"
                            v-model="form.category"
                            required
                            :class="[
                                'form-input',
                                form.errors.category && 'form-input-error',
                            ]"
                        >
                            <option value="" disabled>
                                Selecciona una categoría
                            </option>

                            <option
                                v-for="category in props.categories"
                                :key="category.value"
                                :value="category.value"
                            >
                                {{ category.label }}
                            </option>
                        </select>

                        <p v-if="form.errors.category" class="form-error">
                            {{ form.errors.category }}
                        </p>
                    </div>

                    <div class="form-field md:col-span-2">
                        <label for="short_description" class="form-label">
                            Descripción corta
                        </label>

                        <textarea
                            id="short_description"
                            v-model="form.short_description"
                            rows="3"
                            maxlength="1000"
                            placeholder="Resumen que aparecerá en las tarjetas de servicios."
                            :class="[
                                'form-input min-h-24 resize-y',
                                form.errors.short_description &&
                                    'form-input-error',
                            ]"
                        />

                        <div class="flex items-center justify-between gap-3">
                            <p
                                v-if="form.errors.short_description"
                                class="form-error"
                            >
                                {{ form.errors.short_description }}
                            </p>

                            <p class="ml-auto text-xs text-[#718477]">
                                {{ form.short_description.length }}/1000
                            </p>
                        </div>
                    </div>

                    <div class="form-field md:col-span-2">
                        <label for="description" class="form-label">
                            Descripción completa
                        </label>

                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="6"
                            placeholder="Describe el servicio, sus características y beneficios."
                            :class="[
                                'form-input min-h-36 resize-y',
                                form.errors.description &&
                                    'form-input-error',
                            ]"
                        />

                        <p v-if="form.errors.description" class="form-error">
                            {{ form.errors.description }}
                        </p>
                    </div>
                </div>
            </AdminCard>

            <AdminCard
                title="Galería de imágenes"
                description="Administra las fotografías existentes y agrega nuevas imágenes."
                :icon="ImagePlus"
            >
                <div
                    v-if="existingImages.length"
                    class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="image in existingImages"
                        :key="image.id"
                        class="overflow-hidden rounded-xl border border-[#DCE7DF] bg-white"
                        :class="
                            isImageMarkedForDeletion(image.id)
                                ? 'opacity-50'
                                : ''
                        "
                    >
                        <div
                            class="relative aspect-[4/3] overflow-hidden bg-[#EEF4F0]"
                        >
                            <img
                                :src="image.url"
                                :alt="image.alt ?? service.name"
                                class="h-full w-full object-cover"
                            />

                            <span
                                v-if="image.is_primary"
                                class="absolute left-3 top-3 rounded-full bg-[#187744] px-3 py-1 text-xs font-semibold text-white"
                            >
                                Imagen destacada
                            </span>

                            <span
                                v-if="isImageMarkedForDeletion(image.id)"
                                class="absolute inset-0 flex items-center justify-center bg-black/40 text-sm font-semibold text-white"
                            >
                                Se eliminará al guardar
                            </span>
                        </div>

                        <div class="space-y-3 p-4">
                            <p class="truncate text-sm text-[#718477]">
                                {{ image.alt || 'Sin texto alternativo' }}
                            </p>

                            <label
                                class="flex cursor-pointer items-center gap-2 text-sm font-medium text-red-600"
                            >
                                <input
                                    type="checkbox"
                                    :checked="
                                        isImageMarkedForDeletion(image.id)
                                    "
                                    :disabled="form.processing"
                                    class="h-4 w-4 rounded border-[#DCE7DF] accent-red-600"
                                    @change="
                                        toggleDeleteImage(
                                            image.id,
                                            (
                                                $event.target as HTMLInputElement
                                            ).checked,
                                        )
                                    "
                                />
                                Eliminar esta imagen
                            </label>
                        </div>
                    </div>
                </div>

                <p
                    v-else
                    class="mb-5 rounded-xl border border-dashed border-[#DCE7DF] bg-white p-5 text-center text-sm text-[#718477]"
                >
                    Este servicio todavía no tiene imágenes en su galería.
                </p>

                <div class="border-t border-[#DCE7DF] pt-5">
                    <h3 class="mb-3 text-sm font-semibold text-[#244334]">
                        Agregar nuevas imágenes
                    </h3>

                    <GalleryDropzone
                        v-model="galleryImages"
                        :max-files="20"
                        :max-size-mb="5"
                        :disabled="form.processing"
                    />
                </div>

                <p class="form-help mt-4">
                    Puedes agregar imágenes de hasta 5 MB cada una. Define el
                    texto ALT y selecciona una imagen destacada desde la
                    galería.
                </p>

                <p v-if="form.errors.gallery_images" class="form-error mt-2">
                    {{ form.errors.gallery_images }}
                </p>

                <template v-for="(error, key) in form.errors" :key="key">
                    <p
                        v-if="
                            key.startsWith('gallery_images.') ||
                            key.startsWith('gallery_metadata.')
                        "
                        class="form-error mt-1"
                    >
                        {{ error }}
                    </p>
                </template>

                <p
                    v-if="form.errors.delete_gallery_images"
                    class="form-error mt-2"
                >
                    {{ form.errors.delete_gallery_images }}
                </p>
            </AdminCard>

            <AdminCard
                title="Publicación"
                description="Configura el orden y la disponibilidad del servicio."
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
                                    form.errors.sort_order &&
                                        'form-input-error',
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
                            <span
                                class="block text-sm font-semibold text-[#244334]"
                            >
                                Servicio activo
                            </span>

                            <span class="mt-1 block text-sm text-[#718477]">
                                El servicio estará disponible para mostrarse
                                en el sitio web.
                            </span>
                        </span>
                    </label>
                </div>
            </AdminCard>

            <div
                class="flex flex-col-reverse gap-3 border-t border-[#DCE7DF] pt-5 sm:flex-row sm:items-center sm:justify-end"
            >
                <CancelButton
                    :href="admin.services.index().url"
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