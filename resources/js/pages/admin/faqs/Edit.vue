
<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import {
    ArrowLeft,
    CheckCircle2,
    CircleHelp,
    FileText,
    Hash,
    Info,
    Save,
    Tags,
} from 'lucide-vue-next'

import AdminButton from '@/components/admin/AdminButton.vue'
import AdminCard from '@/components/admin/AdminCard.vue'
import CancelButton from '@/components/admin/CancelButton.vue'
import PageHeader from '@/components/admin/PageHeader.vue'
import admin from '@/routes/admin'

interface Faq {
    id: number
    question: string
    answer: string
    category: string | null
    sort_order: number
    is_active: boolean
}

const props = defineProps<{
    faq: Faq
}>()

const form = useForm({
    question: props.faq.question ?? '',
    answer: props.faq.answer ?? '',
    category: props.faq.category ?? '',
    sort_order: props.faq.sort_order ?? 0,
    is_active: Boolean(props.faq.is_active),
})

function submit(): void {
    form.put(admin.faqs.update(props.faq.id).url, {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head title="Editar pregunta frecuente" />

    <div
        class="flex h-full flex-1 flex-col gap-5 overflow-x-auto bg-[#F2F7F4] p-4 sm:p-5"
    >
        <PageHeader
            title="Editar pregunta frecuente"
            description="Actualiza la información de esta pregunta y su respuesta para el sitio web de Industrias Husky."
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
                description="Actualiza la pregunta y el contenido de su respuesta."
                :icon="Info"
            >
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <!-- Pregunta -->
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
                                required
                                placeholder="Ej. ¿Qué tipos de madera manejan?"
                                :class="[
                                    'form-input pl-11',
                                    form.errors.question && 'form-input-error',
                                ]"
                            />
                        </div>

                        <p v-if="form.errors.question" class="form-error">
                            {{ form.errors.question }}
                        </p>
                    </div>

                    <!-- Respuesta -->
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
                                rows="8"
                                maxlength="20000"
                                required
                                placeholder="Escribe una respuesta clara y completa para tus clientes."
                                :class="[
                                    'form-input min-h-40 resize-y pl-11',
                                    form.errors.answer && 'form-input-error',
                                ]"
                            />
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <p v-if="form.errors.answer" class="form-error">
                                {{ form.errors.answer }}
                            </p>

                            <p class="ml-auto text-xs text-[#718477]">
                                {{ form.answer.length.toLocaleString('es-MX') }}/20,000
                            </p>
                        </div>

                        <p class="form-help">
                            Utiliza información precisa y fácil de comprender.
                            Esta respuesta será visible para los visitantes
                            cuando la pregunta esté publicada.
                        </p>
                    </div>
                </div>
            </AdminCard>

            <!-- Organización -->
            <AdminCard
                title="Organización"
                description="Clasifica la pregunta y establece su posición dentro de la sección de preguntas frecuentes."
                :icon="Tags"
            >
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <!-- Categoría -->
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
                            Ej. Productos, Tratamientos, Servicios y maquila,
                            Proyectos o Cotizaciones.
                        </p>

                        <p v-if="form.errors.category" class="form-error">
                            {{ form.errors.category }}
                        </p>
                    </div>

                    <!-- Orden -->
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
                                    form.errors.sort_order &&
                                        'form-input-error',
                                ]"
                            />
                        </div>

                        <p class="form-help">
                            Los números menores aparecerán primero dentro de
                            su categoría.
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
                description="Controla si esta pregunta estará disponible en el sitio web."
                :icon="CheckCircle2"
            >
                <label
                    for="is_active"
                    class="flex cursor-pointer items-start gap-3 rounded-xl border border-[#DCE7DF] bg-white p-4 transition hover:border-[#B7D2C0]"
                >
                    <input
                        id="is_active"
                        v-model="form.is_active"
                        type="checkbox"
                        class="mt-1 h-4 w-4 rounded border-[#A6C1B3] accent-[#187744]"
                    />

                    <span>
                        <span
                            class="block text-sm font-semibold text-[#244334]"
                        >
                            Pregunta activa
                        </span>

                        <span class="mt-1 block text-sm text-[#718477]">
                            La pregunta podrá mostrarse en la sección pública
                            de preguntas frecuentes.
                        </span>
                    </span>
                </label>

                <p v-if="form.errors.is_active" class="form-error mt-2">
                    {{ form.errors.is_active }}
                </p>
            </AdminCard>

            <!-- Recomendación -->
            <div
                class="flex items-start gap-3 rounded-xl border border-[#DCE7DF] bg-[#F8FBF9] p-4"
            >
                <Info
                    class="mt-0.5 h-5 w-5 shrink-0 text-[#187744]"
                />

                <div>
                    <p class="text-sm font-semibold text-[#244334]">
                        Recomendación
                    </p>

                    <p class="mt-1 text-sm leading-6 text-[#718477]">
                        Mantén las respuestas actualizadas y organizadas por
                        categoría. Esto ayudará a tus visitantes a encontrar
                        rápidamente la información que necesitan.
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
                    Guardar cambios
                </AdminButton>
            </div>
        </form>
    </div>
</template>
