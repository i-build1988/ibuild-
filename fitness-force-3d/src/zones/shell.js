// Simplified architectural shell: slabs, zone floor finishes, the raised
// reception platform, walls/glazing, elevator core and ceilings.
// Everything is generated from config/dimensions.js.
import * as THREE from 'three';
import { ZONES, HALLS, WALLS, ELEVATORS, HEIGHTS, COLUMNS } from '../config/dimensions.js';
import { floorMaterial, MAT } from '../materials/materials.js';
import { V, boxAlong, platformOutline, extrudePlan, rectPts, pointInPolygon } from './geometry.js';
import { buildWalls } from './walls.js';

export function buildShell() {
  const root = new THREE.Group(); root.name = 'shell';
  const ceilings = new THREE.Group(); ceilings.name = 'ceilings';
  const floors = new THREE.Group(); floors.name = 'floors';
  const shadow = (m) => { m.receiveShadow = true; m.castShadow = true; return m; };

  // --- hall slabs (polished concrete, top at 0) ---
  for (const hall of Object.values(HALLS)) {
    const m = new THREE.Mesh(new THREE.BoxGeometry(hall.w, 0.3, hall.d), floorMaterial(hall.floor, hall.w, hall.d));
    m.position.copy(V(hall.x + hall.w / 2, hall.y + hall.d / 2, -0.15)); m.receiveShadow = true; floors.add(m);
  }

  // --- surrounding ground (dark, so the club reads as the subject) ---
  const ground = new THREE.Mesh(new THREE.PlaneGeometry(400, 400), new THREE.MeshStandardMaterial({ color: '#1c1d1f', roughness: 1 }));
  ground.rotation.x = -Math.PI / 2; ground.position.y = -0.31; ground.receiveShadow = true; floors.add(ground);

  // --- rectangular zone finishes (2 cm build-up) ---
  for (const [key, z] of Object.entries(ZONES)) {
    if (z.platform) continue;
    const m = new THREE.Mesh(new THREE.BoxGeometry(z.w, 0.02, z.d), floorMaterial(z.floor, z.w, z.d));
    m.position.copy(V(z.x + z.w / 2, z.y + z.d / 2, 0.01)); m.receiveShadow = true; m.name = 'floor-' + key; floors.add(m);
  }

  // --- LED floor borders: cardio (photo 4) ---
  const c = ZONES.cardio, e = 0.06;
  for (const [a, b] of edges(rectPts(c))) floors.add(boxAlong(a, b, e, 0.0, 0.03, MAT.ledRed));

  // --- reception platform (photos 1–3, 7) ---
  const R = ZONES.reception, pts = platformOutline(R.platform).map(([x, y]) => [x + R.x, y + R.y]);
  const top = floorMaterial(R.floor, 1, 1);                      // cap UVs are in metres
  floors.add(shadow(extrudePlan(pts, HEIGHTS.platformRise, 0, [top, MAT.platformEdge])));
  // warm LED cove under the curved edge
  const cove = new THREE.Group();
  for (let i = 1; i < pts.length; i++) {
    const [a, b] = [pts[i], pts[(i + 1) % pts.length]];
    if (a[1] >= R.platform.right - 1e-6 && b[1] >= R.platform.right - 1e-6) continue;   // not along the back wall
    const m = boxAlong(a, b, 0.05, 0.02, 0.05, MAT.ledWarm); if (m) { m.position.add(outward(a, b, 0.03)); cove.add(m); }
  }
  floors.add(cove);
  // stainless railing along the platform edge facing the entrance (photo 7)
  const E = ZONES.entrance, rx = R.x + R.platform.width - 0.08;
  const rail = new THREE.Group();
  const y0 = E.y, y1 = R.y + R.platform.right - 0.15, hr = HEIGHTS.platformRise + HEIGHTS.railing;
  const tube = new THREE.Mesh(new THREE.CylinderGeometry(0.025, 0.025, y1 - y0, 12), MAT.steel);
  tube.rotation.x = Math.PI / 2; tube.position.copy(V(rx, (y0 + y1) / 2, hr)); rail.add(tube);
  const mid = tube.clone(); mid.position.y = hr - 0.45; rail.add(mid);
  for (let y = y0; y <= y1 + 1e-6; y += (y1 - y0) / 4) {
    const p = new THREE.Mesh(new THREE.CylinderGeometry(0.022, 0.022, HEIGHTS.railing, 10), MAT.steel);
    p.position.copy(V(rx, y, HEIGHTS.platformRise + HEIGHTS.railing / 2)); rail.add(p);
  }
  rail.traverse(o => o.isMesh && (o.castShadow = true));
  root.add(rail);

  // --- walls & glazing ---
  const { group: walls, glassGroup, colliders } = buildWalls(WALLS);
  root.add(walls, glassGroup);

  // --- elevator core [A4] ---
  const ec = ELEVATORS.core;
  const core = new THREE.Mesh(new THREE.BoxGeometry(ec.w, HEIGHTS.frontHall, ec.d), MAT.wallCharcoal);
  core.position.copy(V(ec.x + ec.w / 2, ec.y + ec.d / 2, HEIGHTS.frontHall / 2)); shadow(core); root.add(core);

  // --- columns (none until the grid is known) ---
  for (const col of COLUMNS) {
    const m = new THREE.Mesh(new THREE.BoxGeometry(col.w, HEIGHTS.gymHall, col.d), MAT.wallCharcoal);
    m.position.copy(V(col.x, col.y, HEIGHTS.gymHall / 2)); shadow(m); root.add(m);
    colliders.push([[col.x - col.w / 2, col.y], [col.x + col.w / 2, col.y]]);
  }

  // --- ceilings (shown only at eye level) ---
  const ceil = (x, y, w, d, h, mat) => {
    const m = new THREE.Mesh(new THREE.PlaneGeometry(w, d), mat); m.rotation.x = Math.PI / 2;
    m.position.copy(V(x + w / 2, y + d / 2, h)); ceilings.add(m); return m;
  };
  const F = HALLS.front, G = HALLS.gym, C = ZONES.corridor, X = ZONES.hyrox;
  ceil(F.x, F.y, F.w, F.d, HEIGHTS.frontHall, MAT.ceilingBlack);
  ceil(G.x, G.y, G.w, G.d, HEIGHTS.gymHall, MAT.ceilingBlack);
  ceil(C.x, C.y, C.w, C.d, HEIGHTS.corridor, MAT.ceilingLight);
  const strip = boxAlong([C.x + C.w / 2, C.y + 0.3], [C.x + C.w / 2, C.y + C.d - 0.3], 0.06, HEIGHTS.corridor - 0.03, HEIGHTS.corridor, MAT.ledCool);
  ceilings.add(strip);                                                     // centre linear light (photo 5)
  // HYROX ceiling frame: black beams every 2.4 m with linear LEDs on the long sides (photo 8)
  for (let y = X.y; y <= X.y + X.d + 1e-6; y += X.d / 7)
    ceilings.add(boxAlong([X.x, y], [X.x + X.w, y], 0.2, HEIGHTS.hyroxFrame - 0.3, HEIGHTS.hyroxFrame, MAT.frame));
  for (const x of [X.x + 0.35, X.x + X.w - 0.35]) {
    ceilings.add(boxAlong([x, X.y], [x, X.y + X.d], 0.25, HEIGHTS.hyroxFrame - 0.3, HEIGHTS.hyroxFrame, MAT.frame));
    ceilings.add(boxAlong([x, X.y], [x, X.y + X.d], 0.05, HEIGHTS.hyroxFrame - 0.33, HEIGHTS.hyroxFrame - 0.3, MAT.ledCool));
  }
  // reception bulkhead band above the back wall (photos 1–2)
  ceilings.add(boxAlong([HALLS.front.x, 8.2], [8.0, 8.2], 1.8, HEIGHTS.receptionBulkhead, HEIGHTS.receptionBulkhead + 0.4, MAT.wallCharcoal));

  root.add(floors, ceilings);

  // floor height lookup for walk mode (platform is raised)
  const floorAt = (x, y) => (pointInPolygon([x, y], pts) ? HEIGHTS.platformRise : 0);
  // platform edge acts as a collider only along the railing side
  colliders.push([[rx + 0.08, y0], [rx + 0.08, y1]]);

  return { root, ceilings, glass: glassGroup, colliders, floorAt, platform: pts };
}

function edges(pts) { return pts.map((p, i) => [p, pts[(i + 1) % pts.length]]); }
function outward(a, b, d) {           // outline runs clockwise in plan, so outside = left normal (-dy, dx)
  const dx = b[0] - a[0], dy = b[1] - a[1], L = Math.hypot(dx, dy) || 1;
  return new THREE.Vector3((-dy / L) * d, 0, -(dx / L) * d);
}
