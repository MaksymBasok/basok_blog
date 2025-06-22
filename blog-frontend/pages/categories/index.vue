<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { z } from 'zod'
import type { FormSubmitEvent } from '@nuxt/ui'

/* -------------------------------------------------- */
/*  CONFIG & FETCH                                    */
/* -------------------------------------------------- */
const api = useRuntimeConfig().public.apiBase            // http://localhost/api
const { data: raw, pending, refresh } = await useFetch(
    () => `${api}/blog/categories`,
    { server: false }                                      // лише клієнт
)
const rows = computed(() => raw.value ?? [])

/* -------------------------------------------------- */
/*  FORM & VALIDATION                                 */
/* -------------------------------------------------- */
const Z = z.object({
    title:       z.string().min(3, 'Мінімум 3 символи'),
    description: z.string().max(5000).nullable().optional()
})
type Category = z.infer<typeof Z>

const show     = ref(false)
const mode     = ref<'create' | 'edit'>('create')
const editId   = ref<number|null>(null)
const form     = reactive<Category>({ title:'', description:'' })

/* -------------------------------------------------- */
/*  TABLE COLUMNS  (TanStack rules)                   */
/* -------------------------------------------------- */
const columns = [
    { id:'id',      accessorKey:'id',      header:'ID',     enableSorting:true },
    { id:'title',   accessorKey:'title',   header:'Назва',  enableSorting:true },
    { id:'actions', accessorKey:'actions', header:'Дії' }
]

/* -------------------------------------------------- */
/*  CRUD helpers                                      */
/* -------------------------------------------------- */
function openCreate () {
    Object.assign(form,{ title:'', description:'' })
    mode.value='create'; editId.value=null; show.value=true
}
function openEdit (row:any) {
    Object.assign(form,row)
    mode.value='edit'; editId.value=row.id; show.value=true
}
async function onSubmit (e:FormSubmitEvent<Category>) {
    const url    = mode.value==='create'
        ? `${api}/blog/categories`
        : `${api}/blog/categories/${editId.value}`
    const method = mode.value==='create' ? 'POST' : 'PUT'
    await $fetch(url,{ method, body:e.data })
    await refresh(); show.value=false
}
async function removeRow (row:any){
    if(confirm(`Видалити «${row.title}»?`)){
        await $fetch(`${api}/blog/categories/${row.id}`,{ method:'DELETE' })
        await refresh()
    }
}
</script>

<template>
    <div class="p-6 max-w-5xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Категорії блогу</h1>

        <UButton icon="i-heroicons-plus" @click="openCreate">Додати</UButton>

        <UTable :rows="rows" :columns="columns" :loading="pending" class="mt-6">
            <template #actions-data="{ row }">
                <UDropdown :items="[
          [{ label:'Редагувати', icon:'i-heroicons-pencil-square', click:() => openEdit(row) }],
          [{ label:'Видалити',  icon:'i-heroicons-trash',         class:'text-red-600', click:() => removeRow(row) }]
        ]">
                    <UButton size="xs" color="gray" variant="link"
                             icon="i-heroicons-ellipsis-vertical" :padded="false" />
                </UDropdown>
            </template>
        </UTable>

        <!----- Modal ─────────────────────────────────────-->
        <UModal v-model="show" :title="mode==='create'?'Нова категорія':'Редагувати категорію'">
            <UForm :state="form" :schema="Z" @submit="onSubmit" class="space-y-4">
                <UFormGroup name="title" label="Назва">
                    <UInput v-model="form.title"/>
                </UFormGroup>

                <UFormGroup name="description" label="Опис">
                    <UTextarea v-model="form.description" rows="3" placeholder="Необовʼязково"/>
                </UFormGroup>

                <div class="flex justify-end gap-3 pt-2">
                    <UButton variant="ghost" @click="show=false">Скасувати</UButton>
                    <UButton type="submit" color="primary">{{ mode==='create'?'Створити':'Зберегти' }}</UButton>
                </div>
            </UForm>
        </UModal>
    </div>
</template>
