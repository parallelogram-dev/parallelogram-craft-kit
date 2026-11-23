/**
 * Copies the ParallelogramJS modules the CP UI kit uses (the drag engine, PModal and PSelect, plus
 * every module they import) and the drag engine's stylesheet from node_modules into the kit's
 * asset bundle, with the package's licence and notices.
 *
 * The copy is committed, so installing a plugin needs no npm. Run this only after changing the
 * pinned version in package.json:
 *
 *   npm ci && npm run vendor
 *
 * PJS_PACKAGE points at another copy of the package instead of node_modules.
 */
import { cpSync, existsSync, mkdirSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { dirname, join, normalize } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const pkg = process.env.PJS_PACKAGE || join(root, 'node_modules/@parallelogram-js/core');
const out = join(root, 'src/craft/cp/dist/vendor/parallelogram-js');

const entries = ['dist/draggable.js', 'dist/components/PModal.js', 'dist/components/PSelect.js'];
const extras = ['dist/styles/components/draggable.css', 'LICENSE', 'THIRD-PARTY-NOTICES.md'];

const relativeImports = /(?:from|import)\s*["'](\.{1,2}\/[^"']+)["']/g;

/**
 * Every module the entries need, following their relative imports.
 *
 * @param {string[]} start
 * @returns {string[]}
 */
const modulesFrom = start => {
  const seen = new Set();
  const queue = [...start];

  while (queue.length) {
    const file = queue.shift();
    if (seen.has(file)) continue;
    seen.add(file);

    for (const [, specifier] of readFileSync(join(pkg, file), 'utf8').matchAll(relativeImports)) {
      queue.push(normalize(join(dirname(file), specifier)));
    }
  }

  return [...seen].sort();
};

if (!existsSync(join(pkg, 'package.json'))) {
  console.error(`No @parallelogram-js/core at ${pkg}. Run npm ci first.`);
  process.exit(1);
}

const files = [...modulesFrom(entries), ...extras];
const { version } = JSON.parse(readFileSync(join(pkg, 'package.json'), 'utf8'));

rmSync(out, { recursive: true, force: true });

for (const file of files) {
  const target = join(out, file.replace(/^dist\//, ''));
  mkdirSync(dirname(target), { recursive: true });
  cpSync(join(pkg, file), target);
}

writeFileSync(join(out, 'VERSION'), `${version}\n`);
console.log(`Copied @parallelogram-js/core ${version} (${files.length} files) to ${out}`);
