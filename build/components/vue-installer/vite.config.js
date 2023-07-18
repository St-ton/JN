import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import legacy from '@vitejs/plugin-legacy'
import vue2 from '@vitejs/plugin-vue2'
import { viteStaticCopy } from 'vite-plugin-static-copy'

export default defineConfig({
  base: '',
  build: {
    emptyOutDir: true,
    outDir: '../../.././install'
  },
  plugins: [
    viteStaticCopy({
      targets: [
        {
          src: 'install.php',
          dest: './'
        },
        {
          src: '.htaccess',
          dest: './'
        },
        {
          src: 'initial_schema.sql',
          dest: './'
        },
        {
          src: 'OpenSans-Regular.ttf',
          dest: './'
        }
      ]
    }),
    vue2(),
    legacy({
      targets: ['ie >= 11'],
      additionalLegacyPolyfills: ['regenerator-runtime/runtime']
    })
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  }
})
