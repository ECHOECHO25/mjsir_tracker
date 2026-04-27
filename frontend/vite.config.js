import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vite.dev/config/
export default defineConfig(async () => {
  // Avoid loading Tailwind's native Oxide binary on environments where it crashes.
  process.env.TAILWIND_DISABLE_OXIDE = '1'
  const { default: tailwindcss } = await import('@tailwindcss/vite')

  return {
    plugins: [
      vue(),
      tailwindcss(),
    ],
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./src', import.meta.url))
      }
    }
  }
})
