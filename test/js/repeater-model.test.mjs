import assert from 'node:assert/strict';
import { test } from 'node:test';
import {
  isBlank,
  keptRows,
  moved,
  normaliseRow,
  rowErrors,
  toJson,
  toNamedPairs,
} from '../../src/craft/cp/dist/repeater-model.js';

const fields = [
  {
    name: 'type',
    label: 'Type',
    type: 'select',
    required: true,
    options: [
      { value: 'instagram', label: 'Instagram' },
      { value: 'other', label: 'Other' },
    ],
  },
  { name: 'url', label: 'Address', type: 'url', required: true },
  { name: 'primary', label: 'Main profile', type: 'checkbox' },
];

test('fills in missing fields and trims text', () => {
  assert.deepEqual(normaliseRow({ url: '  https://x.test/a  ', extra: 'dropped' }, fields), {
    type: '',
    url: 'https://x.test/a',
    primary: false,
  });
});

test('reads checkbox values as booleans', () => {
  assert.deepEqual(
    ['1', 1, true, '0', undefined].map(value => normaliseRow({ primary: value }, fields).primary),
    [true, true, true, false, false]
  );
});

test('treats a row with nothing in it as blank', () => {
  assert.deepEqual([isBlank(normaliseRow({}, fields)), isBlank(normaliseRow({ primary: true }, fields))], [true, false]);
});

test('finds no problems in a complete row', () => {
  assert.deepEqual(rowErrors(normaliseRow({ type: 'instagram', url: 'https://instagram.com/if' }, fields), fields), {});
});

test('finds no problems in a blank row, which is dropped instead', () => {
  assert.deepEqual(rowErrors(normaliseRow({}, fields), fields), {});
});

test('names each missing required field', () => {
  assert.deepEqual(rowErrors(normaliseRow({ primary: true }, fields), fields), {
    type: 'Type is required',
    url: 'Address is required',
  });
});

test('wants full web addresses', () => {
  assert.equal(
    rowErrors(normaliseRow({ type: 'other', url: 'instagram.com/if' }, fields), fields).url,
    'Enter a full address starting with https://'
  );
});

test('wants email addresses in email fields', () => {
  const emailFields = [{ name: 'email', type: 'email' }];
  assert.deepEqual(
    [rowErrors({ email: 'nope' }, emailFields), rowErrors({ email: 'a@b.co' }, emailFields)],
    [{ email: 'Enter an email address' }, {}]
  );
});

test('rejects options that are not offered', () => {
  assert.equal(rowErrors(normaliseRow({ type: 'myspace', url: 'https://x.test' }, fields), fields).type, 'Choose a type');
});

test('checks patterns, with their own message or a default one', () => {
  const patterned = [
    { name: 'handle', label: 'Handle', pattern: '[a-z]+', patternMessage: 'Lowercase letters only' },
    { name: 'code', pattern: '[0-9]{4}' },
  ];
  assert.deepEqual(rowErrors({ handle: 'Bad1', code: '12' }, patterned), {
    handle: 'Lowercase letters only',
    code: "code isn't valid",
  });
});

test('falls back to the field name when a label is missing', () => {
  assert.deepEqual(rowErrors({ a: '', b: 'x' }, [{ name: 'a', required: true }, { name: 'b' }]), { a: 'a is required' });
});

test('keeps rows in order and drops blank ones', () => {
  const rows = [{ type: 'other', url: 'https://a.test' }, {}, { type: 'instagram', url: 'https://b.test' }];
  assert.deepEqual(
    keptRows(rows, fields).map(row => row.url),
    ['https://a.test', 'https://b.test']
  );
});

test('serialises the kept rows as JSON', () => {
  assert.equal(
    toJson([{ type: 'other', url: 'https://a.test' }, {}], fields),
    '[{"type":"other","url":"https://a.test","primary":false}]'
  );
});

test('serialises the kept rows as Craft-style input names', () => {
  assert.deepEqual(toNamedPairs('profiles', [{ type: 'other', url: 'https://a.test', primary: true }], fields), [
    ['profiles[0][type]', 'other'],
    ['profiles[0][url]', 'https://a.test'],
    ['profiles[0][primary]', '1'],
  ]);
});

test('submits the bare name when every row is gone, so a form can clear them', () => {
  assert.deepEqual(toNamedPairs('profiles', [{}], fields), [['profiles', '']]);
});

test('moves a row, keeping the others in order', () => {
  assert.deepEqual([moved(['a', 'b', 'c'], 0, 2), moved(['a', 'b', 'c'], 2, 0)], [
    ['b', 'c', 'a'],
    ['c', 'a', 'b'],
  ]);
});

test('ignores moves from outside the list and clamps moves past its ends', () => {
  assert.deepEqual([moved(['a', 'b'], 5, 0), moved(['a', 'b'], 0, 9), moved(['a', 'b'], 1, -3)], [
    ['a', 'b'],
    ['b', 'a'],
    ['b', 'a'],
  ]);
});
