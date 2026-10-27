
<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { onUnmounted, ref } from 'vue';
import {
    ArrowLeft,
    CalendarClock,
    ChevronDown,
    ExternalLink,
    FileImage,
    Hash,
    ImagePlus,
    Info,
    Link as LinkIcon,
    Save,
    Search,
    Type,
    Video,
} from 'lucide-vue-next';

import AdminButton from '@/components/admin/AdminButton.vue';
import AdminCard from '@/components/admin/AdminCard.vue';
import CancelButton from '@/components/admin/CancelButton.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import admin from '@/routes/admin';

interface Props {
    positions: Record<string, string>;
}

const props = defineProps<Props>();

const form = useForm({
    title: '',
    subtitle: '',
    description: '',
    image: null as File | null,
    alt_text: '',
    link_url: '',
    button_text: '',
    open_in_new_tab: false,
    position: Object.keys(props.positions)[0] ?? 'home',
    sort_order: 0,
    is_active: true,
    starts_at: '',
    ends_at: '',
    seo_title: '',
    seo_description: '',
});

const mediaPreview = ref<string | null>(null);
const mediaIsVideo = ref(false);
const mediaName = ref('');

function handleImageChange(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    if (mediaPreview.value) {
        URL.revokeObjectURL(mediaPreview.value);
    }

    mediaPreview.value = null;
    mediaIsVideo.value = false;
    mediaName.value = '';
    form.image = file;

    if (!file) {
        return;
    }

    mediaPreview.value = URL.createObjectURL(file);
    mediaIsVideo.value = file.type.startsWith('video/');
    mediaName.value = file.name;
}

function submit(): void {
    form.transform((data) => ({
        ...data,
        starts_at: data.starts_at || null,
        ends_at: data.ends_at || null,
    })).post(admin.banners.store().url, {
        forceFormData: true,
    });
}

onUnmounted(() => {
    if (mediaPreview.value) {
        URL.revokeObjectURL(mediaPreview.value);
    }
});
</script>

<template>
    <Head title="Nuevo banner" />

    <div
        class="flex h-full flex-1 flex-col gap-5 overflow-x-auto bg-[#F2F7F4] p-4 sm:p-5"
    >
        <PageHeader
            title="Nuevo banner"
            description="Crea y configura un banner para mostrarlo en tu sitio web."
            section="Banners"
            :icon="FileImage"
            :action-icon="ArrowLeft"
            action-label="Volver a banners"
            :action-href="admin.banners.index().url"
        />

        <form class="flex flex-col gap-5" @submit.prevent="submit">
            <AdminCard
                title="Información del banner"
                description="Define el contenido que aparecerá en el banner."
                :icon="Info"
            >
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="form-field">
                        <label for="title" class="form-label">
                            Título <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <Type
                                class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#718477]"
                            />

                            <input
                                id="title"
                                v-model="form.title"
                                type="text"
                                maxlength="255"
                                placeholder="Ej. Bienvenido a Husky"
                                required
                                :class="[
                                    'form-input pl-11',
                                    form.errors.title && 'form-input-error',
                                ]"
                            />
                        </div>

                        <p v-if="form.errors.title" class="form-error">
                            {{ form.errors.title }}
                        </p>
                    </div>

                    <div class="form-field">
                        <label for="subtitle" class="form-label">
                            Subtítulo
                        </label>

                        <div class="relative">
                            <Type
                                class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#718477]"
                            />

                            <input
                                id="subtitle"
                                v-model="form.subtitle"
                                type="text"
                                maxlength="255"
                                placeholder="Texto secundario del banner"
                                :class="[
                                    'form-input pl-11',
                                    form.errors.subtitle && 'form-input-error',
                                ]"
                            />
                        </div>

                        <p v-if="form.errors.subtitle" class="form-error">
                            {{ form.errors.subtitle }}
                        </p>
                    </div>

                    <div class="form-field md:col-span-2">
                        <label for="description" class="form-label">
                            Descripción
                        </label>

                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="4"
                            placeholder="Describe el contenido del banner..."
                            :class="[
                                'form-input min-h-28 resize-y',
                                form.errors.description && 'form-input-error',
                            ]"
                        />

                        <p v-if="form.errors.description" class="form-error">
                            {{ form.errors.description }}
                        </p>
                    </div>
                </div>
            </AdminCard>

            <AdminCard
                title="Imagen o video del banner"
                description="Selecciona una imagen o un video para mostrar en el sitio."
                :icon="ImagePlus"
            >
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="form-field md:col-span-2">
                        <label for="image" class="form-label">
                            Archivo multimedia
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            id="image"
                            type="file"
                            accept=".jpg,.jpeg,.png,.webp,.avif,.mp4,.webm,.mov,image/jpeg,image/png,image/webp,image/avif,video/mp4,video/webm,video/quicktime"
                            required
                            :class="[
                                'form-input file:mr-4 file:rounded-lg file:border-0 file:bg-[#E6F1E9] file:px-4 file:py-2 file:font-medium file:text-[#187744] hover:file:bg-[#D9EBDD]',
                                form.errors.image && 'form-input-error',
                            ]"
                            @change="handleImageChange"
                        />

                        <p class="form-help">
                            Imágenes: JPG, JPEG, PNG, WebP y AVIF. Videos:
                            MP4, WebM y MOV. Tamaño máximo: 50 MB.
                        </p>

                        <p v-if="form.errors.image" class="form-error">
                            {{ form.errors.image }}
                        </p>
                    </div>

                    <div
                        v-if="mediaPreview"
                        class="overflow-hidden rounded-xl border border-[#DCE7DF] bg-white md:col-span-2"
                    >
                        <div
                            class="flex items-center gap-2 border-b border-[#DCE7DF] px-4 py-3"
                        >
                            <Video
                                v-if="mediaIsVideo"
                                class="h-4 w-4 text-[#187744]"
                            />
                            <FileImage
                                v-else
                                class="h-4 w-4 text-[#187744]"
                            />

                            <p class="text-sm font-semibold text-[#244334]">
                                Vista previa
                            </p>

                            <span
                                class="ml-auto max-w-[60%] truncate text-xs text-[#718477]"
                            >
                                {{ mediaName }}
                            </span>
                        </div>

                        <div class="bg-[#EAF0EC] p-3">
                            <video
                                v-if="mediaIsVideo"
                                :key="mediaPreview"
                                :src="mediaPreview"
                                controls
                                playsinline
                                class="mx-auto max-h-96 w-full rounded-lg object-contain"
                            >
                                Tu navegador no admite la reproducción de este
                                video.
                            </video>

                            <img
                                v-else
                                :src="mediaPreview"
                                alt="Vista previa del banner"
                                class="mx-auto max-h-72 w-full rounded-lg object-contain"
                            />
                        </div>
                    </div>

                    <div class="form-field md:col-span-2">
                        <label for="alt_text" class="form-label">
                            Texto alternativo (ALT)
                        </label>

                        <div class="relative">
                            <FileImage
                                class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#718477]"
                            />

                            <input
                                id="alt_text"
                                v-model="form.alt_text"
                                type="text"
                                maxlength="255"
                                placeholder="Describe brevemente la imagen"
                                :class="[
                                    'form-input pl-11',
                                    form.errors.alt_text && 'form-input-error',
                                ]"
                            />
                        </div>

                        <p class="form-help">
                            Texto descriptivo para accesibilidad, especialmente
                            útil cuando el banner contiene una imagen.
                        </p>

                        <p v-if="form.errors.alt_text" class="form-error">
                            {{ form.errors.alt_text }}
                        </p>
                    </div>
                </div>
            </AdminCard>

            <AdminCard
                title="Enlace y botón"
                description="Configura la acción que tendrá el banner al interactuar con él."
                :icon="LinkIcon"
            >
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="form-field">
                        <label for="link_url" class="form-label">
                            URL de destino
                        </label>

                        <div class="relative">
                            <LinkIcon
                                class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#718477]"
                            />

                            <input
                                id="link_url"
                                v-model="form.link_url"
                                type="text"
                                maxlength="2048"
                                placeholder="/productos o https://ejemplo.com"
                                :class="[
                                    'form-input pl-11',
                                    form.errors.link_url && 'form-input-error',
                                ]"
                            />
                        </div>

                        <p v-if="form.errors.link_url" class="form-error">
                            {{ form.errors.link_url }}
                        </p>
                    </div>

                    <div class="form-field">
                        <label for="button_text" class="form-label">
                            Texto del botón
                        </label>

                        <div class="relative">
                            <Type
                                class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#718477]"
                            />

                            <input
                                id="button_text"
                                v-model="form.button_text"
                                type="text"
                                maxlength="100"
                                placeholder="Ej. Ver productos"
                                :class="[
                                    'form-input pl-11',
                                    form.errors.button_text && 'form-input-error',
                                ]"
                            />
                        </div>

                        <p v-if="form.errors.button_text" class="form-error">
                            {{ form.errors.button_text }}
                        </p>
                    </div>

                    <label
                        for="open_in_new_tab"
                        class="flex cursor-pointer items-start gap-3 rounded-xl border border-[#DCE7DF] bg-white p-4 md:col-span-2"
                    >
                        <input
                            id="open_in_new_tab"
                            v-model="form.open_in_new_tab"
                            type="checkbox"
                            class="mt-1 h-4 w-4 rounded border-[#A6C1B3] accent-[#187744]"
                        />

                        <span class="flex items-start gap-2">
                            <ExternalLink
                                class="mt-0.5 h-4 w-4 shrink-0 text-[#187744]"
                            />

                            <span>
                                <span class="block text-sm font-semibold text-[#244334]">
                                    Abrir enlace en una pestaña nueva
                                </span>
                                <span class="mt-1 block text-sm text-[#718477]">
                                    El enlace se abrirá sin cerrar la página actual.
                                </span>
                            </span>
                        </span>
                    </label>
                </div>
            </AdminCard>

            <AdminCard
                title="Configuración de publicación"
                description="Controla la ubicación, el orden y la vigencia del banner."
                :icon="CalendarClock"
            >
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="form-field">
                        <label for="position" class="form-label">
                            Posición <span class="text-red-500">*</span>
                        </label>

                        <div class="form-select-wrapper">
                            <select
                                id="position"
                                v-model="form.position"
                                required
                                :class="[
                                    'form-select',
                                    form.errors.position && 'form-select-error',
                                ]"
                            >
                                <option value="" disabled>
                                    Selecciona una posición
                                </option>

                                <option
                                    v-for="(label, value) in props.positions"
                                    :key="value"
                                    :value="value"
                                >
                                    {{ label }}
                                </option>
                            </select>

                            <ChevronDown class="form-select-icon h-4 w-4" />
                        </div>

                        <p class="form-help">
                            Define en qué sección del sitio se mostrará.
                        </p>

                        <p v-if="form.errors.position" class="form-error">
                            {{ form.errors.position }}
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
                            Los valores menores se muestran primero.
                        </p>

                        <p v-if="form.errors.sort_order" class="form-error">
                            {{ form.errors.sort_order }}
                        </p>
                    </div>

                    <div class="form-field">
                        <label for="starts_at" class="form-label">
                            Fecha de inicio
                        </label>

                        <input
                            id="starts_at"
                            v-model="form.starts_at"
                            type="datetime-local"
                            :class="[
                                'form-input',
                                form.errors.starts_at && 'form-input-error',
                            ]"
                        />

                        <p class="form-help">
                            Déjala vacía para no programar una fecha de inicio.
                        </p>

                        <p v-if="form.errors.starts_at" class="form-error">
                            {{ form.errors.starts_at }}
                        </p>
                    </div>

                    <div class="form-field">
                        <label for="ends_at" class="form-label">
                            Fecha de finalización
                        </label>

                        <input
                            id="ends_at"
                            v-model="form.ends_at"
                            type="datetime-local"
                            :min="form.starts_at || undefined"
                            :class="[
                                'form-input',
                                form.errors.ends_at && 'form-input-error',
                            ]"
                        />

                        <p class="form-help">
                            Déjala vacía para no programar una fecha de finalización.
                        </p>

                        <p v-if="form.errors.ends_at" class="form-error">
                            {{ form.errors.ends_at }}
                        </p>
                    </div>

                    <label
                        for="is_active"
                        class="flex cursor-pointer items-start gap-3 rounded-xl border border-[#DCE7DF] bg-white p-4 md:col-span-2"
                    >
                        <input
                            id="is_active"
                            v-model="form.is_active"
                            type="checkbox"
                            class="mt-1 h-4 w-4 rounded border-[#A6C1B3] accent-[#187744]"
                        />

                        <span>
                            <span class="block text-sm font-semibold text-[#244334]">
                                Banner activo
                            </span>
                            <span class="mt-1 block text-sm text-[#718477]">
                                El banner podrá mostrarse si está dentro de su periodo de publicación.
                            </span>
                        </span>
                    </label>

                    <p v-if="form.errors.is_active" class="form-error md:col-span-2">
                        {{ form.errors.is_active }}
                    </p>
                </div>
            </AdminCard>

            <AdminCard
                title="Configuración SEO"
                description="Personaliza los metadatos del banner cuando sean utilizados por el sitio."
                :icon="Search"
            >
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="form-field md:col-span-2">
                        <label for="seo_title" class="form-label">
                            Título SEO
                        </label>

                        <input
                            id="seo_title"
                            v-model="form.seo_title"
                            type="text"
                            maxlength="255"
                            placeholder="Título para buscadores"
                            :class="[
                                'form-input',
                                form.errors.seo_title && 'form-input-error',
                            ]"
                        />

                        <p v-if="form.errors.seo_title" class="form-error">
                            {{ form.errors.seo_title }}
                        </p>
                    </div>

                    <div class="form-field md:col-span-2">
                        <label for="seo_description" class="form-label">
                            Descripción SEO
                        </label>

                        <textarea
                            id="seo_description"
                            v-model="form.seo_description"
                            rows="3"
                            maxlength="500"
                            placeholder="Descripción para motores de búsqueda"
                            :class="[
                                'form-input min-h-24 resize-y',
                                form.errors.seo_description && 'form-input-error',
                            ]"
                        />

                        <div class="flex items-center justify-between gap-3">
                            <p v-if="form.errors.seo_description" class="form-error">
                                {{ form.errors.seo_description }}
                            </p>

                            <p class="ml-auto text-xs text-[#718477]">
                                {{ form.seo_description.length }}/500
                            </p>
                        </div>
                    </div>
                </div>
            </AdminCard>

            <div
                class="flex flex-col-reverse gap-3 border-t border-[#DCE7DF] pt-5 sm:flex-row sm:items-center sm:justify-end"
            >
                <CancelButton
                    :href="admin.banners.index().url"
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
                    Crear banner
                </AdminButton>
            </div>
        </form>
    </div>
</template>
