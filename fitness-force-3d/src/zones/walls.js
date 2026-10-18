// Builds every wall in WALLS: solid walls with lintels over openings, and the
// four glazing systems seen in the photos. Also returns the collision segments
// used by walk mode (openings stay walkable, elevator doors do not).
import * as THREE from 'three';
import { WALL_TYPES, HEIGHTS } from '../config/dimensions.js';
import { MAT, kickMaterial } from '../materials/materials.js';
import { boxAlong, lerpAt, wallLength, H } from './geometry.js';

function spans(w) {   // solid spans between openings, as [s0, s1] distances from 'a'
  const L = wallLength(w), ops = (w.openings || []).map(o => [o.at - o.w / 2, o.at + o.w / 2, o]).sort((p, q) => p[0] - q[0]);
  const out = []; let s = 0;
  for (const [o0, o1] of ops) { if (o0 > s) out.push([s, o0]); s = Math.max(s, o1); }
  if (s < L) out.push([s, L]);
  return { L, out, ops };
}

export function buildWalls(walls) {
  const group = new THREE.Group(); group.name = 'walls';
  const glassGroup = new THREE.Group(); glassGroup.name = 'glass';
  const colliders = [];
  const add = (m, g = group) => { if (m) { m.castShadow = m.receiveShadow = true; g.add(m); } return m; };

  for (const w of walls) {
    const T = WALL_TYPES[w.type], h = H(w.h), { L, out, ops } = spans(w);
    const P = (s) => lerpAt(w.a, w.b, s);
    for (const [s0, s1] of out) colliders.push([P(s0), P(s1)]);

    if (T.mat !== 'glass') {
      for (const [s0, s1] of out) add(boxAlong(P(s0), P(s1), T.t, 0, h, MAT[T.mat]));
      for (const [o0, o1, o] of ops) {
        const head = o.kind === 'elevator' ? HEIGHTS.doorHead : (o.head ?? HEIGHTS.doorHead);
        add(boxAlong(P(o0), P(o1), T.t, head, h, MAT[T.mat]));               // lintel
        if (o.kind === 'elevator') {                                           // closed steel doors
          add(boxAlong(P(o0), P(o1), T.t * 0.4, 0, 2.30, MAT.elevator));
          colliders.push([P(o0), P(o1)]);
        }
      }
      continue;
    }

    // ---- glazing systems ----
    const glassTop = w.type === 'windows' ? h - 0.4 : h;
    for (const [s0, s1] of out) add(boxAlong(P(s0), P(s1), 0.012, w.type === 'balustrade' ? T.kick : 0.04, glassTop, MAT.glass), glassGroup);
    for (const [o0, o1] of ops) add(boxAlong(P(o0), P(o1), 0.08, HEIGHTS.doorHead, h, MAT.frame));          // transom over doors
    add(boxAlong(w.a, w.b, 0.08, glassTop - 0.06, glassTop, MAT.frame));                                      // head rail
    add(boxAlong(w.a, w.b, 0.08, 0, 0.05, MAT.frame));                                                        // sill rail

    // mullions (and red piers on the tall windows, photo 4)
    const n = Math.max(1, Math.round(L / T.mullion));
    for (let i = 0; i <= n; i++) {
      const s = (i / n) * L; if (ops.some(([o0, o1]) => s > o0 + 0.05 && s < o1 - 0.05)) continue;
      const a = P(Math.max(0, s - 0.03)), b = P(Math.min(L, s + 0.03));
      add(boxAlong(a, b, 0.10, 0, glassTop, MAT.frame));
    }
    if (w.type === 'windows') {
      const np = Math.round(L / T.pier);
      for (let i = 0; i <= np; i++) {
        const s = (i / np) * L; add(boxAlong(P(Math.max(0, s - 0.3)), P(Math.min(L, s + 0.3)), 0.5, 0, h, MAT.wallRed));
      }
      for (const z of [2.6, 5.0]) add(boxAlong(w.a, w.b, 0.08, z, z + 0.06, MAT.frame));                   // transoms
      add(boxAlong(w.a, w.b, 0.3, glassTop, h, MAT.wallCharcoal));
    }
    if (T.frosted) {                                                                                         // frosted manifestation band
      const [f0, f1] = T.frosted;
      for (const [s0, s1] of out) add(boxAlong(P(s0), P(s1), 0.02, f0, f1, MAT.frosted), glassGroup);
    }
    if (w.type === 'balustrade') {                                                                           // photo 8
      add(boxAlong(w.a, w.b, 0.06, 0, T.kick, kickMaterial(L, T.kick)));
      add(boxAlong(w.a, w.b, 0.07, T.kick, T.kick + 0.025, MAT.ledRed));
      add(boxAlong(w.a, w.b, 0.06, 0.0, 0.03, MAT.ledRed));
      const rail = new THREE.Mesh(new THREE.CylinderGeometry(0.025, 0.025, L, 12), MAT.steel);
      const m = lerpAt(w.a, w.b, L / 2); rail.rotation.z = Math.PI / 2;
      rail.rotation.y = Math.atan2(w.b[1] - w.a[1], w.b[0] - w.a[0]);
      rail.position.set(m[0], HEIGHTS.balustrade, -m[1]); add(rail);
    }
  }
  return { group, glassGroup, colliders };
}
