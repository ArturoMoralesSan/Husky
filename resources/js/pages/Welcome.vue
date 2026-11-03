<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3'
import {
    ArrowRight,
    ArrowUpRight,
    Check,
    CheckCircle2,
    ChevronDown,
    Facebook,
    Instagram,
    Phone,
    ShieldCheck,
    TreeDeciduous,
    X,
} from 'lucide-vue-next'
import { computed, nextTick, onMounted, ref } from 'vue'

import HuskyFooter from '@/Components/HuskyFooter.vue'
import HuskyHeader from '@/Components/HuskyHeader.vue'
import HuskyHero from '@/Components/HuskyHero.vue'
import ItemCards from '@/Components/ItemCards.vue'
import ImageGallery from '@/Components/ImageGallery.vue'

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

interface Product {
    id: number
    name: string
    slug: string
    short_description: string | null
    image_url: string | null
    image_alt: string
}

interface Service {
    id: number
    name: string
    slug: string
    description: string | null
}

interface Project {
    id: number
    name: string
    slug: string
    short_description: string | null
    image_url: string | null
    image_alt: string
}

interface Client {
    id: number
    name: string
    logo_url: string | null
}

interface Contact {
    email: string | null
    phone_primary: string | null
    phone_secondary: string | null
    phone_tertiary: string | null
    address: string | null
    whatsapp: string | null
    schedule_weekdays: string | null
    schedule_saturday: string | null
    facebook_url: string | null
    instagram_url: string | null
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
    history: string | null
    gallery_images: AboutGalleryImage[]
}

const props = defineProps<{
    banner: Banner | null
    products: Product[]
    services: Service[]
    projects: Project[]
    clients: Client[]
    contact: Contact | null
    about: About | null
}>()

// -----------------------------------------------------------------------------
// SEO
// -----------------------------------------------------------------------------

const seoTitle = 'Industrias Husky | Madera tratada y soluciones forestales'

const seoDescription =
    'En Industrias Husky ofrecemos madera impregnada, productos forestales preservados y soluciones para construcción, infraestructura, industria y proyectos especiales. Conoce nuestros productos y servicios.'

const canonicalUrl = 'https://industriashusky.com.mx/'

const ogImage = 'https://industriashusky.com.mx/images/og-default.jpg'

const organizationSchema = computed(() => ({
    '@context': 'https://schema.org',
    '@type': 'Organization',
    name: 'Industrias Husky',
    url: canonicalUrl,
    logo: ogImage,
    description: seoDescription,
    email: props.contact?.email || undefined,
    telephone: props.contact?.phone_primary || undefined,
    sameAs: [
        props.contact?.facebook_url,
        props.contact?.instagram_url,
    ].filter((url): url is string => Boolean(url)),
}))

const organizationSchemaJson = computed(() =>
    JSON.stringify(organizationSchema.value),
)

// -----------------------------------------------------------------------------
// COTIZACIONES
// -----------------------------------------------------------------------------

const selectedItem = ref('')
const toastVisible = ref(false)
const toastMessage = ref('')
const toastType = ref<'success' | 'error'>('success')

let toastTimeout: ReturnType<typeof setTimeout> | undefined

const quoteForm = useForm({
    name: '',
    company: '',
    email: '',
    phone: '',
    product_id: null as number | null,
    service_id: null as number | null,
    item_name: '',
    description: '',
})

// -----------------------------------------------------------------------------
// PRESELECCIÓN DE PRODUCTOS Y SERVICIOS DESDE LA URL
// -----------------------------------------------------------------------------

onMounted(async () => {
    const params = new URLSearchParams(window.location.search)

    // Parámetros actuales y compatibilidad con los anteriores.
    const productParam =
        params.get('producto') || params.get('product')

    const serviceParam =
        params.get('servicio') || params.get('service')

    const productId = productParam ? Number(productParam) : null
    const serviceId = serviceParam ? Number(serviceParam) : null

    // Si viene un producto en la URL, lo seleccionamos.
    if (
        productId !== null &&
        Number.isInteger(productId) &&
        productId > 0
    ) {
        const productExists = props.products.some(
            product => product.id === productId,
        )

        if (productExists) {
            selectedItem.value = `product:${productId}`
        }
    }

    // Si viene un servicio en la URL, lo seleccionamos.
    else if (
        serviceId !== null &&
        Number.isInteger(serviceId) &&
        serviceId > 0
    ) {
        const serviceExists = props.services.some(
            service => service.id === serviceId,
        )

        if (serviceExists) {
            selectedItem.value = `service:${serviceId}`
        }
    }

    // Si la URL incluye #contacto, desplazamos hasta el formulario.
    if (window.location.hash === '#contacto') {
        await nextTick()

        document.querySelector('#contacto')?.scrollIntoView({
            behavior: 'smooth',
            block: 'start',
        })
    }
})

function showToast(
    message: string,
    type: 'success' | 'error' = 'success',
) {
    if (toastTimeout) {
        clearTimeout(toastTimeout)
    }

    toastMessage.value = message
    toastType.value = type
    toastVisible.value = true

    toastTimeout = setTimeout(() => {
        toastVisible.value = false
    }, 5000)
}

function closeToast() {
    if (toastTimeout) {
        clearTimeout(toastTimeout)
    }

    toastVisible.value = false
}

function submitQuote() {
    const form = document.querySelector<HTMLFormElement>(
        '#husky-quote-form',
    )

    if (!form || !form.reportValidity()) {
        return
    }

    const [type, id] = selectedItem.value.split(':')
    const selectedId = Number(id)

    quoteForm.product_id = null
    quoteForm.service_id = null
    quoteForm.item_name = ''

    if (type === 'product' && selectedId) {
        const product = props.products.find(
            item => item.id === selectedId,
        )

        if (!product) {
            showToast(
                'El producto seleccionado ya no está disponible.',
                'error',
            )
            return
        }

        quoteForm.product_id = product.id
        quoteForm.item_name = product.name
    } else if (type === 'service' && selectedId) {
        const service = props.services.find(
            item => item.id === selectedId,
        )

        if (!service) {
            showToast(
                'El servicio seleccionado ya no está disponible.',
                'error',
            )
            return
        }

        quoteForm.service_id = service.id
        quoteForm.item_name = service.name
    } else if (selectedItem.value === 'other') {
        quoteForm.item_name = 'Otro proyecto'
    } else {
        showToast('Selecciona un producto o servicio.', 'error')
        return
    }

    quoteForm.post('/quotes', {
        preserveScroll: true,

        onSuccess: () => {
            quoteForm.reset()
            selectedItem.value = ''

            showToast(
                '¡Tu solicitud de cotización se envió correctamente! Nos pondremos en contacto contigo.',
                'success',
            )
        },

        onError: () => {
            showToast(
                'No fue posible enviar tu solicitud. Revisa los campos e inténtalo nuevamente.',
                'error',
            )
        },
    })
}

// -----------------------------------------------------------------------------
// NAVEGACIÓN A PRODUCTOS
// -----------------------------------------------------------------------------

function openProduct(product: { slug: string }) {
    if (!product.slug) return

    router.visit(`/productos/${product.slug}`)
}

// -----------------------------------------------------------------------------
// PRODUCTOS
// -----------------------------------------------------------------------------

const products = computed(() =>
    props.products.map((product, index) => ({
        id: product.id,
        slug: product.slug,
        href: `/productos/${product.slug}`,
        number: String(index + 1).padStart(2, '0'),
        title: product.name,
        description: product.short_description || '',
        image: product.image_url,
        imageAlt: product.image_alt || product.name,
    })),
)

// -----------------------------------------------------------------------------
// SERVICIOS
// -----------------------------------------------------------------------------

const services = computed(() =>
    props.services.map((service, index) => ({
        id: service.id,
        slug: service.slug,
        href: `/servicios/${service.slug}`,
        number: String(index + 1).padStart(2, '0'),
        title: service.name,
        description: service.description || '',
    })),
)

// -----------------------------------------------------------------------------
// PROYECTOS
// -----------------------------------------------------------------------------

const projectItems = computed(() =>
    props.projects.map((project, index) => ({
        id: project.id,
        slug: project.slug,
        href: `/proyectos/${project.slug}`,
        number: String(index + 1).padStart(2, '0'),
        title: project.name,
        description: project.short_description || '',
        image: project.image_url,
        imageAlt: project.image_alt || project.name,
    })),
)

// -----------------------------------------------------------------------------
// GALERÍA NOSOTROS
// -----------------------------------------------------------------------------

const nosotrosGallery = computed(() =>
    (props.about?.gallery_images ?? [])
        .slice()
        .sort((a, b) => a.sort_order - b.sort_order)
        .map(image => ({
            src: image.url,
            alt: image.alt || 'Galería de Industrias Husky',
        })),
)

// -----------------------------------------------------------------------------
// CONTACTO
// -----------------------------------------------------------------------------

const contactEmail = computed(
    () => props.contact?.email || 'info@industriashusky.com.mx',
)

const contactPhones = computed(() =>
    [
        props.contact?.phone_primary,
        props.contact?.phone_secondary,
        props.contact?.phone_tertiary,
    ].filter((phone): phone is string => Boolean(phone)),
)

const whatsappNumber = computed(() => {
    const number =
        props.contact?.whatsapp ||
        props.contact?.phone_primary ||
        ''

    return number.replace(/\D/g, '')
})

const whatsappUrl = computed(() => {
    const message = encodeURIComponent(
        'Hola, me gustaría solicitar información sobre sus productos y servicios.',
    )

    return whatsappNumber.value
        ? `https://wa.me/${whatsappNumber.value}?text=${message}`
        : null
})

function phoneUrl(phone: string) {
    return `tel:${phone.replace(/[^\d+]/g, '')}`
}
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
            content="Industrias Husky, soluciones en madera tratada"
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
            head-key="organization-schema"
            v-html="organizationSchemaJson"
        />
    </Head>

    <div class="husky-site">
        <HuskyHeader />

        <main>
            <!-- PORTADA -->
            <HuskyHero
                :banner="props.banner"
                scroll-target="#nosotros"
                min-height="80vh"
            />

            <!-- NOSOTROS -->
            <section id="nosotros" class="husky-intro husky-section">
                <div class="husky-container husky-intro-grid">
                    <div class="husky-intro-photo">
                        <ImageGallery
                            v-if="nosotrosGallery.length"
                            :items="nosotrosGallery"
                            :autoplay="true"
                            :interval="4000"
                        />
                    </div>

                    <div class="husky-intro-copy">
                        <p class="husky-eyebrow">NUESTRA EMPRESA</p>

                        <h2>Más de 50 años construyendo confianza.</h2>

                        <p
                            v-if="props.about?.history"
                            class="husky-body-copy"
                        >
                            {{ props.about.history }}
                        </p>

                        <p
                            v-else
                            class="husky-body-copy"
                        >
                            Suministramos productos forestales preservados y madera
                            impregnada. Integramos procesamiento, secado, tratamiento,
                            asesoría e instalación para proyectos habitacionales,
                            industriales, turísticos, agropecuarios y de infraestructura.
                        </p>

                        <div class="husky-values">
                            <div class="husky-value">
                                <span>
                                    <ShieldCheck :size="22" stroke-width="1.4" />
                                </span>
                                <strong>Experiencia</strong>
                                <small>Conocimiento que respalda</small>
                            </div>

                            <div class="husky-value">
                                <span>
                                    <Check :size="22" stroke-width="1.4" />
                                </span>
                                <strong>Calidad</strong>
                                <small>Procesos controlados</small>
                            </div>

                            <div class="husky-value">
                                <span>
                                    <TreeDeciduous :size="22" stroke-width="1.4" />
                                </span>
                                <strong>A la medida</strong>
                                <small>Soluciones para cada proyecto</small>
                            </div>

                            <div class="husky-value">
                                <span>
                                    <TreeDeciduous :size="22" stroke-width="1.4" />
                                </span>
                                <strong>Compromiso</strong>
                                <small>Servicio profesional</small>
                            </div>
                        </div>

                        <div class="husky-intro-actions mb-4">
                            <a
                                href="/nosotros"
                                class="husky-button husky-button-solid"
                            >
                                Conocer más
                                <ArrowRight :size="17" />
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- PRODUCTOS -->
            <ItemCards
                id="productos"
                variant="grid"
                :items="products"
                eyebrow="NUESTRAS SOLUCIONES"
                title="Madera tratada para cada necesidad."
                link-text="Encuentra tu solución"
                link-href="/productos"
                empty-text="Estamos preparando nuestro catálogo de productos."
                @select="openProduct"
            />

            <!-- SERVICIOS -->
            <ItemCards
                id="servicios"
                variant="list"
                :items="services"
                eyebrow="SERVICIOS HUSKY"
                title="Soluciones en madera, de principio a fin."
                link-text="Conoce más"
                link-href="/servicios"
                empty-text="Estamos preparando nuestro catálogo de servicios."
            >
                <template #intro>
                    <p class="husky-services-lead">
                        Desde el tratamiento de la madera hasta la planeación
                        e instalación, conoce los procesos y servicios que
                        podemos ofrecer para tu proyecto.
                    </p>
                </template>

                <template #caption>
                    <div class="husky-services-caption">
                        <TreeDeciduous :size="17" stroke-width="1.5" />
                        <span>
                            Experiencia y procesos especializados
                            en productos forestales.
                        </span>
                    </div>
                </template>

                <template #footer>
                    ¿Buscas otro servicio?
                </template>
            </ItemCards>

            <!-- PROYECTOS -->
            <ItemCards
                id="proyectos"
                variant="featured"
                eyebrow="NUESTROS PROYECTOS"
                title="Experiencia que transforma la madera"
                :items="projectItems"
                link-href="/proyectos"
                link-text="Conocer todos los proyectos"
                empty-text="Estamos preparando nuestra galería de proyectos."
            />

            <!-- CLIENTES -->
            <section id="clientes" class="husky-clients husky-section">
                <div class="husky-container">
                    <div class="husky-clients-heading">
                        <p class="husky-eyebrow">RELACIONES QUE PERDURAN</p>
                        <h2>La confianza se construye proyecto a proyecto.</h2>
                    </div>

                    <div
                        v-if="props.clients.length"
                        class="husky-clients-grid"
                    >
                        <div
                            v-for="client in props.clients"
                            :key="client.id"
                            class="husky-client-logo"
                        >
                            <img
                                v-if="client.logo_url"
                                :src="client.logo_url"
                                :alt="`Logotipo de ${client.name}`"
                                loading="lazy"
                            />

                            <span
                                v-else
                                class="husky-client-placeholder"
                                aria-hidden="true"
                            >
                                <TreeDeciduous :size="23" stroke-width="1.2" />
                            </span>
                        </div>
                    </div>

                    <div v-else class="husky-products-empty">
                        <TreeDeciduous :size="34" stroke-width="1.3" />
                        <p>Estamos preparando nuestra lista de clientes.</p>
                    </div>
                </div>
            </section>

            <!-- CONTACTO -->
            <section id="contacto" class="husky-contact husky-section">
                <div class="husky-container husky-contact-grid">
                    <div class="husky-contact-copy">
                        <p class="husky-eyebrow">
                            ¿TIENES UN PROYECTO EN MENTE?
                        </p>

                        <h2>Hagámoslo realidad,<br />paso a paso.</h2>

                        <p>
                            Cuéntanos qué necesitas. Nuestro equipo puede ayudarte
                            a encontrar una solución para tu proyecto.
                        </p>

                        <!-- CORREO -->
                        <div
                            v-if="contactEmail"
                            class="husky-contact-detail"
                        >
                            <span><Phone :size="17" /></span>
                            <div>
                                <small>Correo electrónico</small>
                                <a :href="`mailto:${contactEmail}`">
                                    {{ contactEmail }}
                                </a>
                            </div>
                        </div>

                        <!-- TELÉFONOS -->
                        <div
                            v-if="contactPhones.length"
                            class="husky-contact-detail"
                        >
                            <span><ArrowUpRight :size="18" /></span>
                            <div>
                                <small>Atención comercial</small>

                                <p
                                    v-for="(phone, index) in contactPhones"
                                    :key="index"
                                >
                                    <a :href="phoneUrl(phone)">
                                        {{ phone }}
                                    </a>
                                </p>
                            </div>
                        </div>

                        <!-- DIRECCIÓN Y HORARIOS -->
                        <div
                            v-if="
                                props.contact?.address ||
                                props.contact?.schedule_weekdays ||
                                props.contact?.schedule_saturday
                            "
                            class="husky-contact-detail"
                        >
                            <span><TreeDeciduous :size="17" /></span>
                            <div>
                                <small>Visítanos</small>

                                <p v-if="props.contact?.address">
                                    {{ props.contact.address }}
                                </p>

                                <p v-if="props.contact?.schedule_weekdays">
                                    Lun. a vie. {{ props.contact.schedule_weekdays }}
                                </p>

                                <p v-if="props.contact?.schedule_saturday">
                                    Sábado {{ props.contact.schedule_saturday }}
                                </p>
                            </div>
                        </div>

                        <!-- REDES SOCIALES -->
                        <div
                            v-if="
                                props.contact?.facebook_url ||
                                props.contact?.instagram_url
                            "
                            class="husky-contact-social"
                        >
                            <div class="husky-contact-social-heading">
                                <span class="husky-contact-social-line"></span>
                                <p>CONÉCTATE CON INDUSTRIAS HUSKY</p>
                            </div>

                            <h3>Sigamos en contacto.</h3>

                            <p class="husky-contact-social-copy">
                                Conoce nuestro trabajo, descubre nuestros proyectos
                                y mantente cerca de Industrias Husky.
                            </p>

                            <div class="husky-contact-social-links">
                                <a
                                    v-if="props.contact?.facebook_url"
                                    :href="props.contact.facebook_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="husky-contact-social-card"
                                    aria-label="Visitar Facebook de Industrias Husky"
                                >
                                    <span class="husky-contact-social-icon">
                                        <Facebook :size="23" :stroke-width="1.7" />
                                    </span>

                                    <span class="husky-contact-social-info">
                                        <strong>Facebook</strong>
                                        <small>Conoce nuestras novedades</small>
                                    </span>

                                    <ArrowUpRight
                                        :size="18"
                                        class="husky-contact-social-arrow"
                                    />
                                </a>

                                <a
                                    v-if="props.contact?.instagram_url"
                                    :href="props.contact.instagram_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="husky-contact-social-card"
                                    aria-label="Visitar Instagram de Industrias Husky"
                                >
                                    <span class="husky-contact-social-icon">
                                        <Instagram :size="23" :stroke-width="1.7" />
                                    </span>

                                    <span class="husky-contact-social-info">
                                        <strong>Instagram</strong>
                                        <small>Descubre nuestro trabajo</small>
                                    </span>

                                    <ArrowUpRight
                                        :size="18"
                                        class="husky-contact-social-arrow"
                                    />
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- FORMULARIO DE COTIZACIÓN -->
                    <form
                        id="husky-quote-form"
                        class="husky-quote-form"
                        @submit.prevent="submitQuote"
                    >
                        <div class="husky-form-row">
                            <label>
                                Nombre completo
                                <input
                                    v-model="quoteForm.name"
                                    name="name"
                                    autocomplete="name"
                                    placeholder="¿Cómo te llamas?"
                                    required
                                />
                                <small
                                    v-if="quoteForm.errors.name"
                                    class="husky-form-error"
                                >
                                    {{ quoteForm.errors.name }}
                                </small>
                            </label>

                            <label>
                                Empresa
                                <input
                                    v-model="quoteForm.company"
                                    name="company"
                                    autocomplete="organization"
                                    placeholder="Nombre de tu empresa"
                                />
                                <small
                                    v-if="quoteForm.errors.company"
                                    class="husky-form-error"
                                >
                                    {{ quoteForm.errors.company }}
                                </small>
                            </label>
                        </div>

                        <div class="husky-form-row">
                            <label>
                                Correo electrónico
                                <input
                                    v-model="quoteForm.email"
                                    type="email"
                                    name="email"
                                    autocomplete="email"
                                    placeholder="nombre@empresa.com"
                                    required
                                />
                                <small
                                    v-if="quoteForm.errors.email"
                                    class="husky-form-error"
                                >
                                    {{ quoteForm.errors.email }}
                                </small>
                            </label>

                            <label>
                                Teléfono
                                <input
                                    v-model="quoteForm.phone"
                                    type="tel"
                                    name="phone"
                                    autocomplete="tel"
                                    placeholder="+52"
                                    required
                                />
                                <small
                                    v-if="quoteForm.errors.phone"
                                    class="husky-form-error"
                                >
                                    {{ quoteForm.errors.phone }}
                                </small>
                            </label>
                        </div>

                        <label>
                            ¿Qué solución necesitas?

                            <span class="husky-select-wrap">
                                <select
                                    v-model="selectedItem"
                                    name="interest"
                                    required
                                >
                                    <option value="" disabled>
                                        Selecciona una opción
                                    </option>

                                    <optgroup
                                        v-if="props.products.length"
                                        label="Productos"
                                    >
                                        <option
                                            v-for="product in props.products"
                                            :key="`product-${product.id}`"
                                            :value="`product:${product.id}`"
                                        >
                                            {{ product.name }}
                                        </option>
                                    </optgroup>

                                    <optgroup
                                        v-if="props.services.length"
                                        label="Servicios"
                                    >
                                        <option
                                            v-for="service in props.services"
                                            :key="`service-${service.id}`"
                                            :value="`service:${service.id}`"
                                        >
                                            {{ service.name }}
                                        </option>
                                    </optgroup>

                                    <option value="other">
                                        Otro proyecto
                                    </option>
                                </select>

                                <ChevronDown :size="16" />
                            </span>

                            <small
                                v-if="quoteForm.errors.product_id"
                                class="husky-form-error"
                            >
                                {{ quoteForm.errors.product_id }}
                            </small>

                            <small
                                v-if="quoteForm.errors.service_id"
                                class="husky-form-error"
                            >
                                {{ quoteForm.errors.service_id }}
                            </small>

                            <small
                                v-if="quoteForm.errors.item_name"
                                class="husky-form-error"
                            >
                                {{ quoteForm.errors.item_name }}
                            </small>
                        </label>

                        <label>
                            Cuéntanos un poco más
                            <textarea
                                v-model="quoteForm.description"
                                name="message"
                                rows="4"
                                placeholder="Producto, medidas, volumen, ubicación o fecha estimada…"
                            />
                            <small
                                v-if="quoteForm.errors.description"
                                class="husky-form-error"
                            >
                                {{ quoteForm.errors.description }}
                            </small>
                        </label>

                        <button
                            class="husky-button husky-button-solid husky-submit"
                            type="submit"
                            :disabled="quoteForm.processing"
                        >
                            {{
                                quoteForm.processing
                                    ? 'Enviando solicitud...'
                                    : 'Enviar solicitud'
                            }}

                            <ArrowRight
                                v-if="!quoteForm.processing"
                                :size="17"
                            />
                        </button>
                    </form>
                </div>
            </section>
        </main>

        <HuskyFooter />

        <!-- TOAST DE NOTIFICACIÓN -->
        <Transition name="husky-toast">
            <div
                v-if="toastVisible"
                class="husky-toast"
                :class="`husky-toast-${toastType}`"
                role="status"
                aria-live="polite"
            >
                <span class="husky-toast-icon">
                    <CheckCircle2
                        v-if="toastType === 'success'"
                        :size="22"
                    />
                    <X
                        v-else
                        :size="22"
                    />
                </span>

                <div class="husky-toast-content">
                    <strong>
                        {{
                            toastType === 'success'
                                ? '¡Solicitud enviada!'
                                : 'No se pudo enviar'
                        }}
                    </strong>
                    <p>{{ toastMessage }}</p>
                </div>

                <button
                    type="button"
                    class="husky-toast-close"
                    aria-label="Cerrar notificación"
                    @click="closeToast"
                >
                    <X :size="17" />
                </button>
            </div>
        </Transition>

        <!-- WHATSAPP -->
        <a
            v-if="whatsappUrl"
            class="husky-whatsapp"
            :href="whatsappUrl"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Contactar a Industrias Husky por WhatsApp"
            title="Escríbenos por WhatsApp"
        >
            <svg
                viewBox="0 0 32 32"
                width="29"
                height="29"
                fill="currentColor"
                aria-hidden="true"
            >
                <path
                    d="M16.04 3C8.88 3 3.05 8.81 3.05 15.95c0 2.28.6 4.5 1.73 6.46L3 29l6.78-1.77a13.1 13.1 0 0 0 6.25 1.59h.01c7.15 0 12.98-5.81 12.98-12.96A12.9 12.9 0 0 0 16.04 3Zm0 23.64c-1.95 0-3.87-.53-5.54-1.53l-.4-.24-4.02 1.05 1.07-3.91-.26-.41a10.63 10.63 0 0 1-1.64-5.65c0-5.95 4.84-10.79 10.8-10.79 2.88 0 5.59 1.12 7.62 3.16a10.72 10.72 0 0 1 3.16 7.63c0 5.95-4.84 10.79-10.79 10.79Zm5.92-8.08c-.32-.16-1.9-.94-2.2-1.05-.3-.11-.51-.16-.72.16-.21.32-.83 1.05-1.02 1.26-.19.21-.38.24-.7.08-.32-.16-1.35-.5-2.57-1.59-.95-.85-1.59-1.9-1.78-2.22-.19-.32-.02-.49.14-.65.14-.14.32-.38.48-.56.16-.19.21-.32.32-.54.11-.21.05-.4-.03-.56-.08-.16-.72-1.74-.99-2.38-.26-.62-.52-.53-.72-.54h-.62c-.21 0-.56.08-.86.4-.3.32-1.13 1.1-1.13 2.68s1.16 3.1 1.32 3.32c.16.21 2.28 3.48 5.53 4.88.77.33 1.37.52 1.84.66.77.24 1.47.21 2.02.13.62-.09 1.9-.78 2.17-1.53.27-.75.27-1.39.19-1.53-.08-.14-.3-.22-.62-.38Z"
                />
            </svg>

            <span class="husky-whatsapp-tooltip">
                ¿Hablamos de tu proyecto?
            </span>
        </a>
    </div>
</template>