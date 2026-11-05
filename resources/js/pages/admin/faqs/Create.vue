
<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import {
    ArrowLeft,
    CheckCircle2,
    CircleHelp,
    FileText,
    Hash,
    Info,
    MessageSquareText,
    Save,
    Tags,
    Type,
} from 'lucide-vue-next'

import AdminButton from '@/components/admin/AdminButton.vue'
import AdminCard from '@/components/admin/AdminCard.vue'
import CancelButton from '@/components/admin/CancelButton.vue'
import PageHeader from '@/components/admin/PageHeader.vue'
import admin from '@/routes/admin'

const form = useForm({
    question: '',
    answer: '',
    category: '',
    sort_order: 0,
    is_active: true,
})

const submit = () => {
    form.post(admin.faqs.store().url, {
        preserveScroll: true,
    })
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Panel',
                href: admin.dashboard(),
            },
            {
                title: 'Preguntas frecuentes',
                href: admin.faqs.index(),
            },
            {
                title: 'Nueva pregunta',
                href: admin.faqs.create(),
            },
        ],
    },
})
</script>

<template>
    <Head title="Nueva pregunta frecuente" />

    <div
        class="flex h-full flex-1 flex-col gap-5 overflow-x-auto bg-[#F2F7F4] p-4 sm:p-5"
    >
        <PageHeader
            title="Nueva pregunta"
            description="Registra una pregunta frecuente para orientar a los clientes de Industrias Husky."
            section="Preguntas frecuentes"
            :icon="CircleHelp"
            :action-icon="ArrowLeft"
            action-label="Volver a preguntas"
            :action-href="admin.faqs.index().url"
        />

        <form class="flex flex-col gap-5" @submit.prevent="submit">
            <!-- Información de la pregunta -->
            <AdminCard
                title="Información de la pregunta"
                description="Define el contenido que se mostrará en la sección de preguntas frecuentes."
                :icon="Info"
            >
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="form-field md:col-span-2">
                        <label for="question" class="form-label">
                            Pregunta
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <CircleHelp
                                class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#718477]"
                            />

                            <input
                                id="question"
                                v-model="form.question"
                                type="text"
                                maxlength="255"
                                placeholder="Ej. ¿Qué tipos de madera manejan?"
                                required
                                :class="[
                                    'form-input pl-11',
                                    form.errors.question && 'form-input-error',
                                ]"
                            />
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <p
                                v-if="form.errors.question"
                                class="form-error"
                            >
                                {{ form.errors.question }}
                            </p>

                            <p
                                v-else
                                class="form-help"
                            >
                                Escribe una pregunta clara y directa.
                            </p>

                            <p class="ml-auto shrink-0 text-xs text-[#718477]">
                                {{ form.question.length }}/255
                            </p>
                        </div>
                    </div>

                    <div class="form-field md:col-span-2">
                        <label for="answer" class="form-label">
                            Respuesta
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <FileText
                                class="pointer-events-none absolute left-4 top-4 h-4 w-4 text-[#718477]"
                            />

                            <textarea
                                id="answer"
                                v-model="form.answer"
                                rows="7"
                                maxlength="20000"
                                placeholder="Escribe aquí la respuesta que verá el visitante..."
                                required
                                :class="[
                                    'form-input min-h-40 resize-y pl-11',
                                    form.errors.answer && 'form-input-error',
                                ]"
                            />
                        </div>

                        <div class="flex items-start justify-between gap-3">
                            <p
                                v-if="form.errors.answer"
                                class="form-error"
                            >
                                {{ form.errors.answer }}
                            </p>

                            <p
                                v-else
                                class="form-help"
                            >
                                Proporciona información útil, clara y completa.
                            </p>

                            <p class="ml-auto shrink-0 text-xs text-[#718477]">
                                {{ form.answer.length.toLocaleString('es-MX') }}/20,000
                            </p>
                        </div>
                    </div>
                </div>
            </AdminCard>

            <!-- Organización -->
            <AdminCard
                title="Organización"
                description="Clasifica la pregunta para mantener ordenado el contenido."
                :icon="Tags"
            >
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="form-field">
                        <label for="category" class="form-label">
                            Categoría
                        </label>

                        <div class="relative">
                            <Tags
                                class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#718477]"
                            />

                            <input
                                id="category"
                                v-model="form.category"
                                type="text"
                                maxlength="100"
                                placeholder="Ej. Productos"
                                :class="[
                                    'form-input pl-11',
                                    form.errors.category && 'form-input-error',
                                ]"
                            />
                        </div>

                        <p class="form-help">
                            Ej. Productos, Servicios, Maquila o Cotizaciones.
                        </p>

                        <p v-if="form.errors.category" class="form-error">
                            {{ form.errors.category }}
                        </p>
                    </div>

                    <div class="form-field">
                        <label for="sort_order" class="form-label">
                            Orden de visualización
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <Hash
                                class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#718477]"
                            />

                            <input
                                id="sort_order"
                                v-model.number="form.sort_order"
                                type="number"
                                min="0"
                                step="1"
                                required
                                :class="[
                                    'form-input pl-11',
                                    form.errors.sort_order && 'form-input-error',
                                ]"
                            />
                        </div>

                        <p class="form-help">
                            Los valores menores aparecerán primero.
                        </p>

                        <p v-if="form.errors.sort_order" class="form-error">
                            {{ form.errors.sort_order }}
                        </p>
                    </div>
                </div>
            </AdminCard>

            <!-- Publicación -->
            <AdminCard
                title="Publicación"
                description="Controla la visibilidad de esta pregunta en el sitio web."
                :icon="CheckCircle2"
            >
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <label
                        for="is_active"
                        class="flex cursor-pointer items-start gap-3 rounded-xl border border-[#DCE7DF] bg-white p-4 transition hover:bg-[#F8FBF9]"
                    >
                        <input
                            id="is_active"
                            v-model="form.is_active"
                            type="checkbox"
                            class="mt-1 h-4 w-4 rounded border-[#A6C1B3] accent-[#187744]"
                        />

                        <span>
                            <span class="block text-sm font-semibold text-[#244334]">
                                Pregunta activa
                            </span>

                            <span class="mt-1 block text-sm leading-5 text-[#718477]">
                                La pregunta estará disponible en la sección de preguntas frecuentes.
                            </span>
                        </span>
                    </label>
                </div>

                <p v-if="form.errors.is_active" class="form-error mt-3">
                    {{ form.errors.is_active }}
                </p>
            </AdminCard>

            <!-- Recomendación -->
            <div
                class="flex items-start gap-3 rounded-xl border border-[#DCE7DF] bg-white p-4"
            >
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#EAF3ED] text-[#187744]"
                >
                    <Info class="h-5 w-5" />
                </div>

                <div>
                    <p class="text-sm font-semibold text-[#244334]">
                        Recomendación
                    </p>

                    <p class="mt-1 text-sm leading-6 text-[#718477]">
                        Agrupa las preguntas por tema y evita respuestas demasiado
                        extensas. Una buena pregunta frecuente debe resolver una
                        duda concreta del visitante.
                    </p>
                </div>
            </div>

            <!-- Acciones -->
            <div
                class="flex flex-col-reverse gap-3 border-t border-[#DCE7DF] pt-5 sm:flex-row sm:items-center sm:justify-end"
            >
                <CancelButton
                    :href="admin.faqs.index().url"
                    :icon="ArrowLeft"
                    label="Cancelar"
                />

                <AdminButton
                    type="submit"
                    variant="primary"
                    :icon="Save"
                    :loading="form.processing"
                    loading-text="Guardando..."
                >
                    Crear pregunta
                </AdminButton>
            </div>
        </form>
    </div>
</template>
