<!-- CategoryEditModal.vue -->
<script setup lang="ts">
import { reactive, ref, watch } from 'vue'
import { z } from 'zod'
import type { FormSubmitEvent, UForm } from '@nuxt/ui'
import type { BlogCategory } from '~/types/Category'

const props  = defineProps<{ item: BlogCategory }>()
const isOpen = defineModel<boolean>()
const emit   = defineEmits(['update'])
const toast  = useToast()

// Підтягуємо батьківські категорії для селектора
const { data: parents, status: pStat } = await useFetch(
    'http://localhost/api/blog/categories',
    { server: false, lazy: true, transform: v => (Array.isArray(v) ? v : v?.data ?? []) }
)

// Zod-схема тільки для потрібних полів
const schema = z.object({
    title      : z.string().min(5),        // min:5 відповідно до FormRequest
    slug       : z.string().max(200).nullable().optional(),
    parent_id  : z.number().nullable(),
    description: z.string().min(3).max(500)
})
type Schema = z.infer<typeof schema>

// Стан форми — тільки потрібні поля!
const state = reactive<Schema>({
    title: '', slug: null, parent_id: null, description: ''
})
const formRef = ref<InstanceType<typeof UForm>>()

// Синхронізуємо state з props, але беремо лише потрібні поля!
watch(
    () => props.item,
    c => {
        state.title       = c.title
        state.slug        = c.slug ?? null
        state.parent_id   = c.parent_id ?? null
        state.description = c.description
    },
    { immediate: true }
)

function close() { isOpen.value = false }

// Відправляємо ТІЛЬКИ потрібні поля
async function onSubmit(e: FormSubmitEvent<Schema>) {
    try {
        const payload = {
            title      : e.data.title,
            slug       : e.data.slug?.trim() || null,
            parent_id  : typeof e.data.parent_id === 'number' ? e.data.parent_id : null,
            description: e.data.description
        }
        await $fetch(`http://localhost/api/blog/categories/${props.item.id}`, {
            method: 'PUT',
            body  : payload
        })
        toast.add({ title: 'OK', description: 'Категорію оновлено', color: 'success' })
        emit('update')
        close()
    } catch (err: any) {
        toast.add({ title: 'Помилка', description: JSON.stringify(err?.data ?? err), color: 'error' })
    }
}
</script>

<template>
    <UModal v-model:open="isOpen" title="Редагування категорії">
        <template #body>
            <UForm
                ref="formRef"
                :schema="schema"
                :state="state"
                class="space-y-4"
                @submit="onSubmit"
            >
                <UFormField label="Назва" name="title">
                    <UInput v-model="state.title" />
                </UFormField>
                <UFormField label="Slug" name="slug" hint="Необовʼязково">
                    <UInput v-model="state.slug" />
                </UFormField>
                <UFormField label="Опис" name="description">
                    <UTextarea v-model="state.description" :rows="2" />
                </UFormField>
                <UFormField label="Батьківська категорія" name="parent_id">
                    <USelect
                        v-model="state.parent_id"
                        :items="parents"
                        :loading="pStat === 'pending'"
                        label-key="title"
                        value-key="id"
                        placeholder="Без категорії"
                    />
                </UFormField>
            </UForm>
        </template>
        <template #footer>
            <UButton color="primary" @click="formRef?.submit()">Зберегти</UButton>
            <UButton variant="soft" color="gray" @click="close">Скасувати</UButton>
        </template>
    </UModal>
</template>
