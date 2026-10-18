// DEBUG / INSPECT: click an object to read its name, size, position,
// material and bounding box. The selection is highlighted by a red tint
// (temporary material copies, originals restored on deselect) and a box.
import * as THREE from 'three';
import { OVERLAY_ORDER } from './overlay.js';

const TINT = new THREE.Color('#ff2a36');

export function createInspector(scene) {
  let sel = null, saved = null;
  const helper = new THREE.Box3Helper(new THREE.Box3(), '#ff3b47');
  helper.material.depthTest = false; helper.material.transparent = true; helper.renderOrder = OVERLAY_ORDER; helper.visible = false;
  helper.name = 'INSPECT HIGHLIGHT'; scene.add(helper);

  function tint(m) {
    const c = m.clone();
    if (c.emissive) { c.emissive.copy(TINT); c.emissiveIntensity = 0.55; }
    else if (c.color) c.color.lerp(TINT, 0.6);
    return c;
  }
  function select(obj) {
    if (sel && saved) sel.material = saved;
    sel = obj; saved = null;
    if (!obj) { helper.visible = false; return null; }
    if (obj.isMesh) { saved = obj.material; obj.material = Array.isArray(saved) ? saved.map(tint) : tint(saved); }
    helper.box.setFromObject(obj, true); helper.visible = true;
    return info(obj);
  }
  return { select, get selected() { return sel; }, originalMaterial: () => saved, helper };
}

/** Readable facts about one object. offset = GLB wrapper translation (viewer → native). */
export function info(obj, offset = new THREE.Vector3(), originalMaterial = null) {
  obj.updateWorldMatrix(true, false);
  const box = new THREE.Box3().setFromObject(obj, true), size = box.getSize(new THREE.Vector3()), centre = box.getCenter(new THREE.Vector3());
  const node = obj.getWorldPosition(new THREE.Vector3());
  let mats = [].concat(originalMaterial ?? obj.material ?? []), meshes = 0;
  if (!obj.isMesh) { const set = new Set(); obj.traverse(o => { if (o.isMesh) { meshes++; [].concat(o.material).forEach(m => set.add(m)); } }); mats = [...set]; }
  const g = obj.geometry, tris = g ? (g.index ? g.index.count : g.attributes.position.count) / 3 : 0;
  const path = []; for (let p = obj.parent; p && p.name !== 'REAL GLB MODEL' && p.type !== 'Scene'; p = p.parent) path.unshift(p.name || `(${p.type})`);
  const nat = (v) => v.clone().sub(offset);
  return {
    name: obj.name || `(unnamed ${obj.type})`, type: obj.isMesh ? obj.type : `${obj.type} · ${meshes} meshes`, path: path.join(' › ') || '—', geometry: g?.name || '—',
    materials: mats.map(m => m.name || `(unnamed ${m.type})`), materialTypes: mats.map(m => m.type),
    size, centre, node, box, tris: Math.round(tris), verts: g?.attributes.position.count ?? 0,
    nativeCentre: nat(centre), nativeMin: nat(box.min), nativeMax: nat(box.max),
  };
}
