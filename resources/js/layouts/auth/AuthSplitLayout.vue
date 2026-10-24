<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { home } from '@/routes';

const page = usePage();
const name = page.props.name;

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="relative grid min-h-svh bg-[#f9f8f6] lg:grid-cols-2">
        <!-- PANEL VISUAL -->
        <aside
            class="relative hidden min-h-svh flex-col justify-between overflow-hidden bg-[#223A2D] p-10 text-white lg:flex xl:p-14"
        >
            <!-- Imagen de fondo -->
            <div
                class="absolute inset-0 bg-cover bg-center"
                style="
                    background-image: url('/images/husky-instalaciones.jpg');
                "
            />

            <!-- Capa verde sobre la fotografía -->
            <div
                class="absolute inset-0 bg-gradient-to-b from-[#223A2D]/85 via-[#223A2D]/65 to-[#223A2D]/95"
            />

            <!-- Decoración -->
            <div
                class="pointer-events-none absolute -right-36 -top-32 size-[440px] rounded-full border border-white/[0.08]"
            />

            <div
                class="pointer-events-none absolute -right-20 -top-16 size-[300px] rounded-full border border-white/[0.08]"
            />

            <!-- Logo de Husky -->
            <Link
                :href="home()"
                class="relative z-10 inline-flex w-fit items-center"
            >
                <img
                    src="/images/husky_blanco.webp"
                    :alt="name"
                    class="h-auto w-48 object-contain sm:w-56"
                />
            </Link>

            <!-- Mensaje principal -->
            <div class="relative z-10 max-w-xl py-12">
                <div
                    class="mb-7 inline-flex items-center gap-2.5 rounded-full border border-white/15 bg-white/[0.06] px-4 py-2"
                >
                    <span class="size-2 rounded-full bg-[#D8DFD0]" />

                    <span
                        class="text-xs font-medium tracking-[0.12em] text-[#E7E9DF]"
                    >
                        CALIDAD Y COMPROMISO
                    </span>
                </div>

                <h2
                    class="max-w-lg text-4xl font-medium leading-[1.15] tracking-tight xl:text-5xl"
                >
                    Calidad que nace
                    <span class="block text-[#D8DFD0]">
                        de la madera.
                    </span>
                </h2>

                <p class="mt-6 max-w-md text-base leading-7 text-white/75">
                    Experiencia, trabajo y compromiso en cada corte,
                    transformando la madera en soluciones para cada proyecto.
                </p>

                <div
                    class="mt-9 h-[3px] w-14 rounded-full bg-[#D8DFD0]"
                />
            </div>

            <!-- Pie del panel -->
            <div
                class="relative z-10 flex items-center justify-between gap-4 border-t border-white/10 pt-5 text-xs text-white/60"
            >
                <span>{{ name }}</span>
                <span>Acceso seguro a tu cuenta</span>
            </div>
        </aside>

        <!-- PANEL DEL FORMULARIO -->
        <main
            class="flex min-h-svh flex-col items-center justify-center px-5 py-10 sm:px-8 lg:px-10 xl:px-14"
        >
            <div class="w-full max-w-[440px]">
                <!-- Logo en móvil -->
                <Link
                    :href="home()"
                    class="mb-8 flex items-center justify-center lg:hidden"
                >
                    <img
                        src="/images/industriashusky.webp"
                        :alt="name"
                        class="h-auto w-48 object-contain"
                    />
                </Link>

                <!-- CARD DEL FORMULARIO -->
                <section
                    class="overflow-hidden rounded-2xl border border-[#005A25]/[0.12] bg-white shadow-[0_12px_45px_rgba(34,58,45,0.06)]"
                >
                    <!-- Encabezado -->
                    <div
                        class="border-b border-[#005A25]/[0.08] px-6 pb-6 pt-8 sm:px-9 sm:pt-9"
                    >

                        <h1
                            v-if="title"
                            class="text-2xl font-semibold tracking-tight text-[#34463A] sm:text-[28px]"
                        >
                            {{ title }}
                        </h1>

                        <p
                            v-if="description"
                            class="mt-2 text-sm leading-6 text-[#69766B]"
                        >
                            {{ description }}
                        </p>
                    </div>

                    <!-- Formulario original -->
                    <div
                        class="auth-form px-6 py-7 sm:px-9 sm:py-8"
                    >
                        <slot />
                    </div>
                </section>

                <!-- Pie -->
                <p
                    class="mt-7 px-3 text-center text-xs leading-5 text-[#69766B]"
                >
                    © {{ new Date().getFullYear() }} {{ name }}.
                    Todos los derechos reservados.
                </p>
            </div>
        </main>
    </div>
</template>

<style>
/* Botones verdes dentro del formulario */
.auth-form [type='submit'] {
    background-color: #3b6850;
    border-color: #3b6850;
    color: #ffffff;
    transition:
        background-color 180ms ease,
        border-color 180ms ease;
}

.auth-form [type='submit']:hover {
    background-color: #00451c;
    border-color: #00451c;
}

.auth-form [type='submit']:focus-visible {
    outline: 2px solid #3b6850;
    outline-offset: 3px;
}
/* Títulos del formulario */
.auth-form h1,
.auth-form h2,
.auth-form h3 {
    color: #223a2d;
}

/* Labels de los campos */
.auth-form label {
    color: #223a2d;
}
</style>