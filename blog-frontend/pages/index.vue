<template>
    <div class="flex flex-col items-center justify-center min-h-screen gap-6">
        <UCard class="p-8 w-full max-w-md flex flex-col items-center gap-4 shadow-lg">
            <UIcon name="i-lucide-rocket" class="size-10 text-emerald-500" />
            <h1 class="text-2xl font-bold mb-2">Ласкаво просимо!</h1>
            <UButton block to="/BlogPosts" color="primary" variant="solid">Таблиця постів</UButton>
            <UButton block to="/blogpostsui" color="emerald" variant="outline">Таблиця постів з Nuxt UI</UButton>
            <UButton block to="/posts/11" color="orange" variant="solid">Сторінка з постом</UButton>
            <UButton block to="/BlogCategories" color="gray" variant="outline">Таблиця з категоріями</UButton>
        </UCard>
    </div>
</template>
