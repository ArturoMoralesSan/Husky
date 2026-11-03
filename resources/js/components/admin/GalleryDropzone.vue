
<script setup lang="ts">
import {
    ImagePlus,
    Images,
    Star,
    Trash2,
    Upload,
    X,
} from 'lucide-vue-next'
import { computed, onBeforeUnmount, ref, watch } from 'vue'

export interface GalleryImageItem {
    id?: number
    file: File | null
    preview: string
    alt: string
    is_primary: boolean
    sort_order: number
}

const props = withDefaults(defineProps<{
    modelValue: GalleryImageItem[]
    maxFiles?: number
    maxSizeMb?: number
    disabled?: boolean
}>(), {
    maxFiles: 20,
    maxSizeMb: 5,
    disabled: false,
})

const emit = defineEmits<{
    'update:modelValue': [value: GalleryImageItem[]]
}>()

const inputRef = ref<HTMLInputElement | null>(null)
const isDragging = ref(false)
const errorMessage = ref('')
const localImages = ref<GalleryImageItem[]>([])
const ownedUrls = new Set<string>()

const remainingSlots = computed(() =>
    Math.max(0, props.maxFiles - localImages.value.length)
)

watch(
    () => props.modelValue,
    (value) => {
        localImages.value = value.map((image) => ({ ...image }))
    },
    { immediate: true, deep: true }
)

function openFilePicker() {
    if (props.disabled || remainingSlots.value <= 0) return
    inputRef.value?.click()
}

function emitImages() {
    emit(
        'update:modelValue',
        localImages.value.map((image, index) => ({
            ...image,
            sort_order: index,
        }))
    )
}

function addFiles(fileList: FileList | File[]) {
    errorMessage.value = ''

    if (props.disabled) return

    const files = Array.from(fileList)
    const acceptedTypes = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/avif',
    ]

    for (const file of files) {
        if (remainingSlots.value <= 0) {
            errorMessage.value = `Puedes agregar un máximo de ${props.maxFiles} imágenes.`
            break
        }

        if (!acceptedTypes.includes(file.type)) {
            errorMessage.value = 'Solo se permiten imágenes JPG, PNG, WEBP o AVIF.'
            continue
        }

        if (file.size > props.maxSizeMb * 1024 * 1024) {
            errorMessage.value = `Cada imagen debe pesar máximo ${props.maxSizeMb} MB.`
            continue
        }

        const preview = URL.createObjectURL(file)
        ownedUrls.add(preview)

        localImages.value.push({
            file,
            preview,
            alt: '',
            is_primary: localImages.value.length === 0,
            sort_order: localImages.value.length,
        })
    }

    emitImages()
}

function onInputChange(event: Event) {
    const target = event.target as HTMLInputElement

    if (target.files) {
        addFiles(target.files)
    }

    target.value = ''
}

function onDrop(event: DragEvent) {
    isDragging.value = false

    if (event.dataTransfer?.files) {
        addFiles(event.dataTransfer.files)
    }
}

function removeImage(index: number) {
    const [removed] = localImages.value.splice(index, 1)

    if (removed && ownedUrls.has(removed.preview)) {
        URL.revokeObjectURL(removed.preview)
        ownedUrls.delete(removed.preview)
    }

    if (
        localImages.value.length > 0 &&
        !localImages.value.some((image) => image.is_primary)
    ) {
        localImages.value[0].is_primary = true
    }

    emitImages()
}

function setPrimary(index: number) {
    localImages.value = localImages.value.map((image, imageIndex) => ({
        ...image,
        is_primary: imageIndex === index,
    }))

    emitImages()
}

function moveImage(index: number, direction: -1 | 1) {
    const targetIndex = index + direction

    if (targetIndex < 0 || targetIndex >= localImages.value.length) return

    const images = [...localImages.value]
    const [moved] = images.splice(index, 1)
    images.splice(targetIndex, 0, moved)

    localImages.value = images
    emitImages()
}

function updateAlt(index: number, alt: string) {
    localImages.value[index].alt = alt
    emitImages()
}

onBeforeUnmount(() => {
    ownedUrls.forEach((url) => URL.revokeObjectURL(url))
    ownedUrls.clear()
})
</script>

<template>
    <section class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-[#244334]">
                    Galería de imágenes
                </h3>
                <p class="mt-1 text-xs text-[#668475]">
                    Agrega imágenes, organiza su orden y selecciona la principal.
                </p>
            </div>

            <span
                class="rounded-full border border-[#DCE7DF] bg-[#F2F7F4] px-3 py-1 text-xs font-medium text-[#187744]"
            >
                {{ localImages.length }} / {{ maxFiles }}
            </span>
        </div>

        <div
            role="button"
            tabindex="0"
            :aria-disabled="disabled || remainingSlots <= 0"
            class="group flex min-h-52 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed p-6 text-center transition"
            :class="[
                isDragging
                    ? 'border-[#187744] bg-[#E8F4EC]'
                    : 'border-[#C8D9CE] bg-[#F8FBF9 hover:border-[#187744] hover:bg-[#F2F7F4]',
                disabled || remainingSlots <= 0
                    ? 'cursor-not-allowed opacity-50'
                    : '',
            ]"
            @click="openFilePicker"
            @keydown.enter.prevent="openFilePicker"
            @keydown.space.prevent="openFilePicker"
            @dragenter.prevent="isDragging = true"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="onDrop"
        >
            <input
                ref="inputRef"
                type="file"
                accept="image/jpeg,image/png,image/webp,image/avif"
                multiple
                class="hidden"
                :disabled="disabled || remainingSlots <= 0"
                @change="onInputChange"
            />

            <div
                class="mb-3 flex size-14 items-center justify-center rounded-2xl bg-[#E2F0E6] text-[#187744] transition group-hover:scale-105"
            >
                <Upload :size="25" />
            </div>

            <p class="text-sm font-semibold text-[#244334]">
                Arrastra tus imágenes aquí
            </p>

            <p class="mt-1 text-sm text-[#668475]">
                o
                <span class="font-semibold text-[#187744] underline">
                    selecciona archivos
                </span>
            </p>

            <p class="mt-3 text-xs text-[#82968A]">
                JPG, PNG, WEBP o AVIF · Máximo {{ maxSizeMb }} MB por imagen
            </p>
        </div>

        <div
            v-if="errorMessage"
            class="flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700"
        >
            <X :size="17" class="mt-0.5 shrink-0" />
            {{ errorMessage }}
        </div>

        <div
            v-if="localImages.length"
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3"
        >
            <article
                v-for="(image, index) in localImages"
                :key="image.id ?? image.preview"
                class="overflow-hidden rounded-xl border border-[#DCE7DF] bg-white"
            >
                <div class="group relative aspect-[4/3] bg-[#F2F7F4]">
                    <img
                        :src="image.preview"
                        :alt="image.alt || 'Vista previa de imagen'"
                        class="size-full object-cover"
                    />

                    <span
                        v-if="image.is_primary"
                        class="absolute left-3 top-3 inline-flex items-center gap-1 rounded-full bg-[#187744] px-2.5 py-1 text-xs font-semibold text-white shadow-sm"
                    >
                        <Star :size="13" class="fill-current" />
                        Principal
                    </span>

                    <button
                        type="button"
                        class="absolute right-3 top-3 flex size-8 items-center justify-center rounded-lg border border-red-100 bg-white/95 text-red-600 transition hover:bg-red-50"
                        aria-label="Eliminar imagen"
                        :disabled="disabled"
                        @click.stop="removeImage(index)"
                    >
                        <Trash2 :size="16" />
                    </button>
                </div>

                <div class="space-y-3 p-3">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-medium text-[#668475]">
                            Imagen {{ index + 1 }}
                        </span>

                        <div class="flex items-center gap-1">
                            <button
                                type="button"
                                class="rounded-md border border-[#DCE7DF] px-2 py-1 text-xs text-[#244334] hover:bg-[#F2F7F4] disabled:opacity-40"
                                :disabled="disabled || index === 0"
                                aria-label="Mover imagen a la izquierda"
                                @click="moveImage(index, -1)"
                            >
                                ←
                            </button>

                            <button
                                type="button"
                                class="rounded-md border border-[#DCE7DF] px-2 py-1 text-xs text-[#244334] hover:bg-[#F2F7F4] disabled:opacity-40"
                                :disabled="disabled || index === localImages.length - 1"
                                aria-label="Mover imagen a la derecha"
                                @click="moveImage(index, 1)"
                            >
                                →
                            </button>
                        </div>
                    </div>

                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-[#244334]">
                            Texto alternativo
                        </span>

                        <input
                            :value="image.alt"
                            type="text"
                            maxlength="255"
                            placeholder="Describe la imagen"
                            :disabled="disabled"
                            class="w-full rounded-lg border border-[#DCE7DF] px-3 py-2 text-sm text-[#244334] outline-none transition placeholder:text-[#9AAEA2] focus:border-[#187744] focus:ring-2 focus:ring-[#187744]/10"
                            @input="updateAlt(index, ($event.target as HTMLInputElement).value)"
                        />
                    </label>

                    <button
                        v-if="!image.is_primary"
                        type="button"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#187744] hover:text-[#125D35] disabled:opacity-40"
                        :disabled="disabled"
                        @click="setPrimary(index)"
                    >
                        <Star :size="14" />
                        Marcar como principal
                    </button>

                    <p
                        v-else
                        class="inline-flex items-center gap-1.5 text-xs font-medium text-[#668475]"
                    >
                        <Images :size="14" />
                        Imagen destacada
                    </p>
                </div>
            </article>
        </div>

        <div
            v-else
            class="flex items-center justify-center gap-2 rounded-xl border border-[#DCE7DF] bg-white p-5 text-sm text-[#668475]"
        >
            <ImagePlus :size="18" />
            Aún no has agregado imágenes.
        </div>
    </section>
</template>
