import { test } from 'node:test';
import assert from 'node:assert/strict';
import { addChips, removeChip } from '../../src/craft/cp/dist/chips-model.js';

test('adds each comma-, space- or line-separated value once', () => {
  assert.deepEqual(addChips(['page'], 'utm_*, gclid\nfbclid page  '), ['page', 'utm_*', 'gclid', 'fbclid']);
});

test('ignores blank text', () => {
  assert.deepEqual(addChips(['page'], ' , '), ['page']);
});

test('removes a chip by value', () => {
  assert.deepEqual(removeChip(['page', 'sort'], 'page'), ['sort']);
});
