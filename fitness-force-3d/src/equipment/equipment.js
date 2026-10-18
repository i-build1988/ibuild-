// Equipment layer — PHASE 2 (not built yet, by design).
// Per the brief, equipment waits until the shell, dimensions and circulation
// are approved. The instancing helper below is ready for repeated machines
// (25 treadmills = one draw call per material).
import * as THREE from 'three';
import { V } from '../zones/geometry.js';

/** Place one model many times with a single InstancedMesh per part.
 *  parts: [{ geometry, material }]  ·  placements: [{ x, y, heading }] (plan metres, degrees) */
export function instanced(parts, placements) {
  const g = new THREE.Group(), m4 = new THREE.Matrix4(), q = new THREE.Quaternion(), s = new THREE.Vector3(1, 1, 1);
  for (const { geometry, material } of parts) {
    const im = new THREE.InstancedMesh(geometry, material, placements.length);
    placements.forEach((p, i) => {
      q.setFromAxisAngle(new THREE.Vector3(0, 1, 0), THREE.MathUtils.degToRad(p.heading ?? 0));
      im.setMatrixAt(i, m4.compose(V(p.x, p.y, 0), q, s));
    });
    im.castShadow = im.receiveShadow = true; g.add(im);
  }
  return g;
}

export function buildEquipment() {
  const group = new THREE.Group(); group.name = 'equipment';
  return group;   // empty in phase 1
}
