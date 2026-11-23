/**
 * ⓘ tooltips: a button marked `data-pk-info` shows the element its `aria-describedby` names while
 * it is hovered or focused, and hides it on Escape or when the pointer and focus leave.
 *
 *   <span class="pk-info">
 *     <button type="button" class="pk-info__button" data-pk-info aria-describedby="tip-1" aria-label="More">…</button>
 *     <span class="pk-info__tip" role="tooltip" id="tip-1" hidden>Longer explanation.</span>
 *   </span>
 */

/**
 * @param {ParentNode} root
 */
export function initInfo(root = document) {
  for (const button of root.querySelectorAll('[data-pk-info]:not([data-pk-ready])')) {
    const tip = button.ownerDocument.getElementById(button.getAttribute('aria-describedby') ?? '');
    if (!tip) continue;

    button.dataset.pkReady = '';
    const show = () => tip.removeAttribute('hidden');
    const hide = () => tip.setAttribute('hidden', '');

    button.addEventListener('mouseenter', show);
    button.addEventListener('mouseleave', () => {
      if (button.ownerDocument.activeElement !== button) hide();
    });
    button.addEventListener('focus', show);
    button.addEventListener('blur', hide);
    button.addEventListener('click', event => {
      event.preventDefault();
      if (tip.hasAttribute('hidden')) show();
      else hide();
    });
    button.addEventListener('keydown', event => {
      if (event.key === 'Escape') hide();
    });
  }
}
