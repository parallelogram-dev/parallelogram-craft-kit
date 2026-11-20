/**
 * "8 items (2 hidden)": how many things a list holds and how many of them are hidden, as the kit
 * shows it under a page's heading and in a toolbar. The hidden part only appears when something is
 * hidden; one thing reads "1 item".
 *
 * @param {number} total
 * @param {number} [hidden]
 * @param {{ one?: string, many?: string, hidden?: string, empty?: string }} [words]
 * @returns {string}
 */
export function countLabel(total, hidden = 0, words = {}) {
  const { one = 'item', many = 'items', hidden: hiddenWord = 'hidden', empty = '' } = words;
  const count = Math.max(0, Math.trunc(Number(total) || 0));
  const off = Math.min(count, Math.max(0, Math.trunc(Number(hidden) || 0)));

  if (count === 0 && empty !== '') return empty;

  const label = `${count} ${count === 1 ? one : many}`;
  return off > 0 ? `${label} (${off} ${hiddenWord})` : label;
}
