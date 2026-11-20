import assert from 'node:assert/strict';
import { test } from 'node:test';
import { countLabel } from '../../src/craft/cp/dist/count.js';

test('counts items and the hidden ones in brackets', () => {
  assert.equal(countLabel(8, 2), '8 items (2 hidden)');
});

test('leaves the brackets out when nothing is hidden', () => {
  assert.equal(countLabel(8), '8 items');
});

test('reads one item in the singular', () => {
  assert.equal(countLabel(1, 1), '1 item (1 hidden)');
});

test('never counts more hidden than there are items, or fewer than none', () => {
  assert.deepEqual([countLabel(3, 9), countLabel(3, -4), countLabel(-2, 1)], ['3 items (3 hidden)', '3 items', '0 items']);
});

test('uses its own words, and an empty message when there is nothing', () => {
  const words = { one: 'menu', many: 'menus', hidden: 'off', empty: 'No menus yet' };
  assert.deepEqual([countLabel(2, 1, words), countLabel(0, 0, words)], ['2 menus (1 off)', 'No menus yet']);
});
