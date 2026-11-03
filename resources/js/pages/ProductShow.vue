<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import {
    ArrowLeft,
    ArrowRight,
    ArrowUpRight,
    Check,
    ChevronLeft,
    ChevronRight,
    X,
    Maximize2,
    Ruler,
    ShieldCheck,
    TreeDeciduous,
} from 'lucide-vue-next'

import HuskyHeader from '@/Components/HuskyHeader.vue'
import HuskyFooter from '@/Components/HuskyFooter.vue'

import '../../css/husky-landing.css'

interface GalleryImage {
    id: number
    url: string
    alt: string
    is_primary: boolean
}

interface ProductVariant {
    id: number
    name: string
    length: string | number | null
    length_unit: string | null
    diameter: string | number | null
    diameter_unit: string | null
    width: string | number | null
    width_unit: string | null
    thickness: string | number | null
    thickness_unit: string | null
    specifications: Record<string, unknown> | unknown[] | null
}

interface Product {
    id: number
    name: string
    slug: string
    short_description: string | null
    description: string | null
    material: string | null
    treatment: string | null
    retention: string | number | null
    retention_unit: string | null
    gallery: GalleryImage[]
    main_image: string | null
    variants: ProductVariant[]
}

const props = defineProps<{
    product: Product
}>()

// -----------------------------------------------------------------------------
// GALERÍA
// -----------------------------------------------------------------------------

const selectedImage = ref<GalleryImage | null>(
    props.product.gallery.find(image => image.is_primary) ??
    props.product.gallery[0] ??
    null,
)

const showGallery = ref(false)

const currentImage = computed(
    () => selectedImage.value?.url || props.product.main_image,
)

const galleryIndex = computed(() => {
    if (!selectedImage.value) return 0

    const index = props.product.gallery.findIndex(
        image => image.id === selectedImage.value?.id,
    )

    return index >= 0 ? index : 0
})

const galleryCount = computed(() => props.product.gallery.length)

function selectImage(image: GalleryImage) {
    selectedImage.value = image
}

function openGallery(image?: GalleryImage) {
    if (!props.product.gallery.length) return

    if (image) {
        selectedImage.value = image
    }

    showGallery.value = true
}

function closeGallery() {
    showGallery.value = false
}

function changeImage(direction: number) {
    const images = props.product.gallery

    if (!images.length) return

    const nextIndex =
        (galleryIndex.value + direction + images.length) % images.length

    selectedImage.value = images[nextIndex]
}

function handleGalleryKeydown(event: KeyboardEvent) {
    if (!showGallery.value) return

    if (event.key === 'Escape') {
        closeGallery()
    }

    if (event.key === 'ArrowRight') {
        changeImage(1)
    }

    if (event.key === 'ArrowLeft') {
        changeImage(-1)
    }
}

watch(
    () => props.product.gallery,
    (images) => {
        selectedImage.value =
            images.find(image => image.is_primary) ??
            images[0] ??
            null

        if (!images.length) {
            showGallery.value = false
        }
    },
)

watch(showGallery, (isOpen) => {
    document.body.style.overflow = isOpen ? 'hidden' : ''
})

onMounted(() => {
    window.addEventListener('keydown', handleGalleryKeydown)
})

onUnmounted(() => {
    window.removeEventListener('keydown', handleGalleryKeydown)
    document.body.style.overflow = ''
})

// -----------------------------------------------------------------------------
// VARIANTES
// -----------------------------------------------------------------------------

const selectedVariant = ref<ProductVariant | null>(
    props.product.variants[0] ?? null,
)

watch(
    () => props.product.variants,
    (variants) => {
        selectedVariant.value = variants[0] ?? null
    },
)

const variantDimensions = computed(() => {
    if (!selectedVariant.value) return []

    const variant = selectedVariant.value

    return [
        {
            label: 'Largo',
            value: variant.length,
            unit: variant.length_unit,
        },
        {
            label: 'Diámetro',
            value: variant.diameter,
            unit: variant.diameter_unit,
        },
        {
            label: 'Ancho',
            value: variant.width,
            unit: variant.width_unit,
        },
        {
            label: 'Espesor',
            value: variant.thickness,
            unit: variant.thickness_unit,
        },
    ].filter(item => item.value !== null && item.value !== '')
})

function formatSpecification(value: unknown): string {
    if (value === null || value === undefined) return '—'

    if (typeof value === 'boolean') {
        return value ? 'Sí' : 'No'
    }

    if (typeof value === 'object') {
        return JSON.stringify(value)
    }

    return String(value)
}

function formatLabel(value: string): string {
    return value
        .replace(/_/g, ' ')
        .replace(/\b\w/g, letter => letter.toUpperCase())
}

const specifications = computed(() => {
    const data = selectedVariant.value?.specifications

    if (!data) return []

    if (Array.isArray(data)) {
        return data.map((item, index) => {
            if (
                typeof item === 'object' &&
                item !== null &&
                !Array.isArray(item)
            ) {
                const entry = item as Record<string, unknown>

                return {
                    label: String(
                        entry.label ??
                        entry.name ??
                        `Especificación ${index + 1}`,
                    ),
                    value: formatSpecification(
                        entry.value ?? entry.description ?? '',
                    ),
                }
            }

            return {
                label: `Especificación ${index + 1}`,
                value: formatSpecification(item),
            }
        })
    }

    return Object.entries(data).map(([key, value]) => ({
        label: formatLabel(key),
        value: formatSpecification(value),
    }))
})

// -----------------------------------------------------------------------------
// SEO
// -----------------------------------------------------------------------------

const canonicalUrl = computed(
    () => `https://industriashusky.com.mx/productos/${props.product.slug}`,
)

const seoTitle = computed(
    () => `${props.product.name} | Industrias Husky`,
)

const seoDescription = computed(
    () => props.product.short_description ||
        `Conoce las características y especificaciones de ${props.product.name} en Industrias Husky.`,
)

const productSchema = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'Product',
    name: props.product.name,
    description: seoDescription.value,
    ...(props.product.gallery.length
        ? { image: props.product.gallery.map(image => image.url) }
        : props.product.main_image
            ? { image: [props.product.main_image] }
            : {}),
    material: props.product.material || undefined,
    url: canonicalUrl.value,
    brand: {
        '@type': 'Brand',
        name: 'Industrias Husky',
    },
}))

const productSchemaJson = computed(
    () => JSON.stringify(productSchema.value),
)

// -----------------------------------------------------------------------------
// COTIZACIÓN
// -----------------------------------------------------------------------------

const quotationUrl = computed(
    () => `/?product=${props.product.id}#contacto`,
)
</script>

<template>
    <Head>
        <title>{{ seoTitle }}</title>

        <meta
            head-key="description"
            name="description"
            :content="seoDescription"
        />

        <meta
            head-key="robots"
            name="robots"
            content="index, follow"
        />

        <link
            head-key="canonical"
            rel="canonical"
            :href="canonicalUrl"
        />

        <meta
            head-key="theme-color"
            name="theme-color"
            content="#f5f3ec"
        />

        <meta
            head-key="og:type"
            property="og:type"
            content="product"
        />

        <meta
            head-key="og:title"
            property="og:title"
            :content="seoTitle"
        />

        <meta
            head-key="og:description"
            property="og:description"
            :content="seoDescription"
        />

        <meta
            head-key="og:url"
            property="og:url"
            :content="canonicalUrl"
        />

        <meta
            v-if="currentImage"
            head-key="og:image"
            property="og:image"
            :content="currentImage"
        />

        <meta
            head-key="twitter:card"
            name="twitter:card"
            content="summary_large_image"
        />

        <meta
            head-key="twitter:title"
            name="twitter:title"
            :content="seoTitle"
        />

        <meta
            head-key="twitter:description"
            name="twitter:description"
            :content="seoDescription"
        />

        <component
            :is="'script'"
            type="application/ld+json"
            head-key="product-schema"
            v-html="productSchemaJson"
        />
    </Head>

    <div class="husky-site product-detail">
        <HuskyHeader />

        <main>
            <!-- MIGAS DE PAN -->
            <section class="product-breadcrumb">
                <div class="husky-container">
                    <Link href="/" class="product-back-link">
                        <ArrowLeft :size="16" />
                        Inicio
                    </Link>

                    <span>/</span>

                    <Link href="/productos">
                        Productos
                    </Link>

                    <span>/</span>

                    <span class="product-breadcrumb-current">
                        {{ product.name }}
                    </span>
                </div>
            </section>

            <!-- INFORMACIÓN PRINCIPAL -->
            <section class="product-detail-section">
                <div class="husky-container">
                    <div class="product-detail-grid">
                        <!-- GALERÍA -->
                        <div class="product-gallery">
                            <div
                                class="product-main-image"
                                :class="{ 'has-image': currentImage }"
                                role="button"
                                tabindex="0"
                                aria-label="Abrir galería de imágenes"
                                @click="openGallery()"
                                @keydown.enter.prevent="openGallery()"
                                @keydown.space.prevent="openGallery()"
                            >
                                <img
                                    v-if="currentImage"
                                    :src="currentImage"
                                    :alt="selectedImage?.alt || product.name"
                                />

                                <div
                                    v-else
                                    class="product-image-placeholder"
                                >
                                    <TreeDeciduous :size="64" />
                                    <span>Imagen próximamente</span>
                                </div>

                                <div
                                    v-if="currentImage && galleryCount"
                                    class="product-image-expand"
                                >
                                    <Maximize2 :size="16" />
                                    Ver imágenes
                                </div>

                                <template v-if="galleryCount > 1">
                                    <button
                                        type="button"
                                        class="product-gallery-arrow product-gallery-prev"
                                        aria-label="Imagen anterior"
                                        @click.stop="changeImage(-1)"
                                    >
                                        <ChevronLeft :size="22" />
                                    </button>

                                    <button
                                        type="button"
                                        class="product-gallery-arrow product-gallery-next"
                                        aria-label="Imagen siguiente"
                                        @click.stop="changeImage(1)"
                                    >
                                        <ChevronRight :size="22" />
                                    </button>
                                </template>
                            </div>

                            <div
                                v-if="galleryCount"
                                class="product-gallery-caption"
                            >
                                <span>
                                    {{ galleryIndex + 1 }} / {{ galleryCount }}
                                </span>

                                <span>Haz clic en la imagen para ampliar</span>
                            </div>

                            <div
                                v-if="galleryCount > 1"
                                class="product-thumbnails"
                            >
                                <button
                                    v-for="image in product.gallery"
                                    :key="image.id"
                                    type="button"
                                    class="product-thumbnail"
                                    :class="{
                                        'is-selected': selectedImage?.id === image.id,
                                    }"
                                    :aria-label="`Abrir imagen: ${image.alt || product.name}`"
                                    :aria-pressed="selectedImage?.id === image.id"
                                    @click="openGallery(image)"
                                >
                                    <img
                                        :src="image.url"
                                        :alt="image.alt || product.name"
                                        loading="lazy"
                                    />
                                </button>
                            </div>
                        </div>

                        <!-- DATOS DEL PRODUCTO -->
                        <div class="product-information">
                            <span class="product-eyebrow">
                                INDUSTRIAS HUSKY
                            </span>

                            <h1>{{ product.name }}</h1>

                            <p
                                v-if="product.short_description"
                                class="product-short-description"
                            >
                                {{ product.short_description }}
                            </p>

                            <div class="product-information-divider"></div>

                            <div
                                v-if="product.description"
                                class="product-description"
                            >
                                <h2>Descripción</h2>
                                <p>{{ product.description }}</p>
                            </div>

                            <div
                                v-if="
                                    product.material ||
                                    product.treatment ||
                                    (
                                        product.retention !== null &&
                                        product.retention !== ''
                                    )
                                "
                                class="product-attributes"
                            >
                                <div
                                    v-if="product.material"
                                    class="product-attribute"
                                >
                                    <span class="product-attribute-icon">
                                        <TreeDeciduous :size="19" />
                                    </span>

                                    <div>
                                        <span>Material</span>
                                        <strong>{{ product.material }}</strong>
                                    </div>
                                </div>

                                <div
                                    v-if="product.treatment"
                                    class="product-attribute"
                                >
                                    <span class="product-attribute-icon">
                                        <ShieldCheck :size="19" />
                                    </span>

                                    <div>
                                        <span>Tratamiento</span>
                                        <strong>{{ product.treatment }}</strong>
                                    </div>
                                </div>

                                <div
                                    v-if="
                                        product.retention !== null &&
                                        product.retention !== ''
                                    "
                                    class="product-attribute"
                                >
                                    <span class="product-attribute-icon">
                                        <Check :size="19" />
                                    </span>

                                    <div>
                                        <span>Retención</span>
                                        <strong>
                                            {{ product.retention }}
                                            {{ product.retention_unit || '' }}
                                        </strong>
                                    </div>
                                </div>
                            </div>

                            <a
                                :href="quotationUrl"
                                class="product-quote-button"
                            >
                                Solicitar cotización
                                <ArrowUpRight :size="19" />
                            </a>

                            <p class="product-quote-note">
                                Cuéntanos sobre tu proyecto y te ayudaremos
                                a encontrar la solución adecuada.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- VARIANTES -->
            <section
                v-if="product.variants.length"
                class="product-variants-section"
            >
                <div class="husky-container">
                    <div class="product-section-heading">
                        <span class="product-eyebrow">
                            OPCIONES DISPONIBLES
                        </span>

                        <h2>Variantes y dimensiones</h2>

                        <p>
                            Selecciona una presentación para consultar sus
                            dimensiones y especificaciones.
                        </p>
                    </div>

                    <div class="product-variants-layout">
                        <div class="product-variant-list">
                            <button
                                v-for="variant in product.variants"
                                :key="variant.id"
                                type="button"
                                class="product-variant-option"
                                :class="{
                                    'is-active': selectedVariant?.id === variant.id,
                                }"
                                :aria-pressed="selectedVariant?.id === variant.id"
                                @click="selectedVariant = variant"
                            >
                                <span>
                                    <Ruler :size="18" />
                                    {{ variant.name }}
                                </span>

                                <ArrowRight :size="17" />
                            </button>
                        </div>

                        <div
                            v-if="selectedVariant"
                            class="product-variant-details"
                        >
                            <div class="product-variant-heading">
                                <div>
                                    <span>VARIANTE SELECCIONADA</span>
                                    <h3>{{ selectedVariant.name }}</h3>
                                </div>
                            </div>

                            <div
                                v-if="variantDimensions.length"
                                class="product-dimensions"
                            >
                                <div
                                    v-for="dimension in variantDimensions"
                                    :key="dimension.label"
                                    class="product-dimension"
                                >
                                    <span>{{ dimension.label }}</span>

                                    <strong>
                                        {{ dimension.value }}
                                        {{ dimension.unit || '' }}
                                    </strong>
                                </div>
                            </div>

                            <div
                                v-if="specifications.length"
                                class="product-specifications"
                            >
                                <h4>Especificaciones adicionales</h4>

                                <div
                                    v-for="specification in specifications"
                                    :key="specification.label"
                                    class="product-specification-row"
                                >
                                    <span>{{ specification.label }}</span>
                                    <strong>{{ specification.value }}</strong>
                                </div>
                            </div>

                            <a
                                :href="quotationUrl"
                                class="product-variant-quote"
                            >
                                Cotizar este producto
                                <ArrowUpRight :size="17" />
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- LLAMADA A LA ACCIÓN -->
            <section class="product-contact-section">
                <div class="husky-container">
                    <div class="product-contact-content">
                        <div>
                            <span class="product-eyebrow">
                                HABLEMOS DE TU PROYECTO
                            </span>

                            <h2>
                                ¿Necesitas una solución en madera?
                            </h2>

                            <p>
                                Estamos listos para ayudarte a encontrar
                                el producto adecuado para tus necesidades.
                            </p>
                        </div>

                        <a
                            :href="quotationUrl"
                            class="product-contact-button"
                        >
                            Solicitar cotización
                            <ArrowUpRight :size="19" />
                        </a>
                    </div>
                </div>
            </section>

            <section class="product-back-section">
                <div class="husky-container">
                    <Link href="/productos" class="product-back-link">
                        <ArrowLeft :size="17" />
                        Volver a todos los productos
                    </Link>
                </div>
            </section>
        </main>

        <!-- VISOR DE GALERÍA -->
        <Transition name="gallery-fade">
            <div
                v-if="showGallery && selectedImage"
                class="product-lightbox"
                role="dialog"
                aria-modal="true"
                aria-label="Galería de imágenes del producto"
                @click.self="closeGallery"
            >
                <button
                    type="button"
                    class="product-lightbox-close"
                    aria-label="Cerrar galería"
                    @click="closeGallery"
                >
                    <X :size="24" />
                </button>

                <div class="product-lightbox-content">
                    <div class="product-lightbox-top">
                        <div>
                            <span>INDUSTRIAS HUSKY</span>
                            <strong>{{ product.name }}</strong>
                        </div>

                        <span class="product-lightbox-counter">
                            {{ galleryIndex + 1 }} / {{ galleryCount }}
                        </span>
                    </div>

                    <div class="product-lightbox-stage">
                        <button
                            type="button"
                            class="product-lightbox-arrow"
                            :class="{ 'is-hidden': galleryCount <= 1 }"
                            :disabled="galleryCount <= 1"
                            aria-label="Imagen anterior"
                            @click="changeImage(-1)"
                        >
                            <ChevronLeft :size="28" />
                        </button>

                        <img
                            :key="selectedImage.id"
                            :src="selectedImage.url"
                            :alt="selectedImage.alt || product.name"
                        />

                        <button
                            type="button"
                            class="product-lightbox-arrow"
                            :class="{ 'is-hidden': galleryCount <= 1 }"
                            :disabled="galleryCount <= 1"
                            aria-label="Imagen siguiente"
                            @click="changeImage(1)"
                        >
                            <ChevronRight :size="28" />
                        </button>
                    </div>

                    <p
                        v-if="selectedImage.alt"
                        class="product-lightbox-caption"
                    >
                        {{ selectedImage.alt }}
                    </p>

                    <div
                        v-if="galleryCount > 1"
                        class="product-lightbox-thumbnails"
                    >
                        <button
                            v-for="image in product.gallery"
                            :key="image.id"
                            type="button"
                            :class="{
                                'is-selected': selectedImage.id === image.id,
                            }"
                            :aria-label="`Ver imagen ${product.gallery.indexOf(image) + 1}`"
                            :aria-pressed="selectedImage.id === image.id"
                            @click="selectImage(image)"
                        >
                            <img
                                :src="image.url"
                                :alt="image.alt || product.name"
                            />
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <HuskyFooter />
    </div>
</template>

<style scoped>
.product-detail {
    background: #fffefa;
    color: #25352d;
}

/* BREADCRUMB */

.product-breadcrumb {
    padding: 22px 0;
    border-bottom: 1px solid #e8ebe4;
    background: #fffefa;
}

.product-breadcrumb .husky-container {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    color: #879087;
    font-size: 13px;
}

.product-breadcrumb a,
.product-back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #536b59;
    text-decoration: none;
    transition: color .2s ease;
}

.product-breadcrumb a:hover,
.product-back-link:hover {
    color: #243b30;
}

.product-breadcrumb-current {
    color: #243b30;
}

/* PRODUCTO */

.product-detail-section {
    padding: 76px 0 92px;
}

.product-detail-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.1fr) minmax(0, .9fr);
    align-items: start;
    gap: clamp(36px, 6vw, 88px);
}

/* GALERÍA PRINCIPAL */

.product-gallery {
    min-width: 0;
}

.product-main-image {
    position: relative;
    width: 100%;
    aspect-ratio: 1 / .86;
    overflow: hidden;
    border: 0;
    border-radius: 20px;
    background: #f0f2eb;
    cursor: zoom-in;
    outline-offset: 4px;
}

.product-main-image img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
    transition: transform .65s cubic-bezier(.2, .7, .2, 1);
}

.product-main-image.has-image:hover img {
    transform: scale(1.035);
}

.product-image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    color: #84917f;
    cursor: default;
}

.product-image-expand {
    position: absolute;
    right: 18px;
    bottom: 18px;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    border: 1px solid rgba(255, 255, 255, .55);
    border-radius: 30px;
    color: #243b30;
    background: rgba(255, 254, 250, .92);
    font-size: 12px;
    font-weight: 600;
    pointer-events: none;
    backdrop-filter: blur(8px);
}

.product-gallery-arrow {
    position: absolute;
    top: 50%;
    z-index: 2;
    width: 44px;
    height: 44px;
    display: grid;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, .65);
    border-radius: 50%;
    color: #243b30;
    background: rgba(255, 254, 250, .94);
    cursor: pointer;
    transform: translateY(-50%);
    transition: background .2s ease, transform .2s ease;
}

.product-gallery-arrow:hover {
    background: #fff;
    transform: translateY(-50%) scale(1.06);
}

.product-gallery-prev {
    left: 16px;
}

.product-gallery-next {
    right: 16px;
}

.product-gallery-caption {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    margin-top: 12px;
    color: #92998f;
    font-size: 11px;
}

.product-thumbnails {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(76px, 1fr));
    gap: 12px;
    margin-top: 16px;
}

.product-thumbnail {
    padding: 0;
    aspect-ratio: 1;
    overflow: hidden;
    border: 2px solid transparent;
    border-radius: 12px;
    background: #f0f2eb;
    cursor: pointer;
    transition: border-color .2s ease, opacity .2s ease, transform .2s ease;
}

.product-thumbnail img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-thumbnail:hover {
    opacity: .85;
    transform: translateY(-2px);
}

.product-thumbnail.is-selected {
    border-color: #737d43;
}

/* INFORMACIÓN */

.product-information {
    padding-top: 10px;
}

.product-eyebrow {
    display: inline-block;
    margin-bottom: 16px;
    color: #737d43;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .16em;
}

.product-information h1 {
    margin: 0;
    color: #243b30;
    font-family: Manrope, sans-serif;
    font-size: clamp(32px, 4vw, 48px);
    font-weight: 600;
    line-height: 1.12;
    letter-spacing: -.04em;
}

.product-short-description {
    margin: 22px 0 0;
    color: #667369;
    font-size: 17px;
    line-height: 1.8;
}

.product-information-divider {
    height: 1px;
    margin: 30px 0;
    background: #e6e9e1;
}

.product-description h2 {
    margin: 0 0 12px;
    color: #243b30;
    font-size: 19px;
    font-weight: 600;
}

.product-description p {
    margin: 0;
    color: #667369;
    font-size: 15px;
    line-height: 1.9;
    white-space: pre-line;
}

.product-attributes {
    display: grid;
    gap: 16px;
    margin-top: 30px;
}

.product-attribute {
    display: flex;
    align-items: center;
    gap: 14px;
}

.product-attribute-icon {
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    display: grid;
    place-items: center;
    border-radius: 12px;
    color: #536b43;
    background: #edf1e8;
}

.product-attribute div {
    display: grid;
    gap: 4px;
}

.product-attribute div span {
    color: #899187;
    font-size: 12px;
}

.product-attribute strong {
    color: #304638;
    font-size: 14px;
    font-weight: 600;
}

/* BOTONES */

.product-quote-button,
.product-contact-button,
.product-variant-quote {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 14px;
    border-radius: 10px;
    color: #fffefa;
    background: #243b30;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: background .2s ease, transform .2s ease;
}

.product-quote-button {
    width: 100%;
    min-height: 56px;
    margin-top: 34px;
}

.product-quote-button:hover,
.product-variant-quote:hover {
    background: #3b5946;
    transform: translateY(-2px);
}

.product-quote-note {
    margin: 13px 0 0;
    color: #92988f;
    font-size: 12px;
    line-height: 1.7;
    text-align: center;
}

/* VARIANTES */

.product-variants-section {
    padding: 88px 0;
    background: #f5f3ec;
}

.product-section-heading {
    max-width: 620px;
    margin-bottom: 42px;
}

.product-section-heading h2 {
    margin: 0;
    color: #243b30;
    font-family: Manrope, sans-serif;
    font-size: clamp(28px, 4vw, 40px);
    font-weight: 600;
    letter-spacing: -.035em;
}

.product-section-heading p {
    margin: 14px 0 0;
    color: #758076;
    font-size: 15px;
    line-height: 1.8;
}

.product-variants-layout {
    display: grid;
    grid-template-columns: minmax(220px, .65fr) minmax(0, 1.35fr);
    gap: 28px;
    align-items: start;
}

.product-variant-list {
    display: grid;
    gap: 10px;
}

.product-variant-option {
    min-height: 58px;
    padding: 0 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    border: 1px solid #e0e5db;
    border-radius: 12px;
    color: #526252;
    background: #fffefa;
    cursor: pointer;
    text-align: left;
    transition: border-color .2s ease, background .2s ease;
}

.product-variant-option span {
    display: flex;
    align-items: center;
    gap: 12px;
}

.product-variant-option.is-active {
    border-color: #737d43;
    color: #243b30;
    background: #edf1e8;
}

.product-variant-details {
    padding: 30px;
    border: 1px solid #e3e7de;
    border-radius: 16px;
    background: #fffefa;
}

.product-variant-heading span {
    color: #8a9387;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .14em;
}

.product-variant-heading h3 {
    margin: 8px 0 0;
    color: #243b30;
    font-size: 23px;
    font-weight: 600;
}

.product-dimensions {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin-top: 26px;
}

.product-dimension {
    padding: 18px;
    display: grid;
    gap: 8px;
    border-radius: 10px;
    background: #f5f6f0;
}

.product-dimension span {
    color: #828c80;
    font-size: 12px;
}

.product-dimension strong {
    color: #304638;
    font-size: 18px;
    font-weight: 600;
}

.product-specifications {
    margin-top: 30px;
}

.product-specifications h4 {
    margin: 0 0 14px;
    color: #304638;
    font-size: 15px;
}

.product-specification-row {
    padding: 13px 0;
    display: flex;
    justify-content: space-between;
    gap: 20px;
    border-bottom: 1px solid #e9ece5;
    font-size: 13px;
}

.product-specification-row span {
    color: #899187;
    text-transform: capitalize;
}

.product-specification-row strong {
    color: #304638;
    text-align: right;
    font-weight: 600;
    overflow-wrap: anywhere;
}

.product-variant-quote {
    min-height: 48px;
    margin-top: 26px;
    padding: 0 20px;
}

/* LLAMADA A LA ACCIÓN */

.product-contact-section {
    padding: 72px 0;
    background: #243b30;
}

.product-contact-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 36px;
}

.product-contact-content .product-eyebrow {
    color: #c4d0b1;
}

.product-contact-content h2 {
    max-width: 570px;
    margin: 0;
    color: #fffefa;
    font-family: Manrope, sans-serif;
    font-size: clamp(27px, 4vw, 39px);
    font-weight: 500;
    line-height: 1.2;
}

.product-contact-content p {
    max-width: 540px;
    margin: 14px 0 0;
    color: #d0d9ce;
    font-size: 14px;
    line-height: 1.8;
}

.product-contact-button {
    min-height: 54px;
    flex: 0 0 auto;
    padding: 0 24px;
    color: #243b30;
    background: #fffefa;
}

.product-contact-button:hover {
    color: #243b30;
    background: #dce2d4;
    transform: translateY(-2px);
}

.product-back-section {
    padding: 28px 0;
    background: #fffefa;
}

/* VISOR DE GALERÍA */

.product-lightbox {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100vw;
    height: 100vh;
    height: 100dvh;
    padding: 18px;
    overflow: hidden;
    background: rgba(10, 18, 13, .98);
    backdrop-filter: blur(14px);
}

.product-lightbox-close {
    position: absolute;
    top: 20px;
    right: 24px;
    z-index: 10;
    width: 48px;
    height: 48px;
    display: grid;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, .22);
    border-radius: 50%;
    color: #fffefa;
    background: rgba(255, 255, 255, .09);
    cursor: pointer;
    transition: background .2s ease, transform .2s ease;
}

.product-lightbox-close:hover {
    background: rgba(255, 255, 255, .2);
    transform: rotate(90deg);
}

.product-lightbox-content {
    width: 100%;
    max-width: 1800px;
    height: 100%;
    min-height: 0;
    display: flex;
    flex-direction: column;
    margin: 0 auto;
}

.product-lightbox-top {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin: 8px 76px 12px;
    color: #fffefa;
}

.product-lightbox-top div {
    display: grid;
    gap: 5px;
}

.product-lightbox-top div span {
    color: #aebba9;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .18em;
}

.product-lightbox-top div strong {
    color: #fffefa;
    font-size: 17px;
    font-weight: 500;
}

.product-lightbox-counter {
    color: #dce2d4;
    font-size: 14px;
    font-variant-numeric: tabular-nums;
}

.product-lightbox-stage {
    position: relative;
    flex: 1 1 auto;
    min-width: 0;
    min-height: 0;
    width: 100%;
    display: grid;
    grid-template-columns: 56px minmax(0, 1fr) 56px;
    grid-template-rows: minmax(0, 1fr);
    align-items: center;
    gap: 12px;
}

.product-lightbox-stage img {
    grid-column: 2;
    grid-row: 1;
    width: 100%;
    height: 100%;
    min-width: 0;
    min-height: 0;
    max-height: calc(100dvh - 170px);
    display: block;
    object-fit: contain;
    animation: gallery-image-in .25s ease;
}

.product-lightbox-arrow {
    position: relative;
    z-index: 2;
    width: 48px;
    height: 48px;
    display: grid;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, .22);
    border-radius: 50%;
    color: #fffefa;
    background: rgba(255, 255, 255, .09);
    cursor: pointer;
    transition: background .2s ease, transform .2s ease;
}

.product-lightbox-arrow:hover:not(:disabled) {
    background: rgba(255, 255, 255, .22);
    transform: scale(1.08);
}

.product-lightbox-arrow:disabled,
.product-lightbox-arrow.is-hidden {
    visibility: hidden;
    pointer-events: none;
}

.product-lightbox-caption {
    flex: 0 0 auto;
    margin: 8px 0 0;
    color: #c4cec1;
    font-size: 12px;
    text-align: center;
}

.product-lightbox-thumbnails {
    flex: 0 0 auto;
    display: flex;
    justify-content: center;
    gap: 10px;
    max-width: 100%;
    margin: 12px auto 0;
    padding: 4px 2px 8px;
    overflow-x: auto;
}

.product-lightbox-thumbnails button {
    width: 58px;
    height: 58px;
    flex: 0 0 58px;
    padding: 0;
    overflow: hidden;
    border: 2px solid transparent;
    border-radius: 9px;
    background: #34483a;
    opacity: .55;
    cursor: pointer;
    transition: opacity .2s ease, border-color .2s ease;
}

.product-lightbox-thumbnails button:hover {
    opacity: .85;
}

.product-lightbox-thumbnails button.is-selected {
    border-color: #dce2d4;
    opacity: 1;
}

.product-lightbox-thumbnails img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
}

/* ANIMACIONES */

.gallery-fade-enter-active,
.gallery-fade-leave-active {
    transition: opacity .22s ease;
}

.gallery-fade-enter-from,
.gallery-fade-leave-to {
    opacity: 0;
}

@keyframes gallery-image-in {
    from {
        opacity: .5;
        transform: scale(.985);
    }

    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* RESPONSIVE */

@media (max-width: 900px) {
    .product-detail-grid {
        grid-template-columns: 1fr;
    }

    .product-gallery {
        max-width: 700px;
    }

    .product-variants-layout {
        grid-template-columns: 1fr;
    }

    .product-contact-content {
        align-items: flex-start;
        flex-direction: column;
    }

    .product-lightbox {
        padding: 12px;
    }

    .product-lightbox-content {
        max-width: 100%;
    }

    .product-lightbox-stage {
        grid-template-columns: 44px minmax(0, 1fr) 44px;
        gap: 6px;
    }

    .product-lightbox-stage img {
        max-height: calc(100dvh - 160px);
    }

    .product-lightbox-top {
        margin-left: 54px;
        margin-right: 54px;
    }
}

@media (max-width: 600px) {
    .product-breadcrumb {
        padding: 17px 0;
    }

    .product-detail-section {
        padding: 38px 0 56px;
    }

    .product-main-image {
        aspect-ratio: 1 / .95;
        border-radius: 14px;
    }

    .product-gallery-arrow {
        width: 38px;
        height: 38px;
    }

    .product-gallery-prev {
        left: 10px;
    }

    .product-gallery-next {
        right: 10px;
    }

    .product-thumbnails {
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
    }

    .product-information h1 {
        font-size: 34px;
    }

    .product-variants-section {
        padding: 58px 0;
    }

    .product-variant-details {
        padding: 20px;
    }

    .product-dimensions {
        gap: 8px;
    }

    .product-dimension {
        padding: 14px;
    }

    .product-specification-row {
        flex-direction: column;
        gap: 5px;
    }

    .product-specification-row strong {
        text-align: left;
    }

    .product-contact-section {
        padding: 54px 0;
    }

    .product-contact-button {
        width: 100%;
    }

    .product-lightbox {
        padding: 8px;
    }

    .product-lightbox-close {
        top: 10px;
        right: 10px;
        width: 40px;
        height: 40px;
    }

    .product-lightbox-top {
        margin: 48px 8px 8px;
    }

    .product-lightbox-top div strong {
        max-width: 210px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .product-lightbox-stage {
        grid-template-columns: 32px minmax(0, 1fr) 32px;
        gap: 2px;
    }

    .product-lightbox-stage img {
        max-height: calc(100dvh - 150px);
    }

    .product-lightbox-arrow {
        width: 32px;
        height: 32px;
    }

    .product-lightbox-thumbnails {
        justify-content: flex-start;
        margin-top: 8px;
    }

    .product-lightbox-thumbnails button {
        width: 48px;
        height: 48px;
        flex-basis: 48px;
    }
}

/* ACCESIBILIDAD */

@media (prefers-reduced-motion: reduce) {
    .product-detail *,
    .product-lightbox * {
        scroll-behavior: auto !important;
        animation-duration: .01ms !important;
        transition-duration: .01ms !important;
    }
}
</style>