
<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

interface GalleryItem {
    src: string
    alt: string
}

interface Props {
    items: GalleryItem[]
    autoplay?: boolean
    interval?: number
}

const props = withDefaults(defineProps<Props>(), {
    autoplay: true,
    interval: 4000,
})

const currentIndex = ref(0)
const isPaused = ref(false)

let autoplayTimer: ReturnType<typeof setInterval> | null = null

const stopAutoplay = () => {
    if (autoplayTimer) {
        clearInterval(autoplayTimer)
        autoplayTimer = null
    }
}

const startAutoplay = () => {
    stopAutoplay()

    if (
        !props.autoplay ||
        isPaused.value ||
        props.items.length <= 1
    ) {
        return
    }

    autoplayTimer = setInterval(() => {
        next()
    }, props.interval)
}

const next = () => {
    if (!props.items.length) return

    currentIndex.value =
        (currentIndex.value + 1) % props.items.length
}

const previous = () => {
    if (!props.items.length) return

    currentIndex.value =
        (currentIndex.value - 1 + props.items.length) %
        props.items.length
}

const goTo = (index: number) => {
    if (index < 0 || index >= props.items.length) return

    currentIndex.value = index
    startAutoplay()
}

const pauseAutoplay = () => {
    isPaused.value = true
    stopAutoplay()
}

const resumeAutoplay = () => {
    isPaused.value = false
    startAutoplay()
}

watch(
    () => [props.items.length, props.autoplay, props.interval],
    () => {
        if (currentIndex.value >= props.items.length) {
            currentIndex.value = 0
        }

        startAutoplay()
    },
)

onMounted(startAutoplay)
onUnmounted(stopAutoplay)
</script>

<template>
    <div
        v-if="items.length"
        class="husky-gallery"
        @mouseenter="pauseAutoplay"
        @mouseleave="resumeAutoplay"
        @focusin="pauseAutoplay"
        @focusout="resumeAutoplay"
    >
        <div class="husky-gallery-viewport">
            <div
                class="husky-gallery-track"
                :style="{
                    transform: `translateX(-${currentIndex * 100}%)`,
                }"
            >
                <div
                    v-for="(item, index) in items"
                    :key="`${item.src}-${index}`"
                    class="husky-gallery-slide"
                    :aria-hidden="currentIndex !== index"
                >
                    <img
                        :src="item.src"
                        :alt="item.alt"
                        :loading="index === 0 ? 'eager' : 'lazy'"
                        draggable="false"
                    />
                </div>
            </div>

            <div
                v-if="items.length > 1"
                class="husky-gallery-controls"
            >
                <button
                    type="button"
                    class="husky-gallery-arrow"
                    aria-label="Imagen anterior"
                    @click="previous"
                >
                    <ChevronLeft :size="19" />
                </button>

                <button
                    type="button"
                    class="husky-gallery-arrow"
                    aria-label="Imagen siguiente"
                    @click="next"
                >
                    <ChevronRight :size="19" />
                </button>
            </div>

            <div class="husky-gallery-counter">
                {{ String(currentIndex + 1).padStart(2, '0') }}
                <span>/</span>
                {{ String(items.length).padStart(2, '0') }}
            </div>
        </div>

        <div class="husky-gallery-footer">
            <Transition name="husky-gallery-fade" mode="out-in">
                <p
                    :key="currentIndex"
                    class="husky-gallery-description"
                >
                    {{ items[currentIndex]?.alt }}
                </p>
            </Transition>

            <div
                v-if="items.length > 1"
                class="husky-gallery-dots"
                aria-label="Seleccionar fotografía"
            >
                <button
                    v-for="(item, index) in items"
                    :key="`${item.src}-dot-${index}`"
                    type="button"
                    :class="{ active: currentIndex === index }"
                    :aria-label="`Ver imagen ${index + 1}`"
                    :aria-current="
                        currentIndex === index ? 'true' : undefined
                    "
                    @click="goTo(index)"
                />
            </div>
        </div>
    </div>

    <div v-else class="husky-gallery-empty">
        No hay imágenes disponibles.
    </div>
</template>

<style scoped>
.husky-gallery {
    width: 100%;
    height: 100%;
}

.husky-gallery-viewport {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden;
    background: #d9ded1;
}

.husky-gallery-track {
    display: flex;
    width: 100%;
    height: 100%;
    transition: transform 0.65s cubic-bezier(.22, .61, .36, 1);
    will-change: transform;
}

.husky-gallery-slide {
    position: relative;
    flex: 0 0 100%;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

.husky-gallery-slide img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.7s ease;
}

.husky-gallery:hover .husky-gallery-slide[aria-hidden="false"] img {
    transform: scale(1.035);
}

.husky-gallery-controls {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 15px;
    pointer-events: none;
}

.husky-gallery-arrow {
    display: grid;
    width: 37px;
    height: 37px;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, .45);
    border-radius: 50%;
    color: white;
    background: rgba(25, 44, 36, .55);
    cursor: pointer;
    pointer-events: auto;
    backdrop-filter: blur(6px);
    transition: background .25s ease, transform .25s ease;
}

.husky-gallery-arrow:hover {
    background: rgba(25, 44, 36, .85);
    transform: scale(1.06);
}

.husky-gallery-counter {
    position: absolute;
    right: 18px;
    bottom: 18px;
    padding: 8px 13px;
    border: 1px solid rgba(255, 255, 255, .2);
    border-radius: 30px;
    color: white;
    background: rgba(25, 44, 36, .65);
    font-size: 11px;
    letter-spacing: 1px;
    backdrop-filter: blur(8px);
}

.husky-gallery-counter span {
    margin: 0 5px;
    opacity: .6;
}

.husky-gallery-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 15px 3px 0;
}

.husky-gallery-description {
    margin: 0;
    color: var(--husky-muted);
    font-size: 11px;
    line-height: 1.6;
}

.husky-gallery-dots {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    flex-shrink: 0;
    gap: 7px;
}

.husky-gallery-dots button {
    width: 7px;
    height: 7px;
    padding: 0;
    border: none;
    border-radius: 50%;
    background: #b5c1a6;
    cursor: pointer;
    transition: width .3s ease, background .3s ease;
}

.husky-gallery-dots button.active {
    width: 21px;
    border-radius: 10px;
    background: var(--husky-forest);
}

.husky-gallery-fade-enter-active,
.husky-gallery-fade-leave-active {
    transition: opacity .25s ease;
}

.husky-gallery-fade-enter-from,
.husky-gallery-fade-leave-to {
    opacity: 0;
}

.husky-gallery-empty {
    display: grid;
    width: 100%;
    height: 100%;
    min-height: 250px;
    place-items: center;
    border-radius: 14px;
    color: var(--husky-muted);
    background: #d9ded1;
    font-size: 13px;
}

@media (max-width: 640px) {
    .husky-gallery-footer {
        align-items: flex-start;
        flex-direction: column;
        gap: 12px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .husky-gallery-track,
    .husky-gallery-slide img,
    .husky-gallery-arrow,
    .husky-gallery-dots button {
        transition: none;
    }
}
</style>
