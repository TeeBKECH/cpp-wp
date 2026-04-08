import dotenv from 'dotenv'
import { defineConfig } from 'vite'
import path from 'path'
import fs from 'fs'

// .env.local нужен для HMR с телефона (HMR_HOST=192.168.x.x)
dotenv.config({ path: path.resolve(process.cwd(), '.env.local') })
dotenv.config()
import fg from 'fast-glob'
import pug from 'pug'
import prettier from 'prettier'
import autoprefixer from 'autoprefixer'
import postcssUrl from 'postcss-url'
import { viteStaticCopy } from 'vite-plugin-static-copy'

const SRC_DIR = path.resolve(process.cwd(), 'src')
const PAGES_GLOB = 'src/pages/**/*.pug'
const MAIN_ENTRY = path.resolve(SRC_DIR, 'scripts/main.js')

// Парсим верхние мета-комментарии:
// //- @title: ...
// //- @description: ...
function parseMetaFromPug(filePath) {
  const src = fs.readFileSync(filePath, 'utf8')
  const meta = {}
  for (const line of src.split(/\r?\n/)) {
    const trimmed = line.trim()
    if (!trimmed) continue
    const m = trimmed.match(/^\/\/-\s*@([\w-]+)\s*:\s*(.+)$/)
    if (m) {
      meta[m[1]] = m[2]
      continue
    }
    if (!trimmed.startsWith('//-')) break
  }
  return meta
}

function scanPages() {
  const files = fg.sync(PAGES_GLOB, { dot: false })
  return files.map((file) => {
    const rel = path.relative(path.join(SRC_DIR, 'pages'), file)
    const name = rel.replace(/\.pug$/, '') // about/index -> about/index
    const outName = `${name}.html` // about/index.html
    const meta = parseMetaFromPug(file)
    return { file, name, outName, meta }
  })
}

// Простая инъекция в конец <body>
function injectToBody(html, injectString) {
  if (/(<\/body\s*>)/i.test(html)) {
    return html.replace(/<\/body\s*>/i, `${injectString}\n</body>`)
  }
  return html + injectString
}

// Простая инъекция в конец <head>
function injectToHead(html, injectString) {
  if (/(<\/head\s*>)/i.test(html)) {
    return html.replace(/<\/head\s*>/i, `${injectString}\n</head>`)
  }
  return injectString + '\n' + html
}

function replaceJpgToWebpInHtml(html) {
  return html.replace(
    /(<img\b[^>]*\bsrc=["'])([^"']+\.jpe?g)(["'][^>]*>)/gi,
    (m, p1, src, p3) => `${p1}${src.replace(/\.jpe?g$/i, '.webp')}${p3}`,
  )
}

function compilePug(file, locals, pretty) {
  const fn = pug.compileFile(file, {
    basedir: SRC_DIR, // чтобы include/extends работали от src
    pretty,
  })
  return fn(locals)
}

function renderPug(file, locals, pretty) {
  return pug.renderFile(file, {
    basedir: SRC_DIR,
    pretty,
    filename: file,
    ...locals,
  })
}

// Плагин, который дает MPA на Pug:
// - dev: страница компилируется по запросу, HMR через /@vite/client, никакой полной перекомпиляции
// - build: генерим html для всех страниц, инъектим итоговые js/css, заменяем .jpg→.webp (опционально) и prettify
function pugMpaPlugin({ injectWebpOnProd = false, prettifyOnProd = true } = {}) {
  let config
  let pages = scanPages()

  function resolvePageByUrlPath(urlPathname) {
    // Нормализуем: / -> /index.html, /about -> /about.html
    let p = urlPathname
    if (p === '/' || p === '') p = '/index.html'
    if (!p.endsWith('.html')) p = `${p}.html`
    // pages имеют outName без ведущего слеша
    const withoutSlash = p.replace(/^\//, '')
    return pages.find((pg) => pg.outName === withoutSlash)
  }

  return {
    name: 'pug-mpa',
    enforce: 'pre',

    configResolved(c) {
      config = c
    },

    configureServer(server) {
      // Рескан страниц при добавлении/удалении .pug
      const rescan = () => {
        pages = scanPages()
        server.ws.send({ type: 'full-reload' })
      }
      server.watcher.on('add', (file) => file.endsWith('.pug') && rescan())
      server.watcher.on('unlink', (file) => file.endsWith('.pug') && rescan())

      // На любое изменение .pug — просто full-reload (быстро в Vite)
      server.watcher.on('change', (file) => {
        if (file.endsWith('.pug')) server.ws.send({ type: 'full-reload' })
      })

      // Отдаём HTML как результат компиляции Pug
      server.middlewares.use(async (req, res, next) => {
        try {
          if (!req.url) return next()
          const url = new URL(req.url, 'http://localhost')
          const page = resolvePageByUrlPath(url.pathname)
          if (!page) return next()

          let html = renderPug(
            page.file,
            {
              page: {
                title: page.meta.title || 'Страница',
                description: page.meta.description || '',
                ...page.meta,
              },
              isProd: false,
            },
            true,
          )

          if (html && typeof html.then === 'function') {
            // на всякий случай; у renderFile не должно быть Promise
            html = await html
          }
          if (typeof html !== 'string') {
            throw new Error(`Pug render returned non-string for ${page.file}: ${typeof html}`)
          }

          html = injectToBody(
            html,
            [
              '<script type="module" src="/@vite/client"></script>',
              `<script type="module" src="${config.base || '/'}${path.posix.join('src', 'scripts', 'main.js')}"></script>`,
            ].join('\n'),
          )

          res.setHeader('Content-Type', 'text/html; charset=utf-8')
          res.end(html)
        } catch (e) {
          next(e)
        }
      })
    },

    // На билде генерируем HTML для всех страниц и эмитим как ассеты
    async generateBundle(options, bundle) {
      const chunks = Object.values(bundle)
      const entryChunk = chunks.find(
        (c) =>
          c.type === 'chunk' && c.isEntry && (c.facadeModuleId === MAIN_ENTRY || c.name === 'app'),
      )

      if (!entryChunk) {
        this.error(
          '[pug-mpa] Не найден entry chunk. Проверьте build.rollupOptions.input -> src/scripts/main.js',
        )
      }

      const entryJsPath = entryChunk.fileName
      const importedCss = Array.from(entryChunk.viteMetadata?.importedCss || [])
      const extraCss = chunks
        .filter((a) => a.type === 'asset' && a.fileName.endsWith('.css'))
        .map((a) => a.fileName)
      const cssFiles = Array.from(new Set([...importedCss, ...extraCss]))

      const isProd = true

      for (const page of pages) {
        try {
          let html = renderPug(
            page.file,
            {
              page: {
                title: page.meta.title || 'Страница',
                description: page.meta.description || '',
                ...page.meta,
              },
              isProd,
            },
            false,
          )

          if (typeof html !== 'string') {
            // На всякий случай (renderFile всегда строка)
            throw new Error(`Pug render returned non-string for ${page.file}: ${typeof html}`)
          }

          // CSS в <head>
          if (cssFiles.length) {
            const cssTags = cssFiles
              .map((href) => `<link rel="stylesheet" href="${config.base || '/'}${href}">`)
              .join('\n')
            html = injectToHead(html, cssTags)
          }

          // JS в конец <body>
          html = injectToBody(
            html,
            `<script type="module" crossorigin src="${config.base || '/'}${entryJsPath}"></script>`,
          )

          if (injectWebpOnProd) {
            // html = replaceJpgToWebpInHtml(html)
          }

          if (prettifyOnProd) {
            try {
              // ВАЖНО: ждём промис!
              html = await prettier.format(html, {
                parser: 'html',
                printWidth: 100,
                tabWidth: 2,
                useTabs: false,
                htmlWhitespaceSensitivity: 'ignore',
                bracketSameLine: false,
              })
            } catch (e) {
              this.warn(`[pug-mpa] prettify failed for ${page.outName}: ${e.message}`)
            }
          }

          // Доп. защита: если вдруг куда-то просочился Promise
          if (/$$object Promise$$/.test(html)) {
            this.warn(
              `[pug-mpa] В ${page.outName} обнаружен текст "[object Promise]". Проверьте кастомные пост-обработки HTML.`,
            )
          }

          this.emitFile({
            type: 'asset',
            fileName: page.outName,
            source: html, // гарантированно строка
          })
        } catch (err) {
          this.warn(`[pug-mpa] Не удалось сгенерировать ${page.outName}: ${err?.message || err}`)
        }
      }
    },
  }
}

// Генерирует robots.txt в корень build при сборке (без public)
function robotsTxtPlugin() {
  const content = `User-agent: *
Disallow: /
Disallow: /*
Disallow: *
`
  return {
    name: 'robots-txt',
    apply: 'build',
    generateBundle() {
      this.emitFile({
        type: 'asset',
        fileName: 'robots.txt',
        source: content,
      })
    },
  }
}

// Плагин для разрешения @ алиаса в CSS url()
function cssAliasPlugin() {
  return {
    name: 'css-alias-resolver',
    enforce: 'pre',
    transform(code, id) {
      if (id.endsWith('.css') || id.endsWith('.scss') || id.endsWith('.sass')) {
        // Заменяем @/ на относительный путь, который работает и в dev, и в build
        const resolved = code.replace(/url\(['"]@\/([^'"]+)['"]\)/g, (match, assetPath) => {
          try {
            const resolvedPath = path.resolve(SRC_DIR, assetPath)
            const fileDir = path.dirname(id)
            let relativePath = path.relative(fileDir, resolvedPath).replace(/\\/g, '/')

            // В Windows пути могут быть без ./ в начале, добавляем если нужно
            if (!relativePath.startsWith('.')) {
              relativePath = './' + relativePath
            }

            // Нормализуем путь: убираем лишние сегменты и исправляем двойные слеши
            relativePath = relativePath.replace(/\/\.\//g, '/').replace(/\/+/g, '/')

            // Используем относительный путь, который работает в обоих режимах
            return `url('${relativePath}')`
          } catch (e) {
            // Если не удалось разрешить, возвращаем оригинал
            console.warn(`[css-alias-resolver] Failed to resolve @/${assetPath}:`, e.message)
            return match
          }
        })
        return resolved !== code ? resolved : null
      }
    },
  }
}

export default defineConfig(({ mode }) => {
  const isProd = mode === 'production'
  // WP build is enabled via env var so we can keep normal prod build intact.
  const isWpBuild = process.env.BUILD_TARGET === 'wp'

  return {
    // Если используете public/assets — отключите плагин копирования ниже
    plugins: [
      pugMpaPlugin({
        injectWebpOnProd: false,
        prettifyOnProd: true,
      }),

      robotsTxtPlugin(),

      // Вариант, если хотите оставить src/assets -> build/assets
      // Если перейдёте на public/assets, удалите этот плагин целиком.
      viteStaticCopy({
        targets: [{ src: 'src/assets', dest: '' }], // -> build/assets/*
        // noErrorOnMissing: true можно не указывать — поведение по умолчанию
      }),

      cssAliasPlugin(),

      // Patch bundled CSS URLs for WordPress theme deployment.
      isWpBuild && {
        name: 'wp-css-asset-path-fix',
        generateBundle(options, bundle) {
          for (const fileName of Object.keys(bundle)) {
            const item = bundle[fileName]
            if (item && item.type === 'asset' && fileName.endsWith('.css') && typeof item.source === 'string') {
              item.source = item.source.replace(/url\((['"]?)\/assets\//g, 'url($1../')
            }
          }
        },
      },

      // Для старых браузеров можно раскомментировать:
      // legacy({
      //   targets: ['defaults', 'not IE 11'],
      // }),
    ],

    root: process.cwd(),
    base: isWpBuild ? './' : '/',
    publicDir: 'public', // кладите сюда только статические ассеты без обработки (например, public/assets)

    resolve: {
      alias: {
        '@': SRC_DIR,
      },
      extensions: ['.js', '.scss', '.pug'],
    },

    css: {
      devSourcemap: !isProd,
      postcss: {
        plugins: [
          autoprefixer(),
          ...(isWpBuild
            ? [
                postcssUrl({
                  url: (asset) => {
                    if (asset.url && asset.url.startsWith('/assets/')) {
                      return `/wp-content/themes/cpp-courses-theme/assets/${asset.url.replace(/^\/assets\//, '')}`
                    }
                    return asset.url
                  },
                }),
              ]
            : []),
        ],
      },
      modules: {
        // Имена классов как в webpack-конфиге
        generateScopedName: isProd ? '[hash:base64:6]' : '[name]__[local]__[hash:base64:5]',
      },
      preprocessorOptions: {
        scss: {
          quietDeps: true,
          silenceDeprecations: ['import'],
        },
      },
    },

    server: {
      host: '0.0.0.0',
      port: Number(process.env.PORT) || 5173,
      open: '/index.html',
      strictPort: false,
      // CORS: по умолчанию Vite разрешает только localhost — с телефона запросы блокировались
      cors: true,
      // HMR с телефона: в .env.local задайте HMR_HOST=192.168.x.x (ваш IP), откройте http://HMR_HOST:5173
      hmr: process.env.HMR_HOST
        ? { host: process.env.HMR_HOST, port: Number(process.env.PORT) || 5173, protocol: 'ws' }
        : true,
    },

    build: {
      outDir: 'build',
      emptyOutDir: true,
      sourcemap: false,
      cssCodeSplit: true,
      rollupOptions: {
        // Нам нужен js-энтрипоинт, чтобы собрать бандл и css
        input: {
          app: MAIN_ENTRY,
        },
        output: {
          entryFileNames: isWpBuild ? 'assets/js/[name].js' : 'assets/js/[name].[hash].js',
          chunkFileNames: isWpBuild ? 'assets/js/[name].js' : 'assets/js/[name].[hash].js',
          assetFileNames: (assetInfo) => {
            const ext = path.extname(assetInfo.name || '').toLowerCase()
            if (ext === '.css')
              return isWpBuild ? 'assets/css/[name][extname]' : 'assets/css/[name].[hash][extname]'
            if (/\.(woff2?|ttf|eot|otf)$/.test(ext))
              return isWpBuild ? 'assets/fonts/[name][extname]' : 'assets/fonts/[name].[hash][extname]'
            if (/\.(png|jpe?g|webp|svg|gif|avif)$/.test(ext))
              return isWpBuild ? 'assets/media/[name][extname]' : 'assets/media/[name].[hash][extname]'
            return isWpBuild ? 'assets/[name][extname]' : 'assets/[name].[hash][extname]'
          },
        },
      },
    },
  }
})
