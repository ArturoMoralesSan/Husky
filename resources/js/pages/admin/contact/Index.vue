
<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import {
    AtSign,
    Facebook,
    Instagram,
    MapPin,
    MessageCircle,
    Pencil,
    Phone,
    Plus,
    Clock,
    ContactRound,
} from 'lucide-vue-next'

import PageHeader from '@/components/admin/PageHeader.vue'
import AdminCard from '@/components/admin/AdminCard.vue'
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
    contact: Contact | null
}>()

const contactSections = [
    {
        title: 'Información general',
        icon: AtSign,
        fields: [
            { label: 'Correo electrónico', key: 'email' as const, icon: AtSign },
            { label: 'Dirección', key: 'address' as const, icon: MapPin },
        ],
    },
    {
        title: 'Teléfonos y WhatsApp',
        icon: Phone,
        fields: [
            { label: 'Teléfono principal', key: 'phone_primary' as const, icon: Phone },
            { label: 'Teléfono secundario', key: 'phone_secondary' as const, icon: Phone },
            { label: 'Teléfono terciario', key: 'phone_tertiary' as const, icon: Phone },
            { label: 'WhatsApp', key: 'whatsapp' as const, icon: MessageCircle },
        ],
    },
    {
        title: 'Horarios de atención',
        icon: Clock,
        fields: [
            { label: 'Lunes a viernes', key: 'schedule_weekdays' as const, icon: Clock },
            { label: 'Sábado', key: 'schedule_saturday' as const, icon: Clock },
        ],
    },
    {
        title: 'Redes sociales',
        icon: Instagram,
        fields: [
            { label: 'Facebook', key: 'facebook_url' as const, icon: Facebook },
            { label: 'Instagram', key: 'instagram_url' as const, icon: Instagram },
        ],
    },
]

defineOptions({
    breadcrumbs: [
        { title: 'Panel', href: '/admin' },
        { title: 'Contacto', href: admin.contact.index().url },
    ],
})
</script>

<template>
    <Head title="Contacto" />

    <div class="flex h-full flex-1 flex-col gap-5 overflow-x-hidden p-4 sm:p-6">
        <PageHeader
            title="Contacto"
            description="Administra la información de contacto de Industrias Husky."
            section="Contenido"
            :icon="ContactRound"
            :action-icon="contact ? Pencil : Plus"
            :action-label="contact ? 'Editar información' : 'Agregar información'"
            :action-href="contact ? admin.contact.edit().url : admin.contact.create().url"
        />

        <template v-if="contact">
            <div class="grid gap-5 lg:grid-cols-2">
                <AdminCard
                    v-for="section in contactSections"
                    :key="section.title"
                    :title="section.title"
                    :icon="section.icon"
                >
                    <div class="space-y-4">
                        <div
                            v-for="field in section.fields"
                            :key="field.key"
                            class="flex items-start gap-3"
                        >
                            <div class="flex size-9 shrink-0 items-center justify-center rounded-xl border border-[#DCE7DF] bg-[#F2F7F4] text-[#187744]">
                                <component :is="field.icon" class="size-4" />
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-medium text-[#718579]">
                                    {{ field.label }}
                                </p>

                                <a
                                    v-if="field.key === 'email' && contact[field.key]"
                                    :href="`mailto:${contact[field.key]}`"
                                    class="break-words text-sm font-medium text-[#244334] hover:text-[#187744]"
                                >
                                    {{ contact[field.key] }}
                                </a>

                                <a
                                    v-else-if="field.key === 'whatsapp' && contact[field.key]"
                                    :href="`https://wa.me/${contact[field.key].replace(/\D/g, '')}`"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="break-words text-sm font-medium text-[#187744] hover:underline"
                                >
                                    {{ contact[field.key] }}
                                </a>

                                <a
                                    v-else-if="['phone_primary', 'phone_secondary', 'phone_tertiary'].includes(field.key) && contact[field.key]"
                                    :href="`tel:${contact[field.key]}`"
                                    class="break-words text-sm font-medium text-[#244334] hover:text-[#187744]"
                                >
                                    {{ contact[field.key] }}
                                </a>

                                <a
                                    v-else-if="['facebook_url', 'instagram_url'].includes(field.key) && contact[field.key]"
                                    :href="contact[field.key]!"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="break-all text-sm font-medium text-[#187744] hover:underline"
                                >
                                    {{ contact[field.key] }}
                                </a>

                                <p
                                    v-else
                                    class="break-words text-sm text-[#244334]"
                                >
                                    {{ contact[field.key] || 'Sin información registrada' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </AdminCard>
            </div>
        </template>

        <div
            v-else
            class="rounded-2xl border border-dashed border-[#DCE7DF] bg-white px-6 py-14 text-center"
        >
            <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-[#F2F7F4] text-[#187744]">
                <ContactRound class="size-7" />
            </div>

            <h3 class="mt-4 text-base font-semibold text-[#244334]">
                No hay información de contacto
            </h3>

            <p class="mx-auto mt-2 max-w-md text-sm text-[#718579]">
                Agrega los datos de contacto para mostrarlos en el sitio web de Industrias Husky.
            </p>
        </div>
    </div>
</template>
