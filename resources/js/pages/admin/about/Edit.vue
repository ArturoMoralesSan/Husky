
<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import {
    ArrowLeft,
    BookOpen,
    Building2,
    Compass,
    Eye,
    HeartHandshake,
    ImagePlus,
    Plus,
    Save,
    Target,
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

interface AboutValue {
    id?: number
    name: string
    description: string | null
    icon: string | null
    sort_order: number
}

interface ExistingImage {
    id: number
    url?: string | null
    path?: string | null
    preview?: string | null
    alt?: string | null
    name?: string | null
    is_primary?: boolean
    sort_order?: number
}

interface AboutData {
    id: number
    who_we_are: string | null
    history: string | null
    mission: string | null
    vision: string | null
    objectives: string | null
    values: AboutValue[]
    gallery_images?: ExistingImage[]
    gallery?: {
        images?: ExistingImage[]
    } | null
}

const props = defineProps<{
    about: AboutData
}>()

function getImageUrl(image: ExistingImage): string {
    const imageUrl = image.url || image.preview || image.path || ''

    if (
        imageUrl.startsWith('http://') ||
        imageUrl.startsWith('https://') ||
        imageUrl.startsWith('data:') ||
        imageUrl.startsWith('blob:')
    ) {
        return imageUrl
    }

    const normalizedPath = imageUrl
        .replace(/^\/+/, '')
        .replace(/^storage\//, '')

    return normalizedPath ? `/storage/${normalizedPath}` : ''
}

const form = useForm({
    who_we_are: props.about.who_we_are ?? '',
    history: props.about.history ?? '',
    mission: props.about.mission ?? '',
    vision: props.about.vision ?? '',
    objectives: props.about.objectives ?? '',
    values: (props.about.values ?? [])
        .slice()
        .sort((a, b) => a.sort_order - b.sort_order)
        .map((value) => ({
            id: value.id,
            name: value.name,
            description: value.description ?? '',
            icon: value.icon ?? '',
            sort_order: value.sort_order,
        })),
})

const originalGalleryImages =
    props.about.gallery_images ?? props.about.gallery?.images ?? []

const galleryImages = ref<GalleryImageItem[]>(
    originalGalleryImages.map((image) => ({
        id: image.id,
        file: null,
        preview: getImageUrl(image),
        alt: image.alt ?? image.name ?? '',
        is_primary: Boolean(image.is_primary),
        sort_order: image.sort_order ?? 0,
    })),
)

const removedGalleryImageIds = computed(() => {
    const currentIds = galleryImages.value
        .filter((image) => image.id)
        .map((image) => image.id)

    return originalGalleryImages
        .filter((image) => !currentIds.includes(image.id))
        .map((image) => image.id)
})

function addValue(): void {
    form.values.push({
        name: '',
        description: '',
        icon: '',
        sort_order: form.values.length,
    })
}

function removeValue(index: number): void {
    form.values.splice(index, 1)
    updateOrder()
}

function moveValue(index: number, direction: -1 | 1): void {
    const newIndex = index + direction

    if (newIndex < 0 || newIndex >= form.values.length) {
        return
    }

    const current = form.values[index]
    const target = form.values[newIndex]

    if (!current || !target) {
        return
    }

    form.values[index] = target
    form.values[newIndex] = current

    updateOrder()
}

function updateOrder(): void {
    form.values.forEach((value, index) => {
        value.sort_order = index
    })
}

function submit(): void {
    updateOrder()

    const newGalleryImages = galleryImages.value
        .filter((image) => image.file instanceof File)

    const newImagesMetadata = newGalleryImages.map((image, index) => ({
        alt: image.alt?.trim() || null,
        is_primary: Boolean(image.is_primary),
        sort_order: image.sort_order ?? index,
    }))

    const existingGalleryMetadata = galleryImages.value
        .filter((image) => image.id)
        .map((image, index) => ({
            id: image.id,
            alt: image.alt?.trim() || null,
            is_primary: Boolean(image.is_primary),
            sort_order: image.sort_order ?? index,
        }))

    // Garantiza que exista una sola imagen principal.
    const allImages = [
        ...existingGalleryMetadata,
        ...newImagesMetadata,
    ].sort((a, b) => a.sort_order - b.sort_order)

    const primaryImage = allImages.find((image) => image.is_primary)

    if (allImages.length > 0 && !primaryImage) {
        allImages[0].is_primary = true
    }

    let primaryAssigned = false

    allImages.forEach((image) => {
        if (image.is_primary && !primaryAssigned) {
            primaryAssigned = true
        } else if (image.is_primary) {
            image.is_primary = false
        }
    })

    // Actualiza los metadatos con la selección final de imagen principal.
    const finalExistingMetadata = existingGalleryMetadata.map((image) => {
        const finalImage = allImages.find(
            (item) => item.id === image.id,
        )

        return {
            ...image,
            is_primary: finalImage?.is_primary ?? false,
        }
    })

    const finalNewMetadata = newImagesMetadata.map((image, index) => {
        const finalImage = allImages.find(
            (item) =>
                !item.id &&
                item.sort_order === image.sort_order,
        )

        return {
            ...image,
            is_primary: finalImage?.is_primary ?? false,
        }
    })

    form
        .transform((data) => ({
            ...data,

            _method: 'put',

            who_we_are: data.who_we_are.trim() || null,
            history: data.history.trim() || null,
            mission: data.mission.trim() || null,
            vision: data.vision.trim() || null,
            objectives: data.objectives.trim() || null,

            values: data.values
                .filter((value) => value.name.trim() !== '')
                .map((value, index) => ({
                    ...value,
                    name: value.name.trim(),
                    description: value.description.trim() || null,
                    icon: value.icon.trim() || null,
                    sort_order: index,
                })),

            gallery_images: newGalleryImages.map(
                (image) => image.file,
            ),

            gallery_metadata: finalNewMetadata,

            remove_gallery_images: removedGalleryImageIds.value,

            existing_gallery_metadata: finalExistingMetadata,
        }))
        .post(admin.about.update().url, {
            forceFormData: true,
            preserveScroll: true,
        })
}
</script>

<template>
    <Head title="Editar Acerca de nosotros | Industrias Husky" />

    <div
        class="flex h-full flex-1 flex-col gap-5 overflow-x-auto bg-[#F2F7F4] p-4 sm:p-5"
    >
        <PageHeader
            title="Editar Acerca de nosotros"
            description="Modifica la información institucional que se muestra en el sitio web de Industrias Husky."
            section="Contenido"
            :icon="Building2"
            :action-icon="ArrowLeft"
            action-label="Volver"
            :action-href="admin.about.index().url"
        />

        <form class="flex flex-col gap-5" @submit.prevent="submit">
            <!-- Quiénes somos -->
            <AdminCard
                title="Quiénes somos"
                description="Presenta a Industrias Husky y explica quiénes somos como empresa."
                :icon="Building2"
            >
                <div class="form-field">
                    <label for="who_we_are" class="form-label">
                        Descripción
                    </label>

                    <textarea
                        id="who_we_are"
                        v-model="form.who_we_are"
                        rows="5"
                        placeholder="Describe a Industrias Husky, su actividad y lo que la distingue..."
                        :class="[
                            'form-input resize-y',
                            form.errors.who_we_are && 'form-input-error',
                        ]"
                    />

                    <p class="form-help">
                        Información general sobre la empresa.
                    </p>

                    <p v-if="form.errors.who_we_are" class="form-error">
                        {{ form.errors.who_we_are }}
                    </p>
                </div>
            </AdminCard>

            <!-- Historia -->
            <AdminCard
                title="Historia"
                description="Comparte los antecedentes y la trayectoria de la empresa."
                :icon="BookOpen"
            >
                <div class="form-field">
                    <label for="history" class="form-label">
                        Historia de la empresa
                    </label>

                    <textarea
                        id="history"
                        v-model="form.history"
                        rows="5"
                        placeholder="Describe cómo comenzó Industrias Husky y su evolución..."
                        :class="[
                            'form-input resize-y',
                            form.errors.history && 'form-input-error',
                        ]"
                    />

                    <p v-if="form.errors.history" class="form-error">
                        {{ form.errors.history }}
                    </p>
                </div>
            </AdminCard>

            <!-- Misión y visión -->
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <AdminCard
                    title="Misión"
                    description="Define el propósito y compromiso de la empresa."
                    :icon="Target"
                >
                    <div class="form-field">
                        <label for="mission" class="form-label">
                            Nuestra misión
                        </label>

                        <textarea
                            id="mission"
                            v-model="form.mission"
                            rows="6"
                            placeholder="Escribe la misión de Industrias Husky..."
                            :class="[
                                'form-input resize-y',
                                form.errors.mission && 'form-input-error',
                            ]"
                        />

                        <p v-if="form.errors.mission" class="form-error">
                            {{ form.errors.mission }}
                        </p>
                    </div>
                </AdminCard>

                <AdminCard
                    title="Visión"
                    description="Describe hacia dónde quiere llegar la empresa."
                    :icon="Eye"
                >
                    <div class="form-field">
                        <label for="vision" class="form-label">
                            Nuestra visión
                        </label>

                        <textarea
                            id="vision"
                            v-model="form.vision"
                            rows="6"
                            placeholder="Escribe la visión de Industrias Husky..."
                            :class="[
                                'form-input resize-y',
                                form.errors.vision && 'form-input-error',
                            ]"
                        />

                        <p v-if="form.errors.vision" class="form-error">
                            {{ form.errors.vision }}
                        </p>
                    </div>
                </AdminCard>
            </div>

            <!-- Objetivos -->
            <AdminCard
                title="Objetivos"
                description="Establece los objetivos y metas que orientan el trabajo de la empresa."
                :icon="Compass"
            >
                <div class="form-field">
                    <label for="objectives" class="form-label">
                        Objetivos de la empresa
                    </label>

                    <textarea
                        id="objectives"
                        v-model="form.objectives"
                        rows="5"
                        placeholder="Describe los objetivos de Industrias Husky..."
                        :class="[
                            'form-input resize-y',
                            form.errors.objectives && 'form-input-error',
                        ]"
                    />

                    <p v-if="form.errors.objectives" class="form-error">
                        {{ form.errors.objectives }}
                    </p>
                </div>
            </AdminCard>

            <!-- Valores -->
            <AdminCard
                title="Nuestros valores"
                description="Administra los valores que representan la identidad y filosofía de Industrias Husky."
                :icon="HeartHandshake"
            >
                <div class="flex flex-col gap-4">
                    <div
                        v-if="form.values.length === 0"
                        class="flex flex-col items-center justify-center rounded-xl border border-dashed border-[#C8DCCF] bg-[#FAFCFA] px-5 py-8 text-center"
                    >
                        <div
                            class="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-[#E4F2E8] text-[#187744]"
                        >
                            <HeartHandshake class="h-6 w-6" />
                        </div>

                        <p class="font-medium text-[#244334]">
                            Todavía no hay valores registrados
                        </p>

                        <p class="mt-1 text-sm text-[#718277]">
                            Agrega los valores que identifican a tu empresa.
                        </p>
                    </div>

                    <div
                        v-for="(value, index) in form.values"
                        :key="index"
                        class="rounded-xl border border-[#DCE7DF] bg-[#FAFCFA] p-4 sm:p-5"
                    >
                        <div
                            class="mb-4 flex flex-wrap items-center justify-between gap-3"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#E4F2E8] text-[#187744]"
                                >
                                    <HeartHandshake class="h-4 w-4" />
                                </div>

                                <div>
                                    <h4 class="font-semibold text-[#244334]">
                                        Valor {{ index + 1 }}
                                    </h4>

                                    <p class="text-xs text-[#87958B]">
                                        Posición {{ index + 1 }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    :disabled="index === 0 || form.processing"
                                    class="rounded-lg border border-[#DCE7DF] bg-white px-3 py-2 text-xs font-medium text-[#244334] transition-colors hover:bg-[#EDF4EF] disabled:cursor-not-allowed disabled:opacity-40"
                                    @click="moveValue(index, -1)"
                                >
                                    Subir
                                </button>

                                <button
                                    type="button"
                                    :disabled="
                                        index === form.values.length - 1 ||
                                        form.processing
                                    "
                                    class="rounded-lg border border-[#DCE7DF] bg-white px-3 py-2 text-xs font-medium text-[#244334] transition-colors hover:bg-[#EDF4EF] disabled:cursor-not-allowed disabled:opacity-40"
                                    @click="moveValue(index, 1)"
                                >
                                    Bajar
                                </button>

                                <button
                                    type="button"
                                    :disabled="form.processing"
                                    class="inline-flex items-center gap-2 rounded-lg border border-[#F2D1CD] bg-[#FFF1EF] px-3 py-2 text-xs font-semibold text-[#C62828] transition-colors hover:bg-[#FCE5E2] disabled:opacity-50"
                                    @click="removeValue(index)"
                                >
                                    <Trash2 class="h-3.5 w-3.5" />

                                    <span class="hidden sm:inline">
                                        Eliminar
                                    </span>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="form-field md:col-span-2">
                                <label
                                    :for="`value-name-${index}`"
                                    class="form-label"
                                >
                                    Nombre del valor
                                    <span class="text-red-500">*</span>
                                </label>

                                <div class="relative">
                                    <Type
                                        class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#718477]"
                                    />

                                    <input
                                        :id="`value-name-${index}`"
                                        v-model="value.name"
                                        type="text"
                                        maxlength="255"
                                        placeholder="Ej. Compromiso"
                                        :class="[
                                            'form-input pl-11',
                                            form.errors[`values.${index}.name`] &&
                                                'form-input-error',
                                        ]"
                                    />
                                </div>

                                <p
                                    v-if="form.errors[`values.${index}.name`]"
                                    class="form-error"
                                >
                                    {{ form.errors[`values.${index}.name`] }}
                                </p>
                            </div>

                            <div class="form-field md:col-span-2">
                                <label
                                    :for="`value-description-${index}`"
                                    class="form-label"
                                >
                                    Descripción
                                </label>

                                <textarea
                                    :id="`value-description-${index}`"
                                    v-model="value.description"
                                    rows="3"
                                    placeholder="Explica qué significa este valor para la empresa..."
                                    :class="[
                                        'form-input resize-y',
                                        form.errors[
                                            `values.${index}.description`
                                        ] && 'form-input-error',
                                    ]"
                                />

                                <p
                                    v-if="
                                        form.errors[
                                            `values.${index}.description`
                                        ]
                                    "
                                    class="form-error"
                                >
                                    {{
                                        form.errors[
                                            `values.${index}.description`
                                        ]
                                    }}
                                </p>
                            </div>

                            <div class="form-field">
                                <label
                                    :for="`value-icon-${index}`"
                                    class="form-label"
                                >
                                    Nombre del icono
                                </label>

                                <input
                                    :id="`value-icon-${index}`"
                                    v-model="value.icon"
                                    type="text"
                                    maxlength="100"
                                    placeholder="Ej. Handshake"
                                    :class="[
                                        'form-input',
                                        form.errors[`values.${index}.icon`] &&
                                            'form-input-error',
                                    ]"
                                />

                                <p class="form-help">
                                    Nombre del icono que utilizará el sitio web.
                                </p>

                                <p
                                    v-if="form.errors[`values.${index}.icon`]"
                                    class="form-error"
                                >
                                    {{ form.errors[`values.${index}.icon`] }}
                                </p>
                            </div>

                            <div class="form-field">
                                <label class="form-label">
                                    Orden de visualización
                                </label>

                                <input
                                    :value="index + 1"
                                    type="number"
                                    disabled
                                    class="form-input cursor-not-allowed bg-[#F2F7F4]"
                                />

                                <p class="form-help">
                                    Utiliza los botones subir y bajar para
                                    cambiar la posición.
                                </p>
                            </div>
                        </div>
                    </div>

                    <p v-if="form.errors.values" class="form-error">
                        {{ form.errors.values }}
                    </p>

                    <div>
                        <button
                            type="button"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 rounded-lg border border-[#C8DCCF] bg-[#E9F4EC] px-4 py-2.5 text-sm font-semibold text-[#187744] transition-colors hover:bg-[#DDEEE2] disabled:cursor-not-allowed disabled:opacity-50"
                            @click="addValue"
                        >
                            <Plus class="h-4 w-4" />
                            Agregar valor
                        </button>
                    </div>
                </div>
            </AdminCard>

            <!-- Galería -->
            <AdminCard
                title="Galería de imágenes"
                description="Administra las fotografías de las instalaciones, procesos y equipo de Industrias Husky."
                :icon="ImagePlus"
            >
                <GalleryDropzone
                    v-model="galleryImages"
                    :max-files="20"
                    :max-size-mb="5"
                    :disabled="form.processing"
                />

                <p class="form-help mt-4">
                    Las imágenes existentes se conservan al guardar. Puedes
                    agregar imágenes nuevas, cambiar sus textos ALT, elegir
                    la imagen destacada o eliminar las que ya no necesites.
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
            </AdminCard>

            <!-- Acciones -->
            <div
                class="flex flex-col-reverse gap-3 border-t border-[#DCE7DF] pt-5 sm:flex-row sm:items-center sm:justify-end"
            >
                <CancelButton
                    :href="admin.about.index().url"
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
