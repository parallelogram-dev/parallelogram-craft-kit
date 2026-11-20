/**
 * Parallelogram CP UI kit: tabs made with `pk.tabs(…, { pane: false })`, anywhere on a page. Craft's
 * own tab script (Craft.Tabs) handles the keyboard, focus and the overflow menu; this shows the
 * selected in-page tab's panel and hides the rest. Tabs in the content pane's header (the default)
 * are left to Craft's control panel, which already does this.
 */
import { panelId, panelStates } from './tabs-model.js';

/**
 * @param {ParentNode} root
 */
export function initTabs(root = document) {
  const Tabs = window.Craft?.Tabs;
  if (!Tabs || !window.jQuery) return;

  root.querySelectorAll('[data-pk-tabs]:not([data-pk-tabs-ready])').forEach(container => {
    container.dataset.pkTabsReady = '';
    const links = [...container.querySelectorAll('[role="tab"]')];
    const show = selected => {
      for (const { id, visible } of panelStates(links.map(link => link.getAttribute('href')), selected)) {
        document.getElementById(id)?.classList.toggle('hidden', !visible);
      }
    };

    show(Math.max(0, links.findIndex(link => link.getAttribute('aria-selected') === 'true')));
    const tabs = new Tabs(window.jQuery(container));
    tabs.on('selectTab', event => {
      if (panelId(event.$tab.attr('href'))) show(links.indexOf(event.$tab[0]));
    });
  });
}
