<script setup lang="ts">
import { reactive, ref, watch } from 'vue'
import { z }                    from 'zod'
import type { FormSubmitEvent, UForm } from '@nuxt/ui'
import type Post from '~/types/Post/Post'

/* ── props / io ───────────────────── */
const props   = defineProps<{ item: Post }>()
const isOpen  = defineModel<boolean>()
const emit    = defineEmits(['update'])
const toast   = useToast()

/* ── категорії ────────────────────── */
const { data: categories, status: catStatus } = await useFetch(
    'http://localhost/api/blog/categories',
    {
        server   : false,
        lazy     : true,
        transform: v => (Array.isArray(v) ? v : v?.data ?? [])
    }
)

/* ── схема (та сама, що й у Create) ─ */
const schema = z.object({
    title        : z.string().min(3),
    slug         : z.string().nullable().optional(),
    excerpt      : z.string().nullable().optional(),
    content_raw  : z.string().min(5),
    category_id  : z.number(),
    is_published : z.boolean(),
    published_at : z.string().nullable().optional()
})
type Schema = z.infer<typeof schema>

/* ── state + sync із props.item ───── */
const state = reactive<Schema>({} as any)

watch(
    () => props.item,
    p => {
        Object.assign(state, {
            title        : p.title,
            slug         : p.slug,
            excerpt      : p.excerpt,
            content_raw  : p.content_raw,
            category_id  : p.category_id,
            is_published : !!p.is_published,
            published_at : p.published_at
        })
    },
    { immediate: true }
)

/* ── form ref, щоб викликати submit з футера ─ */
const formRef = ref<InstanceType<typeof UForm>>()

function close () {
    isOpen.value = false
}

async function onSubmit (e: FormSubmitEvent<Schema>) {
    try {
        await $fetch(`http://localhost/api/blog/posts/${props.item.id}`, {
            method: 'PUT',
            body  : e.data
        })
        toast.add({ title: 'OK', description: 'Пост оновлено', color: 'success' })
        emit('update')
        close()
    } catch (err: any) {
        toast.add({
            title      : 'Помилка',
            description: err?.data?.message ?? 'Не вдалося оновити пост',
            color      : 'error'
        })
    }
}
</script>

<template>
    <UModal v-model:open="isOpen" title="Редагування поста">
        <!-- ═════════ BODY ═════════ -->
        <template #body>
            <UForm
                ref="formRef"
                :schema="schema"
                :state="state"
                class="space-y-4"
                @submit="onSubmit"
            >
                <!-- Заголовок -->
                <UFormField label="Заголовок" name="title">
                    <UInput v-model="state.title" />
                </UFormField>

                <!-- Slug / Excerpt -->
                <UFormField label="Slug" name="slug" hint="Необовʼязково">
                    <UInput v-model="state.slug" />
                </UFormField>

                <UFormField label="Excerpt" name="excerpt" hint="Необовʼязково">
                    <UTextarea v-model="state.excerpt" :rows="2" />
                </UFormField>

                <!-- Текст -->
                <UFormField label="Текст" name="content_raw">
                    <UTextarea v-model="state.content_raw" :rows="6" />
                </UFormField>

                <!-- Категорія -->
                <UFormField label="Категорія" name="category_id">
                    <USelect
                        v-model="state.category_id"
                        :items="categories"
                        :loading="catStatus === 'pending'"
                        label-key="title"
                        value-key="id"
                    />
                </UFormField>

                <!-- Публікація -->
                <div class="flex items-center gap-2">
                    <UCheckbox v-model="state.is_published" label="Опубліковано" />
                    <UFormField
                        v-if="state.is_published"
                        name="published_at"
                        class="flex-1"
                    >
                        <UInput type="datetime-local" v-model="state.published_at" />
                    </UFormField>
                </div>
            </UForm>
        </template>

        <!-- ═════════ FOOTER ═════════ -->
        <template #footer>
            <UButton color="primary" @click="formRef?.submit()">Зберегти</UButton>
            <UButton variant="soft" color="gray" @click="close">Скасувати</UButton>
        </template>
    </UModal>
</template>
