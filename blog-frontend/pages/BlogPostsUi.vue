<template>
    <div class="p-4">
        <h1 class="text-3xl font-bold mb-6">Пости (Nuxt UI Table)</h1>
        <client-only placeholder="Завантажую таблицю...">
            <n-data-table
                :columns="columns"
                :data="posts"
                :pagination="pagination"
                :loading="loading"
            />
        </client-only>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { NDataTable } from 'naive-ui'

const posts = ref([])
const loading = ref(true)
const pagination = {
    pageSize: 10,
    showSizePicker: true,
    pageSizes: [5, 10, 20],
}

const columns = [
    { title: '#', key: 'id' },
    { title: 'Автор', key: 'user.name' },
    { title: 'Категорія', key: 'category.title' },
    { title: 'Заголовок', key: 'title' },
    { title: 'Дата публікації', key: 'published_at' },
]

onMounted(async () => {
    try {
        const res = await $fetch('http://localhost/api/blog/posts')
        posts.value = res.data
    } catch (e) {
        console.error('Не вдалося завантажити пости', e)
    } finally {
        loading.value = false
    }
})
</script>
