<script setup lang="ts">
import { Facebook, Instagram } from 'lucide-vue-next'
import { nextTick, onMounted, ref } from 'vue'

const year = new Date().getFullYear()

const links = [
    { label: 'Inicio', href: '#inicio' },
    { label: 'Nosotros', href: '#nosotros' },
    { label: 'Productos', href: '#productos' },
    { label: 'Aplicaciones', href: '#proyectos' },
    { label: 'Servicios', href: '#servicios' },
    { label: 'Proyectos', href: '#proyectos' },
    { label: 'Clientes', href: '#clientes' },
    { label: 'Contacto', href: '#contacto' },
]

async function navigateToSection(hash: string) {
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

            <nav aria-label="Navegación del pie de página">
                <a
                    v-for="link in links"
                    :key="link.href + link.label"
                    :href="`/${link.href}`"
                    @click.prevent="navigateToSection(link.href)"
                >
                    {{ link.label }}
                </a>
            </nav>

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

            <a
                class="husky-back-top"
                href="/#inicio"
                @click.prevent="navigateToSection('#inicio')"
            >
                Volver arriba ↑
            </a>
        </div>

        <div class="husky-container husky-footer-bottom">
            <span>
                © {{ year }} Industrias Husky. Todos los derechos reservados.
            </span>
        </div>
    </footer>
</template>
