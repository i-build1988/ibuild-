// Camera presets generated from the REAL GLB geometry (bounds, floor levels,
// open interior spots, labelled zones). Same format as the Phase 1 presets:
// plan coordinates [x east, y north, height], fov in vertical degrees.
import { HEIGHTS } from '../config/dimensions.js';
import { ZONE_DEFS } from '../config/model.js';
import { orientedRect } from '../tools/geom2d.js';

const r2 = (v) => Math.round(v * 100) / 100;
const P = (a) => a.map(r2);

export function buildPresets(model, world) {
  const b = model.box, H = model.height;
  const x0 = b.min.x, x1 = b.max.x, y0 = -b.max.z, y1 = -b.min.z, cx = (x0 + x1) / 2, cy = (y0 + y1) / 2;
  const W = x1 - x0, D = y1 - y0, R = Math.max(W, D, 8);
  const cams = [
    { id: 'CAM01', name: 'Overall Club View',  zone: 'overview', pos: P([cx + R * 0.62, cy - R * 0.92, H + R * 0.72]), target: P([cx, cy, 0]), fov: 40, orbit: true },
    { id: 'CAM02', name: 'Overall · North-West', zone: 'overview2', pos: P([cx - R * 0.62, cy + R * 0.92, H + R * 0.72]), target: P([cx, cy, 0]), fov: 40, orbit: true },
    { id: 'CAM03', name: 'South Elevation',    zone: 'elev-s', pos: P([cx, y0 - R * 1.25, H * 0.5]), target: P([cx, cy, H * 0.45]), fov: 36 },
    { id: 'CAM04', name: 'East Elevation',     zone: 'elev-e', pos: P([x1 + R * 1.25, cy, H * 0.5]), target: P([cx, cy, H * 0.45]), fov: 36 },
    { id: 'CAM05', name: "Bird's-Eye",         zone: 'bird', pos: P([cx, cy - R * 0.18, H + R * 1.35]), target: P([cx, cy, 0]), fov: 40, orbit: true },
  ];

  // eye-level interior views on each floor level (best open spots)
  let n = 6; const starts = [];
  model.levels.forEach((lvl, i) => {
    const next = model.levels[i + 1]?.y ?? Infinity;
    const spots = world.openSpots(b, lvl.y, next);
    const picks = [];
    for (const s of spots) { if (picks.every(p => Math.hypot(p.x - s.x, p.y - s.y) > Math.max(6, R * 0.25))) picks.push(s); if (picks.length === 2) break; }
    picks.forEach((s, k) => {
      const z = s.floor + HEIGHTS.eye, d = s.view.d, L = Math.min(s.view.dist, 25);
      const lvlName = model.levels.length > 1 ? `Level ${i} · ` : '';
      cams.push({ id: `CAM${String(n++).padStart(2, '0')}`, name: `${lvlName}Interior ${k ? 'B' : 'A'}`, zone: `interior-${i}-${k}`,
        pos: P([s.x, s.y, z]), target: P([s.x + d.x * L, s.y - d.z * L, z - 0.15]), fov: 56, eye: true });
      starts.push({ pos: [s.x, s.y], heading: Math.atan2(-d.z, d.x) * 180 / Math.PI, feet: s.floor });
    });
  });
  cams.push({ id: `CAM${String(n++).padStart(2, '0')}`, name: 'Top Plan', zone: 'plan', top: true });
  const walkStart = starts[0] || { pos: [cx, cy], heading: 90, feet: model.levels[0]?.y ?? 0 };
  return { cams, walkStart };
}

// eye-level camera for a labelled zone: from one short end, looking down the long axis
export function zoneCamera(zone, world, levelY = 0) {
  const def = ZONE_DEFS.find(d => d.key === zone.key);
  const o = orientedRect(zone.pts); if (!o) return null;
  const [ux, uy] = o.u, half = o.len / 2, back = Math.max(0.3, Math.min(0.8, half * 0.15));
  const px = o.cx - ux * (half - back), py = o.cy - uy * (half - back);
  const floor = world ? world.floorAt(px, py, zone.level ?? levelY) : (zone.level ?? levelY);
  const z = floor + HEIGHTS.eye;
  return { id: `ZONE-${zone.key}`, name: `${def?.label ?? zone.key} View`, zone: zone.key, pos: P([px, py, z]),
    target: P([o.cx + ux * half, o.cy + uy * half, z - 0.25]), fov: 58, eye: true };
}
