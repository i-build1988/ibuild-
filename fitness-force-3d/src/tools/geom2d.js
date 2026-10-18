// 2D plan helpers for zone measurement. Points are plan [x, y] in metres.
import { polygonArea } from '../zones/geometry.js';
export { polygonArea };

export function convexHull(pts) {
  const p = [...pts].sort((a, b) => a[0] - b[0] || a[1] - b[1]);
  if (p.length < 3) return p;
  const cross = (o, a, b) => (a[0] - o[0]) * (b[1] - o[1]) - (a[1] - o[1]) * (b[0] - o[0]);
  const lo = [], hi = [];
  for (const q of p) { while (lo.length >= 2 && cross(lo[lo.length - 2], lo[lo.length - 1], q) <= 0) lo.pop(); lo.push(q); }
  for (const q of [...p].reverse()) { while (hi.length >= 2 && cross(hi[hi.length - 2], hi[hi.length - 1], q) <= 0) hi.pop(); hi.push(q); }
  return lo.slice(0, -1).concat(hi.slice(0, -1));
}

/** Minimum-area enclosing rectangle (rotating calipers over hull edges).
 *  Returns length (long side) / width (short side), axis u (unit, along length), centre, corners. */
export function orientedRect(pts) {
  const h = convexHull(pts); if (h.length < 3) return null;
  let best = null;
  for (let i = 0; i < h.length; i++) {
    const a = h[i], b = h[(i + 1) % h.length], L = Math.hypot(b[0] - a[0], b[1] - a[1]); if (L < 1e-6) continue;
    const ux = (b[0] - a[0]) / L, uy = (b[1] - a[1]) / L;
    let u0 = Infinity, u1 = -Infinity, v0 = Infinity, v1 = -Infinity;
    for (const [x, y] of h) { const u = x * ux + y * uy, v = -x * uy + y * ux; u0 = Math.min(u0, u); u1 = Math.max(u1, u); v0 = Math.min(v0, v); v1 = Math.max(v1, v); }
    const area = (u1 - u0) * (v1 - v0);
    if (!best || area < best.area - 1e-9) best = { area, ux, uy, u0, u1, v0, v1 };
  }
  let { ux, uy, u0, u1, v0, v1 } = best;
  const at = (u, v) => [u * ux - v * uy, u * uy + v * ux];
  let len = u1 - u0, wid = v1 - v0, axis = [ux, uy];
  if (wid > len) { [len, wid] = [wid, len]; axis = [-uy, ux]; }
  const [cx, cy] = at((u0 + u1) / 2, (v0 + v1) / 2);
  return { len, wid, u: axis, cx, cy, rectArea: best.area, area: polygonArea(pts),
    corners: [at(u0, v0), at(u1, v0), at(u1, v1), at(u0, v1)],
    angle: ((Math.atan2(axis[1], axis[0]) * 180 / Math.PI) + 360) % 180 };
}
