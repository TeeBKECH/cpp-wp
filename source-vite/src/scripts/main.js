import Swiper from 'swiper'
import { Navigation, Pagination } from 'swiper/modules'
import { Fancybox } from '@fancyapps/ui/dist/fancybox/'
import AOS from 'aos'

import { initResponsiveSwiperAll } from '@/scripts/components/swiper.js'
import { initPhoneMasks } from '@/scripts/components/phone-mask.js'
import { buildToc } from '@/scripts/components/toc.js'
import { initModalSystem, registerModal } from '@/scripts/components/modal.js'
import { initCf7FormToasts } from '@/scripts/components/cf7-toast.js'
import { attachScrollVisibility } from '@/scripts/utils/scroll-visibility.js'

import {
  initViewportHeight,
  setHeaderHeight,
  setHeaderHeightPx,
} from '@/scripts/utils/viewport-height.js'

import '@/scripts/components/smooth-scroll.js'

import '@/styles/styles.scss'

// Инициализируем viewport height для мобильных устройств
initViewportHeight()

function getPageIdFromUrl() {
  const path = window.location.pathname.replace(/\/+/g, '/').replace(/^\/|\/$/g, '') // убираем ведущий/замыкающий слеш
  if (!path || path === '') return 'index'
  // /about -> about, /about/index.html -> about/index
  return path.replace(/\.html$/i, '') // about или about/index
}

const pageModules = import.meta.glob('./pages/**/*.js') // создаст ленивые загрузчики

async function boot() {
  const pageId = getPageIdFromUrl()
  // Пытаемся найти модуль страницы по двум конвенциям:
  // 1) ./pages/${pageId}.js (например, pages/media.js)
  // 2) ./pages/${lastSegment}/index.js (например, pages/media/index.js)
  const last = pageId.split('/').pop()
  const candidates = [`./pages/${pageId}.js`, `./pages/${last}/index.js`]

  for (const key of candidates) {
    const loader = pageModules[key]
    if (loader) {
      const mod = await loader()
      // вызываем init() или default(), если есть
      if (typeof mod.init === 'function') await mod.init()
      else if (typeof mod.default === 'function') await mod.default()
      break
    }
  }
}
boot()

document.addEventListener('DOMContentLoaded', (e) => {
  // AOS Section Animation
  AOS.init({
    duration: 800,
    once: false, // анимация только один раз
    offset: 100, // появление за 100px до границы видимости
  })
  /*
   * Toc for Single
   */
  buildToc({
    root: '.page-content_main',
    toc: '#toc-list',
    // Верстка: h2.section_title; Gutenberg: h2/h3.wp-block-heading (и обычные h2/h3 в контенте)
    h2Sel: 'h2.section_title, .section_header > h2, h2.wp-block-heading, h2',
    h3Sel: 'h3.section_title, .section_header > h3, h3.wp-block-heading, h3',
  })
  /*
   * Phone Masks
   */
  initPhoneMasks()

  /*
   * ==== Swipers
   */
  // Blog Swiper
  initResponsiveSwiperAll('.blog_grid--swiper', (root) => {
    const section = root.closest('.blog')
    const prevEl = section?.querySelector('.swiper_navigation-btn--prev')
    const nextEl = section?.querySelector('.swiper_navigation-btn--next')
    const pagination = section?.querySelector('.swiper_navigation-pages')
    return {
      Swiper, // передаём класс
      modules: [Navigation, Pagination],
      itemsSelector: '.blog_card',
      breakpoint: '(max-width: 9999px)',
      slidesPerView: 1.4,
      spaceBetween: 16,
      loop: false,
      navigation: {
        prevEl,
        nextEl,
        createInside: false,
      },
      pagination: {
        el: pagination,
        clickable: true,
        createInside: false,
        type: 'fraction',
      },
      breakpoints: {
        320: {
          slidesPerView: 1.4,
          spaceBetween: 16,
        },
        992: {
          slidesPerView: 3,
          spaceBetween: 40,
        },
      },
      extendSwiperOptions: (opts) => ({
        ...opts,
        speed: 500,
      }),
    }
  })
  // Services (Другие услуги) Swiper
  initResponsiveSwiperAll('.services_grid--swiper', (root) => {
    const section = root.closest('.services')
    const prevEl = section?.querySelector('.swiper_navigation-btn--prev')
    const nextEl = section?.querySelector('.swiper_navigation-btn--next')
    const pagination = section?.querySelector('.swiper_navigation-pages')
    return {
      Swiper,
      modules: [Navigation, Pagination],
      itemsSelector: '.courses_card',
      breakpoint: '(max-width: 9999px)',
      slidesPerView: 1.4,
      spaceBetween: 16,
      loop: false,
      navigation: {
        prevEl,
        nextEl,
        createInside: false,
      },
      pagination: {
        el: pagination,
        clickable: true,
        createInside: false,
        type: 'fraction',
      },
      breakpoints: {
        320: {
          slidesPerView: 1.4,
          spaceBetween: 16,
        },
        992: {
          slidesPerView: 3,
          spaceBetween: 40,
        },
      },
      extendSwiperOptions: (opts) => ({
        ...opts,
        speed: 500,
      }),
    }
  })
  // Gallery Services Swiper
  initResponsiveSwiperAll('.gallery_items', (root) => {
    const section = root.closest('.gallery--service')
    const prevEl = section?.querySelector('.swiper_navigation-btn--prev')
    const nextEl = section?.querySelector('.swiper_navigation-btn--next')
    const pagination = section?.querySelector('.swiper_navigation-pages')
    return {
      Swiper, // передаём класс
      modules: [Navigation, Pagination],
      itemsSelector: '.gallery_item',
      breakpoint: '(max-width: 9999px)',
      slidesPerView: 1,
      loop: false,
      navigation: {
        prevEl,
        nextEl,
        createInside: false,
      },
      pagination: {
        el: pagination,
        clickable: true,
        createInside: false,
        type: 'fraction',
      },
      extendSwiperOptions: (opts) => ({
        ...opts,
        speed: 500,
      }),
    }
  })
  // Courses Swiper
  initResponsiveSwiperAll('.courses_grid--courses', (root) => {
    return {
      Swiper,
      modules: [],
      itemsSelector: '.courses_card',
      breakpoint: '(max-width: 992px)',
      slidesPerView: 1.4,
      spaceBetween: 16,
      loop: false,
      extendSwiperOptions: (opts) => ({
        ...opts,
        speed: 500,
      }),
    }
  })
  // Vacancies Swiper
  initResponsiveSwiperAll('.vacancies_grid--swiper', (root) => {
    return {
      Swiper,
      modules: [],
      itemsSelector: '.vacancies_card',
      breakpoint: '(max-width: 992px)',
      slidesPerView: 1.4,
      spaceBetween: 16,
      loop: false,
      extendSwiperOptions: (opts) => ({
        ...opts,
        speed: 500,
      }),
    }
  })
  // Teachers Swiper
  initResponsiveSwiperAll('.teachers_grid--teachers', (root) => {
    return {
      Swiper,
      modules: [],
      itemsSelector: '.teachers_card',
      breakpoint: '(max-width: 992px)',
      slidesPerView: 2.4,
      spaceBetween: 16,
      loop: false,
      breakpoints: {
        320: {
          slidesPerView: 1.4,
          spaceBetween: 16,
        },
        576: {
          slidesPerView: 2.4,
          spaceBetween: 24,
        },
      },
      extendSwiperOptions: (opts) => ({
        ...opts,
        speed: 500,
      }),
    }
  })

  // Обновляем AOS после инициализации всех Swiper'ов
  // Используем небольшую задержку, чтобы Swiper'ы успели полностью инициализироваться
  setTimeout(() => {
    AOS.refresh()
  }, 300)

  /*
   * Partners — бесконечная бегущая строка без рывков (JS-анимация, сброс в момент невидимого перехода)
   */
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)')
  const MARQUEE_PX_PER_SEC = 60

  document.querySelectorAll('.partners_line').forEach((line) => {
    const inner = line.querySelector('.partners_line-inner')
    const track = line.querySelector('.partners_track')
    if (!inner || !track) return

    const clone = track.cloneNode(true)
    clone.setAttribute('aria-hidden', 'true')
    inner.appendChild(clone)

    if (prefersReducedMotion.matches) return

    let offset = 0
    let trackWidth = 0
    let lastTime = null

    function measure() {
      trackWidth = track.offsetWidth
    }

    function tick(time) {
      if (trackWidth <= 0) {
        measure()
        if (trackWidth <= 0) {
          requestAnimationFrame(tick)
          return
        }
      }
      lastTime = lastTime ?? time
      const delta = (time - lastTime) / 1000
      lastTime = time
      offset -= MARQUEE_PX_PER_SEC * delta
      // Сброс на «невидимую» границу: держим offset в диапазоне (-trackWidth, 0], цикл бесконечный
      while (offset <= -trackWidth) {
        offset += trackWidth
      }
      inner.style.transform = `translateX(${offset}px)`
      requestAnimationFrame(tick)
    }

    // Запуск после отрисовки (два кадра — лейаут с клоном уже готов)
    requestAnimationFrame(() => {
      requestAnimationFrame(tick)
    })
    window.addEventListener('resize', measure)
  })

  /*
   * FancyBox
   */
  // Preview Image
  Fancybox.bind('[data-fancybox="preview"]', {})
  // Gallery - динамическая инициализация для всех групп галереи
  const galleryGroups = new Set()
  document
    .querySelectorAll(
      '.gallery [data-fancybox], .gallery-mosaic [data-fancybox], .room_gallery [data-fancybox], .orders_list--photos [data-fancybox]',
    )
    .forEach((el) => {
      const group = el.getAttribute('data-fancybox')
      if (group && !galleryGroups.has(group)) {
        galleryGroups.add(group)
        Fancybox.bind(`[data-fancybox="${group}"]`, {})
      }
    })

  /*
   * Accordions
   */
  const accordions = document.querySelectorAll('[data-accordion]')
  if (accordions?.length > 0) {
    accordions.forEach((acc) => {
      const trigger = acc.querySelector(`[data-accordion-trigger]`)
      const content = acc.querySelector(`[data-accordion-content]`)
      trigger.addEventListener('click', (e) => {
        acc.classList.toggle('open')
      })
    })
  }

  /*
   * Header
   */
  const header = document.querySelector('.header')

  /*
   * Modals
   */
  initModalSystem()
  initCf7FormToasts()
  // Регистрируем модалки
  const burgerBtn = document.querySelector('.burger, [data-modal="mobile-menu"]')
  registerModal('mobile-menu', {
    closeOnBackdrop: true,
    closeOnEscape: true,
    exclusive: true,
    onOpen: (modal) => {
      if (burgerBtn) burgerBtn.classList.add('active')
      if (header) header.classList.add('menu-open')
    },
    onClose: (modal) => {
      if (burgerBtn) burgerBtn.classList.remove('active')
      if (header) header.classList.remove('menu-open')
      // Закрываем все подменю при закрытии модалки
      modal.querySelectorAll('.menu_item.is-open').forEach((item) => {
        item.classList.remove('is-open')
        const btn = item.querySelector('[data-menu-drop-toggle]')
        if (btn) btn.setAttribute('aria-expanded', 'false')
      })
    },
  })

  registerModal('lead-form-modal', {
    closeOnBackdrop: true,
    closeOnEscape: true,
    exclusive: true,
  })

  registerModal('big-menu', {
    closeOnBackdrop: true,
    closeOnEscape: true,
    exclusive: true,
    onOpen: async (modal) => {
      // Инициализируем функционал обновления поля контакта при открытии модалки
      const form = modal.querySelector('form')
      if (form) {
        // Небольшая задержка, чтобы select успел инициализироваться
        setTimeout(() => {
          initContactFields()
        }, 100)
      }
    },
    // onClose: (modal) => burger.classList.remove('active'),
  })

  /*
   * Header Scroll: при скролле вниз — плавно скрывается верхняя часть (header_top);
   * при скролле вверх шапка раскрывается только при приближении к верху страницы.
   * --header-height обновляется при скролле (полная/свёрнутая). Для отступа main используется --header-height-full (задаётся при загрузке).
   */
  if (header) {
    const BREAKPOINT_LG = 992 // ниже — только header_top, collapse не применяем
    const SCROLL_THRESHOLD = 150 // скрывать верхнюю часть после этого скролла
    const EXPAND_THRESHOLD = 150 // раскрывать хедер только когда scrollY меньше этого
    const headerBottom = header.querySelector('.header_bottom')
    let lastScrollY = window.scrollY
    let ticking = false

    function updateHeaderHeight() {
      if (header.classList.contains('header--collapsed') && headerBottom) {
        setHeaderHeightPx(headerBottom.offsetHeight)
      } else {
        setHeaderHeight()
      }
    }

    function isCollapseEnabled() {
      return window.innerWidth >= BREAKPOINT_LG
    }

    function handleScroll() {
      if (!isCollapseEnabled()) {
        header.classList.remove('scrolled', 'header--collapsed')
        lastScrollY = window.scrollY
        if (!ticking) {
          requestAnimationFrame(() => {
            updateHeaderHeight()
            ticking = false
          })
          ticking = true
        }
        return
      }

      const scrollY = window.scrollY
      if (scrollY > SCROLL_THRESHOLD) {
        header.classList.add('scrolled')
        if (scrollY > lastScrollY) {
          header.classList.add('header--collapsed')
        } else {
          if (scrollY < EXPAND_THRESHOLD) {
            header.classList.remove('header--collapsed')
          }
        }
      } else {
        header.classList.remove('scrolled', 'header--collapsed')
      }
      lastScrollY = scrollY

      if (!ticking) {
        requestAnimationFrame(() => {
          updateHeaderHeight()
          ticking = false
        })
        ticking = true
      }
    }

    header.addEventListener('transitionend', (e) => {
      if (e.target === header || e.target.classList.contains('header_top')) {
        updateHeaderHeight()
      }
    })

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', updateHeaderHeight)
    } else {
      updateHeaderHeight()
    }

    window.addEventListener('scroll', handleScroll, { passive: true })

    window.addEventListener('resize', () => {
      if (!isCollapseEnabled()) {
        header.classList.remove('scrolled', 'header--collapsed')
        updateHeaderHeight()
      }
    })
  }

  /*
   * Bottom Nav — скрывать при скролле вниз, показывать при скролле вверх (класс hidden-by-scroll)
   */
  attachScrollVisibility('.bottom-nav', { threshold: 60, minDelta: 25 })

  /*
   * Кнопка «Наверх» — показывать при скролле вниз, скрывать при скролле вверх (invert)
   */
  const scrollToTopEl = document.querySelector('.scroll-to-top')
  if (scrollToTopEl) {
    attachScrollVisibility('.scroll-to-top', { invert: true, threshold: 300, minDelta: 25 })
    scrollToTopEl.addEventListener('click', () => {
      const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)')
      window.scrollTo({ top: 0, behavior: prefersReducedMotion.matches ? 'auto' : 'smooth' })
    })
  }

  /*
   * Intro Arrow - плавная прокрутка к следующей секции
   */
  const introArrows = document.querySelectorAll('.intro_arrow')
  introArrows.forEach((arrow) => {
    arrow.addEventListener('click', (e) => {
      e.preventDefault()

      const currentSection = arrow.closest('section')
      if (!currentSection) return

      const allSections = Array.from(document.querySelectorAll('section'))
      const currentIndex = allSections.indexOf(currentSection)
      const nextSection = allSections[currentIndex + 1]

      if (!nextSection) return

      // Добавляем id следующей секции, если его нет
      if (!nextSection.id) {
        nextSection.id = 'next-section'
      }

      // Прокручиваем к следующей секции
      const offset = window.innerWidth < 768 ? 80 : 110
      const rect = nextSection.getBoundingClientRect()
      const targetY = Math.max(0, window.scrollY + rect.top - offset)

      const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)')
      window.scrollTo({
        top: targetY,
        behavior: prefersReducedMotion.matches ? 'auto' : 'smooth',
      })

      // Для доступности
      if (!nextSection.hasAttribute('tabindex')) {
        nextSection.setAttribute('tabindex', '-1')
      }
      nextSection.focus({ preventScroll: true })
    })
  })
})
// Обновляем AOS при изменении размера окна
let resizeTimer
window.addEventListener(
  'resize',
  () => {
    clearTimeout(resizeTimer)
    resizeTimer = setTimeout(() => {
      AOS.refresh()
    }, 250)
  },
  { passive: true },
)
