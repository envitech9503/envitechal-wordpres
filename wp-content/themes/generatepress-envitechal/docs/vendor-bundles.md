# Vendor bundles for the homepage hero

`eta-hero-twin.js` imports two self-hosted bundles instead of four CDN modules.

| File | Contents | Gzipped |
|---|---|---|
| `assets/js/vendor/three-slim.js` | Only the 28 Three.js symbols the hero uses | 113 KB |
| `assets/js/vendor/motion.js` | GSAP, ScrollTrigger, Lenis | 46 KB |

Previously the page pulled the full `three.module.js` (251 KB gzipped) plus three
separate GSAP/Lenis modules from jsDelivr — 301 KB gzipped over four cross-origin
requests. Self-hosting also lets the site's own Brotli and long-cache headers apply,
and removes a third-party origin from the critical path.

## Rebuilding

Only needed when the hero starts using a new Three.js symbol, or a library is
upgraded. The entry files list exactly what is exported.

```bash
mkdir threebuild && cd threebuild
npm init -y
npm install three@0.160.1 gsap@3.12.5 lenis@1.3.11 \
            rollup @rollup/plugin-node-resolve @rollup/plugin-terser

# copy the two entry files out of the theme
cp ../wp-content/themes/generatepress-envitechal/assets/js/vendor/_source-entry-three.js entry.js
cp ../wp-content/themes/generatepress-envitechal/assets/js/vendor/_source-entry-motion.js entry-motion.js
```

`rollup.config.mjs`:

```js
import resolve from "@rollup/plugin-node-resolve";
import terser from "@rollup/plugin-terser";
export default [
  { input: "entry.js",        output: { file: "three-slim.js", format: "esm" },
    plugins: [resolve(), terser({ format: { comments: false } })],
    treeshake: { moduleSideEffects: false, propertyReadSideEffects: false } },
  { input: "entry-motion.js", output: { file: "motion.js", format: "esm" },
    plugins: [resolve(), terser({ format: { comments: false } })] }
];
```

Then `npx rollup -c rollup.config.mjs` and copy `three-slim.js` and `motion.js`
back into `assets/js/vendor/`.

## Adding a Three.js symbol

If the hero references a `THREE.*` export that is not in
`_source-entry-three.js`, it will be `undefined` at runtime rather than failing
the build. Add the symbol to that entry file and rebuild.

## Cache busting

`front-page.php` loads the hero with a `?v=` query string. Bump it whenever the
hero or either bundle changes — LiteSpeed's "Remove Query Strings" option must
stay **off** for that to survive.
