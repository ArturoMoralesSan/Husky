<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import {
    ArrowLeft,
    ArrowUpRight,
    ChevronLeft,
    ChevronRight,
    X,
    Maximize2,
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

interface Project {
    id: number
    name: string
    slug: string
    description: string | null
    gallery: GalleryImage[]
    main_image: string | null
}

const props = defineProps<{
    project: Project
}>()

// -----------------------------------------------------------------------------
// GALERÍA
// -----------------------------------------------------------------------------

const selectedImage = ref<GalleryImage | null>(
    props.project.gallery.find(image => image.is_primary) ??
    props.project.gallery[0] ??
    null,
)

const showGallery = ref(false)

const currentImage = computed(
    () => selectedImage.value?.url || props.project.main_image,
)

const galleryIndex = computed(() => {
    if (!selectedImage.value) return 0

    const index = props.project.gallery.findIndex(
        image => image.id === selectedImage.value?.id,
    )

    return index >= 0 ? index : 0
})

const galleryCount = computed(() => props.project.gallery.length)

function selectImage(image: GalleryImage) {
    selectedImage.value = image
}

function openGallery(image?: GalleryImage) {
    if (!props.project.gallery.length) return

    if (image) {
        selectedImage.value = image
    }

    showGallery.value = true
}

function closeGallery() {
    showGallery.value = false
}

function changeImage(direction: number) {
    const images = props.project.gallery

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
    () => props.project.gallery,
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
// SEO
// -----------------------------------------------------------------------------

const canonicalUrl = computed(
    () => `https://industriashusky.com.mx/proyectos/${props.project.slug}`,
)

const seoTitle = computed(
    () => `${props.project.name} | Industrias Husky`,
)

const seoDescription = computed(
    () => props.project.description ||
        `Conoce el proyecto ${props.project.name} realizado por Industrias Husky.`,
)

const projectSchema = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'CreativeWork',
    name: props.project.name,
    description: seoDescription.value,
    ...(props.project.gallery.length
        ? { image: props.project.gallery.map(image => image.url) }
        : props.project.main_image
            ? { image: [props.project.main_image] }
            : {}),
    url: canonicalUrl.value,
    creator: {
        '@type': 'Organization',
        name: 'Industrias Husky',
    },
}))

const projectSchemaJson = computed(
    () => JSON.stringify(projectSchema.value),
)

// -----------------------------------------------------------------------------
// CONTACTO
// -----------------------------------------------------------------------------

const contactUrl = computed(
    () => `/contacto?proyecto=${props.project.id}`,
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
            content="article"
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
            head-key="project-schema"
            v-html="projectSchemaJson"
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

                    <Link href="/proyectos">
                        Proyectos
                    </Link>

                    <span>/</span>

                    <span class="product-breadcrumb-current">
                        {{ project.name }}
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
                                    :alt="selectedImage?.alt || project.name"
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
                                    v-for="image in project.gallery"
                                    :key="image.id"
                                    type="button"
                                    class="product-thumbnail"
                                    :class="{
                                        'is-selected': selectedImage?.id === image.id,
                                    }"
                                    :aria-label="`Abrir imagen: ${image.alt || project.name}`"
                                    :aria-pressed="selectedImage?.id === image.id"
                                    @click="openGallery(image)"
                                >
                                    <img
                                        :src="image.url"
                                        :alt="image.alt || project.name"
                                        loading="lazy"
                                    />
                                </button>
                            </div>
                        </div>

                        <!-- DATOS DEL PROYECTO -->
                        <div class="product-information">
                            <span class="product-eyebrow">
                                INDUSTRIAS HUSKY
                            </span>

                            <h1>{{ project.name }}</h1>

                            <div class="product-information-divider"></div>

                            <div
                                v-if="project.description"
                                class="product-description"
                            >
                                <h2>Sobre este proyecto</h2>
                                <p>{{ project.description }}</p>
                            </div>
                        </div>
                    </div>
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
                aria-label="Galería de imágenes del proyecto"
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
                            <strong>{{ project.name }}</strong>
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
                            :alt="selectedImage.alt || project.name"
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
                            v-for="image in project.gallery"
                            :key="image.id"
                            type="button"
                            :class="{
                                'is-selected': selectedImage.id === image.id,
                            }"
                            :aria-label="`Ver imagen ${project.gallery.indexOf(image) + 1}`"
                            :aria-pressed="selectedImage.id === image.id"
                            @click="selectImage(image)"
                        >
                            <img
                                :src="image.url"
                                :alt="image.alt || project.name"
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

/* PROYECTO */

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

/* BOTONES */

.product-quote-button,
.product-contact-button {
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

.product-quote-button:hover {
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