/**
 * Searchable pickers: ParallelogramJS's `<p-select>`, with groups (`<optgroup>`) and a description
 * under each option (`data-description`).
 *
 * `data-pk-custom` on the `<p-select>` lets someone keep what they typed when nothing matches:
 * while they type, the first option becomes "Use “…”" with their text as its value.
 */
import './vendor/parallelogram-js/components/PSelect.js';

const CUSTOM = 'data-pk-custom-option';

/**
 * The typed text, read from the event's origin inside the select's shadow root.
 *
 * @param {Event} event
 * @returns {string|null}
 */
const typedText = event => {
  const origin = event.composedPath?.()[0];
  return origin instanceof HTMLInputElement ? origin.value : null;
};

/**
 * The "Use “…”" option for a typed value, or nothing when the text is blank or already an option.
 *
 * @param {HTMLElement} select
 * @param {string} text
 * @param {string} template
 * @returns {HTMLOptionElement|null}
 */
export function customOption(select, text, template = 'Use “{value}”') {
  const value = text.trim();
  if (value === '') return null;

  const exists = [...select.querySelectorAll(`option:not([${CUSTOM}])`)].some(
    option => option.value.toLowerCase() === value.toLowerCase() || option.textContent?.trim().toLowerCase() === value.toLowerCase()
  );
  if (exists) return null;

  const option = select.ownerDocument.createElement('option');
  option.setAttribute(CUSTOM, '');
  option.value = value;
  option.textContent = template.replace('{value}', value);
  return option;
}

/**
 * @param {ParentNode} root
 */
export function initSelects(root = document) {
  for (const select of root.querySelectorAll('p-select[data-pk-custom]:not([data-pk-ready])')) {
    select.dataset.pkReady = '';
    const template = select.getAttribute('data-pk-custom-label') || 'Use “{value}”';

    select.addEventListener('input', event => {
      const text = typedText(event);
      if (text === null) return;

      const previous = select.querySelector(`option[${CUSTOM}]`);
      if (previous && previous.value !== select.value) previous.remove();
      const option = customOption(select, text, template);
      if (option) select.prepend(option);
    });
  }
}
