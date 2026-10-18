// Geometry analysis of the loaded GLB, in its native coordinates:
// exact bounding box, mesh / triangle / material counts, and floor levels.
// Floor levels come from a histogram of upward-facing surface area by height:
// large horizontal areas at one height are floors; small ones are steps,
// platforms, counters. The roof top is excluded.
import * as THREE from 'three';

const BIN = 0.05;            // histogram resolution (m)
const MAX_TRIS = 3_000_000;  // sample above this to keep loading fast

export function analyzeModel(root) {
  root.updateMatrixWorld(true);
  const box = new THREE.Box3().setFromObject(root, true);   // precise: uses vertices, not local boxes

  let meshes = 0, tris = 0; const mats = new Set();
  root.traverse(o => {
    if (!o.isMesh) return;
    meshes++; const g = o.geometry, n = g.index ? g.index.count / 3 : g.attributes.position.count / 3;
    tris += n * (o.isInstancedMesh ? o.count : 1);
    (Array.isArray(o.material) ? o.material : [o.material]).forEach(m => m && mats.add(m));
  });

  const stride = Math.max(1, Math.ceil(tris / MAX_TRIS));
  const up = new Map(), down = new Map();
  const a = new THREE.Vector3(), b = new THREE.Vector3(), c = new THREE.Vector3(), ab = new THREE.Vector3(), ac = new THREE.Vector3();
  const m4 = new THREE.Matrix4(), im = new THREE.Matrix4();
  root.traverse(o => {
    if (!o.isMesh) return;
    const pos = o.geometry.attributes.position, idx = o.geometry.index, count = idx ? idx.count : pos.count;
    const instances = o.isInstancedMesh ? o.count : 1;
    for (let k = 0; k < instances; k++) {
      m4.copy(o.matrixWorld); if (o.isInstancedMesh) { o.getMatrixAt(k, im); m4.multiply(im); }
      for (let i = 0; i + 2 < count; i += 3 * stride) {
        const i0 = idx ? idx.getX(i) : i, i1 = idx ? idx.getX(i + 1) : i + 1, i2 = idx ? idx.getX(i + 2) : i + 2;
        a.fromBufferAttribute(pos, i0).applyMatrix4(m4); b.fromBufferAttribute(pos, i1).applyMatrix4(m4); c.fromBufferAttribute(pos, i2).applyMatrix4(m4);
        ab.subVectors(b, a); ac.subVectors(c, a); ab.cross(ac);
        const len = ab.length(); if (len < 1e-9) continue;
        const ny = ab.y / len, area = (len / 2) * stride;
        if (Math.abs(ny) < 0.95) continue;
        const bin = Math.round((a.y + b.y + c.y) / 3 / BIN), map = ny > 0 ? up : down;
        map.set(bin, (map.get(bin) || 0) + area);
      }
    }
  });

  // floors modelled with flipped normals: fall back to downward faces
  const total = (m) => [...m.values()].reduce((s, v) => s + v, 0);
  const hist = total(up) >= total(down) * 0.2 ? up : down;
  const clusters = clusterBins(hist);
  const maxArea = Math.max(0, ...clusters.map(c => c.area));
  const surfaces = clusters.filter(c => c.area >= Math.max(0.5, maxArea * 0.01)).sort((p, q) => p.y - q.y);

  // floor levels: big areas, ≥ 2.0 m apart, not the roof top
  const roofish = (c) => c.y > box.max.y - 0.25 && clusters.length > 1;
  const levels = [];
  for (const c of [...surfaces].sort((p, q) => q.area - p.area)) {
    if (c.area < maxArea * 0.12 || roofish(c)) continue;
    if (levels.every(l => Math.abs(l.y - c.y) >= 2.0)) levels.push(c);
  }
  levels.sort((p, q) => p.y - q.y);
  if (!levels.length) levels.push({ y: box.min.y, area: 0 });

  return {
    box, size: box.getSize(new THREE.Vector3()), center: box.getCenter(new THREE.Vector3()),
    meshes, tris: Math.round(tris), materials: mats.size, sampled: stride > 1,
    levels, surfaces, ground: levels[0].y,
  };
}

function clusterBins(map) {
  const bins = [...map.entries()].sort((p, q) => p[0] - q[0]), out = [];
  for (const [bin, area] of bins) {
    const y = bin * BIN, last = out[out.length - 1];
    if (last && y - last.yMax <= 0.15) { last.sum += y * area; last.area += area; last.yMax = y; }
    else out.push({ sum: y * area, area, yMax: y });
  }
  return out.map(c => ({ y: c.sum / c.area, area: c.area }));
}
