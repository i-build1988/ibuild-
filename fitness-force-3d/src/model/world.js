// Walk-mode physics against the real GLB geometry, by raycasting:
//  floorAt — first surface below the walker (steps up to 0.6 m, e.g. a platform or stair)
//  collide — horizontal rays at knee and chest height push the walker off walls/glass
// Also finds open interior spots on a level, used for eye-level camera presets.
import * as THREE from 'three';

const DOWN = new THREE.Vector3(0, -1, 0), UP = new THREE.Vector3(0, 1, 0);
const DIRS = Array.from({ length: 8 }, (_, i) => new THREE.Vector3(Math.cos(i * Math.PI / 4), 0, Math.sin(i * Math.PI / 4)));
const STEP = 0.6;

export function createGlbWorld(getMeshes) {
  const ray = new THREE.Raycaster(), o = new THREE.Vector3();
  const first = (origin, dir, far) => {
    ray.set(origin, dir); ray.far = far; ray.firstHitOnly = true;
    return ray.intersectObjects(getMeshes(), false)[0] || null;
  };

  // plan (x, y) → three (x, -y). feet = current floor height
  function floorAt(x, y, feet = 0) {
    const h = first(o.set(x, feet + STEP, -y), DOWN, 80);
    return h ? h.point.y : feet;
  }
  function collide(nx, ny, feet = 0, radius = 0.32) {
    for (const hgt of [0.45, 1.25]) {
      for (const d of DIRS) {
        const h = first(o.set(nx, feet + hgt, -ny), d, radius);
        if (h) { const k = radius - h.distance; nx -= d.x * k; ny += d.z * k; }
      }
    }
    return [nx, ny];
  }

  // open interior spots on a level: floor at that level, a ceiling above, clear around
  function openSpots(box, levelY, nextLevelY = Infinity) {
    const out = [], N = 14, inset = 0.06;
    const sx = box.max.x - box.min.x, sz = box.max.z - box.min.z;
    const probeTop = Math.min(levelY + 2.2, nextLevelY - 0.3);
    for (let i = 0; i < N; i++) for (let j = 0; j < N; j++) {
      const x = box.min.x + sx * (inset + (1 - 2 * inset) * (i + 0.5) / N), z = box.min.z + sz * (inset + (1 - 2 * inset) * (j + 0.5) / N);
      const f = first(o.set(x, probeTop, z), DOWN, probeTop - levelY + 0.5);
      if (!f || Math.abs(f.point.y - levelY) > 0.45) continue;
      const fy = f.point.y;
      if (!first(o.set(x, fy + 1.6, z), UP, 40)) continue;            // outdoors
      let min = Infinity, best = null;
      for (const d of DIRS) {
        const h = first(o.set(x, fy + 1.4, z), d, 60), dist = h ? h.distance : 60;
        min = Math.min(min, dist); if (!best || dist > best.dist) best = { d, dist };
      }
      if (min < 1.0) continue;
      out.push({ x, y: -z, floor: fy, clearance: min, view: best });
    }
    return out.sort((p, q) => q.clearance - p.clearance);
  }
  return { floorAt, collide, openSpots };
}

// adapter for the reference shell's wall segments (original Phase 1 behaviour)
export function createSegmentWorld(colliders, floorAt) {
  return {
    floorAt: (x, y) => floorAt(x, y),
    collide(nx, ny, _feet, radius = 0.32) {
      for (let i = 0; i < 3; i++) for (const [a, b] of colliders) {
        const ax = a[0], ay = a[1], dx = b[0] - ax, dy = b[1] - ay, L2 = dx * dx + dy * dy || 1;
        const t = Math.max(0, Math.min(1, ((nx - ax) * dx + (ny - ay) * dy) / L2));
        const ex = nx - (ax + t * dx), ey = ny - (ay + t * dy), d = Math.hypot(ex, ey);
        if (d < radius) { const k = (radius - d) / (d || 1e-6); nx += ex * k; ny += ey * k; }
      }
      return [nx, ny];
    },
  };
}
