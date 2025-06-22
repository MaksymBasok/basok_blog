<template>
    <div class="p-6 max-w-4xl mx-auto">
        <div v-if="post" class="bg-white shadow p-4 rounded">
            <h1 class="text-2xl font-bold mb-2">{{ post.title }}</h1>
            <p class="text-gray-600 mb-4">
                Категорія: {{ post.category?.title || 'Без категорії' }} |
                Автор: {{ post.user?.name || 'Невідомо' }}
            </p>

            <p v-if="post.published_at" class="mt-4 text-sm text-gray-500">
                Опубліковано: {{ new Date(post.published_at).toLocaleString() }}
            </p>
            <p v-else class="mt-4 text-sm text-red-500 italic">
                Не опубліковано
            </p>

            <p class="text-gray-800 whitespace-pre-line mt-4">
                {{ post.content_raw }}
            </p>
        </div>

        <div v-else class="text-center py-10 text-gray-600">
            Завантаження...
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const post = ref<any>(null)

const fetchPost = async () => {
    try {
        const response = await $fetch(`http://localhost/api/blog/posts/${route.params.id}`)
        post.value = response
    } catch (error) {
        console.error('Помилка завантаження поста', error)
    }
}

onMounted(fetchPost)
</script>
