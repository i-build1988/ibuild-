// Measurement tool: click point A, click point B → distance in metres.
// Points land on the model surface (optionally snapped to the nearest vertex).
// Keeps every measurement until cleared; reports ΔX, ΔY (height), ΔZ and plan distance.
import * as THREE from 'three';
import { labelSprite, overlayLine, overlayPoints, disposeTree } from './overlay.js';

export function createMeasure(scene, onChange = () => {}) {
  const group = new THREE.Group(); group.name = 'MEASUREMENTS'; scene.add(group);
  const list = []; let pending = null, rubber = null;

  function stats(a, b) {
    const d = new THREE.Vector3().subVectors(b, a);
    return { dist: d.length(), dx: Math.abs(d.x), dy: Math.abs(d.y), dz: Math.abs(d.z), plan: Math.hypot(d.x, d.z) };
  }
  function draw(a, b, color = '#ff3b47', dashed = false) {
    const g = new THREE.Group(), s = stats(a, b);
    g.add(overlayLine([a, b], color, { dashed }), overlayPoints([a, b], '#ffffff'));
    const lab = labelSprite(`${s.dist.toFixed(3)} m`); lab.position.copy(a).add(b).multiplyScalar(0.5); g.add(lab);
    return g;
  }
  function clearRubber() { if (rubber) { group.remove(rubber); disposeTree(rubber); rubber = null; } }

  function click(p) {
    if (!pending) {
      const mk = overlayPoints([p], '#ff3b47', 11); group.add(mk);
      pending = { a: p.clone(), mk }; onChange(); return;
    }
    clearRubber(); group.remove(pending.mk); disposeTree(pending.mk);
    const m = { a: pending.a, b: p.clone(), ...stats(pending.a, p) };
    m.obj = draw(m.a, m.b); group.add(m.obj); list.push(m); pending = null; onChange();
  }
  function hover(p) {
    if (!pending) return; clearRubber();
    if (p) { rubber = draw(pending.a, p, '#ffb3b8', true); group.add(rubber); }
  }
  function cancel() { clearRubber(); if (pending) { group.remove(pending.mk); disposeTree(pending.mk); pending = null; } onChange(); }
  function remove(i) { const [m] = list.splice(i, 1); if (m) { group.remove(m.obj); disposeTree(m.obj); } onChange(); }
  function clear() { cancel(); while (list.length) remove(0); }
  return { group, list, click, hover, cancel, remove, clear, get pending() { return pending; } };
}
