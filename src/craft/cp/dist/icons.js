/**
 * Craft's own icons for the parts of the kit drawn in the browser.
 *
 * The `pk.assets()` macro writes them into `<template id="pk-icons">` with Craft's `iconSvg()`, so
 * the kit never ships drawings of its own and always matches the control panel.
 */

/**
 * A copy of one of Craft's icons, wrapped in a span, or an empty span when the page has no icons
 * template or that icon.
 *
 * @param {string} name A Craft system icon name, e.g. "xmark".
 * @param {Document} [doc]
 * @returns {HTMLSpanElement}
 */
export function icon(name, doc = document) {
  const span = doc.createElement('span');
  span.className = 'pk-icon';
  span.setAttribute('aria-hidden', 'true');

  const source = doc.getElementById('pk-icons')?.content?.querySelector(`[data-icon="${name}"]`);
  if (source) span.append(...[...source.childNodes].map(node => node.cloneNode(true)));

  return span;
}
