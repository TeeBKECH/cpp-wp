/**
 * Show CF7 success/error messages in a fixed toast (hide noisy inline box).
 */
const TOAST_MS = 6000

export function initCf7FormToasts() {
  let toastEl = null
  let hideTimer = null

  function ensureToast() {
    if (!toastEl) {
      toastEl = document.createElement('div')
      toastEl.id = 'cpp-cf7-toast'
      toastEl.className = 'cpp-cf7-toast'
      toastEl.setAttribute('role', 'status')
      toastEl.setAttribute('aria-live', 'polite')
      document.body.appendChild(toastEl)
    }
    return toastEl
  }

  function show(msg) {
    const text = (msg || '').trim()
    if (!text) return
    const el = ensureToast()
    el.textContent = text
    el.classList.add('cpp-cf7-toast--visible')
    window.clearTimeout(hideTimer)
    hideTimer = window.setTimeout(() => {
      el.classList.remove('cpp-cf7-toast--visible')
      el.textContent = ''
    }, TOAST_MS)
  }

  function pullMessageFromEvent(ev) {
    const d = ev.detail || {}
    const api = d.apiResponse
    if (api && typeof api.message === 'string' && api.message.trim()) {
      return api.message.trim()
    }
    if (typeof d.message === 'string' && d.message.trim()) {
      return d.message.trim()
    }
    const form = ev.target
    if (!form || typeof form.querySelector !== 'function') return ''
    const out = form.querySelector('.wpcf7-response-output')
    return out ? out.textContent.trim() : ''
  }

  function handle(ev) {
    const msg = pullMessageFromEvent(ev)
    if (msg) show(msg)
    const form = ev.target
    if (form && typeof form.querySelector === 'function') {
      const out = form.querySelector('.wpcf7-response-output')
      if (out) out.textContent = ''
    }
  }

  ;['wpcf7mailsent', 'wpcf7invalid', 'wpcf7spam', 'wpcf7mailfailed'].forEach((type) => {
    document.addEventListener(type, handle, false)
  })
}
