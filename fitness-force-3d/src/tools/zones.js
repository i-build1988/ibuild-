// Zone identification on the TOP VIEW of the real GLB.
// The client picks a zone (RECEPTION, CARDIO …) and outlines it on the plan:
//   drag            → axis-aligned rectangle
//   click corners   → polygon (click the first corner, double-click or Enter to close)
//   "Use selected"  → plan footprint of the mesh picked in INSPECT
// Each zone is measured from its outline: minimum-area enclosing rectangle
// (length × width, works for rotated rooms) and true polygon area.
// Nothing here edits the model.
import * as THREE from 'three';
import { ZONE_DEFS } from '../config/model.js';
import { textPlane, polyLine } from '../zones/planAnnotations.js';
import { orientedRect, convexHull } from './geom2d.js';
import { disposeTree, overlayPoints } from './overlay.js';

const V = (x, y, h) => new THREE.Vector3(x, h, -y);

export function createZones(scene, onChange = () => {}) {
  const group = new THREE.Group(); group.name = 'ZONE LABELS'; scene.add(group);
  const zones = {};                       // key → { key, pts, level, source }
  const drawn = {};                       // key → Object3D
  let active = null, draft = null, draftObj = null;

  const def = (k) => ZONE_DEFS.find(d => d.key === k);
  function measure(key) { const z = zones[key]; return z ? orientedRect(z.pts) : null; }

  function render(key) {
    if (drawn[key]) { group.remove(drawn[key]); disposeTree(drawn[key]); delete drawn[key]; }
    const z = zones[key]; if (!z) return;
    const d = def(key), o = orientedRect(z.pts), g = new THREE.Group(); g.name = `zone-${key}`;
    const h = z.level + 0.06;
    const shape = new THREE.Shape(z.pts.map(([x, y]) => new THREE.Vector2(x, y)));
    const fill = new THREE.Mesh(new THREE.ShapeGeometry(shape), new THREE.MeshBasicMaterial({ color: d.color, transparent: true, opacity: 0.22, depthWrite: false, side: THREE.DoubleSide }));
    fill.rotation.x = -Math.PI / 2; fill.position.y = h; fill.renderOrder = 8; g.add(fill);
    const line = polyLine(z.pts, d.color, true, false, h + 0.01); line.material.linewidth = 2; g.add(line);
    if (o) {
      const lab = textPlane([d.label, `${o.len.toFixed(2)} × ${o.wid.toFixed(2)} m · ${o.area.toFixed(2)} m²`],
        { size: 1.0, bg: 'rgba(10,10,11,.8)', color: d.color, maxW: o.len * 0.85 });
      // keep the label inside the zone: shrink to fit the short side too
      const p = lab.geometry.parameters; const k = Math.min(1, (o.wid * 0.7) / p.height); lab.scale.set(k, k, 1);
      lab.position.copy(V(o.cx, o.cy, h + 0.02));
      let ang = Math.atan2(o.u[1], o.u[0]); if (ang > Math.PI / 2) ang -= Math.PI; if (ang < -Math.PI / 2) ang += Math.PI;
      lab.rotation.z = ang; g.add(lab);
    }
    group.add(g); drawn[key] = g;
  }

  function set(key, pts, level, source) {
    if (!def(key) || !pts || pts.length < 3) return;
    zones[key] = { key, pts: pts.map(([x, y]) => [+x.toFixed(4), +y.toFixed(4)]), level: +(+level).toFixed(3), source };
    render(key); onChange();
  }
  function remove(key) { delete zones[key]; render(key); onChange(); }
  function clearAll() { for (const k of Object.keys(zones)) { delete zones[k]; render(k); } onChange(); }

  // from a mesh: convex hull of its vertices in plan (follows rotated rooms)
  function setFromObject(key, obj, level) {
    const pts = [], v = new THREE.Vector3();
    obj.updateWorldMatrix(true, true);
    obj.traverse(o => {
      if (!o.isMesh) return; const pos = o.geometry.attributes.position, step = Math.max(1, Math.floor(pos.count / 4000));
      for (let i = 0; i < pos.count; i += step) { v.fromBufferAttribute(pos, i).applyMatrix4(o.matrixWorld); pts.push([v.x, -v.z]); }
    });
    const hull = convexHull(pts); if (hull.length < 3) return false;
    set(key, hull, level, `mesh: ${obj.name || '(unnamed)'}`); return true;
  }

  // ---- drawing ------------------------------------------------------------
  function clearDraft() { if (draftObj) { group.remove(draftObj); disposeTree(draftObj); draftObj = null; } }
  function showDraft(extra) {
    clearDraft(); if (!draft) return;
    const pts = extra ? [...draft.pts, extra] : draft.pts; if (!pts.length) return;
    const g = new THREE.Group(), h = draft.level + 0.08, col = def(active)?.color ?? '#fff';
    if (pts.length > 1) g.add(polyLine(draft.rect ? pts : pts, col, !!draft.rect, !draft.rect, h));
    g.add(overlayPoints(pts.map(([x, y]) => V(x, y, h)), col, 10));
    group.add(g); draftObj = g;
  }
  function rectPts(a, b) { return [[a[0], a[1]], [b[0], a[1]], [b[0], b[1]], [a[0], b[1]]]; }

  const api = {
    group, zones, measure, set, remove, clearAll, setFromObject, render,
    get active() { return active; },
    setActive(key) { active = key; api.cancel(); onChange(); },
    get drawing() { return !!draft; },
    cancel() { draft = null; clearDraft(); },
    // plan points come from main.js (already snapped). level = floor height of the drawing
    down(p, level) { if (!active) return; if (!draft) draft = { pts: [], level, start: p, rect: null }; else draft.start = p; },
    move(p) {
      if (!draft) return;
      if (draft.dragging) { draft.rect = rectPts(draft.dragStart, p); draft.pts = draft.rect; showDraft(); }
      else showDraft(p);
    },
    beginDrag(p) { if (draft && !draft.pts.length) { draft.dragging = true; draft.dragStart = draft.start; } },
    up(p, wasDrag, closeTol) {
      if (!draft) return;
      if (draft.dragging) {
        const r = rectPts(draft.dragStart, p), w = Math.abs(p[0] - draft.dragStart[0]), d = Math.abs(p[1] - draft.dragStart[1]);
        if (w > 0.2 && d > 0.2) set(active, r, draft.level, 'drawn: rectangle');
        api.cancel(); return;
      }
      if (wasDrag) return;
      const f = draft.pts[0];
      if (f && draft.pts.length >= 3 && Math.hypot(p[0] - f[0], p[1] - f[1]) <= closeTol) return api.finish();
      draft.pts.push(p); showDraft();
    },
    finish() { if (draft && draft.pts.length >= 3) set(active, draft.pts, draft.level, 'drawn: polygon'); api.cancel(); },
    undoPoint() { if (draft) { draft.pts.pop(); if (!draft.pts.length) api.cancel(); else showDraft(); } },
    corners() { return Object.values(zones).flatMap(z => z.pts); },
    toJSON() { return Object.values(zones); },
    load(arr) { clearAll(); for (const z of arr || []) if (z && def(z.key)) set(z.key, z.pts, z.level ?? 0, z.source ?? 'imported'); },
  };
  return api;
}
