// Lighting rig with two moods.
//  DAY   — daylight through the east windows + soft sky fill.
//  NIGHT — cinematic: dark ambient, 3200 K warm pools in reception,
//          4000 K training light, LED strips do the drawing.
// Point lights are kept few and shadow-free for performance; only the sun casts shadows.
import * as THREE from 'three';
import { ZONES, HALLS, HEIGHTS } from '../config/dimensions.js';
import { V } from '../zones/geometry.js';
import { setLedLevel } from '../materials/materials.js';

// approximate black-body colour for a Kelvin temperature
export function kelvin(k) {
  const t = k / 100; let r, g, b;
  r = t <= 66 ? 255 : 329.698727446 * Math.pow(t - 60, -0.1332047592);
  g = t <= 66 ? 99.4708025861 * Math.log(t) - 161.1195681661 : 288.1221695283 * Math.pow(t - 60, -0.0755148492);
  b = t >= 66 ? 255 : t <= 19 ? 0 : 138.5177312231 * Math.log(t - 10) - 305.0447927307;
  const c = (v) => Math.min(255, Math.max(0, v)) / 255;
  return new THREE.Color(c(r), c(g), c(b));
}

export function buildLighting(scene, renderer) {
  const rig = new THREE.Group(); rig.name = 'lighting'; scene.add(rig);

  const hemi = new THREE.HemisphereLight('#dfe8f2', '#3a3633', 1.0); rig.add(hemi);

  const sun = new THREE.DirectionalLight('#fff3e2', 3.2);
  const G = HALLS.gym;
  sun.position.copy(V(G.x + G.w + 30, G.y + G.d * 0.4, 22));
  sun.target.position.copy(V(G.x + G.w / 2, G.y + G.d / 2, 0));
  sun.castShadow = true; sun.shadow.mapSize.set(2048, 2048);
  Object.assign(sun.shadow.camera, { left: -40, right: 40, top: 40, bottom: -40, near: 1, far: 140 });
  sun.shadow.bias = -0.0005; sun.shadow.normalBias = 0.03;
  rig.add(sun, sun.target);

  // interior pools (a handful, no shadows)
  const warm = kelvin(3200), neutral = kelvin(4000);
  const pools = [];
  const pool = (x, y, h, color, intensity, distance) => {
    const l = new THREE.PointLight(color, intensity, distance, 2); l.position.copy(V(x, y, h)); rig.add(l); pools.push([l, intensity]); return l;
  };
  const R = ZONES.reception, C = ZONES.cardio, X = ZONES.hyrox, E = ZONES.entrance, K = ZONES.corridor;
  pool(R.x + 2.2, R.y + 6.5, 3.3, warm, 14, 9);
  pool(R.x + 5.8, R.y + 5.0, 3.3, warm, 14, 9);
  pool(E.x + E.w / 2, E.y + E.d / 2, 3.2, warm, 12, 8);
  pool(K.x + K.w / 2, K.y + 4, 3.0, neutral, 8, 8);
  pool(K.x + K.w / 2, K.y + 12, 3.0, neutral, 8, 8);
  for (const fx of [0.25, 0.75]) for (const fy of [0.25, 0.75]) pool(C.x + C.w * fx, C.y + C.d * fy, 4.5, neutral, 40, 14);
  for (const fy of [0.2, 0.5, 0.8]) pool(X.x + X.w / 2, X.y + X.d * fy, 3.2, neutral, 14, 9);

  // re-aim the rig at the real GLB: sun + shadow frustum over its bounds,
  // interior pools on a grid under each floor level's ceiling
  function fitTo({ x0, x1, y0, y1, height, levels }) {
    for (const [l] of pools) rig.remove(l); pools.length = 0;
    const cx = (x0 + x1) / 2, cy = (y0 + y1) / 2, R = Math.max(x1 - x0, y1 - y0, 10) * 0.75;
    sun.position.copy(V(cx + R * 1.4, cy + R * 0.5, height + R)); sun.target.position.copy(V(cx, cy, 0));
    Object.assign(sun.shadow.camera, { left: -R * 1.2, right: R * 1.2, top: R * 1.2, bottom: -R * 1.2, near: 0.5, far: R * 5 + height });
    sun.shadow.camera.updateProjectionMatrix();
    // at most 8 pools in total: every point light costs shader time on every material
    const budget = Math.max(1, Math.floor(8 / levels.length)), W = x1 - x0, D = y1 - y0;
    const nx = Math.max(1, Math.min(budget, Math.round(Math.sqrt(budget * W / D)))), ny = Math.max(1, Math.floor(budget / nx));
    const reach = Math.max(W / nx, D / ny) * 1.3;
    levels.forEach((lvl, i) => {
      const top = Math.min(lvl.y + 3.0, (levels[i + 1]?.y ?? Infinity) - 0.4);
      for (let a = 0; a < nx; a++) for (let b = 0; b < ny; b++)
        pool(x0 + (x1 - x0) * (a + 0.5) / nx, y0 + (y1 - y0) * (b + 0.5) / ny, top, neutral, 10 * reach / 9, reach);
    });
    setMode(state.mode);
  }

  const env = scene.environment;                 // reflections: full by day, off at night
  const state = { mode: 'day' };
  function setMode(mode) {
    state.mode = mode;
    const day = mode === 'day';
    hemi.intensity = day ? 1.0 : 0.12;
    hemi.color.set(day ? '#dfe8f2' : '#6d7890');
    sun.intensity = day ? 3.2 : 0;
    sun.castShadow = day;
    for (const [l, i] of pools) l.intensity = (day ? i * 0.35 : i) * 3;
    setLedLevel(day ? 0.7 : 1.6);
    renderer.toneMappingExposure = day ? 0.95 : 1.05;
    scene.background.set(day ? '#2b2d31' : '#050506');
    scene.environment = day ? env : null;
  }
  return { setMode, state, fitTo };
}
