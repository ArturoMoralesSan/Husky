<script setup lang="ts">
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'

import HuskyFooter from '@/Components/HuskyFooter.vue'
import HuskyHeader from '@/Components/HuskyHeader.vue'
import HuskyHero from '@/Components/HuskyHero.vue'
import ItemCards from '@/Components/ItemCards.vue'

import '../../css/husky-landing.css'

// -----------------------------------------------------------------------------
// TIPOS
// -----------------------------------------------------------------------------

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

interface Service {
    id: number
    slug: string
    title: string
    description: string | null
    image: string | null
    imageAlt: string
}

const props = defineProps<{
    banner: Banner | null
    projects: Service[]
    maquila: Service[]
}>()

// -----------------------------------------------------------------------------
// CONFIGURACIÓN SEO
// -----------------------------------------------------------------------------

const siteUrl = 'https://industriashusky.com.mx'

const seoTitle = 'Servicios de madera | Industrias Husky'

const seoDescription =
    'Conoce los servicios de Industrias Husky: proyectos de madera y servicios de maquila para construcción, infraestructura, industria y proyectos especiales.'

const canonicalUrl = `${siteUrl}/servicios`

/**
 * Convierte rutas relativas en URLs absolutas.
 * Conserva las URLs que ya son absolutas.
 */
const absoluteUrl = (
    url: string | null | undefined,
): string | undefined => {
    if (!url) return undefined

    try {
        return new URL(url, `${siteUrl}/`).toString()
    } catch {
        return undefined
    }
}

// Imagen para compartir la página.
const ogImage = computed(
    () =>
        absoluteUrl(props.banner?.image_url) ||
        `${siteUrl}/images/og-default.jpg`,
)

// Texto alternativo para la imagen social.
const ogImageAlt = computed(
    () =>
        props.banner?.alt_text ||
        props.banner?.title ||
        'Servicios de Industrias Husky',
)

// -----------------------------------------------------------------------------
// COLECCIONES PARA SEO
// -----------------------------------------------------------------------------

const allServices = computed(() => [
    ...props.projects,
    ...props.maquila,
])

// -----------------------------------------------------------------------------
// DATOS ESTRUCTURADOS
// -----------------------------------------------------------------------------

const collectionSchema = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'CollectionPage',
    name: seoTitle,
    description: seoDescription,
    url: canonicalUrl,

    isPartOf: {
        '@type': 'WebSite',
        name: 'Industrias Husky',
        url: `${siteUrl}/`,
    },

    mainEntity: {
        '@type': 'ItemList',
        numberOfItems: allServices.value.length,

        itemListElement: allServices.value.map((service, index) => ({
            '@type': 'ListItem',
            position: index + 1,

            item: {
                '@type': 'Service',
                name: service.title,

                ...(service.description
                    ? { description: service.description }
                    : {}),

                ...(absoluteUrl(service.image)
                    ? { image: absoluteUrl(service.image) }
                    : {}),

                url: `${canonicalUrl}/${encodeURIComponent(service.slug)}`,

                provider: {
                    '@type': 'Organization',
                    name: 'Industrias Husky',
                    url: `${siteUrl}/`,
                },
            },
        })),
    },
}))

const collectionSchemaJson = computed(() =>
    JSON.stringify(collectionSchema.value),
)

// -----------------------------------------------------------------------------
// PROYECTOS
// -----------------------------------------------------------------------------

const projects = computed(() =>
    props.projects.map((service, index) => ({
        id: service.id,
        slug: service.slug,
        number: String(index + 1).padStart(2, '0'),
        title: service.title,
        description: service.description || '',
        image: service.image,
        imageAlt: service.imageAlt || service.title,
        href: `/servicios/${service.slug}`,
    })),
)

// -----------------------------------------------------------------------------
// MAQUILA
// -----------------------------------------------------------------------------

const maquila = computed(() =>
    props.maquila.map((service, index) => ({
        id: service.id,
        slug: service.slug,
        number: String(index + 1).padStart(2, '0'),
        title: service.title,
        description: service.description || '',
        image: service.image,
        imageAlt: service.imageAlt || service.title,
        href: `/servicios/${service.slug}`,
    })),
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

        <link
            head-key="canonical"
            rel="canonical"
            :href="canonicalUrl"
        />

        <meta
            head-key="theme-color"
            name="theme-color"
            content="#f8f8f2"
        />

        <meta
            head-key="author"
            name="author"
            content="Industrias Husky"
        />

        <meta
            head-key="language"
            name="language"
            content="es-MX"
        />

        <!-- OPEN GRAPH -->
        <meta
            head-key="og:type"
            property="og:type"
            content="website"
        />

        <meta
            head-key="og:site_name"
            property="og:site_name"
            content="Industrias Husky"
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
            head-key="og:image"
            property="og:image"
            :content="ogImage"
        />

        <meta
            head-key="og:image:alt"
            property="og:image:alt"
            :content="ogImageAlt"
        />

        <meta
            head-key="og:locale"
            property="og:locale"
            content="es_MX"
        />

        <!-- TWITTER -->
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

        <meta
            head-key="twitter:image"
            name="twitter:image"
            :content="ogImage"
        />

        <meta
            head-key="twitter:image:alt"
            name="twitter:image:alt"
            :content="ogImageAlt"
        />

        <!-- DATOS ESTRUCTURADOS -->
        <component
            :is="'script'"
            type="application/ld+json"
            head-key="services-collection-schema"
            v-html="collectionSchemaJson"
        />
    </Head>

    <div class="husky-site">
        <HuskyHeader />

        <main>
            <!-- PORTADA -->
            <HuskyHero
                :banner="props.banner"
                scroll-target="#catalogo-servicios"
                min-height="60vh"
            />

            <!-- INTRODUCCIÓN GENERAL -->
            <section
                id="catalogo-servicios"
                class="mx-auto max-w-7xl px-6 pb-6 pt-16 md:px-10 md:pt-24"
            >
                <div class="max-w-3xl">
                    <p
                        class="mb-4 text-xs font-semibold uppercase tracking-[0.22em] text-[#737d43]"
                    >
                        LO QUE HACEMOS
                    </p>

                    <h1
                        class="text-3xl font-semibold leading-tight tracking-tight text-[#243b30] md:text-5xl"
                    >
                        Conoce todos nuestros servicios.
                    </h1>

                    <p
                        class="mt-6 max-w-2xl text-base leading-8 text-[#5c6b60] md:text-lg"
                    >
                        En Industrias Husky desarrollamos soluciones en madera
                        para diferentes necesidades. Conoce nuestros proyectos
                        y servicios de maquila, respaldados por nuestra
                        experiencia y compromiso con la calidad.
                    </p>
                </div>
            </section>

            <!-- SECCIÓN DE PROYECTOS -->
            <ItemCards
                id="proyectos"
                variant="grid"
                :items="projects"
                :heading-level="2"
                eyebrow="NUESTRO TRABAJO"
                title="Proyectos"
                :compact="true"
                empty-text="Estamos preparando nuestra sección de proyectos."
            />

            <!-- SECCIÓN DE MAQUILA -->
            <ItemCards
                id="maquila"
                variant="grid"
                :items="maquila"
                :heading-level="2"
                eyebrow="SOLUCIONES A TU MEDIDA"
                title="Maquila"
                :compact="true"
                empty-text="Estamos preparando nuestra sección de maquila."
            />
        </main>

        <HuskyFooter />
    </div>
</template>