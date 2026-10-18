// Perspective camera (orbit + eye-level presets) and an orthographic plan camera.
// flyTo() eases the perspective camera between presets.
import * as THREE from 'three';
import { HALLS } from '../config/dimensions.js';
import { V } from '../zones/geometry.js';

export function createCameraRig() {
  const persp = new THREE.PerspectiveCamera(50, 1, 0.05, 600);
  const ortho = new THREE.OrthographicCamera(-10, 10, 10, -10, 0.1, 400);

  // plan extents (reference halls until the GLB is loaded), with margin for dimensions / north arrow
  const plan = {};
  let vw = 1, vh = 1;
  function setPlanBounds(x0, x1, y0, y1, top = 60) {
    // room for the side panels, the north arrow (east) and the scale bar / title (south)
    const W = x1 - x0, D = y1 - y0, m = Math.max(2, Math.max(W, D) * 0.08);
    Object.assign(plan, { x0: x0 - m - W * 0.16, x1: x1 + m + W * 0.22, y0: y0 - m * 1.8 - D * 0.14, y1: y1 + m });
    plan.cx = (plan.x0 + plan.x1) / 2; plan.cy = (plan.y0 + plan.y1) / 2;
    ortho.position.copy(V(plan.cx, plan.cy, top + 60)); ortho.up.set(0, 0, -1); ortho.lookAt(V(plan.cx, plan.cy, 0));
    ortho.far = top + 200; resize(vw, vh);
  }
  const xs = Object.values(HALLS).flatMap(h => [h.x, h.x + h.w]), ys = Object.values(HALLS).flatMap(h => [h.y, h.y + h.d]);
  setPlanBounds(Math.min(...xs), Math.max(...xs), Math.min(...ys), Math.max(...ys));

  function resize(w, h) {
    vw = w; vh = h;
    persp.aspect = w / h; persp.updateProjectionMatrix();
    const pw = plan.x1 - plan.x0, ph = plan.y1 - plan.y0, a = w / h;
    let hw = pw / 2, hh = ph / 2; if (hw / hh > a) hh = hw / a; else hw = hh * a;
    Object.assign(ortho, { left: -hw, right: hw, top: hh, bottom: -hh }); ortho.updateProjectionMatrix();
  }

  let tween = null;
  function flyTo(pos, target, fov, controls, { instant = false, ms = 1100 } = {}) {
    const p0 = persp.position.clone(), t0 = controls.target.clone(), f0 = persp.fov;
    const p1 = V(pos[0], pos[1], pos[2]), t1 = V(target[0], target[1], target[2]);
    const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (instant || reduce) { persp.position.copy(p1); controls.target.copy(t1); persp.fov = fov; persp.updateProjectionMatrix(); tween = null; return; }
    const start = performance.now();
    tween = (now) => {
      let k = Math.min(1, (now - start) / ms); k = k < 0.5 ? 4 * k * k * k : 1 - Math.pow(-2 * k + 2, 3) / 2;
      persp.position.lerpVectors(p0, p1, k); controls.target.lerpVectors(t0, t1, k);
      persp.fov = f0 + (fov - f0) * k; persp.updateProjectionMatrix();
      if (k >= 1) tween = null;
    };
  }
  const update = (now) => tween && tween(now);
  const busy = () => !!tween;
  return { persp, ortho, plan, resize, flyTo, update, busy, setPlanBounds };
}
