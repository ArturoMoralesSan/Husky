
<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { ArrowUpRight, Menu, X } from 'lucide-vue-next'

const menuOpen = ref(false)

const links = [
    { label: 'Inicio', href: '#inicio' },
    { label: 'Nosotros', href: '#nosotros' },
    { label: 'Productos', href: '#productos' },
    { label: 'Servicios', href: '#servicios' },
    { label: 'Proyectos', href: '#proyectos' },
    { label: 'Preguntas frecuentes', href: '/preguntas-frecuentes', page: true },
    { label: 'Clientes', href: '#clientes' },
    { label: 'Contacto', href: '#contacto' },
]

function closeMenu() {
    menuOpen.value = false
}

async function navigateToSection(hash: string, isPage = false) {
    closeMenu()

    // Navegación a una página independiente.
    if (isPage) {
        window.location.href = hash
        return
    }

    const landingPath = '/'

    // Si estamos en otra página, regresar a la landing.
    if (window.location.pathname !== landingPath) {
        window.location.href = `${landingPath}${hash}`
        return
    }

    // Si ya estamos en la landing, desplazarnos a la sección.
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

onUnmounted(() => {
    closeMenu()
})
</script>

<template>
    <header id="inicio" class="husky-header">
        <!-- LOGO -->
        <a
            class="husky-brand"
            href="/#inicio"
            aria-label="Husky Industrias, inicio"
            @click.prevent="navigateToSection('#inicio')"
        >
            <img
                class="husky-brand-logo"
                src="/images/industriashusky.webp"
                alt="Industrias Husky"
                fetchpriority="high"
            />
        </a>

        <!-- MENÚ MÓVIL -->
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

        <!-- NAVEGACIÓN -->
        <nav
            class="husky-nav"
            :class="{ 'is-open': menuOpen }"
            aria-label="Navegación principal"
        >
            <a
                v-for="link in links"
                :key="link.href"
                :href="link.page ? link.href : `/${link.href}`"
                :class="{
                    'is-active': link.href === '#inicio',
                }"
                @click.prevent="navigateToSection(link.href, link.page)"
            >
                {{ link.label }}
            </a>
        </nav>

        <!-- BOTÓN DE COTIZACIÓN -->
        <a
            class="husky-button husky-button-solid husky-header-cta"
            href="/#contacto"
            @click.prevent="navigateToSection('#contacto')"
        >
            Solicitar cotización
            <ArrowUpRight :size="15" />
        </a>
    </header>
</template>
