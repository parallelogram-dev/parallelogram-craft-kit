/**
 * The Simple / Advanced switch. Only admins get one: the `pk.advancedSwitch()` macro outputs
 * nothing for anyone else. While it is on, `[data-pk-advanced]` elements show (the CSS does this
 * with `:has()`, so the first paint is already right); flipping it saves the choice to the user's
 * preferences through the plugin's `pk-ui/advanced` action, and puts it back if that fails.
 *
 *   <button type="button" class="pk-switch" role="switch" aria-checked="false"
 *           data-pk-advanced-switch data-pk-action="p-nav/pk-ui/advanced">Advanced</button>
 */
import { toggled } from './advanced-state.js';

/**
 * @param {HTMLElement} control
 * @param {boolean} on
 */
const show = (control, on) => {
  control.setAttribute('aria-checked', on ? 'true' : 'false');
  control.ownerDocument.documentElement.classList.toggle('pk-advanced-on', on);
  control.dispatchEvent(new CustomEvent('pk-advanced:change', { bubbles: true, detail: { on } }));
};

/**
 * @param {ParentNode} root
 * @param {{ send?: (action: string, on: boolean) => Promise<unknown> }} [options]
 */
export function initAdvanced(root = document, options = {}) {
  const send =
    options.send ??
    ((action, on) => window.Craft.sendActionRequest('POST', action, { data: { advanced: on ? 1 : 0 } }));

  for (const control of root.querySelectorAll('[data-pk-advanced-switch]:not([data-pk-ready])')) {
    control.dataset.pkReady = '';
    let state = { admin: true, on: control.getAttribute('aria-checked') === 'true' };
    control.ownerDocument.documentElement.classList.toggle('pk-advanced-on', state.on);

    control.addEventListener('click', async () => {
      const before = state;
      state = toggled(state);
      show(control, state.on);

      try {
        await send(control.dataset.pkAction ?? '', state.on);
      } catch {
        state = before;
        show(control, state.on);
        window.Craft?.cp?.displayError?.('Couldn’t save the Advanced setting.');
      }
    });
  }
}
