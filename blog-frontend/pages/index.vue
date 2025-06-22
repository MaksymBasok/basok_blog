<template>
    <div class="p-8">
        <h1 class="text-3xl font-bold">Головна сторінка</h1>

        <div class="mt-4 flex gap-4">
            <NuxtLink to="/categories">
                <UButton color="primary">Категорії</UButton>
            </NuxtLink>

            <NuxtLink to="/posts">
                <UButton color="primary">Пости</UButton>
            </NuxtLink>
        </div>
    </div>
</template>
