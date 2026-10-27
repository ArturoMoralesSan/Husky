<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    ArrowDown,
    ArrowRight,
    ArrowUpRight,
    Check,
    ChevronDown,
    Facebook,
    Instagram,
    Leaf,
    Menu,
    Phone,
    Play,
    ShieldCheck,
    TreeDeciduous,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue';

import '../../css/husky-landing.css';

const menuOpen = ref(false);
const videoOpen = ref(false);
const selectedProduct = ref('');
const formSent = ref(false);

const products = [
    {
        number: '01',
        title: 'Madera impregnada',
        description:
            'Madera de pino tratada para aplicaciones que requieren protección.',
        image: 'https://industriashusky.com.mx/wp-content/uploads/2016/03/instalaciones_husky4.jpg',
    },
    {
        number: '02',
        title: 'Postes eléctricos y telefónicos',
        description:
            'Postes de pino impregnado según las especificaciones del proyecto.',
        image: 'https://industriashusky.com.mx/wp-content/uploads/2016/03/2-Poste-Telefonico.jpg',
    },
    {
        number: '03',
        title: 'Vigas y gualdras',
        description:
            'Madera estructural para usos interiores y proyectos constructivos.',
        image: 'https://industriashusky.com.mx/wp-content/uploads/2016/03/Patio-4.jpg',
    },
    {
        number: '04',
        title: 'Madera para muelles y marinas',
        description:
            'Pilotes, madera y duela de acuerdo con especificaciones de ingeniería.',
        image: 'https://industriashusky.com.mx/wp-content/uploads/2016/03/8-Muelle.jpg',
    },
    {
        number: '05',
        title: 'Duelas y deck',
        description:
            'Madera para terrazas y espacios exteriores, según las necesidades de cada obra.',
        image: 'https://industriashusky.com.mx/wp-content/uploads/2016/03/3.-Duela-para-Deck.jpg',
    },
];

const services = [
    {
        number: '01',
        title: 'Impregnación a vacío-presión',
        description:
            'Tratamiento de madera con CCA-C base óxido o creosota, de acuerdo con la aplicación requerida.',
    },
    {
        number: '02',
        title: 'Procesamiento y maquila',
        description:
            'Aserrío, descortezado, torneado, cepillado S2S y S4S, duela, machimbre y crucetas.',
    },
    {
        number: '03',
        title: 'Planeación, ingeniería e instalación',
        description:
            'Diseño, cálculo estructural, planeación de materiales e instalación de proyectos de madera.',
    },
    {
        number: '04',
        title: 'Secado artificial y asesoría técnica',
        description:
            'Orientación técnica y procesos de secado para madera, vigas y gualdras de pino.',
    },
];

const clients = [
    {
        number: '01',
        name: 'CFE',
        logo: 'https://industriashusky.com.mx/wp-content/uploads/2016/12/logo_cfe-150x75.png',
    },
    {
        number: '02',
        name: 'Telmex',
        logo: 'https://industriashusky.com.mx/wp-content/uploads/2016/12/logo_telmex-150x75.png',
    },
    {
        number: '03',
        name: 'Grupo Vidanta',
        logo: 'https://industriashusky.com.mx/wp-content/uploads/2016/12/logo_grupovidanta-150x75.png',
    },
    {
        number: '04',
        name: 'Velas Vallarta',
        logo: 'https://industriashusky.com.mx/wp-content/uploads/2017/03/logo_velasvallarta-150x75.png',
    },
    {
        number: '05',
        name: 'Casa Madero',
        logo: 'https://industriashusky.com.mx/wp-content/uploads/2017/03/logo_casamadero-150x75.png',
    },
    {
        number: '06',
        name: 'Multipak',
        logo: 'https://industriashusky.com.mx/wp-content/uploads/2017/03/logo_multipak-150x75.png',
    },
    {
        number: '07',
        name: 'Marina FMH',
        logo: 'https://industriashusky.com.mx/wp-content/uploads/2017/03/logo_marinafmh-150x75.png',
    },
    {
        number: '08',
        name: 'Albon',
        logo: 'https://industriashusky.com.mx/wp-content/uploads/2017/03/logo_albon-150x75.png',
    },
    {
        number: '09',
        name: 'Equipos Eléctricos de Sinaloa',
        logo: 'https://industriashusky.com.mx/wp-content/uploads/2017/03/logo_equiposelectricosdesinaloa-150x75.png',
    },
    {
        number: '10',
        name: 'Dacesa',
        logo: 'https://industriashusky.com.mx/wp-content/uploads/2017/03/logo_dacesa-150x75.png',
    },
    {
        number: '11',
        name: 'CM Maderas',
        logo: 'https://industriashusky.com.mx/wp-content/uploads/2017/03/logo_cmmaderas-150x75.png',
    },
    {
        number: '12',
        name: 'Eléctrica Valenzuela',
        logo: 'https://industriashusky.com.mx/wp-content/uploads/2017/03/logo_electricavalenzuela-150x75.png',
    },
    {
        number: '13',
        name: 'DAESA',
        logo: 'https://industriashusky.com.mx/wp-content/uploads/2017/03/logo_daesa-150x75.png',
    },
    {
        number: '14',
        name: 'PROCOPSA',
        logo: 'https://industriashusky.com.mx/wp-content/uploads/2017/03/logo_procopsa-150x75.png',
    },
    {
        number: '15',
        name: 'Entre Otros',
        logo: 'https://industriashusky.com.mx/wp-content/uploads/2017/03/logo_entreotros-150x75.png',
    },
];

const year = new Date().getFullYear();

function closeMenu() {
    menuOpen.value = false;
}

function chooseProduct(product: string) {
    selectedProduct.value = product;
    closeMenu();
}

function submitQuote() {
    const form = document.querySelector<HTMLFormElement>('#husky-quote-form');

    if (!form || !form.reportValidity()) {
return;
}

    const data = new FormData(form);

    const subject = encodeURIComponent(
        `Solicitud de cotización — ${String(data.get('interest') || 'Proyecto Husky')}`,
    );

    const body = encodeURIComponent(
        `Hola, me gustaría solicitar información/cotización.\n\n` +
            `Nombre: ${data.get('name') || ''}\n` +
            `Empresa: ${data.get('company') || ''}\n` +
            `Correo: ${data.get('email') || ''}\n` +
            `Teléfono: ${data.get('phone') || ''}\n` +
            `Interés: ${data.get('interest') || ''}\n\n` +
            `Detalles del proyecto:\n${data.get('message') || 'Sin detalles adicionales'}\n`,
    );

    formSent.value = true;

    window.location.href =
        `mailto:info@industriashusky.com.mx?subject=${subject}&body=${body}`;
}
</script>

<template>
    <Head>
        <title>Husky Industrias | Madera que perdura</title>
        <meta
            name="description"
            content="Soluciones en madera tratada para proyectos de infraestructura, construcción, industria y espacios junto al agua. Solicita una cotización a Industrias Husky."
        />
        <meta name="theme-color" content="#f8f8f2" />
    </Head>

    <div class="husky-site">
        <!-- ENCABEZADO -->
        <header id="inicio" class="husky-header">
            <a
                class="husky-brand"
                href="#inicio"
                aria-label="Husky Industrias, inicio"
                @click="closeMenu"
            >
                <img
                    class="husky-brand-logo"
                    src="/images/industriashusky.webp"
                    alt="Industrias Husky"
                    fetchpriority="high"
                />
            </a>

            <button
                class="husky-menu-toggle"
                type="button"
                :aria-expanded="menuOpen"
                aria-label="Abrir o cerrar menú"
                @click="menuOpen = !menuOpen"
            >
                <X v-if="menuOpen" :size="21" />
                <Menu v-else :size="21" />
            </button>

            <nav
                class="husky-nav"
                :class="{ 'is-open': menuOpen }"
                aria-label="Navegación principal"
            >
                <a class="is-active" href="#inicio" @click="closeMenu">Inicio</a>
                <a href="#productos" @click="closeMenu">Productos</a>
                <a href="#servicios" @click="closeMenu">Servicios</a>
                <a href="#proyectos" @click="closeMenu">Aplicaciones</a>
                <a href="#clientes" @click="closeMenu">Clientes</a>
                <a href="#nosotros" @click="closeMenu">Nosotros</a>
                <a href="#contacto" @click="closeMenu">Contacto</a>
            </nav>

            <a
                class="husky-button husky-button-solid husky-header-cta"
                href="#contacto"
            >
                Solicitar cotización
                <ArrowUpRight :size="15" />
            </a>
        </header>

        <main>
            <!-- PORTADA -->
            <section class="husky-hero" aria-labelledby="husky-hero-title">
                <div
                    class="husky-hero-image"
                    role="img"
                    aria-label="Madera y trabajos de Industrias Husky"
                />
                <div class="husky-hero-shade" />

                <div class="husky-container husky-hero-content">
                    <p class="husky-eyebrow husky-eyebrow-light">
                        <span />
                        SOLUCIONES EN MADERA
                    </p>

                    <h1 id="husky-hero-title">
                        Madera preparada para durar.
                    </h1>

                    <p class="husky-hero-copy">
                        Madera de pino impregnada y maderas tropicales para proyectos
                        que requieren resistencia y calidad.
                    </p>

                    <div class="husky-hero-actions">
                        <a
                            class="husky-button husky-button-outline"
                            href="#productos"
                        >
                            Explorar productos
                            <ArrowRight :size="17" />
                        </a>
                    </div>
                </div>

                <a
                    class="husky-scroll-cue"
                    href="#nosotros"
                    aria-label="Desplazarse a la siguiente sección"
                >
                    <ArrowDown :size="19" />
                </a>
            </section>

            <!-- NOSOTROS -->
            <section id="nosotros" class="husky-intro husky-section">
                <div class="husky-container husky-intro-grid">
                    <div class="husky-intro-photo">
                        <img
                            src="https://industriashusky.com.mx/wp-content/uploads/2016/03/instalaciones_husky1.jpg"
                            alt="Instalaciones de tratamiento e impregnación de madera de Industrias Husky"
                            loading="lazy"
                        />
                        <span>Instalaciones y procesos de impregnación</span>
                    </div>

                    <div class="husky-intro-copy">
                        <p class="husky-eyebrow">NUESTRA EMPRESA</p>

                        <h2>Más de 50 años construyendo confianza.</h2>

                        <p class="husky-body-copy">
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
                                    <Leaf :size="22" stroke-width="1.4" />
                                </span>
                                <strong>Compromiso</strong>
                                <small>Servicio profesional</small>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- PRODUCTOS -->
            <section id="productos" class="husky-products husky-section">
                <div class="husky-container">
                    <div class="husky-section-heading">
                        <div>
                            <p class="husky-eyebrow">NUESTRAS SOLUCIONES</p>
                            <h2>Madera tratada para cada necesidad.</h2>
                        </div>

                        <a class="husky-text-link" href="#contacto">
                            Encuentra tu solución
                            <ArrowRight :size="16" />
                        </a>
                    </div>

                    <div class="husky-product-grid">
                        <a
                            v-for="product in products"
                            :key="product.number"
                            class="husky-product-card"
                            href="#contacto"
                            @click="chooseProduct(product.title)"
                        >
                            <div class="husky-product-image">
                                <img
                                    :src="product.image"
                                    :alt="product.title"
                                    loading="lazy"
                                />
                                <span>{{ product.number }}</span>
                            </div>

                            <div class="husky-product-info">
                                <h3>{{ product.title }}</h3>
                                <p>{{ product.description }}</p>
                                <ArrowUpRight
                                    class="husky-card-arrow"
                                    :size="19"
                                />
                            </div>
                        </a>
                    </div>
                </div>
            </section>

            <!-- SERVICIOS -->
            <section id="servicios" class="husky-services husky-section">
                <div class="husky-container husky-services-grid">
                    <div class="husky-services-intro">
                        <span class="husky-services-kicker">
                            <span class="husky-services-kicker-dot"></span>
                            SERVICIOS HUSKY
                        </span>

                        <h2>
                            Soluciones en madera,
                            <span>de principio a fin.</span>
                        </h2>

                        <p class="husky-services-lead">
                            Desde el tratamiento de la madera hasta la planeación
                            e instalación, conoce los procesos y servicios que
                            podemos ofrecer para tu proyecto.
                        </p>

                        <div class="husky-services-caption">
                            <Leaf :size="17" stroke-width="1.5" />
                            <span>
                                Experiencia y procesos especializados
                                en productos forestales.
                            </span>
                        </div>
                    </div>

                    <div class="husky-service-list">
                        <article
                            v-for="service in services"
                            :key="service.number"
                            class="husky-service-item"
                        >
                            <span class="husky-service-number">
                                {{ service.number }}
                            </span>

                            <div class="husky-service-content">
                                <h3>{{ service.title }}</h3>
                                <p>{{ service.description }}</p>
                            </div>

                            <span
                                class="husky-service-arrow"
                                aria-hidden="true"
                            >
                                <ArrowUpRight :size="19" />
                            </span>
                        </article>

                        <a class="husky-services-more" href="#contacto">
                            <span>¿Buscas otro servicio?</span>
                            <strong>
                                Hablemos
                                <ArrowRight :size="15" />
                            </strong>
                        </a>
                    </div>
                </div>
            </section>

            <!-- APLICACIONES -->
            <section id="proyectos" class="husky-project">
                <div class="husky-project-copy">
                    <p class="husky-eyebrow">
                        APLICACIONES DE NUESTRA MADERA
                    </p>

                    <h2>Madera para proyectos que van más allá.</h2>

                    <p>
                        Desde postes, vigas y elementos estructurales hasta madera
                        para muelles y marinas, ofrecemos productos y procesos
                        especializados para distintas necesidades de construcción
                        e infraestructura.
                    </p>

                    <a
                        class="husky-button husky-button-solid"
                        href="#contacto"
                    >
                        Encuentra tu solución
                        <ArrowRight :size="17" />
                    </a>
                </div>

                <div class="husky-project-image">
                    <img
                        src="https://industriashusky.com.mx/wp-content/uploads/2016/03/proyecto_turistico_madera.jpg"
                        alt="Muelle de madera como ejemplo de aplicación de productos forestales"
                        loading="lazy"
                    />
                </div>
            </section>

            <!-- CLIENTES -->
            <section id="clientes" class="husky-clients husky-section">
                <div class="husky-container">
                    <div class="husky-clients-heading">
                        <p class="husky-eyebrow">RELACIONES QUE PERDURAN</p>
                        <h2>La confianza se construye proyecto a proyecto.</h2>
                    </div>

                    <div class="husky-clients-grid">
                        <div
                            v-for="client in clients"
                            :key="client.number"
                            class="husky-client-logo"
                        >
                            <img
                                :src="client.logo"
                                :alt="`Logotipo de ${client.name}`"
                                loading="lazy"
                                @error="($event.target as HTMLImageElement).style.display = 'none'"
                            />

                            <span
                                class="husky-client-placeholder"
                                aria-hidden="true"
                            >
                                <TreeDeciduous :size="23" stroke-width="1.2" />
                            </span>
                        </div>
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

                        <div class="husky-contact-detail">
                            <span><Phone :size="17" /></span>
                            <div>
                                <small>Correo electrónico</small>
                                <a href="mailto:info@industriashusky.com.mx">
                                    info@industriashusky.com.mx
                                </a>
                            </div>
                        </div>

                        <div class="husky-contact-detail">
                            <span><ArrowUpRight :size="18" /></span>
                            <div>
                                <small>Atención comercial</small>
                                <p>
                                    <a href="tel:+526188357186">
                                        +52 (618) 835-7186
                                    </a>
                                    ·
                                    <a href="tel:+526188357187">
                                        +52 (618) 835-7187
                                    </a>
                                </p>
                                <p>
                                    <a href="tel:+526188357190">
                                        +52 (618) 835-7190
                                    </a>
                                </p>
                            </div>
                        </div>

                        <div class="husky-contact-detail">
                            <span><Leaf :size="17" /></span>
                            <div>
                                <small>Visítanos</small>
                                <p>
                                    Carretera Panamericana Km. 961, Durango,
                                    Dgo., C.P. 34304, México
                                </p>
                                <p>Lun. a vie. 8:00–16:00 · Sáb. 8:00–15:00</p>
                            </div>
                        </div>

                        <!-- REDES SOCIALES EN CONTACTO -->
                        <div class="husky-contact-social">
                            <div class="husky-contact-social-heading">
                                <span class="husky-contact-social-line"></span>
                                <p>CONÉCTATE CON HUSKY</p>
                            </div>

                            <h3>Sigamos en contacto.</h3>

                            <p class="husky-contact-social-copy">
                                Conoce nuestro trabajo, descubre nuestros proyectos
                                y mantente cerca de Industrias Husky.
                            </p>

                            <div class="husky-contact-social-links">
                                <a
                                    href="https://www.facebook.com/industriashusky"
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
                                    href="https://www.instagram.com/industrias.husky/"
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
                                    name="name"
                                    autocomplete="name"
                                    placeholder="¿Cómo te llamas?"
                                    required
                                />
                            </label>

                            <label>
                                Empresa
                                <input
                                    name="company"
                                    autocomplete="organization"
                                    placeholder="Nombre de tu empresa"
                                />
                            </label>
                        </div>

                        <div class="husky-form-row">
                            <label>
                                Correo electrónico
                                <input
                                    type="email"
                                    name="email"
                                    autocomplete="email"
                                    placeholder="nombre@empresa.com"
                                    required
                                />
                            </label>

                            <label>
                                Teléfono
                                <input
                                    type="tel"
                                    name="phone"
                                    autocomplete="tel"
                                    placeholder="+52"
                                />
                            </label>
                        </div>

                        <label>
                            ¿Qué solución necesitas?
                            <span class="husky-select-wrap">
                                <select
                                    v-model="selectedProduct"
                                    name="interest"
                                    required
                                >
                                    <option value="" disabled>
                                        Selecciona una opción
                                    </option>

                                    <option
                                        v-for="product in products"
                                        :key="product.number"
                                        :value="product.title"
                                    >
                                        {{ product.title }}
                                    </option>

                                    <option value="Impregnación a vacío-presión">
                                        Impregnación a vacío-presión
                                    </option>

                                    <option value="Procesamiento y maquila">
                                        Procesamiento y maquila
                                    </option>

                                    <option
                                        value="Planeación, ingeniería e instalación"
                                    >
                                        Planeación, ingeniería e instalación
                                    </option>

                                    <option
                                        value="Secado artificial y asesoría técnica"
                                    >
                                        Secado artificial y asesoría técnica
                                    </option>

                                    <option value="Otro proyecto">
                                        Otro proyecto
                                    </option>
                                </select>

                                <ChevronDown :size="16" />
                            </span>
                        </label>

                        <label>
                            Cuéntanos un poco más
                            <textarea
                                name="message"
                                rows="4"
                                placeholder="Producto, medidas, volumen, ubicación o fecha estimada…"
                            />
                        </label>

                        <button
                            class="husky-button husky-button-solid husky-submit"
                            type="submit"
                        >
                            Preparar solicitud
                            <ArrowRight :size="17" />
                        </button>

                        <p
                            v-if="formSent"
                            class="husky-form-feedback"
                            role="status"
                        >
                            Se está abriendo tu aplicación de correo. Revisa el
                            mensaje antes de enviarlo.
                        </p>
                    </form>
                </div>
            </section>
        </main>

        <!-- PIE DE PÁGINA -->
        <footer class="husky-footer">
            <div class="husky-container husky-footer-main">
                <a
                    class="husky-brand husky-brand-light"
                    href="#inicio"
                    aria-label="Husky Industrias, inicio"
                >
                    <img
                        class="husky-brand-logo husky-brand-logo-white"
                        src="/images/husky_blanco.webp"
                        alt="Industrias Husky"
                        loading="lazy"
                    />
                </a>

                <nav aria-label="Navegación del pie de página">
                    <a href="#productos">Productos</a>
                    <a href="#servicios">Servicios</a>
                    <a href="#proyectos">Aplicaciones</a>
                    <a href="#clientes">Clientes</a>
                    <a href="#nosotros">Nosotros</a>
                    <a href="#contacto">Contacto</a>
                </nav>

                <!-- REDES SOCIALES EN FOOTER -->
                <div class="husky-footer-social">
                    <a
                        href="https://www.facebook.com/industriashusky"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Facebook de Industrias Husky"
                        title="Facebook"
                    >
                        <Facebook :size="19" />
                    </a>

                    <a
                        href="https://www.instagram.com/industrias.husky/"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Instagram de Industrias Husky"
                        title="Instagram"
                    >
                        <Instagram :size="19" />
                    </a>
                </div>

                <a class="husky-back-top" href="#inicio">
                    Volver arriba ↑
                </a>
            </div>

            <div class="husky-container husky-footer-bottom">
                <span>
                    © {{ year }} Industrias Husky. Todos los derechos reservados.
                </span>

                <span>
                    Hecho para construir a largo plazo.
                    <Leaf :size="14" />
                </span>
            </div>
        </footer>

        <!-- WHATSAPP -->
        <a
            class="husky-whatsapp"
            href="https://wa.me/526188357186?text=Hola%2C%20me%20gustar%C3%ADa%20solicitar%20informaci%C3%B3n%20sobre%20sus%20productos%20y%20servicios."
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

        <!-- MODAL DE PRESENTACIÓN -->
        <div
            v-if="videoOpen"
            class="husky-video-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="husky-video-title"
            @click.self="videoOpen = false"
            @keydown.esc="videoOpen = false"
        >
            <div class="husky-video-modal-panel">
                <button
                    class="husky-modal-close"
                    type="button"
                    aria-label="Cerrar"
                    @click="videoOpen = false"
                >
                    <X :size="22" />
                </button>

                <p class="husky-eyebrow">HUSKY INDUSTRIAS</p>

                <h2 id="husky-video-title">
                    Una mirada a nuestra experiencia.
                </h2>

                <p>
                    Aquí podemos integrar el video corporativo o las tomas de dron
                    de las instalaciones. Cuando tengas el video final, se puede
                    reproducir en este espacio.
                </p>

                <a
                    class="husky-button husky-button-solid"
                    href="#contacto"
                    @click="videoOpen = false"
                >
                    Solicitar información
                    <ArrowRight :size="17" />
                </a>
            </div>
        </div>
    </div>
</template>