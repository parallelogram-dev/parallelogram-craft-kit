/**
 * Parallelogram CP UI kit: the list behind `pk.chips()`, without the DOM. Typed or pasted text
 * becomes chips at each comma, space or line break; blanks and repeats are dropped.
 */

/**
 * @param {string[]} chips
 * @param {string} text
 * @returns {string[]}
 */
export function addChips(chips, text) {
  const next = [...chips];

  for (const part of String(text).split(/[\s,]+/)) {
    const chip = part.trim();
    if (chip !== '' && !next.includes(chip)) next.push(chip);
  }

  return next;
}

/**
 * @param {string[]} chips
 * @param {string} chip
 * @returns {string[]}
 */
export function removeChip(chips, chip) {
  return chips.filter(other => other !== chip);
}
