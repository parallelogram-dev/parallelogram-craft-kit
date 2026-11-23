/**
 * Parallelogram CP UI kit: sets up every kit control on the page, and exports the pieces for
 * plugins that build their own UI in script.
 *
 * Plugins import it through the import map UiKitAsset registers:
 *
 *   import { initAll, createModal, countLabel } from '@parallelogram/ui-kit/kit.js';
 *   import { Sortable } from '@parallelogram-js/core/draggable.js';
 *
 * Call `initAll(element)` after adding kit markup to the page yourself.
 */
import { initAdvanced } from './advanced.js';
import { initChips } from './chips.js';
import { initInfo } from './info.js';
import { initModals } from './modal.js';
import { initRepeaters } from './repeater.js';
import { initSelects } from './select.js';
import { initTabs } from './tabs.js';

export { advancedMode, isOn, PREFERENCE, toggled } from './advanced-state.js';
export { addChips, removeChip } from './chips-model.js';
export { countLabel } from './count.js';
export { icon } from './icons.js';
export { createModal } from './modal.js';
export { keptRows, moved, rowErrors, toJson, toNamedPairs } from './repeater-model.js';
export { customOption } from './select.js';
export { panelId, panelStates } from './tabs-model.js';

/**
 * @param {ParentNode} root
 */
export function initAll(root = document) {
  initInfo(root);
  initAdvanced(root);
  initChips(root);
  initModals(root);
  initRepeaters(root);
  initSelects(root);
  initTabs(root);
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => initAll());
} else {
  initAll();
}
