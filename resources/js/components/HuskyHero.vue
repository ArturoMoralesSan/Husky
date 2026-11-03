
<script setup lang="ts">
import { ArrowDown, ArrowRight } from 'lucide-vue-next'
import { onMounted, onUnmounted } from 'vue'

interface Banner {
    title: string
    subtitle: string | null
    description: string | null
    image: string
    image_url: string
    alt_text: string | null
    link_url: string | null
    button_text: string | null
    open_in_new_tab: boolean
}

const props = withDefaults(
    defineProps<{
        banner: Banner | null
        scrollTarget?: string
        minHeight?: string
    }>(),
    {
        scrollTarget: '#nosotros',
        minHeight: '90vh',
    },
)

const isVideo = (image?: string | null) =>
    Boolean(image && /\.(mp4|webm|mov)$/i.test(image))

let animationFrame = 0

function updateParallax() {
    cancelAnimationFrame(animationFrame)

    animationFrame = requestAnimationFrame(() => {
        const hero = document.querySelector<HTMLElement>('.husky-hero')
        const background = hero?.querySelector<HTMLElement>('.husky-hero-image')

        if (!hero || !background) return

        const rect = hero.getBoundingClientRect()
        const movement = rect.top * -0.18

        background.style.setProperty('--parallax-y', `${movement}px`)
    })
}

onMounted(() => {
    window.addEventListener('scroll', updateParallax, { passive: true })
    window.addEventListener('resize', updateParallax)

    updateParallax()
})

onUnmounted(() => {
    window.removeEventListener('scroll', updateParallax)
    window.removeEventListener('resize', updateParallax)

    cancelAnimationFrame(animationFrame)
})
</script>

<template>
    <section
        class="husky-hero"
        aria-labelledby="husky-hero-title"
        :style="{ minHeight: props.minHeight }"
    >
        <!-- VIDEO -->
        <video
            v-if="props.banner && isVideo(props.banner.image)"
            class="husky-hero-image husky-hero-video"
            :src="props.banner.image_url"
            :aria-label="props.banner.alt_text || props.banner.title"
            autoplay
            muted
            loop
            playsinline
            preload="auto"
        />

        <!-- IMAGEN -->
        <div
            v-else-if="props.banner"
            class="husky-hero-image husky-hero-background"
            role="img"
            :aria-label="props.banner.alt_text || props.banner.title"
            :style="{
                backgroundImage: `url('${props.banner.image_url}')`,
            }"
        />

        <!-- IMAGEN DE RESPALDO -->
        <div
            v-else
            class="husky-hero-image husky-hero-background"
            role="img"
            aria-label="Imagen de portada de Industrias Husky"
        />

        <!-- SOMBRA -->
        <div class="husky-hero-shade" />

        <!-- CONTENIDO DEL BANNER -->
        <div
            v-if="props.banner"
            class="husky-container husky-hero-content"
        >
            <p
                v-if="props.banner.subtitle"
                class="husky-eyebrow husky-eyebrow-light"
            >
                <span />
                {{ props.banner.subtitle }}
            </p>

            <h1 id="husky-hero-title">
                {{ props.banner.title }}
            </h1>

            <p
                v-if="props.banner.description"
                class="husky-hero-copy"
            >
                {{ props.banner.description }}
            </p>

            <!-- BOTÓN DEL BANNER -->
            <div
                v-if="props.banner.link_url && props.banner.button_text"
                class="husky-hero-actions"
            >
                <a
                    class="husky-button husky-button-outline"
                    :href="props.banner.link_url"
                    :target="
                        props.banner.open_in_new_tab
                            ? '_blank'
                            : undefined
                    "
                    :rel="
                        props.banner.open_in_new_tab
                            ? 'noopener noreferrer'
                            : undefined
                    "
                >
                    {{ props.banner.button_text }}
                    <ArrowRight :size="17" />
                </a>
            </div>
        </div>

        <!-- INDICADOR DE DESPLAZAMIENTO -->
        <a
            class="husky-scroll-cue"
            :href="props.scrollTarget"
            aria-label="Desplazarse a la siguiente sección"
        >
            <ArrowDown :size="19" />
        </a>
    </section>
</template>

<style scoped>
/* =========================================
   HERO PRINCIPAL
========================================= */

.husky-hero {
    position: relative;
    display: flex;
    min-height: min(780px, 92vh);
    min-height: min(780px, 92svh);
    align-items: center;
    overflow: hidden;
    isolation: isolate;
    color: #fff;
    background: #334c3d;
}

/* =========================================
   BASE COMPARTIDA: PARALLAX
========================================= */

.husky-hero-image {
    position: absolute;
    inset: -12% 0;
    z-index: 0;

    width: 100%;
    height: 124%;

    transform: translate3d(
        0,
        var(--parallax-y, 0px),
        0
    );

    will-change: transform;
}

/* =========================================
   IMAGEN
========================================= */

.husky-hero-background {
    background-position: center 29%;
    background-size: cover;
    background-repeat: no-repeat;

    filter: brightness(1.08) contrast(1.08) saturate(1.12);

    transform: translate3d(
        0,
        var(--parallax-y, 0px),
        0
    ) scale(1.02);
}

/* =========================================
   VIDEO
========================================= */

.husky-hero-video {
    object-fit: cover;
    object-position: bottom !important;

    filter: brightness(1.05) contrast(1.08) saturate(1.1);

    transform: translate3d(
        0,
        var(--parallax-y, 0px),
        0
    ) scale(1.05);
}



/* =========================================
   INDICADOR DE SCROLL
========================================= */

.husky-scroll-cue {
    position: absolute;
    z-index: 2;

    right: 50%;
    bottom: 30px;

    display: flex;
    align-items: center;
    justify-content: center;

    width: 44px;
    height: 44px;

    color: #fff;

    border: 1px solid rgba(255, 255, 255, 0.45);
    border-radius: 50%;

    transform: translateX(50%);

    transition:
        background 0.3s ease,
        border-color 0.3s ease;
}

.husky-scroll-cue:hover {
    background: rgba(255, 255, 255, 0.15);
    border-color: #fff;
}

/* =========================================
   MOVIMIENTO REDUCIDO
========================================= */

@media (prefers-reduced-motion: reduce) {
    .husky-hero-image {
        transform: none !important;
        will-change: auto;
    }

    .husky-scroll-cue {
        transition: none;
    }
}

/* =========================================
   RESPONSIVE
========================================= */

@media (max-width: 600px) {
    .husky-hero-content {
        padding-top: 80px;
        padding-bottom: 75px;
    }

    .husky-scroll-cue {
        bottom: 18px;
    }
}
</style>
