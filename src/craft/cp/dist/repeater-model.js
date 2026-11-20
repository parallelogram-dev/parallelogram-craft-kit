/**
 * The data side of a repeater: rows of declared fields, checked, and turned into what the form
 * submits. The DOM side lives in repeater.js.
 *
 * A field is `{ name, label, type?, required?, pattern?, patternMessage?, options?, placeholder? }`
 * where type is text (default), url, email, select or checkbox. A row is an object keyed by field
 * name.
 */

/**
 * A row with every declared field present, as strings (checkboxes as booleans).
 *
 * @param {Record<string, unknown>} row
 * @param {Array<{ name: string, type?: string }>} fields
 * @returns {Record<string, string|boolean>}
 */
export function normaliseRow(row, fields) {
  const out = {};

  for (const field of fields) {
    const value = row?.[field.name];
    out[field.name] = field.type === 'checkbox' ? value === true || value === '1' || value === 1 : value == null ? '' : String(value).trim();
  }

  return out;
}

/**
 * Whether every field in a row is empty, so the row can be left out.
 *
 * @param {Record<string, string|boolean>} row
 * @returns {boolean}
 */
export function isBlank(row) {
  return Object.values(row).every(value => value === '' || value === false);
}

const checks = {
  url: value => /^https?:\/\/[^\s/$.?#][^\s]*$/i.test(value),
  email: value => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
};

const typeMessages = {
  url: 'Enter a full address starting with https://',
  email: 'Enter an email address',
};

/**
 * The problems with one row, keyed by field name. Blank rows have none: they are dropped.
 *
 * @param {Record<string, string|boolean>} row
 * @param {Array<{ name: string, label?: string, type?: string, required?: boolean, pattern?: string, patternMessage?: string, options?: Array<{ value: string }> }>} fields
 * @returns {Record<string, string>}
 */
export function rowErrors(row, fields) {
  if (isBlank(row)) return {};

  const errors = {};

  for (const field of fields) {
    const value = row[field.name];
    if (typeof value === 'boolean') continue;

    if (value === '') {
      if (field.required) errors[field.name] = `${field.label ?? field.name} is required`;
      continue;
    }

    if (field.type in checks && !checks[field.type](value)) {
      errors[field.name] = typeMessages[field.type];
    } else if (field.pattern && !new RegExp(`^(?:${field.pattern})$`).test(value)) {
      errors[field.name] = field.patternMessage ?? `${field.label ?? field.name} isn't valid`;
    } else if (field.type === 'select' && field.options && !field.options.some(option => option.value === value)) {
      errors[field.name] = `Choose a ${String(field.label ?? field.name).toLowerCase()}`;
    }
  }

  return errors;
}

/**
 * The rows worth keeping, normalised, in order.
 *
 * @param {Array<Record<string, unknown>>} rows
 * @param {Array<{ name: string, type?: string }>} fields
 * @returns {Array<Record<string, string|boolean>>}
 */
export function keptRows(rows, fields) {
  return rows.map(row => normaliseRow(row, fields)).filter(row => !isBlank(row));
}

/**
 * The rows as one JSON string, for a single hidden input.
 *
 * @param {Array<Record<string, unknown>>} rows
 * @param {Array<{ name: string, type?: string }>} fields
 * @returns {string}
 */
export function toJson(rows, fields) {
  return JSON.stringify(keptRows(rows, fields));
}

/**
 * The rows as Craft-style input names and values, e.g. `profiles[0][url]`, ready to become hidden
 * inputs. An empty list still submits the base name, so a form can clear every row.
 *
 * @param {string} base
 * @param {Array<Record<string, unknown>>} rows
 * @param {Array<{ name: string, type?: string }>} fields
 * @returns {Array<[string, string]>}
 */
export function toNamedPairs(base, rows, fields) {
  const kept = keptRows(rows, fields);
  if (kept.length === 0) return [[base, '']];

  return kept.flatMap((row, index) =>
    fields.map(field => {
      const value = row[field.name];
      return [`${base}[${index}][${field.name}]`, typeof value === 'boolean' ? (value ? '1' : '') : value];
    })
  );
}

/**
 * Moves a row from one index to another, returning a new list.
 *
 * @template T
 * @param {T[]} rows
 * @param {number} from
 * @param {number} to
 * @returns {T[]}
 */
export function moved(rows, from, to) {
  const next = [...rows];
  if (from < 0 || from >= next.length) return next;

  const [row] = next.splice(from, 1);
  next.splice(Math.max(0, Math.min(to, next.length)), 0, row);
  return next;
}
