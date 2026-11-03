
<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import {
    ArrowLeft,
    ExternalLink,
    ImagePlus,
    Info,
    Save,
    Type,
    Users,
    X,
} from 'lucide-vue-next'
import { computed, onBeforeUnmount, ref } from 'vue'

import AdminButton from '@/components/admin/AdminButton.vue'
import AdminCard from '@/components/admin/AdminCard.vue'
import CancelButton from '@/components/admin/CancelButton.vue'
import PageHeader from '@/components/admin/PageHeader.vue'
import admin from '@/routes/admin'

const form = useForm({
    name: '',
    logo: null as File | null,
    url: '',
})

const logoInput = ref<HTMLInputElement | null>(null)
const logoPreview = ref<string | null>(null)

const logoName = computed(() => form.logo?.name ?? '')

function selectLogo(event: Event): void {
    const input = event.target as HTMLInputElement
    const file = input.files?.[0] ?? null

    if (!file) return

    if (logoPreview.value) {
        URL.revokeObjectURL(logoPreview.value)
    }

    form.logo = file
    logoPreview.value = URL.createObjectURL(file)
}

function removeLogo(): void {
    if (logoPreview.value) {
        URL.revokeObjectURL(logoPreview.value)
    }

    logoPreview.value = null
    form.logo = null

    if (logoInput.value) {
        logoInput.value.value = ''
    }
}

function submit(): void {
    form
        .transform((data) => ({
            ...data,
            name: data.name.trim(),
            url: data.url.trim() || null,
        }))
        .post(admin.clients.store().url, {
            forceFormData: true,
            preserveScroll: true,
        })
}

onBeforeUnmount(() => {
    if (logoPreview.value) {
        URL.revokeObjectURL(logoPreview.value)
    }
})
</script>

<template>
    <Head title="Crear cliente | Industrias Husky" />

    <div
        class="flex h-full flex-1 flex-col gap-5 overflow-x-auto bg-[#F2F7F4] p-4 sm:p-5"
    >
        <PageHeader
            title="Crear cliente"
            description="Registra un cliente para mostrar su logotipo en el sitio web de Industrias Husky."
            section="Clientes"
            :icon="Users"
            :action-icon="ArrowLeft"
            action-label="Volver a clientes"
            :action-href="admin.clients.index().url"
        />

        <form class="flex flex-col gap-5" @submit.prevent="submit">
            <AdminCard
                title="Información del cliente"
                description="Ingresa el nombre y los datos del sitio web del cliente."
                :icon="Info"
            >
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <!-- Nombre -->
                    <div class="form-field md:col-span-2">
                        <label for="name" class="form-label">
                            Nombre del cliente
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <Type
                                class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#718477]"
                            />

                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                maxlength="255"
                                required
                                placeholder="Ej. Empresa del cliente"
                                :class="[
                                    'form-input pl-11',
                                    form.errors.name && 'form-input-error',
                                ]"
                            />
                        </div>

                        <p v-if="form.errors.name" class="form-error">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- URL -->
                    <div class="form-field md:col-span-2">
                        <label for="url" class="form-label">
                            Sitio web
                        </label>

                        <div class="relative">
                            <ExternalLink
                                class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#718477]"
                            />

                            <input
                                id="url"
                                v-model="form.url"
                                type="url"
                                maxlength="255"
                                placeholder="https://www.ejemplo.com"
                                :class="[
                                    'form-input pl-11',
                                    form.errors.url && 'form-input-error',
                                ]"
                            />
                        </div>

                        <p class="form-help">
                            Ingresa la dirección completa del sitio web.
                            Este campo es opcional.
                        </p>

                        <p v-if="form.errors.url" class="form-error">
                            {{ form.errors.url }}
                        </p>
                    </div>
                </div>
            </AdminCard>

            <!-- Logotipo -->
            <AdminCard
                title="Logotipo del cliente"
                description="Selecciona la imagen que identificará al cliente en el sitio web."
                :icon="ImagePlus"
            >
                <div class="grid grid-cols-1 gap-5 md:grid-cols-[220px_1fr]">
                    <!-- Vista previa -->
                    <div
                        class="flex min-h-44 items-center justify-center overflow-hidden rounded-xl border border-[#DCE7DF] bg-white p-4"
                    >
                        <img
                            v-if="logoPreview"
                            :src="logoPreview"
                            :alt="form.name || 'Vista previa del logotipo'"
                            class="max-h-36 max-w-full object-contain"
                        />

                        <div
                            v-else
                            class="flex flex-col items-center gap-2 text-[#87958B]"
                        >
                            <ImagePlus class="h-9 w-9" />

                            <span class="text-xs font-medium">
                                Vista previa
                            </span>
                        </div>
                    </div>

                    <!-- Selector -->
                    <div class="flex flex-col justify-center gap-3">
                        <div>
                            <p class="text-sm font-semibold text-[#244334]">
                                Imagen del cliente
                                <span class="text-red-500">*</span>
                            </p>

                            <p class="mt-1 text-sm text-[#718477]">
                                Selecciona el logotipo en formato JPG, PNG o
                                WEBP. Tamaño máximo: 5 MB.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <input
                                ref="logoInput"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                class="hidden"
                                @change="selectLogo"
                            />

                            <button
                                type="button"
                                :disabled="form.processing"
                                class="inline-flex items-center gap-2 rounded-lg border border-[#C8DCCF] bg-[#E9F4EC] px-4 py-2.5 text-sm font-semibold text-[#187744] transition-colors hover:bg-[#DDEEE2] disabled:cursor-not-allowed disabled:opacity-50"
                                @click="logoInput?.click()"
                            >
                                <ImagePlus class="h-4 w-4" />
                                {{ logoPreview ? 'Cambiar imagen' : 'Seleccionar imagen' }}
                            </button>

                            <button
                                v-if="logoPreview"
                                type="button"
                                :disabled="form.processing"
                                class="inline-flex items-center gap-2 rounded-lg border border-[#F2D1CD] bg-[#FFF1EF] px-4 py-2.5 text-sm font-semibold text-[#C62828] transition-colors hover:bg-[#FCE5E2] disabled:opacity-50"
                                @click="removeLogo"
                            >
                                <X class="h-4 w-4" />
                                Quitar
                            </button>
                        </div>

                        <p
                            v-if="logoName"
                            class="break-all text-xs text-[#718477]"
                        >
                            Archivo seleccionado: {{ logoName }}
                        </p>

                        <p v-if="form.errors.logo" class="form-error">
                            {{ form.errors.logo }}
                        </p>
                    </div>
                </div>
            </AdminCard>

            <!-- Acciones -->
            <div
                class="flex flex-col-reverse gap-3 border-t border-[#DCE7DF] pt-5 sm:flex-row sm:items-center sm:justify-end"
            >
                <CancelButton
                    :href="admin.clients.index().url"
                    :icon="ArrowLeft"
                    label="Cancelar"
                />

                <AdminButton
                    type="submit"
                    variant="primary"
                    :icon="Save"
                    :loading="form.processing"
                    loading-text="Guardando..."
                    :disabled="!form.logo"
                >
                    Crear cliente
                </AdminButton>
            </div>
        </form>
    </div>
</template>
