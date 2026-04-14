/**
 * Fancybox for education archive license thumbnails (.orders_list--photos).
 * Delegated click + Fancybox.fromNodes — reliable with Cyrillic data-fancybox group names.
 */
import { Fancybox } from '@fancyapps/ui/dist/fancybox/'

export function initEducationLicenseGallery() {
  document.body.addEventListener(
    'click',
    (e) => {
      const a = e.target.closest(
        '.orders_list--photos a.orders_item--photo[href], .orders--photos a.orders_item--photo[href]',
      )
      if (!a) return
      const group = a.getAttribute('data-fancybox')
      if (!group) return
      e.preventDefault()
      const esc = window.CSS && typeof window.CSS.escape === 'function' ? window.CSS.escape(group) : group.replace(/"/g, '\\"')
      const links = Array.from(
        document.querySelectorAll(
          `.orders_list--photos a.orders_item--photo[data-fancybox="${esc}"], .orders--photos a.orders_item--photo[data-fancybox="${esc}"]`,
        ),
      )
      if (!links.length) return
      const idx = links.indexOf(a)
      Fancybox.fromNodes(links, { startIndex: idx >= 0 ? idx : 0 })
    },
    false,
  )
}
