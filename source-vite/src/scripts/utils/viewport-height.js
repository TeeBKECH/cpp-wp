// Храним фиксированное значение высоты viewport для предотвращения скачков при скролле
let fixedViewportHeight = 0
let isScrolling = false
let scrollTimeout = null
let lastWindowWidth = window.innerWidth

/**
 * Устанавливает CSS переменную --vh с реальной высотой viewport
 * Решает проблему с 100vh на мобильных устройствах, где браузерный UI
 * (адресная строка и т.д.) может скрывать контент
 */
export function setViewportHeight() {
  const viewportHeight = window.visualViewport?.height || window.innerHeight

  // Фиксируем максимальное значение
  if (viewportHeight > fixedViewportHeight) {
    fixedViewportHeight = viewportHeight
  }

  const vh = fixedViewportHeight * 0.01
  document.documentElement.style.setProperty('--vh', `${vh}px`)
}

/**
 * Устанавливает CSS переменную --header-height с текущей высотой хедера.
 * Динамическая: при скролле вызывается из обработчика (полная или свёрнутая высота).
 */
export function setHeaderHeight() {
  const header = document.querySelector('.header')
  if (header) {
    document.documentElement.style.setProperty('--header-height', `${header.offsetHeight}px`)
  }
}

/**
 * Устанавливает --header-height вручную (например при collapsed-состоянии хедера).
 */
export function setHeaderHeightPx(px) {
  document.documentElement.style.setProperty('--header-height', `${px}px`)
}

/**
 * Возвращает полную высоту хедера (как если бы он был развёрнут).
 * При свёрнутом хедере временно отключает transition, снимает класс, измеряет, восстанавливает —
 * иначе замер даёт промежуточное значение (transition ещё не закончился).
 */
function getHeaderFullHeight() {
  const header = document.querySelector('.header')
  if (!header) return 0
  const headerTop = header.querySelector('.header_top')
  const wasCollapsed = header.classList.contains('header--collapsed')
  let savedTransition = ''
  if (headerTop) {
    savedTransition = headerTop.style.transition
    headerTop.style.transition = 'none'
  }
  if (wasCollapsed) header.classList.remove('header--collapsed')
  const height = header.offsetHeight
  if (wasCollapsed) header.classList.add('header--collapsed')
  if (headerTop) headerTop.style.transition = savedTransition
  return height
}

/**
 * Устанавливает --header-height-full один раз при загрузке (и при resize).
 * Не меняется при скролле — для фиксированного отступа main и др.
 */
export function setHeaderHeightFull() {
  const height = getHeaderFullHeight()
  if (height > 0) {
    document.documentElement.style.setProperty('--header-height-full', `${height}px`)
  }
}

/**
 * Инициализирует отслеживание высоты viewport и хедера
 */
export function initViewportHeight() {
  const isMobile = window.innerWidth <= 992

  // Блокируем обновления во время скролла
  window.addEventListener(
    'scroll',
    () => {
      isScrolling = true
      clearTimeout(scrollTimeout)
      scrollTimeout = setTimeout(() => {
        isScrolling = false
      }, 500)
    },
    { passive: true },
  )

  // Инициализация значений
  const initHeights = () => {
    fixedViewportHeight = 0
    setViewportHeight()
    setHeaderHeight()
    setHeaderHeightFull()

    // На мобильных делаем несколько попыток для фиксации максимального значения
    if (isMobile) {
      requestAnimationFrame(() => {
        setViewportHeight()
        setTimeout(() => setViewportHeight(), 300)
        setTimeout(() => setViewportHeight(), 1000)
      })
    }
  }

  // Инициализируем при готовности DOM
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHeights)
  } else {
    requestAnimationFrame(initHeights)
  }

  // Обновляем после полной загрузки
  if (document.readyState !== 'complete') {
    window.addEventListener('load', () => {
      setTimeout(() => {
        setViewportHeight()
        setHeaderHeight()
        setHeaderHeightFull()
      }, 100)
    })
  }

  // Обновляем при изменении размера окна (только если не скроллим)
  let resizeTimeout
  window.addEventListener('resize', () => {
    // Игнорируем resize во время скролла на мобильных
    if (isMobile && isScrolling) {
      return
    }

    const currentWidth = window.innerWidth
    const widthChanged = Math.abs(currentWidth - lastWindowWidth) > 5

    // Обновляем только при изменении ширины (реальное изменение размера окна)
    if (widthChanged) {
      lastWindowWidth = currentWidth
      fixedViewportHeight = 0 // Сбрасываем для пересчета
      clearTimeout(resizeTimeout)
      resizeTimeout = setTimeout(() => {
        setViewportHeight()
        setHeaderHeight()
        setHeaderHeightFull()
      }, 100)
    }
  })

  // Обновляем при изменении ориентации
  window.addEventListener('orientationchange', () => {
    fixedViewportHeight = 0
    setTimeout(() => {
      setViewportHeight()
      setHeaderHeight()
      setHeaderHeightFull()
    }, 100)
  })

  // Отслеживаем изменения хедера
  const header = document.querySelector('.header')
  if (header && typeof MutationObserver !== 'undefined') {
    new MutationObserver(setHeaderHeight).observe(header, {
      attributes: true,
      attributeFilter: ['class', 'style'],
      childList: true,
      subtree: true,
    })
  }
}
