export default defineNuxtConfig({
    modules: ['@nuxt/ui'],
    runtimeConfig: {
        public: {
            apiBase: 'http://localhost/api'
        }
    }
})
