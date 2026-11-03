
<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
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

const form = useForm({
    name: '',
    category: '',
    description: '',
    location: '',
    year: null as number | null,
    sort_order: 0,
    is_active: true,
})

const galleryImages = ref<GalleryImageItem[]>([])

function submit(): void {
    const galleryFiles = galleryImages.value
        .filter((item) => item.file instanceof File)
        .map((item) => ({
            file: item.file as File,
            alt: item.alt?.trim() || null,
            is_primary: Boolean(item.is_primary),
            sort_order: item.sort_order,
        }))

    // Si no se seleccionó una imagen destacada,
    // se utiliza la primera imagen de la galería.
    if (
        galleryFiles.length > 0 &&
        !galleryFiles.some((item) => item.is_primary)
    ) {
        galleryFiles[0].is_primary = true
    }

    // Garantiza que solamente una imagen sea destacada.
    let primaryAssigned = false

    galleryFiles.forEach((item) => {
        if (item.is_primary && !primaryAssigned) {
            primaryAssigned = true
        } else if (item.is_primary) {
            item.is_primary = false
        }
    })

    form
        .transform((data) => ({
            ...data,
            category: data.category.trim() || null,
            description: data.description.trim() || null,
            location: data.location.trim() || null,
            year: data.year === null || data.year === ('' as unknown as number)
                ? null
                : Number(data.year),
            gallery_images: galleryFiles.map((item) => item.file),
            gallery_metadata: galleryFiles.map((item, index) => ({
                alt: item.alt,
                is_primary: item.is_primary,
                sort_order: index,
            })),
        }))
        .post(admin.projects.store().url, {
            forceFormData: true,
            preserveScroll: true,
        })
}
</script>

<template>
    <Head title="Nuevo proyecto" />

    <div
        class="flex h-full flex-1 flex-col gap-5 overflow-x-auto bg-[#F2F7F4] p-4 sm:p-5"
    >
        <PageHeader
            title="Nuevo proyecto"
            description="Registra un proyecto y muestra las aplicaciones de la madera."
            section="Proyectos"
            :icon="FolderKanban"
            :action-icon="ArrowLeft"
            action-label="Volver a proyectos"
            :action-href="admin.projects.index().url"
        />

        <form class="flex flex-col gap-5" @submit.prevent="submit">
            <AdminCard
                title="Información del proyecto"
                description="Define el nombre y los detalles generales del proyecto."
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
                                placeholder="Ej. Infraestructura eléctrica en zona rural"
                                required
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
                                placeholder="Ej. Infraestructura eléctrica"
                                :class="[
                                    'form-input pl-11',
                                    form.errors.category && 'form-input-error',
                                ]"
                            />
                        </div>

                        <p class="form-help">
                            Identifica el tipo de aplicación o sector al que pertenece el proyecto.
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
                            placeholder="Describe el proyecto, su aplicación y cómo se utiliza la madera."
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
                description="Agrega fotografías del proyecto, define la imagen destacada y organiza el orden."
                :icon="ImagePlus"
            >
                <GalleryDropzone
                    v-model="galleryImages"
                    :max-files="20"
                    :max-size-mb="5"
                    :disabled="form.processing"
                />

                <p class="form-help mt-4">
                    Puedes agregar hasta 20 imágenes de máximo 5 MB cada una.
                    Define el texto ALT y selecciona una imagen destacada desde la galería.
                </p>

                <p
                    v-if="form.errors.gallery_images"
                    class="form-error mt-2"
                >
                    {{ form.errors.gallery_images }}
                </p>

                <p
                    v-for="(error, key) in form.errors"
                    :key="key"
                    class="form-error mt-1"
                >
                    <template v-if="key.startsWith('gallery_images.')">
                        {{ error }}
                    </template>
                    <template v-else-if="key.startsWith('gallery_metadata.')">
                        {{ error }}
                    </template>
                </p>
            </AdminCard>

            <AdminCard
                title="Publicación"
                description="Define el orden en que aparecerá el proyecto y si estará visible."
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
                    Crear proyecto
                </AdminButton>
            </div>
        </form>
    </div>
</template>
