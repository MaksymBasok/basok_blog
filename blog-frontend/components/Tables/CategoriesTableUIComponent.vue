<!-- components/CategoriesTableUIComponent.vue -->
<script setup lang="ts">
import { ref, h, resolveComponent } from 'vue'
import { getPaginationRowModel } from '@tanstack/vue-table'
import type { Row } from '@tanstack/vue-table'
import type { TableColumn } from '@nuxt/ui'
import type { BlogCategory } from '~/types/Category'

import CategoryCreateModal from '~/components/Modals/CategoryCreateModal.vue'
import CategoryEditModal   from '~/components/Modals/CategoryEditModal.vue'
import CategoryDeleteModal from '~/components/Modals/CategoryDeleteModal.vue'

const UButton = resolveComponent('UButton')
const UDropdownMenu = resolveComponent('UDropdownMenu')
const table = useTemplateRef('table')

// CRUD стани
const isCreateModalOpen = ref(false)
const isEditModalOpen   = ref(false)
const isDeleteModalOpen = ref(false)
const editItem   = ref<BlogCategory | null>(null)
const deleteItem = ref<BlogCategory | null>(null)

function openCreate()               { isCreateModalOpen.value = true }
function openEdit(category: BlogCategory)   { editItem.value = category; isEditModalOpen.value = true }
function openDelete(category: BlogCategory) { deleteItem.value = category; isDeleteModalOpen.value = true }

// Підтягування категорій
const { data, status, refresh } = await useFetch<BlogCategory[]>('http://localhost/api/blog/categories', { server:false, transform: r => r?.data ?? [] })

// Колонки для таблиці
const columns: TableColumn<BlogCategory>[] = [
    { accessorKey: 'id', header: '#' },
    { accessorKey: 'title', header: 'Назва' },
    { accessorKey: 'slug', header: 'Slug', cell: ({ row }) => row.getValue('slug') || '—' },
    {
        accessorKey: 'parent_title',
        header: 'Батьківська',
        cell: ({ row }) => row.original.parentCategory?.title ?? row.getValue('parent_title') ?? '—'
    },
    {
        id: 'actions',
        cell: ({ row }) =>
            h(
                'div',
                { class: 'text-right' },
                h(
                    UDropdownMenu,
                    {
                        content: { align: 'end' },
                        items: getRowItems(row),
                        'aria-label': 'Actions dropdown'
                    },
                    () =>
                        h(UButton, {
                            icon: 'i-lucide-ellipsis-vertical',
                            color: 'neutral',
                            variant: 'ghost',
                            class: 'ml-auto',
                            'aria-label': 'Actions dropdown'
                        })
                )
            )
    }
]

function getRowItems(row: Row<BlogCategory>) {
    return [
        { type: 'label', label: 'Дії' },
        {
            label: 'Редагувати',
            icon: 'i-lucide-edit',
            onSelect() { openEdit(row.original) }
        },
        {
            label: 'Видалити',
            icon: 'i-lucide-trash-2',
            onSelect() { openDelete(row.original) }
        }
    ]
}

const pagination = ref({
    pageIndex: 0,
    pageSize: 10
})
</script>

<template>
    <div class="w-full space-y-4 pb-4">
        <div class="flex justify-end mb-2">
            <UButton icon="i-lucide-plus" color="primary" @click="openCreate">Додати категорію</UButton>
        </div>

        <UTable
            ref="table"
            v-model:pagination="pagination"
            :data="data"
            :columns="columns"
            :loading="status === 'pending'"
            :pagination-options="{ getPaginationRowModel: getPaginationRowModel() }"
            class="flex-1"
        />

        <div class="flex justify-center border-t border-default pt-4">
            <UPagination
                :default-page="(table?.tableApi?.getState().pagination.pageIndex || 0) + 1"
                :items-per-page="table?.tableApi?.getState().pagination.pageSize"
                :total="table?.tableApi?.getFilteredRowModel().rows.length"
                @update:page="(p) => table?.tableApi?.setPageIndex(p - 1)"
            />
        </div>

        <!-- CRUD модалки -->
        <CategoryCreateModal v-model="isCreateModalOpen" @update="refresh" />
        <CategoryEditModal
            v-if="editItem"
            v-model="isEditModalOpen"
            :item="editItem"
            @update="refresh"
        />
        <CategoryDeleteModal
            v-if="deleteItem"
            v-model="isDeleteModalOpen"
            :id="deleteItem.id"
            :title="deleteItem.title"
            @update="refresh"
        />
    </div>
</template>
