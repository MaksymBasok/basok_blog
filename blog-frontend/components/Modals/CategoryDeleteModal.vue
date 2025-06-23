<!-- CategoryDeleteModal.vue -->
<script setup lang="ts">
const props  = defineProps<{ id: number, title: string }>()
const isOpen = defineModel<boolean>()
const emit   = defineEmits(['update'])
const toast  = useToast()

const state = ref<'confirm'|'loading'|'success'|'error'>('confirm')

function close() {
    if (state.value === 'loading') return
    state.value = 'confirm'
    isOpen.value = false
}
async function submit() {
    state.value = 'loading'
    try {
        await $fetch(`http://localhost/api/blog/categories/${props.id}`, { method: 'DELETE' })
        state.value = 'success'
        toast.add({ title: 'Успіх', description: 'Категорію видалено!', color: 'success' })
        emit('update')
    } catch {
        state.value = 'error'
        toast.add({ title: 'Помилка', description: 'Не вдалося видалити категорію', color: 'error' })
    }
}
</script>

<template>
    <UModal v-model:open="isOpen" title="Видалення категорії">
        <template #body>
            <div v-if="state === 'confirm'" class="text-center space-y-2">
                <p>Ви впевнені, що хочете видалити <b>{{ props.title }}</b>?</p>
                <p class="text-red-500">Цю дію неможливо скасувати.</p>
            </div>
            <div v-else-if="state === 'loading'" class="flex flex-col items-center">
                <UIcon name="i-heroicons-arrow-path" class="animate-spin h-12 w-12" />
                <p class="mt-4 text-lg">Видалення…</p>
            </div>
            <div v-else-if="state === 'success'" class="flex flex-col items-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                    <UIcon name="i-heroicons-check" class="h-6 w-6 text-green-600" />
                </div>
                <h3 class="mt-4 text-lg font-medium">Категорію успішно видалено!</h3>
            </div>
            <div v-else class="flex flex-col items-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <UIcon name="i-heroicons-x-mark" class="h-6 w-6 text-red-600" />
                </div>
                <h3 class="mt-4 text-lg font-medium">Помилка!</h3>
                <p class="mt-1 text-sm">Будь ласка, спробуйте пізніше.</p>
            </div>
        </template>
        <template #footer>
            <UButton
                v-if="state === 'confirm'"
                color="error"
                variant="outline"
                @click="close"
                label="Ні"
            />
            <UButton
                v-if="state === 'confirm'"
                color="primary"
                class="ml-2"
                @click="submit"
                label="Так"
            />
            <UButton
                v-if="state === 'success' || state === 'error'"
                color="info"
                variant="outline"
                @click="close"
                label="Закрити"
            />
            <UButton
                v-if="state === 'error'"
                color="warning"
                variant="outline"
                class="ml-2"
                @click="submit"
                label="Спробувати знову"
            />
        </template>
    </UModal>
</template>
