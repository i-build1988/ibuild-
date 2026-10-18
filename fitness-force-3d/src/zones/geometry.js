// Small geometry helpers shared by the shell, plan and walk collisions.
// Plan (x east, y north, h up)  →  three.js (X = x, Y = h, Z = -y)
import * as THREE from 'three';
import { HEIGHTS } from '../config/dimensions.js';

export const V = (x, y, h = 0) => new THREE.Vector3(x, h, -y);
export const H = (h) => (typeof h === 'string' ? HEIGHTS[h] : h);

// Axis-aligned or rotated box between plan points a→b, thickness t, from h0 to h1
export function boxAlong(a, b, t, h0, h1, mat) {
  const dx = b[0] - a[0], dy = b[1] - a[1], len = Math.hypot(dx, dy);
  if (len < 1e-4 || h1 - h0 < 1e-4) return null;
  const g = new THREE.BoxGeometry(len, h1 - h0, t);
  const m = new THREE.Mesh(g, mat);
  m.position.copy(V((a[0] + b[0]) / 2, (a[1] + b[1]) / 2, (h0 + h1) / 2));
  m.rotation.y = Math.atan2(dy, dx);
  return m;
}
// Point along a→b at distance s
export const lerpAt = (a, b, s) => { const L = Math.hypot(b[0] - a[0], b[1] - a[1]); const k = s / L; return [a[0] + (b[0] - a[0]) * k, a[1] + (b[1] - a[1]) * k]; };
export const wallLength = (w) => Math.hypot(w.b[0] - w.a[0], w.b[1] - w.a[1]);

// Reception platform outline (plan points) from the config parameters.
// Back edge along y = right; left side x = 0 from (right-left) to right; right side
// x = width from cornerR to right; curved front from left side to the rounded corner.
export function platformOutline(p, steps = 28) {
  const { width: W, left: L, right: R, cornerR: r, frontCtrl: [cx, cy] } = p;
  const pts = [[0, R], [W, R], [W, r]];
  for (let i = 1; i <= 8; i++) { const t = (i / 8) * Math.PI / 2; pts.push([W - r + r * Math.cos(t), r - r * Math.sin(t)]); }
  const p0 = [W - r, 0], p2 = [0, R - L];
  for (let i = 1; i <= steps; i++) {                      // quadratic Bézier front edge
    const t = i / steps, u = 1 - t;
    pts.push([u * u * p0[0] + 2 * u * t * cx + t * t * p2[0], u * u * p0[1] + 2 * u * t * cy + t * t * p2[1]]);
  }
  return pts;
}
export function polygonArea(pts) {
  let s = 0; for (let i = 0; i < pts.length; i++) { const [x1, y1] = pts[i], [x2, y2] = pts[(i + 1) % pts.length]; s += x1 * y2 - x2 * y1; }
  return Math.abs(s) / 2;
}
export function pointInPolygon([x, y], pts) {
  let inside = false;
  for (let i = 0, j = pts.length - 1; i < pts.length; j = i++) {
    const [xi, yi] = pts[i], [xj, yj] = pts[j];
    if ((yi > y) !== (yj > y) && x < ((xj - xi) * (y - yi)) / (yj - yi) + xi) inside = !inside;
  }
  return inside;
}
// Shape in plan coords → extruded slab lying on the floor (top at h0+depth)
export function extrudePlan(pts, depth, h0, mats) {
  const s = new THREE.Shape(pts.map(([x, y]) => new THREE.Vector2(x, y)));
  const g = new THREE.ExtrudeGeometry(s, { depth, bevelEnabled: false, curveSegments: 1 });
  g.rotateX(-Math.PI / 2); g.translate(0, h0, 0);          // shape y → -Z, extrude up
  return new THREE.Mesh(g, mats);
}
export const rectPts = (z) => [[z.x, z.y], [z.x + z.w, z.y], [z.x + z.w, z.y + z.d], [z.x, z.y + z.d]];
