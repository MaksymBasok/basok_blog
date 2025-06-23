<script setup lang="ts">
import type Post from '~/types/Post/Post'
import { unref } from 'vue'

const props = defineProps<{
    id: string | number | Ref<string | number>
}>()

/**
 * ▸ 3 `unref(props.id)` → отримуємо чисте значення, навіть якщо прийшла ref
 */
const { data, pending, error } = await useFetch<Post>(
    () => `http://localhost/api/blog/posts/${unref(props.id)}`,
    {
        server: false,
        transform(post: Post) {
            if (post.published_at) {
                post.published_at = new Date(post.published_at).toLocaleString('en-US', {
                    year: 'numeric',
                    day: 'numeric',
                    month: 'short',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                })
            }
            return post
        }
    }
)
</script>

<template>
    <NotFoundComponent v-if="error" />

    <div
        v-else
        class="m-4 bg-gray-500 rounded-lg p-4 flex flex-col gap-4"
    >
        <h2 class="text-xl font-bold text-white mb-2">
            {{ data?.title }}
        </h2>

        <div class="flex items-center gap-2">
            <img
                :src="data?.user?.profile_photo_url"
                alt="avatar"
                class="rounded-full w-6 h-6 object-cover"
            />
            <span class="text-gray-300">{{ data?.user?.name ?? 'Невідомий автор' }}</span>
        </div>

        <p>{{ data?.content_raw }}</p>

        <div class="flex flex-col items-end gap-1 text-gray-300">
            <div>Категорія: {{ data?.category?.title ?? '—' }}</div>
            <div>Дата публікації: {{ data?.published_at ?? '—' }}</div>
        </div>
    </div>
</template>
