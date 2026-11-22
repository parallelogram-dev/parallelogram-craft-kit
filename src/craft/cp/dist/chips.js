/**
 * Parallelogram CP UI kit: a list of short values as chips (`pk.chips()`), such as query
 * parameter names. Type and press Enter or a comma to add one; × or Backspace removes one. The
 * values post as `name[]`, after an empty `name` so an emptied list still posts.
 */
import { addChips, removeChip } from './chips-model.js';
import { icon } from './icons.js';

/**
 * @param {HTMLElement} root
 */
function mount(root) {
  const doc = root.ownerDocument;
  const marker = /** @type {HTMLInputElement|null} */ (root.querySelector('input[data-pk-chips-name]'));
  const input = /** @type {HTMLInputElement|null} */ (root.querySelector('input[data-pk-chips-input]'));
  const list = root.querySelector('[data-pk-chips-list]');
  if (!marker || !input || !list) return;

  let chips = [...root.querySelectorAll('input[data-pk-chip]')].map(chip => /** @type {HTMLInputElement} */ (chip).value);

  const render = () => {
    list.replaceChildren();

    for (const value of chips) {
      const chip = doc.createElement('span');
      chip.className = 'pk-chip';
      const label = doc.createElement('span');
      label.textContent = value;
      const hidden = doc.createElement('input');
      hidden.type = 'hidden';
      hidden.name = `${marker.name}[]`;
      hidden.value = value;
      hidden.dataset.pkChip = '';
      const remove = doc.createElement('button');
      remove.type = 'button';
      remove.className = 'pk-chip__remove';
      remove.setAttribute('aria-label', `Remove ${value}`);
      remove.append(icon('xmark', doc));
      remove.addEventListener('click', () => {
        chips = removeChip(chips, value);
        render();
        input.focus();
      });
      chip.append(label, remove, hidden);
      list.append(chip);
    }
  };

  const commit = () => {
    if (input.value.trim() === '') return;
    chips = addChips(chips, input.value);
    input.value = '';
    render();
  };

  input.addEventListener('keydown', event => {
    if (event.key === 'Enter' || event.key === ',') {
      event.preventDefault();
      commit();
    } else if (event.key === 'Backspace' && input.value === '' && chips.length > 0) {
      chips = chips.slice(0, -1);
      render();
    }
  });
  input.addEventListener('paste', () => setTimeout(commit));
  input.addEventListener('blur', commit);
  root.addEventListener('click', event => {
    if (event.target === root || event.target === list) input.focus();
  });

  render();
}

/**
 * @param {ParentNode} root
 */
export function initChips(root = document) {
  for (const chips of root.querySelectorAll('[data-pk-chips]:not([data-pk-ready])')) {
    chips.dataset.pkReady = '';
    mount(/** @type {HTMLElement} */ (chips));
  }
}
