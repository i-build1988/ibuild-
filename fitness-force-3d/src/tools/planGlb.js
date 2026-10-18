// TOP VIEW drawing layer for the real GLB: 1 m / 5 m drafting grid around the
// building, overall dimension lines, north arrow, scale bar and level tag.
// Built from the model bounds; sits at the selected floor level.
import * as THREE from 'three';
import { textPlane, polyLine, dimLine } from '../zones/planAnnotations.js';
import { BRAND } from '../config/brand.js';

export function buildPlanLayer(model, levelIndex, groundY = model.box.min.y) {
  const g = new THREE.Group(); g.name = 'TOP VIEW DRAWING';
  const b = model.box, lvl = model.levels[levelIndex] ?? { y: 0 };
  const x0 = b.min.x, x1 = b.max.x, y0 = -b.max.z, y1 = -b.min.z, W = x1 - x0, D = y1 - y0, R = Math.max(W, D);
  const k = Math.max(1, R / 40);               // annotation scale for big / small models

  // drafting grid, just above the viewer ground: shows around the building, hidden under it
  const gy = groundY + 0.01 - lvl.y, pad = R * 0.35, gx0 = Math.floor((x0 - pad) / 5) * 5, gx1 = Math.ceil((x1 + pad) / 5) * 5, gy0 = Math.floor((y0 - pad) / 5) * 5, gy1 = Math.ceil((y1 + pad) / 5) * 5;
  const minor = [], major = [];
  for (let x = gx0; x <= gx1; x++) (x % 5 ? minor : major).push(x, gy, -gy0, x, gy, -gy1);
  for (let y = gy0; y <= gy1; y++) (y % 5 ? minor : major).push(gx0, gy, -y, gx1, gy, -y);
  for (const [arr, color, op] of [[minor, '#3a3d42', 0.55], [major, '#565a61', 0.9]]) {
    const geo = new THREE.BufferGeometry(); geo.setAttribute('position', new THREE.Float32BufferAttribute(arr, 3));
    g.add(new THREE.LineSegments(geo, new THREE.LineBasicMaterial({ color, transparent: true, opacity: op })));
  }

  // building bounding box + overall dimensions (dimLine draws 0.45 m above the level)
  g.add(polyLine([[x0, y0], [x1, y0], [x1, y1], [x0, y1]], '#8a8f95', true, true));
  dimLine([x0, y0], [x1, y0], -1.6 * k, `${W.toFixed(2)} m`, g);
  dimLine([x1, y0], [x1, y1], -1.6 * k, `${D.toFixed(2)} m`, g);

  // north arrow (plan north = -Z of the GLB) + 5 m scale bar, south-east of the building
  const nx = x1 + 4.5 * k, ny = y0 + 2.5 * k, Z = 0.45, white = '#ffffff';
  g.add(polyLine([[nx, ny - 1.4 * k], [nx, ny + 1.4 * k]], white, false));
  g.add(polyLine([[nx - 0.6 * k, ny + 0.6 * k], [nx, ny + 1.4 * k], [nx + 0.6 * k, ny + 0.6 * k]], white, false));
  const N = textPlane(['N'], { size: 0.8 * k }); N.position.set(nx, Z, -(ny + 2.4 * k)); g.add(N);
  const sx = x1 - 5, sy = y0 - 4.2 * k;
  for (let i = 0; i < 5; i++) {
    const m = new THREE.Mesh(new THREE.PlaneGeometry(1, 0.25 * k), new THREE.MeshBasicMaterial({ color: i % 2 ? '#ffffff' : BRAND.red, depthTest: false }));
    m.rotation.x = -Math.PI / 2; m.position.set(sx + i + 0.5, Z, -sy); m.renderOrder = 10; g.add(m);
  }
  const S = textPlane(['0 — 5 m'], { size: 0.7 * k }); S.position.set(sx + 2.5, Z, -(sy - 0.8 * k)); g.add(S);
  const L = textPlane([`${model.fileName.toUpperCase()}`, `LEVEL ${levelIndex} · ${lvl.y >= 0 ? '+' : ''}${lvl.y.toFixed(2)} m · CUT +1.25 m`], { size: 0.9 * k, align: 'left' });
  L.position.set(x0 + L.geometry.parameters.width / 2, Z, -(y0 - 4.2 * k)); g.add(L);

  g.position.y = lvl.y;
  return g;
}
