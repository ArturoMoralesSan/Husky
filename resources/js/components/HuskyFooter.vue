<script setup lang="ts">
import { Facebook, Instagram } from 'lucide-vue-next'
import { nextTick, onMounted } from 'vue'

const year = new Date().getFullYear()

const links = [
    { label: 'Inicio', href: '/' , page: true },
    { label: 'Nosotros', href: '/nosotros', page: true },
    { label: 'Productos', href: '/productos', page: true },
    { label: 'Servicios', href: '/servicios', page: true },
    { label: 'Proyectos', href: '/proyectos', page: true },
    { label: 'Clientes', href: '#clientes' },
    { label: 'Contacto', href: '#contacto' },
    { label: 'FAQ', href: '/preguntas-frecuentes', page: true },
]

function navigateToSection(hash: string, isPage = false) {
    if (isPage) {
        window.location.href = hash
        return
    }

    const landingPath = '/'

    if (window.location.pathname !== landingPath) {
        window.location.href = `${landingPath}${hash}`
        return
    }

    const target = document.querySelector(hash)

    if (target) {
        history.pushState(null, '', hash)

        target.scrollIntoView({
            behavior: 'smooth',
            block: 'start',
        })

        return
    }

    window.location.hash = hash
}

async function scrollToInitialHash() {
    if (window.location.pathname !== '/') return

    const hash = window.location.hash

    if (!hash) return

    await nextTick()

    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            const target = document.querySelector(hash)

            target?.scrollIntoView({
                behavior: 'auto',
                block: 'start',
            })
        })
    })
}

onMounted(() => {
    scrollToInitialHash()
})
</script>

<template>
    <footer class="husky-footer">
        <div class="husky-container husky-footer-main">
            <!-- LOGO -->
            <a
                class="husky-brand husky-brand-light"
                href="/#inicio"
                aria-label="Husky Industrias, inicio"
                @click.prevent="navigateToSection('#inicio')"
            >
                <img
                    class="husky-brand-logo husky-brand-logo-white"
                    src="/images/husky_blanco.webp"
                    alt="Industrias Husky"
                    loading="lazy"
                />
            </a>

            <!-- NAVEGACIÓN -->
            <nav aria-label="Navegación del pie de página">
                <a
                    v-for="link in links"
                    :key="link.href + link.label"
                    :href="link.page ? link.href : `/${link.href}`"
                    @click.prevent="
                        navigateToSection(link.href, link.page)
                    "
                >
                    {{ link.label }}
                </a>
            </nav>

            <!-- REDES SOCIALES -->
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

            <!-- VOLVER ARRIBA -->
            <a
                class="husky-back-top"
                href="/#inicio"
                @click.prevent="navigateToSection('#inicio')"
            >
                Volver arriba ↑
            </a>
        </div>

        <!-- COPYRIGHT -->
        <div class="husky-container husky-footer-bottom">
            <span>
                © {{ year }} Industrias Husky. Todos los derechos reservados.
            </span>
        </div>
    </footer>
</template>