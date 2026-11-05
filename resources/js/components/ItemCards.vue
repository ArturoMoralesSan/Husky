<script setup lang="ts">
import { computed } from 'vue'
import {
    ArrowRight,
    ArrowUpRight,
    TreeDeciduous,
} from 'lucide-vue-next'

import ImageGallery from '@/Components/ImageGallery.vue'

interface ProductVariant {
    id: number
    name: string
}

interface Product {
    id: number
    name: string
    slug?: string
    short_description?: string | null
    image_url?: string | null
    image_alt?: string | null
    variants?: ProductVariant[]
}

interface ItemCard {
    id?: number | string
    number?: string
    title?: string
    name?: string
    description?: string | null
    short_description?: string | null
    image?: string | null
    image_url?: string | null
    imageAlt?: string
    image_alt?: string | null
    href?: string
    slug?: string
    variants?: ProductVariant[]
}

const props = withDefaults(defineProps<{
    variant?: 'grid' | 'list' | 'featured'
    items?: ItemCard[]
    products?: Product[]
    eyebrow?: string
    title?: string
    headingLevel?: 1 | 2
    linkText?: string
    linkHref?: string
    emptyText?: string
    image?: string | null
    imageAlt?: string
    actionLabel?: string
    compact?: boolean
}>(), {
    variant: 'grid',
    items: () => [],
    products: () => [],
    eyebrow: '',
    title: '',
    headingLevel: 2,
    linkText: '',
    linkHref: '#contacto',
    emptyText: 'Estamos preparando esta sección.',
    image: null,
    imageAlt: '',
    actionLabel: 'Conocer más',
    compact: false,
})

const emit = defineEmits<{
    select: [item: ItemCard]
}>()

// -----------------------------------------------------------------------------
// NORMALIZAR PRODUCTOS
// -----------------------------------------------------------------------------

const productItems = computed<ItemCard[]>(() => {
    const source = props.products.length
        ? props.products
        : props.items

    return source.map((product, index) => ({
        id: product.id,
        number: product.number || String(index + 1).padStart(2, '0'),
        title: product.name || product.title || '',
        description: product.short_description ?? product.description ?? '',
        image: product.image_url ?? product.image ?? null,
        imageAlt: product.image_alt ?? product.imageAlt ?? '',
        href: product.href || (
            product.slug ? `/productos/${product.slug}` : undefined
        ),
        variants: product.variants ?? [],
    }))
})

// -----------------------------------------------------------------------------
// GALERÍA DESTACADA
// -----------------------------------------------------------------------------

const featuredGallery = computed(() => {
    const galleryItems = props.items
        .filter((item) => item.image)
        .map((item) => ({
            src: item.image!,
            alt: item.imageAlt || item.title || '',
        }))

    if (galleryItems.length) {
        return galleryItems
    }

    if (props.image) {
        return [
            {
                src: props.image,
                alt: props.imageAlt || props.title,
            },
        ]
    }

    return []
})
</script>

<template>
    <!-- PRODUCTOS -->
    <section
        v-if="props.variant === 'grid'"
        :class="[
            'husky-products husky-section',
            { 'husky-section-compact': props.compact },
        ]"
    >
        <div class="husky-container">
            <div class="husky-section-heading">
                <div>
                    <p class="husky-eyebrow">{{ props.eyebrow }}</p>

                    <component
                        :is="`h${props.headingLevel}`"
                        class="husky-section-title"
                    >
                        {{ props.title }}
                    </component>
                </div>

                <a
                    v-if="props.linkText"
                    class="husky-text-link"
                    :href="props.linkHref"
                >
                    {{ props.linkText }}
                    <ArrowRight :size="16" />
                </a>
            </div>

            <slot name="intro" />

            <div
                v-if="productItems.length"
                class="husky-product-grid"
            >
                <a
                    v-for="(item, index) in productItems"
                    :key="item.id ?? index"
                    class="husky-product-card"
                    :href="item.href || '#contacto'"
                    @click="emit('select', item)"
                >
                    <div class="husky-product-image">
                        <img
                            v-if="item.image"
                            :src="item.image"
                            :alt="item.imageAlt || item.title || ''"
                            loading="lazy"
                        />

                        <div
                            v-else
                            class="husky-product-image-empty"
                            role="img"
                            :aria-label="`Imagen no disponible para ${item.title}`"
                        >
                            <TreeDeciduous
                                :size="42"
                                stroke-width="1.2"
                            />
                        </div>

                        <span>{{ item.number }}</span>
                    </div>

                    <div class="husky-product-info">
                        <h3>{{ item.title }}</h3>

                        <p>{{ item.description }}</p>

                        <div
                            v-if="item.variants?.length"
                            class="husky-product-variants"
                        >
                            <span class="husky-variants-label">
                                Disponibles en
                            </span>

                            <div class="husky-variants-list">
                                <span
                                    v-for="productVariant in item.variants"
                                    :key="productVariant.id"
                                    class="husky-variant-badge"
                                >
                                    {{ productVariant.name }}
                                </span>
                            </div>
                        </div>

                        <ArrowUpRight
                            class="husky-card-arrow"
                            :size="19"
                        />
                    </div>
                </a>
            </div>

            <div v-else class="husky-products-empty">
                <TreeDeciduous
                    :size="34"
                    stroke-width="1.3"
                />
                <p>{{ props.emptyText }}</p>
            </div>
        </div>
    </section>

    <!-- SERVICIOS -->
    <section
        v-else-if="props.variant === 'list'"
        class="husky-services husky-section"
    >
        <div class="husky-container husky-services-grid">
            <div class="husky-services-intro">
                <span class="husky-services-kicker">
                    <span class="husky-services-kicker-dot"></span>
                    {{ props.eyebrow }}
                </span>

                <component
                    :is="`h${props.headingLevel}`"
                    class="husky-section-title"
                >
                    {{ props.title }}
                </component>

                <slot name="intro" />
                <slot name="caption" />
            </div>

            <div class="husky-service-list">
                <a
                    v-for="(item, index) in props.items"
                    :key="item.id ?? index"
                    class="husky-service-item"
                    :href="item.href || props.linkHref"
                    :aria-label="`Conocer más sobre ${item.title || item.name}`"
                >
                    <span class="husky-service-number">
                        {{ item.number || String(index + 1).padStart(2, '0') }}
                    </span>

                    <div class="husky-service-content">
                        <h3>{{ item.title || item.name }}</h3>
                        <p>{{ item.description }}</p>
                    </div>

                    <span class="husky-service-arrow" aria-hidden="true">
                        <ArrowUpRight :size="19" />
                    </span>
                </a>

                <div
                    v-if="!props.items.length"
                    class="husky-service-empty"
                >
                    <p>{{ props.emptyText }}</p>
                </div>

                <a
                    v-if="props.linkText"
                    class="husky-services-more"
                    :href="props.linkHref"
                >
                    <span>
                        <slot name="footer">¿Buscas otro servicio?</slot>
                    </span>

                    <strong>
                        {{ props.linkText }}
                        <ArrowRight :size="15" />
                    </strong>
                </a>
            </div>
        </div>
    </section>

    <!-- PROYECTO DESTACADO -->
    <section
        v-else-if="props.variant === 'featured'"
        class="husky-project"
    >
        <div class="husky-project-copy husky-project-copy-responsive">
            <p class="husky-eyebrow">{{ props.eyebrow }}</p>

            <component
                :is="`h${props.headingLevel}`"
                class="husky-section-title"
            >
                {{ props.title }}
            </component>

            <p>{{ props.items[0]?.description }}</p>

            <a
                class="husky-button husky-button-solid"
                :href="props.linkHref"
            >
                {{ props.actionLabel }}
                <ArrowRight :size="17" />
            </a>
        </div>

        <div class="husky-project-image">
            <ImageGallery
                v-if="featuredGallery.length"
                :items="featuredGallery"
                :autoplay="true"
                :interval="4000"
            />

            <div
                v-else
                class="husky-product-image-empty"
                role="img"
                :aria-label="`Imagen no disponible para ${props.title}`"
            >
                <TreeDeciduous :size="42" stroke-width="1.2" />
            </div>
        </div>
    </section>
</template>

<style scoped>
/* -----------------------------------------------------------------------------
   ESPACIADO OPCIONAL
----------------------------------------------------------------------------- */

.husky-section-compact {
    padding-top: 2rem;
    padding-bottom: 2rem;
}

/* -----------------------------------------------------------------------------
   GRID DE PRODUCTOS
----------------------------------------------------------------------------- */

.husky-product-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 24px;
}

@media (max-width: 1024px) {
    .husky-product-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }
}

@media (max-width: 640px) {
    .husky-product-grid {
        grid-template-columns: 1fr;
        gap: 18px;
    }
}

/* -----------------------------------------------------------------------------
   VARIANTES DE PRODUCTOS
----------------------------------------------------------------------------- */

.husky-product-variants {
    display: flex;
    flex-direction: column;
    gap: 9px;
    margin-top: 16px;
    padding-top: 14px;
    border-top: 1px solid #e5eae4;
}

.husky-variants-label {
    color: #737d43;
    font-size: 0.66rem;
    font-weight: 800;
    letter-spacing: 0.11em;
    text-transform: uppercase;
}

.husky-variants-list {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
}

.husky-variant-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 7px 14px;
    border: 1px solid #34463a;
    border-radius: 999px;
    background-color: #34463a;
    color: #fff;
    font-size: 0.75rem;
    font-weight: 700;
    line-height: 1.4;
}
</style>