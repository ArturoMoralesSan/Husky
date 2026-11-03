
<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { onUnmounted, ref } from 'vue'
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

const form = useForm({
    name: '',
    slug: '',
    short_description: '',
    description: '',
    material: '',
    treatment: '',
    retention: null as number | null,
    retention_unit: 'lbs/pie³',
    sort_order: 0,
    is_active: true,
    variants: [] as Variant[],
})

const galleryImages = ref<GalleryImageItem[]>([])

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
    const galleryFiles = galleryImages.value
        .filter((item) => item.file instanceof File)
        .map((item) => ({
            file: item.file as File,
            alt: item.alt?.trim() || null,
            is_primary: Boolean(item.is_primary),
            sort_order: item.sort_order,
        }))

    // En la creación, si no se eligió una destacada,
    // se utiliza la primera imagen de la galería.
    if (
        galleryFiles.length > 0 &&
        !galleryFiles.some((item) => item.is_primary)
    ) {
        galleryFiles[0].is_primary = true
    }

    // Garantiza que solo una imagen nueva sea destacada.
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
            slug: data.slug.trim() || null,
            retention:
                data.retention === null || data.retention === ('' as unknown as number)
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
            gallery_images: galleryFiles.map((item) => item.file),
            gallery_metadata: galleryFiles.map((item, index) => ({
                alt: item.alt,
                is_primary: item.is_primary,
                sort_order: index,
            })),
        }))
        .post(admin.products.store().url, {
            forceFormData: true,
            preserveScroll: true,
        })
}

onUnmounted(() => {
    // GalleryDropzone es responsable de liberar las URL
    // temporales que crea para sus propias vistas previas.
})
</script>

<template>
    <Head title="Nuevo producto" />

    <div
        class="flex h-full flex-1 flex-col gap-5 overflow-x-auto bg-[#F2F7F4] p-4 sm:p-5"
    >
        <PageHeader
            title="Nuevo producto"
            description="Registra un producto del catálogo de Industrias Husky."
            section="Productos"
            :icon="Package"
            :action-icon="ArrowLeft"
            action-label="Volver a productos"
            :action-href="admin.products.index().url"
        />

        <form class="flex flex-col gap-5" @submit.prevent="submit">
            <AdminCard
                title="Información del producto"
                description="Define el nombre, las descripciones y la dirección del producto."
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
                                placeholder="Ej. Poste de madera tratado"
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
                            Puedes dejarlo vacío para que Laravel lo genere a partir del nombre.
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
                                form.errors.short_description && 'form-input-error',
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
                description="Agrega fotografías, define la imagen destacada y organiza el orden."
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
                title="Material y tratamiento"
                description="Especifica la composición del producto y los datos de su tratamiento."
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
                                form.errors.retention_unit && 'form-input-error',
                            ]"
                        />

                        <p v-if="form.errors.retention_unit" class="form-error">
                            {{ form.errors.retention_unit }}
                        </p>
                    </div>
                </div>
            </AdminCard>

            <AdminCard
                title="Variantes y dimensiones"
                description="Agrega las medidas y especificaciones disponibles para este producto."
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
                            Puedes registrar largos, diámetros, anchos y espesores.
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
                                <p class="text-sm font-semibold text-[#244334]">
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
                                    :disabled="index === 0 || form.processing"
                                    aria-label="Mover variante arriba"
                                    @click="moveVariant(index, -1)"
                                >
                                    <ArrowUp class="h-4 w-4" />
                                </button>

                                <button
                                    type="button"
                                    class="rounded-lg border border-[#DCE7DF] bg-white p-2 text-[#526B5C] transition hover:bg-[#EAF3ED] disabled:opacity-40"
                                    :disabled="index === form.variants.length - 1 || form.processing"
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

                        <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2">
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

                            <div class="form-field">
                                <label
                                    :for="`variant-length-${index}`"
                                    class="form-label"
                                >
                                    Largo
                                </label>

                                <div class="flex gap-2">
                                    <input
                                        :id="`variant-length-${index}`"
                                        v-model="variant.length"
                                        type="number"
                                        min="0"
                                        step="0.001"
                                        placeholder="0.000"
                                        class="form-input min-w-0 flex-1"
                                    />

                                    <select
                                        v-model="variant.length_unit"
                                        class="form-input w-24 shrink-0"
                                        aria-label="Unidad de largo"
                                    >
                                        <option value="m">m</option>
                                        <option value="cm">cm</option>
                                        <option value="mm">mm</option>
                                        <option value="ft">ft</option>
                                        <option value="in">in</option>
                                    </select>
                                </div>

                                <p
                                    v-if="form.errors[`variants.${index}.length`]"
                                    class="form-error"
                                >
                                    {{ form.errors[`variants.${index}.length`] }}
                                </p>
                            </div>

                            <div class="form-field">
                                <label
                                    :for="`variant-diameter-${index}`"
                                    class="form-label"
                                >
                                    Diámetro
                                </label>

                                <div class="flex gap-2">
                                    <input
                                        :id="`variant-diameter-${index}`"
                                        v-model="variant.diameter"
                                        type="number"
                                        min="0"
                                        step="0.001"
                                        placeholder="0.000"
                                        class="form-input min-w-0 flex-1"
                                    />

                                    <select
                                        v-model="variant.diameter_unit"
                                        class="form-input w-24 shrink-0"
                                        aria-label="Unidad de diámetro"
                                    >
                                        <option value="cm">cm</option>
                                        <option value="m">m</option>
                                        <option value="mm">mm</option>
                                        <option value="in">in</option>
                                    </select>
                                </div>

                                <p
                                    v-if="form.errors[`variants.${index}.diameter`]"
                                    class="form-error"
                                >
                                    {{ form.errors[`variants.${index}.diameter`] }}
                                </p>
                            </div>

                            <div class="form-field">
                                <label
                                    :for="`variant-width-${index}`"
                                    class="form-label"
                                >
                                    Ancho
                                </label>

                                <div class="flex gap-2">
                                    <input
                                        :id="`variant-width-${index}`"
                                        v-model="variant.width"
                                        type="number"
                                        min="0"
                                        step="0.001"
                                        placeholder="0.000"
                                        class="form-input min-w-0 flex-1"
                                    />

                                    <select
                                        v-model="variant.width_unit"
                                        class="form-input w-24 shrink-0"
                                        aria-label="Unidad de ancho"
                                    >
                                        <option value="cm">cm</option>
                                        <option value="m">m</option>
                                        <option value="mm">mm</option>
                                        <option value="in">in</option>
                                    </select>
                                </div>

                                <p
                                    v-if="form.errors[`variants.${index}.width`]"
                                    class="form-error"
                                >
                                    {{ form.errors[`variants.${index}.width`] }}
                                </p>
                            </div>

                            <div class="form-field">
                                <label
                                    :for="`variant-thickness-${index}`"
                                    class="form-label"
                                >
                                    Espesor
                                </label>

                                <div class="flex gap-2">
                                    <input
                                        :id="`variant-thickness-${index}`"
                                        v-model="variant.thickness"
                                        type="number"
                                        min="0"
                                        step="0.001"
                                        placeholder="0.000"
                                        class="form-input min-w-0 flex-1"
                                    />

                                    <select
                                        v-model="variant.thickness_unit"
                                        class="form-input w-24 shrink-0"
                                        aria-label="Unidad de espesor"
                                    >
                                        <option value="cm">cm</option>
                                        <option value="m">m</option>
                                        <option value="mm">mm</option>
                                        <option value="in">in</option>
                                    </select>
                                </div>

                                <p
                                    v-if="form.errors[`variants.${index}.thickness`]"
                                    class="form-error"
                                >
                                    {{ form.errors[`variants.${index}.thickness`] }}
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
                                    <span class="block text-sm font-semibold text-[#244334]">
                                        Variante activa
                                    </span>
                                    <span class="mt-1 block text-xs text-[#718477]">
                                        Esta presentación estará disponible en el catálogo.
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
                                Producto activo
                            </span>
                            <span class="mt-1 block text-sm text-[#718477]">
                                El producto estará disponible para mostrarse en el catálogo.
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
                    Crear producto
                </AdminButton>
            </div>
        </form>
    </div>
</template>
