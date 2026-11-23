/**
 * Parallelogram CP UI kit: which panel each in-page tab shows. Plain data, so it runs under Node's
 * test runner as well as in the control panel.
 */

/**
 * The panel ID an in-page tab points at, or null for a tab that loads another page.
 *
 * @param {string|null|undefined} href
 * @returns {string|null}
 */
export function panelId(href) {
  return href && href.length > 1 && href.charAt(0) === '#' ? href.slice(1) : null;
}

/**
 * For each in-page tab, whether its panel should be visible: only the selected tab's.
 *
 * @param {Array<string|null|undefined>} hrefs The tabs' hrefs, in order.
 * @param {number} selected The selected tab's index.
 * @returns {Array<{ id: string, visible: boolean }>}
 */
export function panelStates(hrefs, selected) {
  return hrefs.flatMap((href, index) => {
    const id = panelId(href);
    return id ? [{ id, visible: index === selected }] : [];
  });
}
