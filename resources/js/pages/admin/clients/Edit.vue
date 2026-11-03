
<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import {
    ArrowLeft,
    ExternalLink,
    ImagePlus,
    Info,
    Link,
    Save,
    Users,
} from 'lucide-vue-next'
import { computed, onBeforeUnmount, ref } from 'vue'

import AdminButton from '@/components/admin/AdminButton.vue'
import AdminCard from '@/components/admin/AdminCard.vue'
import CancelButton from '@/components/admin/CancelButton.vue'
import PageHeader from '@/components/admin/PageHeader.vue'
import admin from '@/routes/admin'

interface ClientData {
    id: number
    name: string
    logo: string
    logo_url: string
    url: string | null
}

const props = defineProps<{
    client: ClientData
}>()

const form = useForm({
    name: props.client.name ?? '',
    logo: null as File | null,
    url: props.client.url ?? '',
})

const logoInput = ref<HTMLInputElement | null>(null)
const previewUrl = ref<string | null>(null)

const currentLogo = computed(
    () => previewUrl.value || props.client.logo_url,
)

function selectLogo(event: Event): void {
    const input = event.target as HTMLInputElement
    const file = input.files?.[0] ?? null

    if (!file) {
        return
    }

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value)
    }

    form.logo = file
    previewUrl.value = URL.createObjectURL(file)
}

function removeSelectedLogo(): void {
    form.logo = null

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value)
        previewUrl.value = null
    }

    if (logoInput.value) {
        logoInput.value.value = ''
    }
}

function submit(): void {
    form
        .transform((data) => ({
            ...data,
            _method: 'put',
            url: data.url.trim() || null,
        }))
        .post(admin.clients.update(props.client.id).url, {
            forceFormData: true,
            preserveScroll: true,
        })
}

onBeforeUnmount(() => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value)
    }
})
</script>

<template>
    <Head :title="`Editar ${client.name} | Industrias Husky`" />

    <div
        class="flex h-full flex-1 flex-col gap-5 overflow-x-hidden bg-[#F2F7F4] p-4 sm:p-5"
    >
        <PageHeader
            title="Editar cliente"
            :description="`Modifica la información de ${client.name}.`"
            section="Clientes"
            :icon="Users"
            :action-icon="ArrowLeft"
            action-label="Volver a clientes"
            :action-href="admin.clients.index().url"
        />

        <form
            class="flex flex-col gap-5"
            @submit.prevent="submit"
        >
            <AdminCard
                title="Información del cliente"
                description="Actualiza el nombre y la dirección del sitio web del cliente."
                :icon="Info"
            >
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="form-field md:col-span-2">
                        <label for="name" class="form-label">
                            Nombre del cliente
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <Users
                                class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#718477]"
                            />

                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                maxlength="255"
                                required
                                placeholder="Ej. Nombre de la empresa"
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

                    <div class="form-field md:col-span-2">
                        <label for="url" class="form-label">
                            Sitio web
                        </label>

                        <div class="relative">
                            <Link
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
                            Dirección web del cliente. Este campo es opcional.
                        </p>

                        <p v-if="form.errors.url" class="form-error">
                            {{ form.errors.url }}
                        </p>
                    </div>
                </div>
            </AdminCard>

            <AdminCard
                title="Logotipo del cliente"
                description="Visualiza el logotipo actual o selecciona uno nuevo para reemplazarlo."
                :icon="ImagePlus"
            >
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="form-field">
                        <label class="form-label">
                            Logotipo actual
                        </label>

                        <div
                            class="flex min-h-52 items-center justify-center overflow-hidden rounded-xl border border-[#DCE7DF] bg-white p-5"
                        >
                            <img
                                :src="currentLogo"
                                :alt="`Logotipo de ${client.name}`"
                                class="max-h-44 max-w-full object-contain"
                            />
                        </div>

                        <p class="form-help">
                            <template v-if="form.logo">
                                Vista previa del nuevo logotipo.
                            </template>

                            <template v-else>
                                Este es el logotipo que se encuentra guardado.
                            </template>
                        </p>
                    </div>

                    <div class="form-field">
                        <label for="logo" class="form-label">
                            Reemplazar logotipo
                        </label>

                        <label
                            for="logo"
                            class="flex min-h-52 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-[#C8D9CD] bg-white p-5 text-center transition-colors hover:border-[#187744] hover:bg-[#F8FBF8]"
                            :class="form.processing ? 'pointer-events-none opacity-60' : ''"
                        >
                            <div
                                class="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-[#E4F2E8] text-[#187744]"
                            >
                                <ImagePlus class="h-6 w-6" />
                            </div>

                            <span class="text-sm font-semibold text-[#244334]">
                                Seleccionar nuevo logotipo
                            </span>

                            <span class="mt-1 text-xs text-[#718477]">
                                JPG, JPEG, PNG o WEBP
                            </span>

                            <span class="mt-1 text-xs text-[#718477]">
                                Máximo 5 MB
                            </span>

                            <input
                                id="logo"
                                ref="logoInput"
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                class="sr-only"
                                :disabled="form.processing"
                                @change="selectLogo"
                            />
                        </label>

                        <div
                            v-if="form.logo"
                            class="mt-3 flex items-center justify-between gap-3 rounded-lg border border-[#DCE7DF] bg-white p-3"
                        >
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-[#244334]">
                                    {{ form.logo.name }}
                                </p>

                                <p class="text-xs text-[#718477]">
                                    {{ (form.logo.size / 1024 / 1024).toFixed(2) }} MB
                                </p>
                            </div>

                            <button
                                type="button"
                                class="shrink-0 text-sm font-medium text-red-600 hover:text-red-700"
                                :disabled="form.processing"
                                @click="removeSelectedLogo"
                            >
                                Quitar
                            </button>
                        </div>

                        <p v-if="form.errors.logo" class="form-error">
                            {{ form.errors.logo }}
                        </p>
                    </div>
                </div>
            </AdminCard>

            <AdminCard
                v-if="client.url"
                title="Enlace del cliente"
                description="Consulta el sitio web registrado actualmente."
                :icon="ExternalLink"
            >
                <a
                    :href="client.url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex max-w-full items-center gap-2 break-all text-sm font-medium text-[#187744] hover:text-[#125C35] hover:underline"
                >
                    {{ client.url }}
                    <ExternalLink class="h-4 w-4 shrink-0" />
                </a>
            </AdminCard>

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
                >
                    Guardar cambios
                </AdminButton>
            </div>
        </form>
    </div>
</template>
