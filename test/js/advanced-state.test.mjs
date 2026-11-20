import assert from 'node:assert/strict';
import { test } from 'node:test';
import { advancedMode, isOn, PREFERENCE, toggled } from '../../src/craft/cp/dist/advanced-state.js';

test('saves under one preference shared by every plugin', () => {
  assert.equal(PREFERENCE, 'pkAdvancedMode');
});

test('reads the ways Craft can store "on"', () => {
  assert.deepEqual(
    [true, 1, '1', 'true', ' On ', 'yes'].map(isOn),
    [true, true, true, true, true, true]
  );
});

test('reads everything else as off', () => {
  assert.deepEqual(
    [false, 0, '0', '', 'no', null, undefined, {}].map(isOn),
    [false, false, false, false, false, false, false, false]
  );
});

test('is only ever on for admins', () => {
  assert.deepEqual([advancedMode(true, true), advancedMode(false, true), advancedMode(true, false)], [true, false, false]);
});

test('flips for admins', () => {
  assert.deepEqual(toggled({ admin: true, on: false }), { admin: true, on: true });
});

test('stays off for everyone else', () => {
  assert.deepEqual(toggled({ admin: false, on: true }), { admin: false, on: false });
});
