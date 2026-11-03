
<script setup lang="ts">
import { computed } from 'vue'
import {
    ArrowRight,
    ArrowUpRight,
    TreeDeciduous,
} from 'lucide-vue-next'

import ImageGallery from '@/Components/ImageGallery.vue'

interface ItemCard {
    id?: number | string
    number?: string
    title: string
    description?: string | null
    image?: string | null
    imageAlt?: string
    href?: string
}

const props = withDefaults(defineProps<{
    variant?: 'grid' | 'list' | 'featured'
    items?: ItemCard[]
    eyebrow?: string
    title?: string
    linkText?: string
    linkHref?: string
    emptyText?: string
    image?: string | null
    imageAlt?: string
    actionLabel?: string
}>(), {
    variant: 'grid',
    items: () => [],
    eyebrow: '',
    title: '',
    linkText: '',
    linkHref: '#contacto',
    emptyText: 'Estamos preparando esta sección.',
    image: null,
    imageAlt: '',
    actionLabel: 'Conocer más',
})

const emit = defineEmits<{
    select: [item: ItemCard]
}>()

const featuredGallery = computed(() => {
    const galleryItems = props.items
        .filter((item) => item.image)
        .map((item) => ({
            src: item.image!,
            alt: item.imageAlt || item.title,
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
    <!-- PRODUCTOS: TARJETAS EN CUADRÍCULA -->
    <section
        v-if="props.variant === 'grid'"
        class="husky-products husky-section"
    >
        <div class="husky-container">
            <div class="husky-section-heading">
                <div>
                    <p class="husky-eyebrow">{{ props.eyebrow }}</p>
                    <h2>{{ props.title }}</h2>
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

            <div v-if="props.items.length" class="husky-product-grid">
                <a
                    v-for="(item, index) in props.items"
                    :key="item.id ?? index"
                    class="husky-product-card"
                    :href="item.href || '#contacto'"
                    @click="emit('select', item)"
                >
                    <div class="husky-product-image">
                        <img
                            v-if="item.image"
                            :src="item.image"
                            :alt="item.imageAlt || item.title"
                            loading="lazy"
                        />

                        <div
                            v-else
                            class="husky-product-image-empty"
                            role="img"
                            :aria-label="`Imagen no disponible para ${item.title}`"
                        >
                            <TreeDeciduous :size="42" stroke-width="1.2" />
                        </div>

                        <span>
                            {{ item.number || String(index + 1).padStart(2, '0') }}
                        </span>
                    </div>

                    <div class="husky-product-info">
                        <h3>{{ item.title }}</h3>
                        <p>{{ item.description }}</p>

                        <ArrowUpRight
                            class="husky-card-arrow"
                            :size="19"
                        />
                    </div>
                </a>
            </div>

            <div v-else class="husky-products-empty">
                <TreeDeciduous :size="34" stroke-width="1.3" />
                <p>{{ props.emptyText }}</p>
            </div>
        </div>
    </section>

    <!-- SERVICIOS: LISTADO -->
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

                <h2>{{ props.title }}</h2>

                <slot name="intro" />
                <slot name="caption" />
            </div>

            <div class="husky-service-list">
                <a
                    v-for="(item, index) in props.items"
                    :key="item.id ?? index"
                    class="husky-service-item"
                    :href="item.href || props.linkHref"
                    :aria-label="`Conocer más sobre ${item.title}`"
                >
                    <span class="husky-service-number">
                        {{ item.number || String(index + 1).padStart(2, '0') }}
                    </span>

                    <div class="husky-service-content">
                        <h3>{{ item.title }}</h3>
                        <p>{{ item.description }}</p>
                    </div>

                    <span class="husky-service-arrow" aria-hidden="true">
                        <ArrowUpRight :size="19" />
                    </span>
                </a>

                <div v-if="!props.items.length" class="husky-service-empty">
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

    <!-- APLICACIÓN O PROYECTO DESTACADO -->
    <section
        v-else-if="props.variant === 'featured'"
        class="husky-project"
    >
        <div class="husky-project-copy husky-project-copy-responsive">
            <p class="husky-eyebrow">{{ props.eyebrow }}</p>

            <h2>{{ props.title }}</h2>

            <p>
                {{ props.items[0]?.description }}
            </p>

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
