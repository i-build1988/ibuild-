// Procedural canvas textures — no external image files, so the project runs
// offline and stays light. Each returns a THREE.CanvasTexture tiled in metres:
// call tex.repeat.set(w / tileMetres, d / tileMetres) on the floor.
import * as THREE from 'three';

function canvas(size = 512) {
  const c = document.createElement('canvas'); c.width = c.height = size;
  return [c, c.getContext('2d')];
}
function rng(seed) { let s = seed >>> 0; return () => ((s = (s * 1664525 + 1013904223) >>> 0) / 4294967296); }
function finish(c, color = true, aniso = 8) {
  const t = new THREE.CanvasTexture(c);
  t.wrapS = t.wrapT = THREE.RepeatWrapping;
  t.anisotropy = aniso;
  if (color) t.colorSpace = THREE.SRGBColorSpace;
  return t;
}

// Wood planks. One texture tile = 2.4 m × 2.4 m (8 rows of 0.30 m planks).
export function woodTexture({ base = [122, 85, 54], seed = 7, grey = 0 } = {}) {
  const [c, g] = canvas(1024); const r = rng(seed);
  const rows = 8, h = c.height / rows;
  for (let i = 0; i < rows; i++) {
    let x = -r() * 400;
    while (x < c.width) {
      const len = 300 + r() * 420;
      const v = (r() - 0.5) * 26;
      let [R, G, B] = base.map(k => k + v);
      if (grey) { const m = (R + G + B) / 3; R = R + (m - R) * grey; G = G + (m - G) * grey; B = B + (m - B) * grey; }
      g.fillStyle = `rgb(${R|0},${G|0},${B|0})`; g.fillRect(x, i * h, len, h);
      for (let k = 0; k < 14; k++) {                       // grain
        g.strokeStyle = `rgba(30,18,8,${0.04 + r() * 0.07})`; g.lineWidth = 1 + r() * 2;
        const yy = i * h + r() * h; g.beginPath(); g.moveTo(x, yy);
        g.bezierCurveTo(x + len * 0.3, yy + (r() - .5) * 8, x + len * 0.7, yy + (r() - .5) * 8, x + len, yy + (r() - .5) * 6); g.stroke();
      }
      g.fillStyle = 'rgba(15,10,6,.55)'; g.fillRect(x, i * h, 2, h);   // butt joint
      x += len;
    }
    g.fillStyle = 'rgba(15,10,6,.5)'; g.fillRect(0, i * h, c.width, 2);  // long joint
  }
  return finish(c);
}

// Large-format porcelain, stone look. Tile = 1.20 × 1.20 m, texture = 2 × 2 tiles.
export function stoneTileTexture({ base = [104, 102, 100], seed = 3 } = {}) {
  const [c, g] = canvas(1024); const r = rng(seed);
  g.fillStyle = `rgb(${base})`; g.fillRect(0, 0, 1024, 1024);
  for (let i = 0; i < 2600; i++) {                                     // cloudy body
    g.fillStyle = `rgba(${r() > .5 ? '255,255,255' : '40,40,40'},${r() * 0.035})`;
    const s = 20 + r() * 90; g.beginPath(); g.arc(r() * 1024, r() * 1024, s, 0, 7); g.fill();
  }
  for (let i = 0; i < 9; i++) {                                         // veins
    g.strokeStyle = `rgba(235,235,235,${0.18 + r() * 0.25})`; g.lineWidth = 0.6 + r() * 1.8;
    let x = r() * 1024, y = r() * 1024; g.beginPath(); g.moveTo(x, y);
    for (let k = 0; k < 18; k++) { x += (r() - .3) * 70; y += (r() - .5) * 70; g.lineTo(x, y); } g.stroke();
  }
  g.strokeStyle = 'rgba(60,60,60,.7)'; g.lineWidth = 2.5;             // grout
  for (const p of [0, 512, 1024]) { g.beginPath(); g.moveTo(p, 0); g.lineTo(p, 1024); g.moveTo(0, p); g.lineTo(1024, p); g.stroke(); }
  return finish(c);
}

// Commercial rubber, optional EPDM fleck colour. Tile = 1.0 × 1.0 m.
export function rubberTexture({ base = 22, fleck = null, density = 0, seed = 11 } = {}) {
  const [c, g] = canvas(512); const r = rng(seed);
  g.fillStyle = `rgb(${base},${base},${base + 1})`; g.fillRect(0, 0, 512, 512);
  for (let i = 0; i < 9000; i++) {
    const v = base + (r() - .5) * 14; g.fillStyle = `rgb(${v|0},${v|0},${v|0})`;
    g.fillRect(r() * 512, r() * 512, 1 + r() * 2.5, 1 + r() * 2.5);
  }
  if (fleck) for (let i = 0; i < density; i++) {
    g.fillStyle = fleck; g.globalAlpha = 0.55 + r() * 0.45;
    g.beginPath(); g.ellipse(r() * 512, r() * 512, 1 + r() * 2.2, 0.8 + r() * 1.6, r() * 3, 0, 7); g.fill();
  }
  g.globalAlpha = 1;
  g.strokeStyle = 'rgba(0,0,0,.6)'; g.lineWidth = 2; g.strokeRect(0, 0, 512, 512);  // 1 m tile seam
  return finish(c);
}

// Polished concrete for the surrounding hall floors. Tile = 4 × 4 m.
export function concreteTexture({ base = 78, seed = 5 } = {}) {
  const [c, g] = canvas(512); const r = rng(seed);
  g.fillStyle = `rgb(${base},${base - 2},${base - 5})`; g.fillRect(0, 0, 512, 512);
  for (let i = 0; i < 1600; i++) {
    g.fillStyle = `rgba(${r() > .5 ? '255,255,255' : '0,0,0'},${r() * 0.012})`;
    g.beginPath(); g.arc(r() * 512, r() * 512, 4 + r() * 40, 0, 7); g.fill();
  }
  for (let i = 0; i < 4000; i++) { g.fillStyle = `rgba(0,0,0,${r() * .15})`; g.fillRect(r() * 512, r() * 512, 1, 1); }
  return finish(c);
}

// Vertical wood slats (wall cladding). Tile = 1.2 m wide.
export function slatTexture() {
  const [c, g] = canvas(256);
  g.fillStyle = '#1a120c'; g.fillRect(0, 0, 256, 256);
  for (let i = 0; i < 8; i++) { g.fillStyle = i % 2 ? '#6e4a2e' : '#75502f'; g.fillRect(i * 32 + 5, 0, 22, 256); }
  return finish(c);
}

// Diamond-plate kick panel (HYROX sides, photo 8). Tile = 0.6 m.
export function diamondPlateTexture() {
  const [c, g] = canvas(256);
  g.fillStyle = '#1b1c1e'; g.fillRect(0, 0, 256, 256);
  g.fillStyle = '#2e3033';
  for (let y = 0; y < 256; y += 32) for (let x = 0; x < 256; x += 32) {
    g.save(); g.translate(x + ((y / 32) % 2) * 16 + 8, y + 8); g.rotate(((y / 32) % 2 ? 1 : -1) * 0.8);
    g.fillRect(-9, -2.5, 18, 5); g.restore();
  }
  return finish(c);
}
