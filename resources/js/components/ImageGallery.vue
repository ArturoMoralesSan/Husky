
<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Autoplay } from 'swiper/modules'
import type { Swiper as SwiperInstance } from 'swiper'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

import 'swiper/css'

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
const swiperInstance = ref<SwiperInstance | null>(null)
const isPaused = ref(false)

const modules = [Autoplay]

const autoplayConfig = computed(() => ({
    delay: props.interval,
    disableOnInteraction: false,
    pauseOnMouseEnter: true,
}))

const hasMultipleImages = computed(() => props.items.length > 1)

const onSwiper = (swiper: SwiperInstance) => {
    swiperInstance.value = swiper

    if (!props.autoplay || props.items.length <= 1) {
        swiper.autoplay.stop()
    }
}

const onSlideChange = (swiper: SwiperInstance) => {
    currentIndex.value = swiper.realIndex
}

const next = () => {
    swiperInstance.value?.slideNext()
}

const previous = () => {
    swiperInstance.value?.slidePrev()
}

const goTo = (index: number) => {
    if (index < 0 || index >= props.items.length) return

    swiperInstance.value?.slideToLoop(index)
    resumeAutoplay()
}

const pauseAutoplay = () => {
    isPaused.value = true
    swiperInstance.value?.autoplay.stop()
}

const resumeAutoplay = () => {
    isPaused.value = false

    if (props.autoplay && props.items.length > 1) {
        swiperInstance.value?.autoplay.start()
    }
}

const handleFocusOut = (event: FocusEvent) => {
    const container = event.currentTarget as HTMLElement
    const nextTarget = event.relatedTarget as Node | null

    if (!nextTarget || !container.contains(nextTarget)) {
        resumeAutoplay()
    }
}

watch(
    () => [props.items.length, props.autoplay, props.interval],
    async () => {
        await nextTick()

        const swiper = swiperInstance.value

        if (!swiper) return

        swiper.params.autoplay = {
            delay: props.interval,
            disableOnInteraction: false,
            pauseOnMouseEnter: true,
        }

        swiper.update()

        if (currentIndex.value >= props.items.length) {
            currentIndex.value = 0
            swiper.slideToLoop(0, 0)
        }

        if (props.autoplay && props.items.length > 1 && !isPaused.value) {
            swiper.autoplay.start()
        } else {
            swiper.autoplay.stop()
        }
    },
)

watch(
    () => props.items.map(item => item.src),
    async () => {
        await nextTick()

        const swiper = swiperInstance.value

        if (!swiper) return

        currentIndex.value = 0
        swiper.update()
        swiper.slideToLoop(0, 0)
    },
)

onMounted(() => {
    if (!props.autoplay) {
        swiperInstance.value?.autoplay.stop()
    }
})

onUnmounted(() => {
    swiperInstance.value = null
})
</script>

<template>
    <div
        v-if="items.length"
        class="husky-gallery"
        @mouseenter="pauseAutoplay"
        @mouseleave="resumeAutoplay"
        @focusin="pauseAutoplay"
        @focusout="handleFocusOut"
    >
        <div class="husky-gallery-viewport">
            <Swiper
                class="husky-gallery-swiper"
                :modules="modules"
                :loop="hasMultipleImages"
                :slides-per-view="1"
                :speed="650"
                :autoplay="autoplayConfig"
                :allow-touch-move="hasMultipleImages"
                :grab-cursor="hasMultipleImages"
                :watch-overflow="true"
                @swiper="onSwiper"
                @slide-change="onSlideChange"
            >
                <SwiperSlide
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
                </SwiperSlide>
            </Swiper>

            <div
                v-if="hasMultipleImages"
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
                v-if="hasMultipleImages"
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

.husky-gallery-swiper {
    width: 100%;
    height: 100%;
}

.husky-gallery-swiper :deep(.swiper-wrapper) {
    height: 100%;
}

.husky-gallery-slide {
    position: relative;
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
    user-select: none;
    -webkit-user-drag: none;
}

.husky-gallery:hover .husky-gallery-slide[aria-hidden="false"] img {
    transform: scale(1.035);
}

.husky-gallery-controls {
    position: absolute;
    z-index: 5;
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
    z-index: 5;
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
    pointer-events: none;
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

    .husky-gallery-arrow {
        width: 34px;
        height: 34px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .husky-gallery-slide img,
    .husky-gallery-arrow,
    .husky-gallery-dots button {
        transition: none;
    }
}
</style>
