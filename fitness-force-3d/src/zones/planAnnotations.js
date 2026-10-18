// Top-view floor plan layer: zone outlines, labels with size + area,
// dimension lines, unspecified-area outlines, north arrow and scale bar.
// Lies flat just above the floors; shown only in TOP VIEW.
import * as THREE from 'three';
import { ZONES, UNSPECIFIED, HALLS, WALLS, WALL_TYPES, ELEVATORS } from '../config/dimensions.js';
import { BRAND } from '../config/brand.js';
import { V, platformOutline, polygonArea, rectPts, lerpAt, wallLength } from './geometry.js';

const Z = 0.45;  // drawing height (above the 0.30 m platform)
const fmt = (n) => n.toFixed(2);

export function textPlane(lines, { size = 0.5, color = '#fff', bg = null, weight = 600, align = 'center', maxW = null } = {}) {
  const px = 128, pad = 24, c = document.createElement('canvas'), g = c.getContext('2d');
  const font = (i) => `${i === 0 ? weight : 500} ${i === 0 ? px : px * 0.72}px "Archivo Narrow", "Arial Narrow", sans-serif`;
  let w = 0; lines.forEach((t, i) => { g.font = font(i); w = Math.max(w, g.measureText(t).width); });
  const lh = px * 1.12; c.width = Math.ceil(w + pad * 2); c.height = Math.ceil(lh * lines.length + pad * 1.4);
  if (bg) { g.fillStyle = bg; g.fillRect(0, 0, c.width, c.height); }
  g.textBaseline = 'top'; g.textAlign = align; g.fillStyle = color;
  lines.forEach((t, i) => { g.font = font(i); g.fillStyle = i === 0 ? color : 'rgba(255,255,255,.72)'; g.fillText(t, align === 'center' ? c.width / 2 : pad, pad * 0.7 + i * lh); });
  const tex = new THREE.CanvasTexture(c); tex.colorSpace = THREE.SRGBColorSpace; tex.anisotropy = 8;
  let hgt = size * (c.height / px), wid = hgt * (c.width / c.height);
  if (maxW && wid > maxW) { hgt *= maxW / wid; wid = maxW; }
  const m = new THREE.Mesh(new THREE.PlaneGeometry(wid, hgt), new THREE.MeshBasicMaterial({ map: tex, transparent: true, depthWrite: false }));
  m.rotation.x = -Math.PI / 2; m.renderOrder = 10; return m;
}
export function polyLine(pts, color, closed = true, dashed = false, h = Z) {
  const arr = pts.map(([x, y]) => V(x, y, h)); if (closed) arr.push(arr[0]);
  const g = new THREE.BufferGeometry().setFromPoints(arr);
  const mat = dashed ? new THREE.LineDashedMaterial({ color, dashSize: 0.5, gapSize: 0.35, depthTest: false })
                     : new THREE.LineBasicMaterial({ color, depthTest: false });
  const l = new THREE.Line(g, mat); if (dashed) l.computeLineDistances(); l.renderOrder = 9; return l;
}
// dimension line from a to b, offset perpendicular by off (plan metres)
export function dimLine(a, b, off, label, group, color = '#ffffff') {
  const dx = b[0] - a[0], dy = b[1] - a[1], L = Math.hypot(dx, dy), nx = -dy / L * off, ny = dx / L * off;
  const A = [a[0] + nx, a[1] + ny], B = [b[0] + nx, b[1] + ny];
  group.add(polyLine([A, B], color, false));
  for (const [p, q] of [[a, A], [b, B]]) group.add(polyLine([[p[0] + nx * 0.25, p[1] + ny * 0.25], [q[0] + nx * 0.2, q[1] + ny * 0.2]], color, false));
  for (const P of [A, B]) group.add(polyLine([[P[0] - 0.18 * (dx + dy) / L, P[1] - 0.18 * (dy - dx) / L], [P[0] + 0.18 * (dx + dy) / L, P[1] + 0.18 * (dy - dx) / L]], color, false));
  const t = textPlane([label], { size: 0.7, color });
  const mx = (A[0] + B[0]) / 2 + nx * 0.6 / Math.abs(off || 1), my = (A[1] + B[1]) / 2 + ny * 0.6 / Math.abs(off || 1);
  t.position.copy(V(mx, my, Z + 0.01)); t.rotation.z = Math.atan2(dy, dx); if (Math.abs(t.rotation.z) > Math.PI / 2) t.rotation.z += Math.PI;
  group.add(t);
}

export function buildPlanAnnotations() {
  const g = new THREE.Group(); g.name = 'plan';
  const red = BRAND.red, white = '#ffffff';

  // halls (light outline)
  for (const h of Object.values(HALLS)) g.add(polyLine(rectPts(h), '#5d6166'));

  // rectangular zones
  for (const [key, z] of Object.entries(ZONES)) {
    if (z.platform) continue;
    const pts = rectPts(z); g.add(polyLine(pts, red));
    const area = z.w * z.d;
    const label = textPlane([z.label, `${fmt(z.w)} × ${fmt(z.d)} m · ${area.toFixed(2)} m²`], { size: 1.0, bg: 'rgba(10,10,11,.78)', maxW: (z.d > z.w * 1.6 ? z.d : z.w) * 0.8 });
    label.position.copy(V(z.x + z.w / 2, z.y + z.d / 2, Z + 0.02));
    if (z.d > z.w * 1.6) label.rotation.z = Math.PI / 2;    // long thin zones: label along the length
    g.add(label);
    dimLine([z.x, z.y], [z.x + z.w, z.y], -0.9, `${fmt(z.w)}`, g);            // width, south side
    dimLine([z.x + z.w, z.y], [z.x + z.w, z.y + z.d], -0.9, `${fmt(z.d)}`, g); // depth, east side
  }

  // reception platform
  const R = ZONES.reception, pts = platformOutline(R.platform).map(([x, y]) => [x + R.x, y + R.y]);
  g.add(polyLine(pts, red));
  const a = polygonArea(pts), p = R.platform;
  const lab = textPlane([R.label, `platform ${a.toFixed(1)} m² (photo)`, `brief ${R.briefSize[0].toFixed(2)} × ${R.briefSize[1].toFixed(2)} = ${R.briefArea.toFixed(2)} m²`], { size: 0.85, maxW: 6.4, bg: 'rgba(10,10,11,.78)' });
  lab.position.copy(V(R.x + 4.0, R.y + 5.6, Z + 0.02)); g.add(lab);
  dimLine([R.x, R.y + p.right], [R.x + p.width, R.y + p.right], -0.9, fmt(p.width), g);
  dimLine([R.x, R.y + p.right - p.left], [R.x, R.y + p.right], 0.9, fmt(p.left), g);
  dimLine([R.x + p.width, R.y + p.right], [R.x + p.width, R.y], 0.9, fmt(p.right), g);

  // areas not in the brief
  for (const u of UNSPECIFIED) {
    g.add(polyLine(rectPts(u), '#8a8f95', true, true));
    const t = textPlane([u.label, 'not in brief'], { size: 0.7, color: '#a7acb2', maxW: (u.d > u.w * 1.6 ? u.d : u.w) * 0.8 });
    t.position.copy(V(u.x + u.w / 2, u.y + u.d / 2, Z)); if (u.d > u.w * 1.6) t.rotation.z = Math.PI / 2; g.add(t);
  }

  // north arrow + 5 m scale bar
  const nx = HALLS.gym.x + HALLS.gym.w + 3.5, ny = HALLS.gym.y + HALLS.gym.d - 3;
  g.add(polyLine([[nx, ny - 1.4], [nx, ny + 1.4]], white, false));
  g.add(polyLine([[nx - 0.6, ny + 0.6], [nx, ny + 1.4], [nx + 0.6, ny + 0.6]], white, false));
  const N = textPlane(['N'], { size: 0.8 }); N.position.copy(V(nx, ny + 2.4, Z)); g.add(N);
  const sx = nx - 2.5, sy = ny - 4.2;
  for (let i = 0; i < 5; i++) {
    const m = new THREE.Mesh(new THREE.PlaneGeometry(1, 0.25), new THREE.MeshBasicMaterial({ color: i % 2 ? '#ffffff' : red, depthTest: false }));
    m.rotation.x = -Math.PI / 2; m.position.copy(V(sx + i + 0.5, sy, Z)); m.renderOrder = 10; g.add(m);
  }
  const S = textPlane(['0 — 5 m'], { size: 0.7 }); S.position.copy(V(sx + 2.5, sy - 0.8, Z)); g.add(S);

  // wall poché: solid walls filled black, glazing as a thin cyan line
  const solid = new THREE.MeshBasicMaterial({ color: '#050506', depthTest: false });
  const glassLine = '#7fd3e6';
  for (const w of WALLS) {
    const T = WALL_TYPES[w.type], L = wallLength(w);
    if (T.mat === 'glass') { g.add(polyLine([w.a, w.b], glassLine, false, false, Z + 0.03)); continue; }
    const ops = (w.openings || []).map(o => [o.at - o.w / 2, o.at + o.w / 2]).sort((a, b) => a[0] - b[0]);
    let s = 0; const spans = [];
    for (const [o0, o1] of ops) { if (o0 > s) spans.push([s, o0]); s = o1; }
    if (s < L) spans.push([s, L]);
    for (const [s0, s1] of spans) {
      const a = lerpAt(w.a, w.b, s0), b = lerpAt(w.a, w.b, s1), len = s1 - s0;
      const m = new THREE.Mesh(new THREE.PlaneGeometry(len, Math.max(T.t, 0.2)), solid);
      m.rotation.x = -Math.PI / 2; m.rotation.z = Math.atan2(b[1] - a[1], b[0] - a[0]);
      m.position.copy(V((a[0] + b[0]) / 2, (a[1] + b[1]) / 2, Z + 0.02)); m.renderOrder = 11; g.add(m);
    }
  }
  const ec = ELEVATORS.core;
  const core = new THREE.Mesh(new THREE.PlaneGeometry(ec.w, ec.d), solid); core.rotation.x = -Math.PI / 2;
  core.position.copy(V(ec.x + ec.w / 2, ec.y + ec.d / 2, Z + 0.02)); core.renderOrder = 11; g.add(core);
  const el = textPlane(['LIFTS'], { size: 0.7, color: '#a7acb2' }); el.rotation.z = Math.PI / 2;
  el.position.copy(V(ec.x + ec.w / 2, ec.y + ec.d / 2, Z + 0.05)); g.add(el);

  g.visible = false;
  return g;
}
