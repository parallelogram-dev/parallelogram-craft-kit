/**
 * Modals: ParallelogramJS's `<p-modal>`, which already has a scrolling body and a footer that
 * stays put, styled for the control panel by kit.css.
 *
 * Markup comes from the `{% embed '_pk/modal' %}` template; anything marked
 * `data-pk-modal-open="#id"` opens that modal, and `data-modal-close` inside it closes it.
 * `createModal()` builds one in script for editors that need it.
 */
import './vendor/parallelogram-js/components/PModal.js';

/**
 * @param {ParentNode} root
 */
export function initModals(root = document) {
  for (const trigger of root.querySelectorAll('[data-pk-modal-open]:not([data-pk-ready])')) {
    trigger.dataset.pkReady = '';
    trigger.addEventListener('click', event => {
      const modal = trigger.ownerDocument.querySelector(trigger.dataset.pkModalOpen ?? '');
      if (modal && typeof modal.open === 'function') {
        event.preventDefault();
        modal.open();
      }
    });
  }
}

/**
 * Builds and opens a modal.
 *
 * @param {{
 *   title: string,
 *   body: Node|string,
 *   primaryLabel?: string,
 *   cancelLabel?: string,
 *   dangerLabel?: string,
 *   size?: 'sm'|'md'|'lg',
 *   onPrimary?: (modal: HTMLElement) => (boolean|void|Promise<boolean|void>),
 *   onDanger?: (modal: HTMLElement) => void,
 * }} options onPrimary returning false keeps the modal open.
 * @returns {HTMLElement} The `<p-modal>`, removed from the page once it closes.
 */
export function createModal(options) {
  const modal = document.createElement('p-modal');
  modal.className = 'pk-modal';
  modal.dataset.modalSize = options.size ?? 'md';

  const title = document.createElement('h2');
  title.slot = 'title';
  title.textContent = options.title;

  const body = document.createElement('div');
  body.className = 'pk-modal__body';
  body.append(typeof options.body === 'string' ? document.createTextNode(options.body) : options.body);

  const actions = document.createElement('div');
  actions.slot = 'actions';
  actions.className = 'pk-modal__actions';

  const cancel = document.createElement('button');
  cancel.type = 'button';
  cancel.className = 'btn';
  cancel.dataset.modalClose = '';
  cancel.textContent = options.cancelLabel ?? 'Cancel';
  actions.append(cancel);

  if (options.primaryLabel) {
    const primary = document.createElement('button');
    primary.type = 'button';
    primary.className = 'btn submit';
    primary.textContent = options.primaryLabel;
    primary.addEventListener('click', async () => {
      const keep = (await options.onPrimary?.(modal)) === false;
      if (!keep) modal.close();
    });
    actions.append(primary);
  }

  modal.append(title, body, actions);

  if (options.dangerLabel) {
    const secondary = document.createElement('div');
    secondary.slot = 'secondary';
    const danger = document.createElement('button');
    danger.type = 'button';
    danger.className = 'pk-link pk-link--danger';
    danger.textContent = options.dangerLabel;
    danger.addEventListener('click', () => options.onDanger?.(modal));
    secondary.append(danger);
    modal.append(secondary);
  }

  modal.addEventListener('p-modal:close', () => modal.remove(), { once: true });
  document.body.append(modal);
  modal.open();
  return modal;
}
