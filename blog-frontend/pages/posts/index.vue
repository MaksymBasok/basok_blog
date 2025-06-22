<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { z } from 'zod'
import type { FormSubmitEvent } from '@nuxt/ui'

const api = useRuntimeConfig().public.apiBase

/* ---------- завантажуємо категорії для select ------- */
const { data: catRaw } = await useFetch(
    () => `${api}/blog/categories`,
    { server:false }
)
const catOpts = computed(() =>
    (catRaw.value ?? []).map((c:any)=>({ label:c.title, value:c.id }))
)

/* ---------- таблиця постів -------------------------- */
const { data: postRaw, pending, refresh } = await useFetch(
    () => `${api}/blog/posts`,
    { server:false }
)
const rows = computed(()=> postRaw.value ?? [])

/* ---------- схема форми ----------------------------- */
const Z = z.object({
    title:        z.string().min(5),
    category_id:  z.number().positive(),
    content_raw:  z.string().min(20),
    excerpt:      z.string().max(1000).nullable().optional(),
    is_published: z.boolean().optional()
})
type Post = z.infer<typeof Z>

const modal   = ref(false)
const mode    = ref<'create'|'edit'>('create')
const editId  = ref<number|null>(null)
const form    = reactive<Post>({
    title:'', category_id:0, content_raw:'', excerpt:'', is_published:false
})

/* ---------- колонки таблиці ------------------------- */
const columns = [
    { id:'id',      accessorKey:'id',              header:'ID',        enableSorting:true },
    { id:'title',   accessorKey:'title',           header:'Заголовок', enableSorting:true },
    { id:'cat',     accessorKey:'category.title',  header:'Категорія' },
    { id:'author',  accessorKey:'user.name',       header:'Автор' },
    { id:'actions', accessorKey:'actions',         header:'Дії' }
]

/* ---------- helpers -------------------------------- */
function openNew(){
    Object.assign(form,{ title:'', category_id:catOpts.value[0]?.value ?? 0,
        content_raw:'', excerpt:'', is_published:false })
    mode.value='create'; modal.value=true
}
function openEdit(row:any){
    Object.assign(form,{
        title:row.title,
        category_id: row.category?.id ?? row.category_id,
        content_raw: row.content_raw,
        excerpt: row.excerpt,
        is_published: !!row.is_published
    })
    editId.value=row.id; mode.value='edit'; modal.value=true
}
async function onSubmit(e:FormSubmitEvent<Post>){
    const url    = mode.value==='create'
        ? `${api}/blog/posts`
        : `${api}/blog/posts/${editId.value}`
    const method = mode.value==='create' ? 'POST' : 'PUT'
    await $fetch(url,{ method, body:e.data })
    await refresh(); modal.value=false
}
async function removeRow(row:any){
    if(confirm(`Видалити пост «${row.title}»?`)){
        await $fetch(`${api}/blog/posts/${row.id}`,{ method:'DELETE' })
        await refresh()
    }
}
</script>

<template>
    <div class="p-6 max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Пости блогу</h1>

        <UButton icon="i-heroicons-plus" @click="openNew">Додати пост</UButton>

        <UTable :rows="rows" :columns="columns" :loading="pending" class="mt-6">
            <template #actions-data="{ row }">
                <UDropdown :items="[
          [{ label:'Редагувати', icon:'i-heroicons-pencil-square', click:() => openEdit(row) }],
          [{ label:'Видалити',  icon:'i-heroicons-trash', class:'text-red-600', click:() => removeRow(row) }]
        ]">
                    <UButton size="xs" color="gray" variant="link"
                             icon="i-heroicons-ellipsis-vertical" :padded="false" />
                </UDropdown>
            </template>
        </UTable>

        <!----- Modal ─────────────────────────────────────-->
        <UModal v-model="modal" :title="mode==='create'?'Новий пост':'Редагувати пост'">
            <UForm :state="form" :schema="Z" @submit="onSubmit" class="space-y-4">
                <UFormGroup name="title" label="Заголовок">
                    <UInput v-model="form.title"/>
                </UFormGroup>

                <UFormGroup name="category_id" label="Категорія">
                    <USelectMenu v-model="form.category_id" :options="catOpts"/>
                </UFormGroup>

                <UFormGroup name="content_raw" label="Контент">
                    <UTextarea v-model="form.content_raw" rows="6"/>
                </UFormGroup>

                <UFormGroup name="excerpt" label="Опис">
                    <UTextarea v-model="form.excerpt" rows="3"/>
                </UFormGroup>

                <UFormGroup name="is_published" label="Опублікувати">
                    <USwitch v-model="form.is_published"/>
                </UFormGroup>

                <div class="flex justify-end gap-3 pt-2">
                    <UButton variant="ghost" @click="modal=false">Скасувати</UButton>
                    <UButton type="submit" color="primary">{{ mode==='create'?'Створити':'Зберегти' }}</UButton>
                </div>
            </UForm>
        </UModal>
    </div>
</template>
