/**
 * Repeaters: "+ Add …" rows of declared fields, reordered by dragging their handle, removed with ×,
 * checked as they change, and written to hidden inputs so the surrounding form submits them.
 *
 * The `pk.repeater()` macro outputs the element and its config:
 *
 *   <div class="pk-repeater" data-pk-repeater>
 *     <script type="application/json" data-pk-config>{"name": "profiles", "mode": "names", …}</script>
 *   </div>
 *
 * Config: name (input name), mode ("names" for `name[0][field]` inputs, "json" for one input
 * holding a JSON array), fields (see repeater-model.js), rows (the starting rows), addLabel,
 * errors (server-side problems, per row index then field name), and min.
 *
 * It dispatches `pk-repeater:change` with `{ rows, valid }` after every change.
 */
import { moved, normaliseRow, rowErrors, toJson, toNamedPairs } from './repeater-model.js';
import { icon } from './icons.js';
import { Sortable } from './vendor/parallelogram-js/draggable.js';

let uid = 0;

/**
 * @param {Document} doc
 * @param {string} tag
 * @param {string} [className]
 * @returns {HTMLElement}
 */
const el = (doc, tag, className = '') => {
  const element = doc.createElement(tag);
  if (className) element.className = className;
  return element;
};

/**
 * One field's control.
 *
 * @param {Document} doc
 * @param {{ name: string, label?: string, type?: string, options?: Array<{ value: string, label: string }>, placeholder?: string }} field
 * @param {string|boolean} value
 * @param {string} id
 * @returns {HTMLInputElement|HTMLSelectElement}
 */
const control = (doc, field, value, id) => {
  if (field.type === 'select') {
    const select = /** @type {HTMLSelectElement} */ (el(doc, 'select'));
    select.append(new Option(field.placeholder ?? '', ''));
    for (const option of field.options ?? []) select.append(new Option(option.label, option.value));
    select.value = String(value);
    select.id = id;
    return select;
  }

  const input = /** @type {HTMLInputElement} */ (el(doc, 'input', field.type === 'checkbox' ? 'checkbox' : 'text fullwidth'));
  input.type = field.type === 'checkbox' ? 'checkbox' : field.type === 'url' ? 'url' : field.type === 'email' ? 'email' : 'text';
  if (field.type === 'checkbox') input.checked = value === true;
  else input.value = String(value);
  if (field.placeholder) input.placeholder = field.placeholder;
  input.id = id;
  return input;
};

/**
 * @param {HTMLElement} root
 */
function mount(root) {
  const doc = root.ownerDocument;
  const config = JSON.parse(root.querySelector('script[data-pk-config]')?.textContent || '{}');
  const fields = config.fields ?? [];
  const name = config.name ?? 'rows';
  const mode = config.mode === 'json' ? 'json' : 'names';
  const serverErrors = config.errors ?? {};

  /** @type {Array<{ key: number, data: Record<string, string|boolean>, touched: boolean }>} */
  let rows = (config.rows ?? []).map((row, index) => ({ key: ++uid, data: normaliseRow(row, fields), touched: index in serverErrors }));

  const head = el(doc, 'div', 'pk-repeater__head');
  head.append(el(doc, 'span'));
  for (const field of fields) head.append(Object.assign(el(doc, 'span', 'pk-repeater__label'), { textContent: field.label ?? field.name }));
  head.append(el(doc, 'span'));

  const list = el(doc, 'div', 'pk-repeater__rows');
  const hidden = el(doc, 'div', 'pk-repeater__inputs');
  hidden.hidden = true;

  const add = /** @type {HTMLButtonElement} */ (el(doc, 'button', 'btn dashed add icon pk-repeater__add'));
  add.type = 'button';
  add.textContent = config.addLabel ?? 'Add';

  root.style.setProperty('--pk-repeater-fields', String(Math.max(1, fields.length)));
  root.append(head, list, add, hidden);

  const sorter = new Sortable([list], {
    draggable: '.pk-repeater__row',
    handle: '.pk-repeater__handle',
    mirror: { appendTo: doc.body, constrainDimensions: true },
  });

  sorter.on('sortable:stop', event => {
    const { oldIndex, newIndex } = event;
    if (oldIndex === newIndex) return;
    rows = moved(rows, oldIndex, newIndex);
    requestAnimationFrame(() => {
      render();
      write();
    });
  });

  function write() {
    hidden.replaceChildren();
    const data = rows.map(row => row.data);

    if (mode === 'json') {
      const input = /** @type {HTMLInputElement} */ (el(doc, 'input'));
      input.type = 'hidden';
      input.name = name;
      input.value = toJson(data, fields);
      hidden.append(input);
    } else {
      for (const [inputName, value] of toNamedPairs(name, data, fields)) {
        const input = /** @type {HTMLInputElement} */ (el(doc, 'input'));
        input.type = 'hidden';
        input.name = inputName;
        input.value = value;
        hidden.append(input);
      }
    }

    const valid = rows.every(row => Object.keys(rowErrors(row.data, fields)).length === 0);
    root.classList.toggle('pk-repeater--empty', rows.length === 0);
    root.dispatchEvent(new CustomEvent('pk-repeater:change', { bubbles: true, detail: { rows: data, valid } }));
  }

  function render(focusKey = null) {
    list.replaceChildren();

    rows.forEach((row, index) => {
      const line = el(doc, 'div', 'pk-repeater__row');
      const errors = row.touched ? { ...(serverErrors[index] ?? {}), ...rowErrors(row.data, fields) } : {};

      const handle = el(doc, 'span', 'pk-repeater__handle');
      handle.title = 'Drag to reorder';
      handle.append(icon('grip-dots-vertical', doc));
      line.append(handle);

      for (const field of fields) {
        const cell = el(doc, 'div', 'pk-repeater__cell');
        const id = `pk-r${row.key}-${field.name}`;
        const input = control(doc, field, row.data[field.name], id);
        input.setAttribute('aria-label', field.label ?? field.name);

        if (errors[field.name]) {
          input.setAttribute('aria-invalid', 'true');
          input.setAttribute('aria-describedby', `${id}-error`);
          const message = el(doc, 'p', 'pk-repeater__error error');
          message.id = `${id}-error`;
          message.textContent = errors[field.name];
          cell.append(input, message);
        } else {
          cell.append(input);
        }

        const update = () => {
          row.data = normaliseRow({ ...row.data, [field.name]: field.type === 'checkbox' ? input.checked : input.value }, fields);
          write();
        };
        input.addEventListener('input', update);
        input.addEventListener('change', () => {
          row.touched = true;
          update();
          render(`${row.key}:${field.name}`);
        });
        line.append(cell);
      }

      const remove = /** @type {HTMLButtonElement} */ (el(doc, 'button', 'pk-repeater__remove'));
      remove.type = 'button';
      remove.setAttribute('aria-label', 'Remove this row');
      remove.append(icon('xmark', doc));
      remove.addEventListener('click', () => {
        rows = rows.filter(other => other !== row);
        render();
        write();
        (list.querySelector('.pk-repeater__row:last-child input, .pk-repeater__row:last-child select') ?? add).focus();
      });
      line.append(remove);
      list.append(line);
    });

    if (focusKey) {
      const [key, field] = focusKey.split(':');
      doc.getElementById(`pk-r${key}-${field}`)?.focus();
    }
  }

  add.addEventListener('click', () => {
    const row = { key: ++uid, data: normaliseRow({}, fields), touched: false };
    rows = [...rows, row];
    render();
    write();
    doc.getElementById(`pk-r${row.key}-${fields[0]?.name}`)?.focus();
  });

  while (rows.length < (config.min ?? 0)) rows.push({ key: ++uid, data: normaliseRow({}, fields), touched: false });

  render();
  write();
}

/**
 * @param {ParentNode} root
 */
export function initRepeaters(root = document) {
  for (const repeater of root.querySelectorAll('[data-pk-repeater]:not([data-pk-ready])')) {
    repeater.dataset.pkReady = '';
    mount(/** @type {HTMLElement} */ (repeater));
  }
}
