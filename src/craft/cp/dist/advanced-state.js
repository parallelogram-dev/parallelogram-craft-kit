/**
 * The Simple / Advanced switch's state, as read from a user's preference and the page.
 *
 * Only admins ever get Advanced mode; for anyone else it is off whatever the preference says.
 */

/** The user preference the switch is saved under, shared by every plugin that uses the kit */
export const PREFERENCE = 'pkAdvancedMode';

/**
 * Whether a stored preference value means "on". Craft keeps preferences as JSON, so the value may
 * come back as a boolean, a number or a string.
 *
 * @param {unknown} value
 * @returns {boolean}
 */
export function isOn(value) {
  if (typeof value === 'string') return ['1', 'true', 'yes', 'on'].includes(value.trim().toLowerCase());
  return value === true || value === 1;
}

/**
 * Whether Advanced mode applies.
 *
 * @param {boolean} admin
 * @param {unknown} preference
 * @returns {boolean}
 */
export function advancedMode(admin, preference) {
  return admin === true && isOn(preference);
}

/**
 * The state after the switch is flipped, or the same state when the user can't use it.
 *
 * @param {{ admin: boolean, on: boolean }} state
 * @returns {{ admin: boolean, on: boolean }}
 */
export function toggled(state) {
  return state.admin ? { admin: true, on: !state.on } : { admin: false, on: false };
}
