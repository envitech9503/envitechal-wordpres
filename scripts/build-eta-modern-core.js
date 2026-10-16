#!/usr/bin/env node
/*
 * Generates assets/css/eta-modern-core.css from assets/css/eta-modern.css by removing
 * every rule whose selectors are all scoped to body.single-post (Knowledge Hub posts).
 * Pages other than single posts load the core file; posts load the full file, so the
 * cascade is identical on both. Requires postcss: npm i --no-save postcss
 *
 *   node scripts/build-eta-modern-core.js          write the core file
 *   node scripts/build-eta-modern-core.js --check  exit 1 if the core file is stale
 */
const fs = require('fs');
const path = require('path');
const postcss = require('postcss');

const dir = path.join(__dirname, '..', 'wp-content/themes/generatepress-envitechal/assets/css');
const full = fs.readFileSync(path.join(dir, 'eta-modern.css'), 'utf8');
const root = postcss.parse(full);
const isPost = (s) => /(^|\s|>|\+|~)(body)?\.single-post(?![\w-])/.test(s);

root.walkRules((rule) => {
    for (let p = rule.parent; p && p.type === 'atrule'; p = p.parent) {
        if (/keyframes/.test(p.name)) return;
    }
    if (rule.selectors.every(isPost)) rule.remove();
});
for (let i = 0; i < 3; i++) {
    root.walkAtRules((at) => { if (at.nodes && at.nodes.length === 0) at.remove(); });
}

const out = root.toString();
const target = path.join(dir, 'eta-modern-core.css');
if (process.argv.includes('--check')) {
    const current = fs.existsSync(target) ? fs.readFileSync(target, 'utf8') : '';
    if (current !== out) {
        console.error('eta-modern-core.css is stale: run node scripts/build-eta-modern-core.js');
        process.exit(1);
    }
    console.log('eta-modern-core.css is in sync with eta-modern.css.');
} else {
    fs.writeFileSync(target, out);
    console.log('Wrote eta-modern-core.css (' + out.length + ' bytes).');
}
