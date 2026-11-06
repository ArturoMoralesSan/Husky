
<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { ArrowUpRight, Menu, X } from 'lucide-vue-next'

const menuOpen = ref(false)
const activeLink = ref('')

const links = [
    { label: 'Inicio', href: '#inicio', type: 'anchor' },
    { label: 'Nosotros', href: '/nosotros', type: 'page' },
    { label: 'Productos', href: '/productos', type: 'page' },
    { label: 'Servicios', href: '/servicios', type: 'page' },
    { label: 'Proyectos', href: '#proyectos', type: 'anchor' },
    { label: 'Clientes', href: '#clientes', type: 'anchor' },
    { label: 'Contacto', href: '#contacto', type: 'anchor' },
    { label: 'FAQ', href: '/preguntas-frecuentes', type: 'page' },
]

let sectionObserver: IntersectionObserver | null = null

function closeMenu() {
    menuOpen.value = false
}

function getCurrentPage() {
    return window.location.pathname.replace(/\/$/, '') || '/'
}

function isActive(link: typeof links[number]) {
    const currentPath = getCurrentPage()

    if (link.type === 'page') {
        return currentPath === link.href
    }

    if (currentPath !== '/') {
        return false
    }

    return activeLink.value === link.href
}

function navigateToSection(hash: string) {
    closeMenu()

    if (window.location.pathname !== '/') {
        window.location.href = `/${hash}`
        return
    }

    const target = document.querySelector(hash)

    if (target) {
        activeLink.value = hash

        history.pushState(null, '', hash)

        target.scrollIntoView({
            behavior: 'smooth',
            block: 'start',
        })

        return
    }

    window.location.href = `/${hash}`
}

function navigateToPage(path: string) {
    closeMenu()

    if (getCurrentPage() === path) return

    window.location.href = path
}

async function scrollToInitialHash() {
    if (window.location.pathname !== '/') return

    const hash = window.location.hash

    if (!hash) {
        activeLink.value = '#inicio'
        return
    }

    activeLink.value = hash

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

function setupSectionObserver() {
    if (window.location.pathname !== '/') return

    sectionObserver?.disconnect()

    const sections = [
        '#inicio',
        '#proyectos',
        '#clientes',
        '#contacto',
    ]

    sectionObserver = new IntersectionObserver(
        (entries) => {
            const visibleSections = entries
                .filter((entry) => entry.isIntersecting)
                .sort(
                    (a, b) =>
                        b.intersectionRatio - a.intersectionRatio,
                )

            if (visibleSections.length > 0) {
                activeLink.value = `#${visibleSections[0].target.id}`
            }
        },
        {
            rootMargin: '-25% 0px -60% 0px',
            threshold: [0, 0.1, 0.25, 0.5],
        },
    )

    sections.forEach((selector) => {
        const section = document.querySelector(selector)

        if (section) {
            sectionObserver?.observe(section)
        }
    })
}

onMounted(async () => {
    await scrollToInitialHash()
    setupSectionObserver()
})

onUnmounted(() => {
    closeMenu()
    sectionObserver?.disconnect()
})
</script>

<template>
    <header id="inicio" class="husky-header">
        <!-- LOGO -->
        <a
            class="husky-brand"
            href="/#inicio"
            aria-label="Industrias Husky, inicio"
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
            <template v-for="link in links" :key="link.href">
                <a
                    v-if="link.type === 'page'"
                    :href="link.href"
                    :class="{ 'is-active': isActive(link) }"
                    :aria-current="isActive(link) ? 'page' : undefined"
                    @click.prevent="navigateToPage(link.href)"
                >
                    {{ link.label }}
                </a>

                <a
                    v-else
                    :href="`/${link.href}`"
                    :class="{ 'is-active': isActive(link) }"
                    :aria-current="isActive(link) ? 'location' : undefined"
                    @click.prevent="navigateToSection(link.href)"
                >
                    {{ link.label }}
                </a>
            </template>
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
