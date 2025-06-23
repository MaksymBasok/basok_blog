<script setup lang="ts">
import { reactive, ref }          from 'vue'
import { z }                      from 'zod'
import type { FormSubmitEvent, UForm } from '@nuxt/ui'

/* ── IO ─────────────────────────────────────────────── */
const isOpen = defineModel<boolean>()
const emit   = defineEmits(['update'])
const toast  = useToast()

/* ── список категорій для вибору parent_id ─────────── */
const { data: parents, status: pStat } = await useFetch(
    'http://localhost/api/blog/categories',
    {
        server   : false,
        lazy     : true,
        transform: v => (Array.isArray(v) ? v : v?.data ?? [])
    }
)

/* ── схема (± як у Laravel Request) ─────────────────── */
const schema = z.object({
    title       : z.string().min(3),
    slug        : z.string().optional().nullable(),
    parent_id   : z.number().nullable(),
    description : z.string().min(3)
})
type Schema = z.infer<typeof schema>

/* ── state + form ref ───────────────────────────────── */
const state   = reactive<Schema>({
    title:'', slug:null, parent_id:null, description:''
})
const formRef = ref<InstanceType<typeof UForm>>()

/* ── helpers ────────────────────────────────────────── */
function reset () {
    Object.assign(state,{ title:'', slug:null, parent_id:null, description:'' })
}
function close () {
    isOpen.value = false
    reset()
}
async function onSubmit (e: FormSubmitEvent<Schema>) {
    try {
        await $fetch('http://localhost/api/blog/categories',{
            method:'POST',
            body  : e.data
        })
        toast.add({ title:'OK', description:'Категорію створено', color:'success' })
        emit('update')
        close()
    } catch (err:any) {
        toast.add({ title:'Помилка', description: err?.data?.message ?? 'Не вдалося', color:'error' })
    }
}
</script>

<template>
    <UModal v-model:open="isOpen" title="Створення категорії">
        <!-- ═════════ BODY ═════════ -->
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

        <!-- ═════════ FOOTER ═════════ -->
        <template #footer>
            <UButton color="primary" @click="formRef?.submit()">Створити</UButton>
            <UButton variant="soft" color="gray" @click="close">Скасувати</UButton>
        </template>
    </UModal>
</template>
