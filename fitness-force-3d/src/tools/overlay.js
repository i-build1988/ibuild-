// Debug overlay primitives: always drawn on top, constant on-screen size.
import * as THREE from 'three';

export const OVERLAY_ORDER = 50;

/** Screen-space label (Sprite, sizeAttenuation off). h = fraction of viewport height. */
export function labelSprite(text, { color = '#ffffff', bg = 'rgba(10,10,11,.88)', border = '#d4121e', h = 0.034 } = {}) {
  const lines = String(text).split('\n'), px = 64, pad = 18, c = document.createElement('canvas'), g = c.getContext('2d');
  const font = (i) => `${i ? 500 : 600} ${i ? px * 0.7 : px}px "JetBrains Mono", ui-monospace, monospace`;
  let w = 0; lines.forEach((t, i) => { g.font = font(i); w = Math.max(w, g.measureText(t).width); });
  const lh = px * 1.15; c.width = Math.ceil(w + pad * 2); c.height = Math.ceil(lh * lines.length + pad * 1.2);
  g.fillStyle = bg; g.fillRect(0, 0, c.width, c.height); g.fillStyle = border; g.fillRect(0, 0, 6, c.height);
  g.textBaseline = 'top';
  lines.forEach((t, i) => { g.font = font(i); g.fillStyle = i ? 'rgba(255,255,255,.75)' : color; g.fillText(t, pad, pad * 0.6 + i * lh); });
  const tex = new THREE.CanvasTexture(c); tex.colorSpace = THREE.SRGBColorSpace;
  const s = new THREE.Sprite(new THREE.SpriteMaterial({ map: tex, depthTest: false, depthWrite: false, sizeAttenuation: false }));
  const hh = h * lines.length * 0.9 + h * 0.3; s.scale.set(hh * c.width / c.height, hh, 1);
  s.center.set(0.5, -0.25); s.renderOrder = OVERLAY_ORDER + 2;
  return s;
}

export function overlayLine(points, color = '#ff3b47', { dashed = false } = {}) {
  const g = new THREE.BufferGeometry().setFromPoints(points);
  const m = dashed ? new THREE.LineDashedMaterial({ color, dashSize: 0.15, gapSize: 0.1, depthTest: false, transparent: true })
                   : new THREE.LineBasicMaterial({ color, depthTest: false, transparent: true });
  const l = new THREE.Line(g, m); if (dashed) l.computeLineDistances(); l.renderOrder = OVERLAY_ORDER; return l;
}

export function overlayPoints(points, color = '#ffffff', size = 9) {
  const g = new THREE.BufferGeometry().setFromPoints(points);
  const p = new THREE.Points(g, new THREE.PointsMaterial({ color, size, sizeAttenuation: false, depthTest: false, transparent: true }));
  p.renderOrder = OVERLAY_ORDER + 1; return p;
}

export function disposeTree(o) {
  o.traverse(c => { c.geometry?.dispose(); const m = c.material; (Array.isArray(m) ? m : m ? [m] : []).forEach(x => { x.map?.dispose(); x.dispose(); }); });
}

/** Snap a raycast hit to the nearest vertex of the hit triangle if within `tol` metres. */
export function snapHit(hit, tol) {
  if (!hit?.face || !hit.object?.geometry) return { point: hit.point.clone(), snapped: false };
  const pos = hit.object.geometry.attributes.position, v = new THREE.Vector3();
  let best = null, bd = tol;
  for (const i of [hit.face.a, hit.face.b, hit.face.c]) {
    v.fromBufferAttribute(pos, i);
    if (hit.object.isInstancedMesh && hit.instanceId != null) { const m = new THREE.Matrix4(); hit.object.getMatrixAt(hit.instanceId, m); v.applyMatrix4(m); }
    v.applyMatrix4(hit.object.matrixWorld);
    const d = v.distanceTo(hit.point); if (d < bd) { bd = d; best = v.clone(); }
  }
  return best ? { point: best, snapped: true } : { point: hit.point.clone(), snapped: false };
}
