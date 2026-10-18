// Builds the single-file preview (dist/preview.html): all modules bundled,
// CSS inlined, three.js loaded from the CDN through the import map.
import { build } from 'esbuild';
import fs from 'fs';
const r = await build({ entryPoints: ['src/main.js'], bundle: true, format: 'esm', write: false, minify: true,
  external: ['three', 'three/addons/*', 'three-mesh-bvh'], target: 'es2022' });
const js = r.outputFiles[0].text.replace(/<\/script/g, '<\\/script');
const css = fs.readFileSync('src/ui/ui.css', 'utf8');
const html = `<title>Fitness Force Club 3D</title>
<meta name="claude:viewport-fit-sides" content="cover">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:ital,wght@0,500;0,600;0,700;0,900;1,900&family=Archivo+Narrow:wght@400;500;600&family=JetBrains+Mono:wght@500&display=swap">
<style>${css}</style>
<script type="importmap">{"imports":{"three":"https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js","three/addons/":"https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/","three-mesh-bvh":"https://cdn.jsdelivr.net/npm/three-mesh-bvh@0.7.8/build/index.module.js"}}</script>
<div id="viewport"></div>
<div id="ff-loading">Fitness Force · loading club</div>
<script>window.FF_MODEL_URL = "../models/men_gym_3d.glb";</script>
<script type="module">${js}</script>
`;
fs.mkdirSync('dist', { recursive: true });
fs.writeFileSync('dist/preview.html', html);
console.log('dist/preview.html', (html.length / 1024).toFixed(0), 'KB');
