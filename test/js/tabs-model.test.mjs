import assert from 'node:assert/strict';
import { describe, it } from 'node:test';

import { panelId, panelStates } from '../../src/craft/cp/dist/tabs-model.js';

describe('panelId', () => {
  it('reads the panel an in-page tab points at', () => {
    assert.deepEqual(
      [panelId('#settings'), panelId('#'), panelId('/admin/p-nav'), panelId(''), panelId(null), panelId(undefined)],
      ['settings', null, null, null, null, null],
    );
  });
});

describe('panelStates', () => {
  it('shows only the selected in-page tab’s panel, skipping link tabs', () => {
    assert.deepEqual(panelStates(['#one', '/admin/elsewhere', '#two', '#three'], 2), [
      { id: 'one', visible: false },
      { id: 'two', visible: true },
      { id: 'three', visible: false },
    ]);
  });

  it('hides every panel when a link tab is the selected one', () => {
    assert.deepEqual(panelStates(['/admin/a', '#b'], 0), [{ id: 'b', visible: false }]);
  });
});
