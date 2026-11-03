
<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import {
    AtSign,
    Clock,
    Facebook,
    Instagram,
    MessageCircle,
    Phone,
    Save,
    ArrowLeft,
} from 'lucide-vue-next'

import AdminButton from '@/components/admin/AdminButton.vue'
import AdminCard from '@/components/admin/AdminCard.vue'
import CancelButton from '@/components/admin/CancelButton.vue'
import PageHeader from '@/components/admin/PageHeader.vue'
import admin from '@/routes/admin'

interface Contact {
    id: number
    email: string | null
    phone_primary: string | null
    phone_secondary: string | null
    phone_tertiary: string | null
    whatsapp: string | null
    address: string | null
    schedule_weekdays: string | null
    schedule_saturday: string | null
    facebook_url: string | null
    instagram_url: string | null
}

const props = defineProps<{
    contact: Contact
}>()

const form = useForm({
    email: props.contact.email ?? '',
    phone_primary: props.contact.phone_primary ?? '',
    phone_secondary: props.contact.phone_secondary ?? '',
    phone_tertiary: props.contact.phone_tertiary ?? '',
    whatsapp: props.contact.whatsapp ?? '',
    address: props.contact.address ?? '',
    schedule_weekdays: props.contact.schedule_weekdays ?? '',
    schedule_saturday: props.contact.schedule_saturday ?? '',
    facebook_url: props.contact.facebook_url ?? '',
    instagram_url: props.contact.instagram_url ?? '',
})

function submit() {
    form.transform((data) => ({
        email: data.email.trim() || null,
        phone_primary: data.phone_primary.trim() || null,
        phone_secondary: data.phone_secondary.trim() || null,
        phone_tertiary: data.phone_tertiary.trim() || null,
        whatsapp: data.whatsapp.trim() || null,
        address: data.address.trim() || null,
        schedule_weekdays: data.schedule_weekdays.trim() || null,
        schedule_saturday: data.schedule_saturday.trim() || null,
        facebook_url: data.facebook_url.trim() || null,
        instagram_url: data.instagram_url.trim() || null,
    })).put(admin.contact.update().url)
}

defineOptions({
    breadcrumbs: [
        { title: 'Panel', href: '/admin' },
        { title: 'Contacto', href: admin.contact.index().url },
        { title: 'Editar información', href: admin.contact.edit().url },
    ],
})
</script>

<template>
    <Head title="Editar información de contacto" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-hidden p-4 sm:p-6">
        <PageHeader
            title="Editar información de contacto"
            description="Actualiza los datos de contacto que aparecen en el sitio web."
            section="Contenido"
            :icon="AtSign"
        />

        <form @submit.prevent="submit" class="space-y-5">
            <AdminCard title="Información general" :icon="AtSign">
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="space-y-2">
                        <label for="email" class="text-sm font-medium text-[#244334]">
                            Correo electrónico
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="contacto@industriashusky.com.mx"
                            class="w-full rounded-xl border border-[#DCE7DF] bg-white px-4 py-3 text-sm text-[#244334] outline-none transition placeholder:text-[#9AAFA1] focus:border-[#187744] focus:ring-2 focus:ring-[#187744]/10"
                        />
                        <p v-if="form.errors.email" class="text-xs text-red-600">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label for="address" class="text-sm font-medium text-[#244334]">
                            Dirección
                        </label>
                        <textarea
                            id="address"
                            v-model="form.address"
                            rows="3"
                            placeholder="Dirección de Industrias Husky"
                            class="w-full resize-y rounded-xl border border-[#DCE7DF] bg-white px-4 py-3 text-sm text-[#244334] outline-none transition placeholder:text-[#9AAFA1] focus:border-[#187744] focus:ring-2 focus:ring-[#187744]/10"
                        />
                        <p v-if="form.errors.address" class="text-xs text-red-600">
                            {{ form.errors.address }}
                        </p>
                    </div>
                </div>
            </AdminCard>

            <AdminCard title="Teléfonos y WhatsApp" :icon="Phone">
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="space-y-2">
                        <label for="phone_primary" class="text-sm font-medium text-[#244334]">
                            Teléfono principal
                        </label>
                        <input
                            id="phone_primary"
                            v-model="form.phone_primary"
                            type="text"
                            placeholder="+52 (618) 000-0000"
                            class="w-full rounded-xl border border-[#DCE7DF] bg-white px-4 py-3 text-sm text-[#244334] outline-none transition placeholder:text-[#9AAFA1] focus:border-[#187744] focus:ring-2 focus:ring-[#187744]/10"
                        />
                        <p v-if="form.errors.phone_primary" class="text-xs text-red-600">
                            {{ form.errors.phone_primary }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label for="phone_secondary" class="text-sm font-medium text-[#244334]">
                            Teléfono secundario
                        </label>
                        <input
                            id="phone_secondary"
                            v-model="form.phone_secondary"
                            type="text"
                            placeholder="+52 (618) 000-0000"
                            class="w-full rounded-xl border border-[#DCE7DF] bg-white px-4 py-3 text-sm text-[#244334] outline-none transition placeholder:text-[#9AAFA1] focus:border-[#187744] focus:ring-2 focus:ring-[#187744]/10"
                        />
                        <p v-if="form.errors.phone_secondary" class="text-xs text-red-600">
                            {{ form.errors.phone_secondary }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label for="phone_tertiary" class="text-sm font-medium text-[#244334]">
                            Teléfono terciario
                        </label>
                        <input
                            id="phone_tertiary"
                            v-model="form.phone_tertiary"
                            type="text"
                            placeholder="+52 (618) 000-0000"
                            class="w-full rounded-xl border border-[#DCE7DF] bg-white px-4 py-3 text-sm text-[#244334] outline-none transition placeholder:text-[#9AAFA1] focus:border-[#187744] focus:ring-2 focus:ring-[#187744]/10"
                        />
                        <p v-if="form.errors.phone_tertiary" class="text-xs text-red-600">
                            {{ form.errors.phone_tertiary }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label for="whatsapp" class="flex items-center gap-2 text-sm font-medium text-[#244334]">
                            <MessageCircle class="size-4 text-[#187744]" />
                            WhatsApp
                        </label>
                        <input
                            id="whatsapp"
                            v-model="form.whatsapp"
                            type="text"
                            placeholder="526181234567"
                            class="w-full rounded-xl border border-[#DCE7DF] bg-white px-4 py-3 text-sm text-[#244334] outline-none transition placeholder:text-[#9AAFA1] focus:border-[#187744] focus:ring-2 focus:ring-[#187744]/10"
                        />
                        <p class="text-xs text-[#718579]">
                            Incluye el código de país, sin espacios ni símbolos.
                        </p>
                        <p v-if="form.errors.whatsapp" class="text-xs text-red-600">
                            {{ form.errors.whatsapp }}
                        </p>
                    </div>
                </div>
            </AdminCard>

            <AdminCard title="Horarios de atención" :icon="Clock">
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="space-y-2">
                        <label for="schedule_weekdays" class="text-sm font-medium text-[#244334]">
                            Lunes a viernes
                        </label>
                        <input
                            id="schedule_weekdays"
                            v-model="form.schedule_weekdays"
                            type="text"
                            placeholder="Lunes a viernes, 8:00 a 16:00"
                            class="w-full rounded-xl border border-[#DCE7DF] bg-white px-4 py-3 text-sm text-[#244334] outline-none transition placeholder:text-[#9AAFA1] focus:border-[#187744] focus:ring-2 focus:ring-[#187744]/10"
                        />
                        <p v-if="form.errors.schedule_weekdays" class="text-xs text-red-600">
                            {{ form.errors.schedule_weekdays }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label for="schedule_saturday" class="text-sm font-medium text-[#244334]">
                            Sábado
                        </label>
                        <input
                            id="schedule_saturday"
                            v-model="form.schedule_saturday"
                            type="text"
                            placeholder="Sábado, 8:00 a 15:00"
                            class="w-full rounded-xl border border-[#DCE7DF] bg-white px-4 py-3 text-sm text-[#244334] outline-none transition placeholder:text-[#9AAFA1] focus:border-[#187744] focus:ring-2 focus:ring-[#187744]/10"
                        />
                        <p v-if="form.errors.schedule_saturday" class="text-xs text-red-600">
                            {{ form.errors.schedule_saturday }}
                        </p>
                    </div>
                </div>
            </AdminCard>

            <AdminCard title="Redes sociales" :icon="Instagram">
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="space-y-2">
                        <label for="facebook_url" class="flex items-center gap-2 text-sm font-medium text-[#244334]">
                            <Facebook class="size-4 text-[#187744]" />
                            Facebook
                        </label>
                        <input
                            id="facebook_url"
                            v-model="form.facebook_url"
                            type="url"
                            placeholder="https://facebook.com/..."
                            class="w-full rounded-xl border border-[#DCE7DF] bg-white px-4 py-3 text-sm text-[#244334] outline-none transition placeholder:text-[#9AAFA1] focus:border-[#187744] focus:ring-2 focus:ring-[#187744]/10"
                        />
                        <p v-if="form.errors.facebook_url" class="text-xs text-red-600">
                            {{ form.errors.facebook_url }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label for="instagram_url" class="flex items-center gap-2 text-sm font-medium text-[#244334]">
                            <Instagram class="size-4 text-[#187744]" />
                            Instagram
                        </label>
                        <input
                            id="instagram_url"
                            v-model="form.instagram_url"
                            type="url"
                            placeholder="https://instagram.com/..."
                            class="w-full rounded-xl border border-[#DCE7DF] bg-white px-4 py-3 text-sm text-[#244334] outline-none transition placeholder:text-[#9AAFA1] focus:border-[#187744] focus:ring-2 focus:ring-[#187744]/10"
                        />
                        <p v-if="form.errors.instagram_url" class="text-xs text-red-600">
                            {{ form.errors.instagram_url }}
                        </p>
                    </div>
                </div>
            </AdminCard>

            <div class="flex flex-col-reverse justify-end gap-3 border-t border-[#DCE7DF] pt-5 sm:flex-row">
                <CancelButton
                    :href="admin.contact.index().url"
                    :icon="ArrowLeft"
                    label="Cancelar"
                />

                <AdminButton type="submit" :disabled="form.processing">
                    <Save class="mr-2 size-4" />
                    {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                </AdminButton>
            </div>
        </form>
    </div>
</template>
