<script setup lang="ts">
const props = defineProps<{ id: number; title: string }>()
const emit = defineEmits(['update'])
const isModalOpen = defineModel<boolean>()
const toast = useToast()
const currentState = ref<'confirm' | 'loading' | 'success' | 'error'>('confirm')

function close() {
    if (currentState.value === 'loading') return
    currentState.value = 'confirm'
    isModalOpen.value = false
}

async function submitData() {
    currentState.value = 'loading'
    await useFetch(`http://localhost/api/blog/posts/${props.id}`, {
        method: 'DELETE',
        onResponse({ response }) {
            if (response.status === 204) {
                currentState.value = 'success'
                toast.add({ title: 'Успіх', description: 'Пост видалено!', color: 'success' })
                emit('update')
            } else {
                currentState.value = 'error'
                toast.add({ title: 'Помилка', description: 'Не вдалося видалити пост', color: 'error' })
            }
        },
        onResponseError() {
            currentState.value = 'error'
            toast.add({ title: 'Помилка', description: 'Не вдалося видалити пост', color: 'error' })
        }
    })
}
</script>

<template>
    <UModal v-model:open="isModalOpen" title="Видалення поста">
        <template #body>
            <div v-if="currentState === 'confirm'" class="text-center">
                <p>Ви впевнені, що хочете видалити <b>{{ props.title }}</b>?</p>
                <p class="text-red-500">Ця дія не може бути скасована.</p>
            </div>
            <div v-else-if="currentState === 'loading'" class="flex flex-col items-center justify-center">
                <UIcon name="i-heroicons-arrow-path" class="animate-spin h-12 w-12 mx-auto" />
                <p class="mt-4 text-lg">Завантаження...</p>
            </div>
            <div v-else-if="currentState === 'success'" class="flex flex-col items-center justify-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                    <UIcon name="i-heroicons-check" class="h-6 w-6 text-green-600" />
                </div>
                <h3 class="mt-4 text-lg font-medium">Пост успішно видалено!</h3>
            </div>
            <div v-else-if="currentState === 'error'" class="flex flex-col items-center justify-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <UIcon name="i-heroicons-x-mark" class="h-6 w-6 text-red-600" />
                </div>
                <h3 class="mt-4 text-lg font-medium">Помилка!</h3>
                <p class="mt-1 text-sm">Будь ласка, спробуйте пізніше.</p>
            </div>
        </template>
        <template #footer>
            <UButton
                v-if="currentState === 'confirm'"
                color="error"
                variant="outline"
                class="min-w-[100px]"
                @click="close"
                label="Ні"
            />
            <UButton
                v-if="currentState === 'confirm'"
                color="primary"
                class="min-w-[100px]"
                @click="submitData"
                label="Так"
            />
            <UButton
                v-if="currentState === 'success' || currentState === 'error'"
                color="info"
                variant="outline"
                class="min-w-[100px]"
                @click="close"
                label="Закрити"
            />
            <UButton
                v-if="currentState === 'error'"
                color="warning"
                variant="outline"
                class="min-w-[100px]"
                @click="submitData"
                label="Спробувати знову"
            />
        </template>
    </UModal>
</template>
