// First-person walk mode.
// Desktop: drag to look, W A S D / arrow keys to move, Shift to move faster.
// Touch:   drag to look, on-screen ▲ ▼ buttons (wired by the UI) to move.
// Collides with the world (real GLB geometry, or the reference shell's wall
// segments) and follows its floors: platforms, steps, other levels.
import * as THREE from 'three';
import { HEIGHTS } from '../config/dimensions.js';

const RADIUS = 0.32, SPEED = 1.5;   // metres, metres/second (relaxed walking pace)

export function createWalk(camera, dom, initialWorld) {
  let world = initialWorld;
  const s = { on: false, yaw: 0, pitch: 0, x: 0, y: 0, keys: new Set(), push: 0, drag: null, eyeH: HEIGHTS.eye, feet: 0 };

  const down = (e) => { if (!s.on) return; s.drag = { x: e.clientX, y: e.clientY }; dom.setPointerCapture?.(e.pointerId); };
  const move = (e) => {
    if (!s.on || !s.drag) return;
    const dx = e.clientX - s.drag.x, dy = e.clientY - s.drag.y; s.drag = { x: e.clientX, y: e.clientY };
    s.yaw += dx * 0.0042; s.pitch = Math.max(-1.2, Math.min(1.2, s.pitch - dy * 0.0042));
  };
  const up = () => (s.drag = null);
  const key = (e, v) => {
    if (!s.on) return;
    const k = e.key.toLowerCase();
    if (['w', 'a', 's', 'd', 'arrowup', 'arrowdown', 'arrowleft', 'arrowright', 'shift'].includes(k)) {
      v ? s.keys.add(k) : s.keys.delete(k); if (k.startsWith('arrow')) e.preventDefault();
    }
  };
  dom.addEventListener('pointerdown', down); addEventListener('pointermove', move); addEventListener('pointerup', up);
  addEventListener('keydown', (e) => key(e, true)); addEventListener('keyup', (e) => key(e, false));
  addEventListener('blur', () => s.keys.clear());

  // plan heading: yaw 0 = east; plan direction (cos, sin)
  // feetHint = height to search for a floor from (multi-level models)
  function enter(planX, planY, headingDeg = 90, pitch = 0, feetHint = 0) {
    s.on = true; s.x = planX; s.y = planY; s.yaw = -THREE.MathUtils.degToRad(headingDeg) + Math.PI / 2; s.pitch = pitch;
    s.feet = world.floorAt(planX, planY, feetHint); s.eyeH = HEIGHTS.eye + s.feet;
  }
  // take over from the current camera pose
  function enterFromCamera() {
    const d = new THREE.Vector3(); camera.getWorldDirection(d);
    enter(camera.position.x, -camera.position.z, THREE.MathUtils.radToDeg(Math.atan2(-d.z, d.x)), 0, camera.position.y - HEIGHTS.eye);
  }
  function exit() { s.on = false; s.keys.clear(); s.push = 0; }

  function update(dt) {
    if (!s.on) return;
    const K = s.keys, f = (K.has('w') || K.has('arrowup') ? 1 : 0) - (K.has('s') || K.has('arrowdown') ? 1 : 0) + s.push;
    const r = (K.has('d') ? 1 : 0) - (K.has('a') ? 1 : 0);
    s.yaw += ((K.has('arrowright') ? 1 : 0) - (K.has('arrowleft') ? 1 : 0)) * 1.6 * dt;
    // facing direction in plan: yaw measured clockwise from north → (sin, cos)
    const fx = Math.sin(s.yaw), fy = Math.cos(s.yaw), rx = Math.cos(s.yaw), ry = -Math.sin(s.yaw);
    const sp = SPEED * (K.has('shift') ? 2 : 1) * dt;
    let nx = s.x + (fx * f + rx * r) * sp, ny = s.y + (fy * f + ry * r) * sp;
    [nx, ny] = world.collide(nx, ny, s.feet, RADIUS);
    s.x = nx; s.y = ny;
    s.feet = world.floorAt(s.x, s.y, s.feet);
    const target = HEIGHTS.eye + s.feet; s.eyeH += (target - s.eyeH) * Math.min(1, dt * 8);   // step onto platform
    camera.position.set(s.x, s.eyeH, -s.y);
    const cp = Math.cos(s.pitch);
    camera.lookAt(s.x + fx * cp, s.eyeH + Math.sin(s.pitch), -(s.y + fy * cp));
  }
  return { state: s, enter, enterFromCamera, exit, update, setPush: (v) => (s.push = v), setWorld: (w) => (world = w) };
}
