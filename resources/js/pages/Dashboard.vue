<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    ShieldCheck,
    LogOut,
    MousePointerClick,
} from 'lucide-vue-next';
</script>

<template>
    <Head title="Inicio | Industrias Husky" />

    <div
    class="flex h-full flex-1 flex-col gap-5 overflow-x-auto bg-[#F2F7F4] p-4 sm:p-6 lg:px-8"
>
        <!-- Bienvenida principal -->
        <section
            class="relative overflow-hidden rounded-2xl border border-[#DCE7DF] bg-white p-6 sm:p-10"
        >
            <!-- Elementos decorativos -->
            <div
                class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full bg-[#EAF3ED]"
            />

            <div
                class="pointer-events-none absolute -bottom-24 right-36 h-48 w-48 rounded-full bg-[#F2F7F4]"
            />

            <div class="relative z-10 max-w-3xl">
                <p
                    class="mt-2 text-sm font-semibold uppercase tracking-[0.16em] text-[#187744]"
                >
                    Industrias Husky
                </p>

                <h1
                    class="mt-3 text-3xl font-semibold tracking-tight text-[#244334] sm:text-4xl"
                >
                    Bienvenido al panel administrativo
                </h1>
            </div>
        </section>

        <!-- Guía de uso -->
        <section class="space-y-4">
            <div>
                <h2 class="text-lg font-semibold text-[#244334]">
                    Guía de uso
                </h2>

                <p class="mt-1 text-sm leading-6 text-[#718477]">
                    Consulta estas indicaciones para utilizar el sistema
                    correctamente.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <!-- Navegación -->
                <article
                    class="rounded-2xl border border-[#DCE7DF] bg-white p-5 sm:p-6"
                >
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#EAF3ED] text-[#187744]"
                    >
                        <MousePointerClick class="h-5 w-5" />
                    </div>

                    <h3 class="mt-4 text-base font-semibold text-[#244334]">
                        Navegación del sistema
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-[#718477]">
                        Utiliza el menú lateral para acceder a los diferentes
                        módulos del sistema. Selecciona la sección que
                        necesitas y consulta las opciones disponibles según
                        los permisos de tu cuenta.
                    </p>
                </article>

                <!-- Cerrar sesión -->
                <article
                    class="rounded-2xl border border-[#DCE7DF] bg-white p-5 sm:p-6"
                >
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#FFF1EF] text-[#C62828]"
                    >
                        <LogOut class="h-5 w-5" />
                    </div>

                    <h3 class="mt-4 text-base font-semibold text-[#244334]">
                        Cómo cerrar sesión
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-[#718477]">
                        Cuando termines tus actividades, sigue estos pasos
                        para salir de tu cuenta:
                    </p>

                    <ol class="mt-4 space-y-3">
                        <li class="flex items-start gap-3">
                            <span
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#F2F7F4] text-xs font-semibold text-[#187744]"
                            >
                                1
                            </span>

                            <p class="text-sm leading-6 text-[#718477]">
                                Dirígete a la parte inferior del menú lateral.
                            </p>
                        </li>

                        <li class="flex items-start gap-3">
                            <span
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#F2F7F4] text-xs font-semibold text-[#187744]"
                            >
                                2
                            </span>

                            <p class="text-sm leading-6 text-[#718477]">
                                Localiza tu nombre o abre las opciones de tu
                                cuenta.
                            </p>
                        </li>

                        <li class="flex items-start gap-3">
                            <span
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#F2F7F4] text-xs font-semibold text-[#187744]"
                            >
                                3
                            </span>

                            <p class="text-sm leading-6 text-[#718477]">
                                Selecciona
                                <strong class="font-semibold text-[#244334]">
                                    Cerrar sesión
                                </strong>
                                para finalizar tu acceso al sistema.
                            </p>
                        </li>
                    </ol>
                </article>
            </div>
        </section>

        <!-- Seguridad -->
        <section
            class="flex items-start gap-4 rounded-2xl border border-[#DCE7DF] bg-white p-5 sm:p-6"
        >
            <div
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#EAF3ED] text-[#187744]"
            >
                <ShieldCheck class="h-5 w-5" />
            </div>

            <div>
                <h2 class="text-sm font-semibold text-[#244334]">
                    Seguridad de tu cuenta
                </h2>

                <p class="mt-1 text-sm leading-6 text-[#718477]">
                    Protege tus credenciales y evita compartirlas con otras
                    personas. Si utilizas un equipo compartido, cierra tu
                    sesión antes de retirarte para proteger la información
                    de Industrias Husky.
                </p>
            </div>
        </section>
    </div>
</template>