<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { computed } from 'vue'
import {
    ArrowRight,
    Award,
    CheckCircle2,
    Compass,
    Eye,
    Factory,
    Gem,
    Handshake,
    HeartHandshake,
    Leaf,
    Lightbulb,
    ShieldCheck,
    Sprout,
    Star,
    Target,
    TreeDeciduous,
    Users,
    type LucideIcon,
} from 'lucide-vue-next'

import HuskyFooter from '@/Components/HuskyFooter.vue'
import HuskyHeader from '@/Components/HuskyHeader.vue'
import HuskyHero from '@/Components/HuskyHero.vue'
import ImageGallery from '@/Components/ImageGallery.vue'

import '../../css/husky-landing.css'

// -----------------------------------------------------------------------------
// TIPOS
// -----------------------------------------------------------------------------

interface Banner {
    id: number
    title?: string | null
    subtitle?: string | null
    description?: string | null
    image_url?: string | null
    video_url?: string | null
    button_text?: string | null
    button_url?: string | null
}

interface AboutValue {
    id: number
    name: string
    description: string
    icon?: string | null
    sort_order?: number
}

interface AboutGalleryImage {
    id: number
    file: string
    url: string
    alt: string | null
    is_primary: boolean
    sort_order: number
}

interface About {
    id: number
    who_we_are?: string | null
    history?: string | null
    mission?: string | null
    vision?: string | null
    objectives?: string | null
    values?: AboutValue[]
    gallery_images?: AboutGalleryImage[]
}

const props = defineProps<{
    banner: Banner | null
    about: About | null
}>()

// -----------------------------------------------------------------------------
// ICONOS DE VALORES
// -----------------------------------------------------------------------------

const iconMap: Record<string, LucideIcon> = {
    arrowright: ArrowRight,
    award: Award,
    checkcircle2: CheckCircle2,
    compass: Compass,
    eye: Eye,
    factory: Factory,
    gem: Gem,
    handshake: Handshake,
    hearthandshake: HeartHandshake,
    leaf: Leaf,
    lightbulb: Lightbulb,
    shieldcheck: ShieldCheck,
    sprout: Sprout,
    star: Star,
    target: Target,
    treedeciduous: TreeDeciduous,
    tree: TreeDeciduous,
    users: Users,
}

// -----------------------------------------------------------------------------
// VALORES
// -----------------------------------------------------------------------------

const values = computed(() => props.about?.values ?? [])

// -----------------------------------------------------------------------------
// GALERÍA DE HISTORIA
// -----------------------------------------------------------------------------

const historyGallery = computed(() =>
    (props.about?.gallery_images ?? [])
        .slice()
        .sort((a, b) => a.sort_order - b.sort_order)
        .map(image => ({
            src: image.url,
            alt: image.alt || 'Galería de Industrias Husky',
        })),
)

// -----------------------------------------------------------------------------
// MISIÓN, VISIÓN Y OBJETIVOS
// -----------------------------------------------------------------------------

const sections = computed(() => [
    {
        id: 'mission',
        label: 'Nuestra misión',
        title: 'Lo que nos mueve',
        description: props.about?.mission,
        icon: Target,
    },
    {
        id: 'vision',
        label: 'Nuestra visión',
        title: 'Hacia dónde vamos',
        description: props.about?.vision,
        icon: Eye,
    },
    {
        id: 'objectives',
        label: 'Nuestros objetivos',
        title: 'Lo que buscamos',
        description: props.about?.objectives,
        icon: Compass,
    },
].filter(section => section.description?.trim()))

// -----------------------------------------------------------------------------
// OBTENER ICONO DE VALOR
// -----------------------------------------------------------------------------

function getValueIcon(icon?: string | null): LucideIcon {
    if (!icon) return Star

    const normalizedIcon = icon
        .replace(/[-_\s]/g, '')
        .toLowerCase()

    return iconMap[normalizedIcon] || Star
}
</script>

<template>
    <Head title="Nosotros | Industrias Husky">
        <meta
            name="description"
            content="Conoce Industrias Husky, nuestra historia, misión, visión y compromiso con la calidad en madera de pino impregnada y maderas tropicales."
        />

        <meta
            name="keywords"
            content="Industrias Husky, madera, madera de pino, madera impregnada, maderas tropicales, soluciones en madera"
        />

        <meta
            property="og:title"
            content="Nosotros | Industrias Husky"
        />

        <meta
            property="og:description"
            content="Conoce nuestra historia, filosofía y compromiso con la calidad."
        />

        <meta
            property="og:type"
            content="website"
        />
    </Head>

    <div class="about-page">
        <HuskyHeader />

        <main>
            <!-- HERO -->
            <HuskyHero
                :banner="props.banner"
                scroll-target="#nosotros"
                min-height="60vh"
            />

            <!-- INTRODUCCIÓN -->
            <section
                id="nosotros"
                class="about-section about-intro"
            >
                <div class="about-container">
                    <div class="intro-grid">
                        <div class="intro-heading">
                            <span class="about-eyebrow">
                                CONÓCENOS
                            </span>

                            <h1 class="about-title">
                                Somos <span>Industrias Husky</span>
                            </h1>
                        </div>

                        <div class="intro-content">
                            <p
                                v-if="about?.who_we_are"
                                class="about-description"
                            >
                                {{ about.who_we_are }}
                            </p>

                            <p
                                v-else
                                class="about-description"
                            >
                                En Industrias Husky trabajamos para ofrecer
                                soluciones en madera que respondan a las
                                necesidades de cada proyecto, combinando
                                calidad, experiencia y compromiso.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- HISTORIA -->
            <section
                v-if="about?.history"
                class="about-section about-history"
            >
                <div class="about-container">
                    <div class="history-grid">
                        <div class="history-visual">
                            <ImageGallery
                                v-if="historyGallery.length"
                                :items="historyGallery"
                                :autoplay="true"
                                :interval="4000"
                            />

                            <div
                                v-else
                                class="history-visual-inner"
                            >
                                <div class="history-icon">
                                    <TreeDeciduous />
                                </div>

                                <span class="history-caption">
                                    INDUSTRIAS HUSKY
                                </span>

                                <h2>
                                    Experiencia que construye confianza.
                                </h2>

                                <div class="history-decoration"></div>
                            </div>
                        </div>

                        <div class="history-content">
                            <span class="about-eyebrow">
                                NUESTRA TRAYECTORIA
                            </span>

                            <h2 class="about-heading">
                                Una historia construida con compromiso
                            </h2>

                            <p class="about-description">
                                {{ about.history }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FILOSOFÍA -->
            <section
                v-if="sections.length"
                class="about-section about-purpose"
            >
                <div class="about-container">
                    <div class="section-heading">
                        <span class="about-eyebrow">
                            NUESTRA FILOSOFÍA
                        </span>

                        <h2 class="about-heading">
                            Lo que nos define
                        </h2>

                        <p class="section-description">
                            Nuestra forma de trabajar se basa en principios
                            que orientan cada decisión y cada proyecto.
                        </p>
                    </div>

                    <div class="purpose-grid">
                        <article
                            v-for="(section, index) in sections"
                            :key="section.id"
                            class="purpose-card"
                            :class="`purpose-card-${index + 1}`"
                        >
                            <div class="purpose-card-top">
                                <span class="purpose-number">
                                    0{{ index + 1 }}
                                </span>

                                <component
                                    :is="section.icon"
                                    class="purpose-icon"
                                />
                            </div>

                            <span class="purpose-label">
                                {{ section.label }}
                            </span>

                            <h3>{{ section.title }}</h3>

                            <p>{{ section.description }}</p>

                            <div class="purpose-card-decoration"></div>
                        </article>
                    </div>
                </div>
            </section>

            <!-- VALORES -->
            <section
                v-if="values.length"
                class="about-section about-values"
            >
                <div class="about-container">
                    <div class="section-heading values-heading">
                        <span class="about-eyebrow">
                            NUESTROS VALORES
                        </span>

                        <h2 class="about-heading">
                            Principios que nos representan
                        </h2>

                        <p class="section-description">
                            Son la base de nuestras relaciones, nuestro
                            trabajo y la manera en que desarrollamos
                            cada proyecto.
                        </p>
                    </div>

                    <div class="values-grid">
                        <article
                            v-for="(value, index) in values"
                            :key="value.id"
                            class="value-card"
                        >
                            <div class="value-card-header">
                                <div class="value-icon-wrapper">
                                    <component
                                        :is="getValueIcon(value.icon)"
                                        class="value-icon"
                                    />
                                </div>

                                <span class="value-number">
                                    {{ String(index + 1).padStart(2, '0') }}
                                </span>
                            </div>

                            <h3>{{ value.name }}</h3>

                            <p>{{ value.description }}</p>

                            <div class="value-card-line"></div>
                        </article>
                    </div>
                </div>
            </section>
        </main>

        <HuskyFooter />
    </div>
</template>

<style scoped>
.about-page {
    width: 100%;
    overflow: hidden;
    color: var(--ink, #25352d);
    background: var(--paper, #fffefa);
}

.about-container {
    width: min(1180px, calc(100% - 48px));
    margin: 0 auto;
}

.about-section {
    position: relative;
    padding: 110px 0;
}

.about-eyebrow {
    display: inline-block;
    color: var(--olive, #737d43);
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.2em;
    line-height: 1.5;
}

.about-title {
    margin: 22px 0 20px;
    color: var(--forest, #243b30);
    font-family: 'Manrope', sans-serif;
    font-size: clamp(2.7rem, 5vw, 4.8rem);
    font-weight: 600;
    letter-spacing: -0.065em;
    line-height: 1.05;
}

.about-title span {
    display: block;
    color: var(--olive, #737d43);
}

.about-title-line {
    width: 64px;
    height: 3px;
    margin: 28px 0;
    border-radius: 10px;
    background: var(--olive, #737d43);
}

.about-heading {
    margin: 18px 0 24px;
    color: var(--forest, #243b30);
    font-family: 'Manrope', sans-serif;
    font-size: clamp(2rem, 3.5vw, 3.1rem);
    font-weight: 600;
    letter-spacing: -0.045em;
    line-height: 1.15;
}

.about-description {
    color: #68766d;
    font-size: 1rem;
    line-height: 1.95;
    white-space: pre-line;
}

.intro-grid {
    display: grid;
    grid-template-columns: 0.85fr 1.15fr;
    align-items: center;
    gap: clamp(50px, 8vw, 120px);
}

.intro-heading {
    max-width: 460px;
}

.intro-highlight {
    max-width: 320px;
    color: var(--olive, #737d43);
    font-size: 1.08rem;
    font-weight: 600;
    line-height: 1.7;
}

.intro-content {
    padding: 36px 0;
}

.intro-content .about-description {
    font-size: 1.08rem;
}

.intro-points {
    display: grid;
    gap: 16px;
    margin-top: 32px;
}

.intro-point {
    display: flex;
    align-items: center;
    gap: 12px;
    color: var(--forest, #243b30);
    font-size: 0.94rem;
    font-weight: 600;
}

.intro-point svg {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
    color: var(--olive, #737d43);
    stroke-width: 1.8;
}

/* HISTORIA */

.about-history {
    background: var(--linen, #f5f3ec);
}

.history-grid {
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    align-items: center;
    gap: clamp(50px, 8vw, 110px);
}

.history-visual {
    min-height: 420px;
    padding: 22px;
    border-radius: 28px;
    background: var(--sage, #dce2d4);
}

/* CONTENEDOR DE LA GALERÍA */

.history-visual :deep(.image-gallery) {
    width: 100%;
    height: 100%;
    min-height: 376px;
    overflow: hidden;
    border-radius: 18px;
}

.history-visual-inner {
    position: relative;
    display: flex;
    min-height: 376px;
    overflow: hidden;
    flex-direction: column;
    align-items: flex-start;
    justify-content: flex-end;
    padding: 44px;
    border-radius: 18px;
    background:
        radial-gradient(
            circle at 80% 15%,
            rgba(255, 255, 255, 0.42),
            transparent 38%
        ),
        linear-gradient(
            145deg,
            #526b50,
            #243b30 72%
        );
}

.history-icon {
    position: absolute;
    top: 30px;
    right: 30px;
    display: grid;
    width: 110px;
    height: 110px;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    color: rgba(255, 255, 255, 0.82);
}

.history-icon svg {
    width: 58px;
    height: 58px;
    stroke-width: 1;
}

.history-caption {
    margin-bottom: 18px;
    color: #dce2d4;
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.2em;
}

.history-visual-inner h2 {
    position: relative;
    z-index: 1;
    max-width: 370px;
    margin: 0;
    color: white;
    font-family: 'Manrope', sans-serif;
    font-size: clamp(1.9rem, 3vw, 2.7rem);
    font-weight: 600;
    letter-spacing: -0.045em;
    line-height: 1.2;
}

.history-decoration {
    position: absolute;
    right: -60px;
    bottom: -110px;
    width: 260px;
    height: 260px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 50%;
}

.history-content {
    max-width: 460px;
}

/* FILOSOFÍA */

.about-purpose {
    background: var(--paper, #fffefa);
}

.section-heading {
    max-width: 650px;
    margin: 0 auto 54px;
    text-align: center;
}

.section-heading .about-heading {
    margin-bottom: 16px;
}

.section-description {
    max-width: 540px;
    margin: 0 auto;
    color: #748078;
    font-size: 0.98rem;
    line-height: 1.8;
}

/* GRID AUTOMÁTICO: MISIÓN, VISIÓN Y OBJETIVOS */

.purpose-grid {
    display: grid;
    grid-template-columns: repeat(
        auto-fit,
        minmax(min(100%, 280px), 1fr)
    );
    gap: 22px;
    align-items: stretch;
}

.purpose-card {
    position: relative;
    display: flex;
    min-height: 340px;
    overflow: hidden;
    flex-direction: column;
    align-items: flex-start;
    padding: 32px;
    border: 1px solid #e4e9e1;
    border-radius: 22px;
    background: #fff;
    transition:
        transform 0.3s ease,
        border-color 0.3s ease,
        background 0.3s ease;
}

.purpose-card:hover {
    transform: translateY(-6px);
    border-color: #c6d2c1;
    background: #fbfcf8;
}

.purpose-card-top {
    display: flex;
    width: 100%;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 44px;
}

.purpose-number {
    color: #a3ad9e;
    font-family: 'Manrope', sans-serif;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.1em;
}

.purpose-icon {
    width: 30px;
    height: 30px;
    color: var(--olive, #737d43);
    stroke-width: 1.5;
}

.purpose-label {
    margin-bottom: 12px;
    color: var(--olive, #737d43);
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.13em;
    text-transform: uppercase;
}

.purpose-card h3 {
    margin: 0 0 14px;
    color: var(--forest, #243b30);
    font-family: 'Manrope', sans-serif;
    font-size: 1.35rem;
    font-weight: 650;
    letter-spacing: -0.035em;
}

.purpose-card p {
    position: relative;
    z-index: 1;
    margin: 0;
    color: #748078;
    font-size: 0.91rem;
    line-height: 1.85;
    white-space: pre-line;
}

.purpose-card-decoration {
    position: absolute;
    right: -35px;
    bottom: -45px;
    width: 120px;
    height: 120px;
    border: 1px solid #e9eee6;
    border-radius: 50%;
}

/* VALORES */

.about-values {
    background: var(--linen, #f5f3ec);
}

/* GRID AUTOMÁTICO: VALORES */

.values-heading {
    margin-bottom: 60px;
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(
        auto-fit,
        minmax(min(100%, 235px), 1fr)
    );
    gap: 18px;
    align-items: stretch;
}

.value-card {
    position: relative;
    display: flex;
    min-height: 250px;
    overflow: hidden;
    flex-direction: column;
    padding: 27px;
    border: 1px solid #e0e6dc;
    border-radius: 20px;
    background: var(--paper, #fffefa);
    transition:
        transform 0.3s ease,
        box-shadow 0.3s ease,
        border-color 0.3s ease;
}

.value-card:hover {
    transform: translateY(-5px);
    border-color: #c5d2bf;
    box-shadow: 0 15px 35px rgba(36, 59, 48, 0.07);
}

.value-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
}

.value-icon-wrapper {
    display: grid;
    width: 54px;
    height: 54px;
    place-items: center;
    border-radius: 16px;
    background: var(--sage, #dce2d4);
}

.value-icon {
    width: 25px;
    height: 25px;
    color: var(--forest, #243b30);
    stroke-width: 1.7;
}

.value-number {
    color: #a3ad9e;
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.1em;
}

.value-card h3 {
    margin: 0 0 12px;
    color: var(--forest, #243b30);
    font-family: 'Manrope', sans-serif;
    font-size: 1.12rem;
    font-weight: 700;
    letter-spacing: -0.025em;
}

.value-card p {
    margin: 0;
    color: #748078;
    font-size: 0.87rem;
    line-height: 1.8;
    white-space: pre-line;
}

.value-card-line {
    width: 34px;
    height: 2px;
    margin-top: auto;
    padding-top: 20px;
    border-bottom: 2px solid var(--olive, #737d43);
}

/* CTA */

.about-cta {
    padding: 100px 0;
    background: var(--forest, #243b30);
}

.cta-content {
    display: flex;
    max-width: 760px;
    flex-direction: column;
    align-items: center;
    margin: 0 auto;
    text-align: center;
}

.cta-icon {
    display: grid;
    width: 66px;
    height: 66px;
    margin-bottom: 28px;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    color: var(--sage, #dce2d4);
}

.cta-icon svg {
    width: 30px;
    height: 30px;
    stroke-width: 1.4;
}

.cta-content .about-eyebrow {
    color: #c4cfb8;
}

.cta-content h2 {
    max-width: 680px;
    margin: 20px 0;
    color: #fffefa;
    font-family: 'Manrope', sans-serif;
    font-size: clamp(2rem, 4.5vw, 3.7rem);
    font-weight: 600;
    letter-spacing: -0.05em;
    line-height: 1.15;
}

.cta-content p {
    max-width: 540px;
    margin: 0;
    color: rgba(255, 254, 250, 0.72);
    font-size: 0.98rem;
    line-height: 1.8;
}

.about-cta-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-top: 32px;
    padding: 15px 24px;
    border: 1px solid transparent;
    border-radius: 50px;
    background: var(--sage, #dce2d4);
    color: var(--forest, #243b30);
    font-size: 0.9rem;
    font-weight: 700;
    text-decoration: none;
    transition:
        background 0.25s ease,
        transform 0.25s ease;
}

.about-cta-button:hover {
    transform: translateY(-3px);
    background: #fffefa;
}

.about-cta-button svg {
    width: 18px;
    height: 18px;
}

/* RESPONSIVE */

@media (max-width: 1024px) {
    .about-section {
        padding: 85px 0;
    }

    .intro-grid {
        grid-template-columns: 1fr 1fr;
        gap: 45px;
    }

    .history-grid {
        gap: 45px;
    }

    /*
     * Las grids de propósito y valores ya son automáticas.
     * No se fuerzan columnas en este breakpoint.
     */
}

@media (max-width: 700px) {
    .about-container {
        width: min(100% - 36px, 520px);
    }

    .about-section {
        padding: 68px 0;
    }

    .intro-grid,
    .history-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }

    .intro-content {
        padding: 0;
    }

    .history-visual {
        min-height: 330px;
        padding: 12px;
    }

    .history-visual :deep(.image-gallery) {
        min-height: 306px;
    }

    .history-visual-inner {
        min-height: 306px;
        padding: 30px;
    }

    .history-icon {
        top: 22px;
        right: 22px;
        width: 80px;
        height: 80px;
    }

    .history-icon svg {
        width: 42px;
        height: 42px;
    }

    .purpose-card {
        min-height: 280px;
        padding: 27px;
    }

    .purpose-card-top {
        margin-bottom: 32px;
    }

    .value-card {
        min-height: 220px;
    }

    .section-heading {
        margin-bottom: 38px;
    }

    .about-cta {
        padding: 75px 0;
    }
}

@media (max-width: 480px) {
    .purpose-grid {
        grid-template-columns: minmax(0, 1fr);
    }

    .values-grid {
        grid-template-columns: minmax(0, 1fr);
    }
}

@media (max-width: 380px) {
    .about-container {
        width: calc(100% - 28px);
    }

    .about-title {
        font-size: 2.45rem;
    }

    .purpose-card,
    .value-card {
        padding: 22px;
    }

    .history-visual-inner {
        padding: 24px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .purpose-card,
    .value-card,
    .about-cta-button {
        transition: none;
    }

    .purpose-card:hover,
    .value-card:hover,
    .about-cta-button:hover {
        transform: none;
    }
}
</style>