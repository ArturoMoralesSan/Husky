
<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import {
    ArrowRight,
    ArrowUpRight,
    ChevronDown,
    CircleHelp,
    Search,
    X,
} from 'lucide-vue-next'

import HuskyFooter from '@/Components/HuskyFooter.vue'
import HuskyHeader from '@/Components/HuskyHeader.vue'
import HuskyHero from '@/Components/HuskyHero.vue'

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

interface Faq {
    id: number
    question: string
    answer: string
    category: string | null
    sort_order: number
}

const props = defineProps<{
    banner: Banner | null
    faqs: Faq[]
}>()

// -----------------------------------------------------------------------------
// SEO
// -----------------------------------------------------------------------------

const siteUrl = 'https://industriashusky.com.mx'

const seoTitle = 'Preguntas frecuentes | Industrias Husky'

const seoDescription =
    'Encuentra respuestas sobre productos de madera, tratamientos, aserraderos portátiles, pijas, servicios, maquila y cotizaciones de Industrias Husky.'

const canonicalUrl = `${siteUrl}/preguntas-frecuentes`

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

const ogImage = computed(
    () =>
        absoluteUrl(props.banner?.image_url) ||
        `${siteUrl}/images/og-default.jpg`,
)

const ogImageAlt = computed(
    () =>
        props.banner?.alt_text ||
        props.banner?.title ||
        'Preguntas frecuentes de Industrias Husky',
)

// -----------------------------------------------------------------------------
// BÚSQUEDA Y CATEGORÍAS
// -----------------------------------------------------------------------------

const search = ref('')
const activeCategory = ref('Todas')
const openFaq = ref<number | null>(null)

const categories = computed(() => {
    const values = props.faqs
        .map((faq) => faq.category?.trim())
        .filter((category): category is string => Boolean(category))

    return ['Todas', ...new Set(values)]
})

const filteredFaqs = computed(() => {
    const term = search.value.trim().toLowerCase()

    return props.faqs.filter((faq) => {
        const matchesCategory =
            activeCategory.value === 'Todas' ||
            faq.category === activeCategory.value

        const matchesSearch =
            !term ||
            faq.question.toLowerCase().includes(term) ||
            faq.answer.toLowerCase().includes(term) ||
            (faq.category ?? '').toLowerCase().includes(term)

        return matchesCategory && matchesSearch
    })
})

const groupedFaqs = computed(() => {
    const groups = new Map<string, Faq[]>()

    filteredFaqs.value.forEach((faq) => {
        const category = faq.category?.trim() || 'General'

        if (!groups.has(category)) {
            groups.set(category, [])
        }

        groups.get(category)?.push(faq)
    })

    return Array.from(groups.entries()).map(([category, faqs]) => ({
        category,
        faqs,
    }))
})

// -----------------------------------------------------------------------------
// INTERACCIÓN
// -----------------------------------------------------------------------------

function toggleFaq(id: number) {
    openFaq.value = openFaq.value === id ? null : id
}

function selectCategory(category: string) {
    activeCategory.value = category
    openFaq.value = null
}

function clearSearch() {
    search.value = ''
    activeCategory.value = 'Todas'
    openFaq.value = null
}

// -----------------------------------------------------------------------------
// DATOS ESTRUCTURADOS
// -----------------------------------------------------------------------------

const faqSchema = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'FAQPage',
    name: seoTitle,
    description: seoDescription,
    url: canonicalUrl,
    inLanguage: 'es-MX',

    isPartOf: {
        '@type': 'WebSite',
        name: 'Industrias Husky',
        url: `${siteUrl}/`,
    },

    mainEntity: props.faqs.map((faq) => ({
        '@type': 'Question',
        name: faq.question,
        acceptedAnswer: {
            '@type': 'Answer',
            text: faq.answer,
        },
    })),
}))

const faqSchemaJson = computed(() =>
    JSON.stringify(faqSchema.value),
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
            head-key="faq-schema"
            v-html="faqSchemaJson"
        />
    </Head>

    <div class="husky-site">
        <HuskyHeader />

        <main>
            <!-- PORTADA -->
            <HuskyHero
                :banner="props.banner"
                scroll-target="#preguntas"
                min-height="60vh"
            />

            <!-- PREGUNTAS FRECUENTES -->
            <section id="preguntas" class="scroll-mt-10">
                <div
                    class="mx-auto max-w-5xl px-6 py-20 sm:px-10 lg:px-12 lg:py-28"
                >
                    <!-- ENCABEZADO -->
                    <div class="mx-auto max-w-3xl text-center">
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.2em] text-[#737d43]"
                        >
                            CENTRO DE AYUDA
                        </p>

                        <h1
                            class="mt-4 text-3xl font-medium tracking-tight text-[#25352d] sm:text-4xl lg:text-5xl"
                        >
                            ¿Cómo podemos ayudarte?
                        </h1>

                        <p
                            class="mx-auto mt-5 max-w-2xl text-sm leading-8 text-[#718075] sm:text-base"
                        >
                            Encuentra respuestas sobre nuestros productos,
                            materiales, servicios y soluciones para tus
                            proyectos.
                        </p>
                    </div>

                    <!-- BUSCADOR -->
                    <div class="relative mx-auto mt-10 max-w-2xl">
                        <Search
                            class="absolute left-5 top-1/2 h-5 w-5 -translate-y-1/2 text-[#87948a]"
                        />

                        <input
                            v-model="search"
                            type="search"
                            placeholder="¿Qué te gustaría saber?"
                            class="w-full rounded-2xl border border-[#e2e6dc] bg-white py-4 pl-14 pr-12 text-sm outline-none transition placeholder:text-[#a0aaa2] focus:border-[#737d43] focus:ring-2 focus:ring-[#737d43]/10"
                        />

                        <button
                            v-if="search"
                            type="button"
                            @click="search = ''"
                            aria-label="Limpiar búsqueda"
                            class="absolute right-4 top-1/2 -translate-y-1/2 rounded-full p-1 text-[#829087] hover:bg-[#f1f3ec]"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <!-- CONTADOR -->
                    <div
                        class="mt-14 flex items-center justify-between border-b border-[#e4e8df] pb-4"
                    >
                        <div class="flex items-center gap-3">
                            <CircleHelp
                                class="h-5 w-5 text-[#737d43]"
                                :stroke-width="1.5"
                            />

                            <h2 class="text-sm font-semibold text-[#25352d]">
                                Preguntas frecuentes
                            </h2>
                        </div>

                        <span class="text-xs text-[#829087]">
                            {{ filteredFaqs.length }}
                            {{
                                filteredFaqs.length === 1
                                    ? 'resultado'
                                    : 'resultados'
                            }}
                        </span>
                    </div>

                    <!-- RESULTADOS -->
                    <div
                        v-if="groupedFaqs.length"
                        class="mt-8 space-y-12"
                    >
                        <section
                            v-for="group in groupedFaqs"
                            :key="group.category"
                        >
                            <div class="mb-5 flex items-center gap-4">
                                <h3
                                    class="text-xs font-semibold uppercase tracking-[0.18em] text-[#737d43]"
                                >
                                    {{ group.category }}
                                </h3>

                                <span class="h-px flex-1 bg-[#e4e8df]" />

                                <span class="text-xs text-[#9aa49b]">
                                    {{ group.faqs.length }}
                                </span>
                            </div>

                            <div
                                class="divide-y divide-[#e7eae3] border-y border-[#e7eae3]"
                            >
                                <article
                                    v-for="faq in group.faqs"
                                    :key="faq.id"
                                    class="py-1"
                                >
                                    <button
                                        type="button"
                                        @click="toggleFaq(faq.id)"
                                        class="flex w-full items-center justify-between gap-6 py-6 text-left"
                                        :aria-expanded="openFaq === faq.id"
                                    >
                                        <span
                                            class="text-base font-medium leading-7 transition sm:text-lg"
                                            :class="
                                                openFaq === faq.id
                                                    ? 'text-[#737d43]'
                                                    : 'text-[#25352d]'
                                            "
                                        >
                                            {{ faq.question }}
                                        </span>

                                        <span
                                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full transition"
                                            :class="
                                                openFaq === faq.id
                                                    ? 'rotate-180 bg-[#243b30] text-white'
                                                    : 'bg-[#f1f3ec] text-[#526456]'
                                            "
                                        >
                                            <ChevronDown class="h-4 w-4" />
                                        </span>
                                    </button>

                                    <div
                                        v-if="openFaq === faq.id"
                                        class="pb-7 pr-12"
                                    >
                                        <p
                                            class="whitespace-pre-line text-sm leading-8 text-[#718075] sm:text-base"
                                        >
                                            {{ faq.answer }}
                                        </p>
                                    </div>
                                </article>
                            </div>
                        </section>
                    </div>

                    <!-- SIN RESULTADOS -->
                    <div
                        v-else
                        class="mt-8 rounded-3xl border border-dashed border-[#dce2d4] bg-[#f7f8f3] px-6 py-16 text-center"
                    >
                        <CircleHelp
                            class="mx-auto h-10 w-10 text-[#a0aa91]"
                            :stroke-width="1.2"
                        />

                        <h3 class="mt-5 text-xl font-medium text-[#25352d]">
                            No encontramos resultados
                        </h3>

                        <p
                            class="mx-auto mt-3 max-w-sm text-sm leading-7 text-[#829087]"
                        >
                            Intenta con otras palabras o selecciona una
                            categoría diferente.
                        </p>

                        <button
                            type="button"
                            @click="clearSearch"
                            class="mt-6 inline-flex items-center gap-2 rounded-full bg-[#243b30] px-5 py-3 text-sm font-medium text-white transition hover:bg-[#344e40]"
                        >
                            Limpiar filtros
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </section>
        </main>

        <HuskyFooter />
    </div>
</template>
