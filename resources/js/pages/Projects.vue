
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

interface Project {
    id: number
    slug: string
    title: string
    description: string | null
    image: string | null
    imageAlt: string
}

const props = defineProps<{
    banner: Banner | null
    projects: Project[]
}>()

// -----------------------------------------------------------------------------
// SEO
// -----------------------------------------------------------------------------

const seoTitle = 'Proyectos de madera | Industrias Husky'

const seoDescription =
    'Conoce los proyectos de Industrias Husky: soluciones en madera de pino impregnada y maderas tropicales para construcción, infraestructura, industria y proyectos especiales.'

const canonicalUrl = 'https://industriashusky.com.mx/proyectos'

const ogImage = computed(
    () =>
        props.banner?.image_url ||
        'https://industriashusky.com.mx/images/og-default.jpg',
)

const collectionSchema = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'CollectionPage',
    name: seoTitle,
    description: seoDescription,
    url: canonicalUrl,

    isPartOf: {
        '@type': 'WebSite',
        name: 'Industrias Husky',
        url: 'https://industriashusky.com.mx/',
    },

    mainEntity: {
        '@type': 'ItemList',
        numberOfItems: props.projects.length,

        itemListElement: props.projects.map((project, index) => ({
            '@type': 'ListItem',
            position: index + 1,

            item: {
                '@type': 'CreativeWork',
                name: project.title,
                description: project.description || undefined,
                image: project.image || undefined,
                url: `${canonicalUrl}/${project.slug}`,
                creator: {
                    '@type': 'Organization',
                    name: 'Industrias Husky',
                    url: 'https://industriashusky.com.mx/',
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
    props.projects.map((project, index) => ({
        id: project.id,
        slug: project.slug,
        number: String(index + 1).padStart(2, '0'),
        title: project.title,
        description: project.description || '',
        image: project.image,
        imageAlt: project.imageAlt || project.title,

        // Cada tarjeta abre su propia ficha individual.
        href: `/proyectos/${project.slug}`,
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
            content="Proyectos de Industrias Husky"
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

        <!-- DATOS ESTRUCTURADOS -->
        <component
            :is="'script'"
            type="application/ld+json"
            head-key="projects-collection-schema"
            v-html="collectionSchemaJson"
        />
    </Head>

    <div class="husky-site">
        <HuskyHeader />

        <main>
            <!-- PORTADA -->
            <HuskyHero
                :banner="props.banner"
                scroll-target="#catalogo-proyectos"
                min-height="60vh"
            />

            <!-- CATÁLOGO DE PROYECTOS -->
            <ItemCards
                id="catalogo-proyectos"
                variant="grid"
                :items="projects"
                eyebrow="NUESTROS PROYECTOS"
                title="Proyectos que reflejan nuestra experiencia."
                empty-text="Estamos preparando nuestro catálogo de proyectos."
            />
        </main>

        <HuskyFooter />
    </div>
</template>
