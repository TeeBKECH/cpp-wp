/**
 * CF7: show success/error in a fixed toast; hide inline response.
 */
import { closeModal } from '@/scripts/components/modal.js'

const TOAST_MS = 6000
const LEAD_FORM_MODAL_ID = 'lead-form-modal'

function extractStatusFromMessage(msg) {
  const m = String(msg).match(/\/([a-z0-9_-]+)\/?\s*$/i)
  return m ? m[1] : ''
}

function messageFromStatus(status) {
  const map = {
    mail_sent_ok: 'Спасибо за Ваше сообщение. Оно успешно отправлено.',
    mail_sent_ng: 'Ошибка при отправке сообщения. Попробуйте позже.',
    validation_error: 'Одно или несколько полей заполнены с ошибкой.',
    spam: 'Ошибка при отправке сообщения.',
    acceptance_missing: 'Пожалуйста, подтвердите согласие.',
  }
  return map[status] || ''
}

export function initCf7FormToasts() {
  let toastEl = null
  let hideTimer = null

  function ensureToast() {
    if (!toastEl) {
      toastEl = document.createElement('div')
      toastEl.id = 'cpp-cf7-toast'
      toastEl.className = 'cpp-cf7-toast'
      toastEl.setAttribute('role', 'alert')
      toastEl.setAttribute('aria-live', 'assertive')
      document.body.appendChild(toastEl)
    }
    return toastEl
  }

  function show(msg) {
    const text = (msg || '').trim()
    if (!text) return
    const el = ensureToast()
    el.textContent = text
    void el.offsetWidth
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
    const status = extractStatusFromMessage(String(d.status || ''))
    if (status) {
      const mapped = messageFromStatus(status)
      if (mapped) return mapped
    }
    const form = ev.target
    if (!form || typeof form.querySelector !== 'function') return ''
    const out = form.querySelector('.wpcf7-response-output')
    return out ? out.textContent.trim() : ''
  }

  function handle(ev) {
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        const msg = pullMessageFromEvent(ev)
        if (msg) show(msg)
        const form = ev.target
        if (form && typeof form.querySelector === 'function') {
          const out = form.querySelector('.wpcf7-response-output')
          if (out) {
            out.style.display = 'none'
          }
        }
        if (ev.type === 'wpcf7mailsent' && form && typeof form.closest === 'function') {
          if (form.closest(`#${LEAD_FORM_MODAL_ID}`)) {
            closeModal(LEAD_FORM_MODAL_ID)
          }
        }
      })
    })
  }

  ;['wpcf7mailsent', 'wpcf7invalid', 'wpcf7spam', 'wpcf7mailfailed'].forEach((type) => {
    document.addEventListener(type, handle, false)
  })
}
