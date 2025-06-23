<script setup lang="ts">
import { reactive, ref } from 'vue'
import { z }              from 'zod'
import type { FormSubmitEvent, UForm } from '@nuxt/ui'

/* ── IO ───────────────────────────── */
const isOpen = defineModel<boolean>()
const emit   = defineEmits(['update'])
const toast  = useToast()

/* ── Категорії для селектора ──────── */
const { data: categories, status: catStatus } = await useFetch(
    'http://localhost/api/blog/categories',
    {
        server  : false,
        lazy    : true,
        transform: v => (Array.isArray(v) ? v : v?.data ?? [])
    }
)

/* ── Zod-схема (усі поля, які приймає бек) ── */
const schema = z.object({
    title        : z.string().min(3),
    slug         : z.string().min(3).nullable().optional(),
    excerpt      : z.string().min(3).nullable().optional(),
    content_raw  : z.string().min(5),
    category_id  : z.number(),
    is_published : z.boolean().default(false),
    published_at : z.string().nullable().optional()
})
type Schema = z.infer<typeof schema>

/* ── React-стан форми ──────────────── */
const state = reactive<Schema>({
    title        : '',
    slug         : null,
    excerpt      : null,
    content_raw  : '',
    category_id  : null as any,
    is_published : false,
    published_at : null
})

/* ── ref на форму, щоб викликати submit вручну ── */
const formRef = ref<InstanceType<typeof UForm>>()

/* ── helpers ───────────────────────── */
function resetState () {
    Object.assign(state, {
        title        : '',
        slug         : null,
        excerpt      : null,
        content_raw  : '',
        category_id  : null,
        is_published : false,
        published_at : null
    })
}

function close () {
    isOpen.value = false
    resetState()
}

async function onSubmit (e: FormSubmitEvent<Schema>) {
    try {
        await $fetch('http://localhost/api/blog/posts', {
            method: 'POST',
            body  : e.data
        })
        toast.add({ title: 'OK', description: 'Пост створено', color: 'success' })
        emit('update')
        close()
    } catch (err: any) {
        toast.add({
            title      : 'Помилка',
            description: err?.data?.message ?? 'Не вдалося створити пост',
            color      : 'error'
        })
    }
}
</script>

<template>
    <UModal v-model:open="isOpen" title="Створення поста">
        <!-- ═════════ BODY ═════════ -->
        <template #body>
            <UForm
                ref="formRef"
                :schema="schema"
                :state="state"
                class="space-y-4"
                @submit="onSubmit"
            >
                <!-- заголовок -->
                <UFormField label="Заголовок" name="title">
                    <UInput v-model="state.title" />
                </UFormField>

                <!-- slug / excerpt -->
                <UFormField label="Slug" name="slug" hint="Необовʼязково">
                    <UInput v-model="state.slug" />
                </UFormField>

                <UFormField label="Excerpt" name="excerpt" hint="Необовʼязково">
                    <!-- rows потрібен числовий, тому :rows -->
                    <UTextarea v-model="state.excerpt" :rows="2" />
                </UFormField>

                <!-- текст -->
                <UFormField label="Текст" name="content_raw">
                    <UTextarea v-model="state.content_raw" :rows="6" />
                </UFormField>

                <!-- категорія -->
                <UFormField label="Категорія" name="category_id">
                    <USelect
                        v-model="state.category_id"
                        :items="categories"
                        :loading="catStatus === 'pending'"
                        label-key="title"
                        value-key="id"
                        placeholder="Оберіть категорію"
                    />
                </UFormField>

                <!-- публікація -->
                <div class="flex items-center gap-2">
                    <UCheckbox v-model="state.is_published" label="Опублікувати" />
                    <UFormField
                        v-if="state.is_published"
                        name="published_at"
                        class="flex-1"
                    >
                        <UInput
                            type="datetime-local"
                            v-model="state.published_at"
                        />
                    </UFormField>
                </div>
            </UForm>
        </template>

        <!-- ═════════ FOOTER ═════════ -->
        <template #footer>
            <UButton color="primary" @click="formRef?.submit()">Створити</UButton>
            <UButton variant="soft" color="gray" @click="close">Скасувати</UButton>
        </template>
    </UModal>
</template>
