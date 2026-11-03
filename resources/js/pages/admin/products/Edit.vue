<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import {
    ArrowDown,
    ArrowLeft,
    ArrowUp,
    CheckCircle2,
    Hash,
    ImagePlus,
    Info,
    Layers3,
    Package,
    Plus,
    Ruler,
    Save,
    ShieldCheck,
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

interface Variant {
    id?: number
    name: string
    length: number | string | null
    length_unit: string
    diameter: number | string | null
    diameter_unit: string
    width: number | string | null
    width_unit: string
    thickness: number | string | null
    thickness_unit: string
    specifications: Record<string, string>
    is_active: boolean
    sort_order: number
}

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

interface Product {
    id: number
    name: string
    slug: string | null
    short_description: string | null
    description: string | null
    material: string | null
    treatment: string | null
    retention: number | string | null
    retention_unit: string | null
    sort_order: number
    is_active: boolean
    variants?: Variant[]
    gallery_images?: ExistingImage[]
    gallery?: {
        images?: ExistingImage[]
    } | null
}

const props = defineProps<{
    product: Product
}>()

/**
 * Convierte las rutas almacenadas en Laravel en URLs absolutas
 * desde la raíz del sitio.
 */
function getImageUrl(path: string | null | undefined): string | null {
    if (!path) {
        return null
    }

    const value = path.trim()

    if (!value) {
        return null
    }

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

            const productsIndex = parsed.pathname.indexOf('/products/')

            if (productsIndex !== -1) {
                return `/storage${parsed.pathname.substring(productsIndex)}${parsed.search}${parsed.hash}`
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
    name: props.product.name ?? '',
    slug: props.product.slug ?? '',
    short_description: props.product.short_description ?? '',
    description: props.product.description ?? '',
    material: props.product.material ?? '',
    treatment: props.product.treatment ?? '',
    retention:
        props.product.retention === null
            ? null
            : Number(props.product.retention),
    retention_unit: props.product.retention_unit ?? 'lbs/pie³',
    sort_order: props.product.sort_order ?? 0,
    is_active: Boolean(props.product.is_active),
    variants: (props.product.variants ?? []).map((variant, index) => ({
        ...variant,
        name: variant.name ?? '',
        length: variant.length ?? null,
        length_unit: variant.length_unit ?? 'm',
        diameter: variant.diameter ?? null,
        diameter_unit: variant.diameter_unit ?? 'cm',
        width: variant.width ?? null,
        width_unit: variant.width_unit ?? 'cm',
        thickness: variant.thickness ?? null,
        thickness_unit: variant.thickness_unit ?? 'cm',
        specifications: variant.specifications ?? {},
        is_active: Boolean(variant.is_active),
        sort_order: variant.sort_order ?? index,
    })) as Variant[],
})

/**
 * Las imágenes originales pueden llegar como gallery_images
 * o como gallery.images.
 */
const originalGalleryImages = (
    props.product.gallery_images ??
    props.product.gallery?.images ??
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

            if (!preview) {
                return null
            }

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

/**
 * Identifica las imágenes originales que se eliminaron de la galería.
 */
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

function addVariant(): void {
    form.variants.push({
        name: '',
        length: null,
        length_unit: 'm',
        diameter: null,
        diameter_unit: 'cm',
        width: null,
        width_unit: 'cm',
        thickness: null,
        thickness_unit: 'cm',
        specifications: {},
        is_active: true,
        sort_order: form.variants.length,
    })
}

function removeVariant(index: number): void {
    form.variants.splice(index, 1)
    reorderVariants()
}

function moveVariant(index: number, direction: -1 | 1): void {
    const targetIndex = index + direction

    if (targetIndex < 0 || targetIndex >= form.variants.length) {
        return
    }

    const [variant] = form.variants.splice(index, 1)
    form.variants.splice(targetIndex, 0, variant)

    reorderVariants()
}

function reorderVariants(): void {
    form.variants.forEach((variant, index) => {
        variant.sort_order = index
    })
}

function submit(): void {
    const newGalleryImages = galleryImages.value
        .filter((item) => item.file instanceof File)
        .map((item, index) => ({
            file: item.file as File,
            alt: item.alt?.trim() || null,
            is_primary: Boolean(item.is_primary),
            sort_order: item.sort_order ?? index,
        }))

    form
        .transform((data) => ({
            ...data,

            // Actualización mediante POST multipart y method spoofing.
            _method: 'put',

            slug: data.slug.trim() || null,

            retention:
                data.retention === null
                    ? null
                    : Number(data.retention),

            variants: data.variants.map((variant, index) => ({
                ...variant,
                length: variant.length === '' ? null : variant.length,
                diameter: variant.diameter === '' ? null : variant.diameter,
                width: variant.width === '' ? null : variant.width,
                thickness:
                    variant.thickness === '' ? null : variant.thickness,
                sort_order: index,
            })),

            gallery_images: newGalleryImages.map((item) => item.file),

            gallery_metadata: newGalleryImages.map((item) => ({
                alt: item.alt,
                is_primary: item.is_primary,
                sort_order: item.sort_order,
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
        .post(admin.products.update(props.product.id).url, {
            forceFormData: true,
            preserveScroll: true,
        })
}
</script>

<template>
    <Head :title="`Editar ${product.name}`" />

    <div
        class="flex h-full flex-1 flex-col gap-5 overflow-x-auto bg-[#F2F7F4] p-4 sm:p-5"
    >
        <PageHeader
            title="Editar producto"
            description="Actualiza la información del producto de Industrias Husky."
            section="Productos"
            :icon="Package"
            :action-icon="ArrowLeft"
            action-label="Volver a productos"
            :action-href="admin.products.index().url"
        />

        <form class="flex flex-col gap-5" @submit.prevent="submit">
            <AdminCard
                title="Información del producto"
                description="Actualiza el nombre, las descripciones y la URL del producto."
                :icon="Info"
            >
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="form-field md:col-span-2">
                        <label for="name" class="form-label">
                            Nombre del producto
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <Package
                                class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#718477]"
                            />

                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                maxlength="255"
                                required
                                placeholder="Ej. Poste de madera tratado"
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
                                placeholder="poste-de-madera-tratado"
                                :class="[
                                    'form-input pl-11',
                                    form.errors.slug && 'form-input-error',
                                ]"
                            />
                        </div>

                        <p class="form-help">
                            Puedes dejarlo vacío para que Laravel lo genere a
                            partir del nombre.
                        </p>

                        <p v-if="form.errors.slug" class="form-error">
                            {{ form.errors.slug }}
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
                            placeholder="Resumen que aparecerá en las tarjetas del catálogo."
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
                            placeholder="Describe las características, aplicaciones y beneficios del producto."
                            :class="[
                                'form-input min-h-36 resize-y',
                                form.errors.description && 'form-input-error',
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
                    Las imágenes existentes se conservan al guardar. Puedes
                    agregar imágenes nuevas, cambiar sus textos ALT, elegir la
                    imagen destacada o eliminar las que ya no necesites.
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
                title="Material y tratamiento"
                description="Actualiza la composición del producto y los datos de su tratamiento."
                :icon="ShieldCheck"
            >
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="form-field">
                        <label for="material" class="form-label">
                            Material
                        </label>

                        <input
                            id="material"
                            v-model="form.material"
                            type="text"
                            maxlength="255"
                            placeholder="Ej. Pino"
                            :class="[
                                'form-input',
                                form.errors.material && 'form-input-error',
                            ]"
                        />

                        <p v-if="form.errors.material" class="form-error">
                            {{ form.errors.material }}
                        </p>
                    </div>

                    <div class="form-field">
                        <label for="treatment" class="form-label">
                            Tratamiento
                        </label>

                        <input
                            id="treatment"
                            v-model="form.treatment"
                            type="text"
                            maxlength="255"
                            placeholder="Ej. CCA-C"
                            :class="[
                                'form-input',
                                form.errors.treatment && 'form-input-error',
                            ]"
                        />

                        <p v-if="form.errors.treatment" class="form-error">
                            {{ form.errors.treatment }}
                        </p>
                    </div>

                    <div class="form-field">
                        <label for="retention" class="form-label">
                            Retención
                        </label>

                        <input
                            id="retention"
                            v-model.number="form.retention"
                            type="number"
                            min="0"
                            step="0.0001"
                            placeholder="Ej. 0.6000"
                            :class="[
                                'form-input',
                                form.errors.retention && 'form-input-error',
                            ]"
                        />

                        <p v-if="form.errors.retention" class="form-error">
                            {{ form.errors.retention }}
                        </p>
                    </div>

                    <div class="form-field">
                        <label for="retention_unit" class="form-label">
                            Unidad de retención
                        </label>

                        <input
                            id="retention_unit"
                            v-model="form.retention_unit"
                            type="text"
                            maxlength="50"
                            placeholder="lbs/pie³"
                            :class="[
                                'form-input',
                                form.errors.retention_unit &&
                                    'form-input-error',
                            ]"
                        />

                        <p
                            v-if="form.errors.retention_unit"
                            class="form-error"
                        >
                            {{ form.errors.retention_unit }}
                        </p>
                    </div>
                </div>
            </AdminCard>

            <AdminCard
                title="Variantes y dimensiones"
                description="Edita las medidas, especificaciones y disponibilidad de cada presentación."
                :icon="Layers3"
            >
                <div class="flex flex-col gap-4">
                    <div
                        v-if="form.variants.length === 0"
                        class="rounded-xl border border-dashed border-[#C8D9CE] bg-[#F8FBF9] p-6 text-center"
                    >
                        <Ruler class="mx-auto h-8 w-8 text-[#8AA696]" />

                        <p class="mt-2 text-sm font-semibold text-[#244334]">
                            Sin variantes agregadas
                        </p>

                        <p class="mt-1 text-sm text-[#718477]">
                            Puedes registrar largos, diámetros, anchos y
                            espesores.
                        </p>
                    </div>

                    <article
                        v-for="(variant, index) in form.variants"
                        :key="variant.id ?? `new-${index}`"
                        class="overflow-hidden rounded-xl border border-[#DCE7DF] bg-white"
                    >
                        <div
                            class="flex flex-wrap items-center gap-3 border-b border-[#DCE7DF] bg-[#F8FBF9] px-4 py-3"
                        >
                            <div
                                class="flex size-9 items-center justify-center rounded-lg bg-[#E2F0E6] text-[#187744]"
                            >
                                <Ruler class="h-4 w-4" />
                            </div>

                            <div class="min-w-0 flex-1">
                                <p
                                    class="text-sm font-semibold text-[#244334]"
                                >
                                    Variante {{ index + 1 }}
                                </p>

                                <p class="text-xs text-[#718477]">
                                    Configura sus dimensiones y disponibilidad.
                                </p>
                            </div>

                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    class="rounded-lg border border-[#DCE7DF] bg-white p-2 text-[#526B5C] transition hover:bg-[#EAF3ED] disabled:opacity-40"
                                    :disabled="
                                        index === 0 || form.processing
                                    "
                                    aria-label="Mover variante arriba"
                                    @click="moveVariant(index, -1)"
                                >
                                    <ArrowUp class="h-4 w-4" />
                                </button>

                                <button
                                    type="button"
                                    class="rounded-lg border border-[#DCE7DF] bg-white p-2 text-[#526B5C] transition hover:bg-[#EAF3ED] disabled:opacity-40"
                                    :disabled="
                                        index === form.variants.length - 1 ||
                                        form.processing
                                    "
                                    aria-label="Mover variante abajo"
                                    @click="moveVariant(index, 1)"
                                >
                                    <ArrowDown class="h-4 w-4" />
                                </button>

                                <button
                                    type="button"
                                    class="rounded-lg border border-red-200 bg-white p-2 text-red-600 transition hover:bg-red-50 disabled:opacity-40"
                                    :disabled="form.processing"
                                    aria-label="Eliminar variante"
                                    @click="removeVariant(index)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <div
                            class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2"
                        >
                            <div class="form-field sm:col-span-2">
                                <label
                                    :for="`variant-name-${index}`"
                                    class="form-label"
                                >
                                    Nombre o presentación
                                </label>

                                <input
                                    :id="`variant-name-${index}`"
                                    v-model="variant.name"
                                    type="text"
                                    maxlength="255"
                                    placeholder="Ej. Poste de 8 metros"
                                    class="form-input"
                                />
                            </div>

                            <div
                                v-for="dimension in [
                                    {
                                        key: 'length',
                                        label: 'Largo',
                                        unit: 'length_unit',
                                        options: ['m', 'cm', 'mm', 'ft', 'in'],
                                    },
                                    {
                                        key: 'diameter',
                                        label: 'Diámetro',
                                        unit: 'diameter_unit',
                                        options: ['cm', 'm', 'mm', 'in'],
                                    },
                                    {
                                        key: 'width',
                                        label: 'Ancho',
                                        unit: 'width_unit',
                                        options: ['cm', 'm', 'mm', 'in'],
                                    },
                                    {
                                        key: 'thickness',
                                        label: 'Espesor',
                                        unit: 'thickness_unit',
                                        options: ['cm', 'm', 'mm', 'in'],
                                    },
                                ]"
                                :key="dimension.key"
                                class="form-field"
                            >
                                <label
                                    :for="`variant-${dimension.key}-${index}`"
                                    class="form-label"
                                >
                                    {{ dimension.label }}
                                </label>

                                <div class="flex gap-2">
                                    <input
                                        :id="`variant-${dimension.key}-${index}`"
                                        v-model="variant[dimension.key]"
                                        type="number"
                                        min="0"
                                        step="0.001"
                                        placeholder="0.000"
                                        class="form-input min-w-0 flex-1"
                                    />

                                    <select
                                        v-model="variant[dimension.unit]"
                                        class="form-input w-24 shrink-0"
                                        :aria-label="`Unidad de ${dimension.label.toLowerCase()}`"
                                    >
                                        <option
                                            v-for="unit in dimension.options"
                                            :key="unit"
                                            :value="unit"
                                        >
                                            {{ unit }}
                                        </option>
                                    </select>
                                </div>

                                <p
                                    v-if="
                                        form.errors[
                                            `variants.${index}.${dimension.key}`
                                        ]
                                    "
                                    class="form-error"
                                >
                                    {{
                                        form.errors[
                                            `variants.${index}.${dimension.key}`
                                        ]
                                    }}
                                </p>
                            </div>

                            <label
                                :for="`variant-active-${index}`"
                                class="flex cursor-pointer items-start gap-3 rounded-lg border border-[#DCE7DF] bg-[#F8FBF9] p-3 sm:col-span-2"
                            >
                                <input
                                    :id="`variant-active-${index}`"
                                    v-model="variant.is_active"
                                    type="checkbox"
                                    class="mt-1 h-4 w-4 accent-[#187744]"
                                />

                                <span>
                                    <span
                                        class="block text-sm font-semibold text-[#244334]"
                                    >
                                        Variante activa
                                    </span>

                                    <span
                                        class="mt-1 block text-xs text-[#718477]"
                                    >
                                        Esta presentación estará disponible en
                                        el catálogo.
                                    </span>
                                </span>
                            </label>
                        </div>
                    </article>

                    <div
                        v-for="(error, key) in form.errors"
                        :key="key"
                        class="text-sm text-red-600"
                    >
                        <template v-if="key.startsWith('variants.')">
                            {{ error }}
                        </template>
                    </div>

                    <div>
                        <AdminButton
                            type="button"
                            variant="secondary"
                            :icon="Plus"
                            :disabled="form.processing"
                            @click="addVariant"
                        >
                            Agregar variante
                        </AdminButton>
                    </div>
                </div>
            </AdminCard>

            <AdminCard
                title="Publicación"
                description="Define el orden en el catálogo y el estado del producto."
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
                                Producto activo
                            </span>

                            <span class="mt-1 block text-sm text-[#718477]">
                                El producto estará disponible para mostrarse en
                                el catálogo.
                            </span>
                        </span>
                    </label>
                </div>
            </AdminCard>

            <div
                class="flex flex-col-reverse gap-3 border-t border-[#DCE7DF] pt-5 sm:flex-row sm:items-center sm:justify-end"
            >
                <CancelButton
                    :href="admin.products.index().url"
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