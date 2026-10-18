// Loads the real architectural GLB and prepares it for the viewer.
// The glTF scene graph is kept exactly as exported (hierarchy, names,
// transforms, materials). It is placed inside one wrapper group whose only
// job is a translation: plan centre → origin, ground floor → height 0.
// No rotation, no scaling.
import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { DRACOLoader } from 'three/addons/loaders/DRACOLoader.js';
import { MeshoptDecoder } from 'three/addons/libs/meshopt_decoder.module.js';
import { MODEL } from '../config/model.js';
import { analyzeModel } from './analyze.js';

let bvh = null;   // three-mesh-bvh, optional: speeds up picking and walk collisions on big models
const bvhReady = import('three-mesh-bvh').then(m => (bvh = m)).catch(() => null);

function makeLoader() {
  const loader = new GLTFLoader();
  const draco = new DRACOLoader(); draco.setDecoderPath(MODEL.dracoPath);
  loader.setDRACOLoader(draco); loader.setMeshoptDecoder(MeshoptDecoder);
  return loader;
}

/** source: URL string or File. onProgress(0..1 | null) */
export async function loadGLB(source, onProgress) {
  const loader = makeLoader();
  let buf, name;
  if (typeof source === 'string') {
    const res = await fetch(source);
    if (!res.ok) throw Object.assign(new Error(`${source}: HTTP ${res.status}`), { notFound: res.status === 404 });
    buf = await readWithProgress(res, onProgress); name = source.split('/').pop();
  } else { buf = await source.arrayBuffer(); name = source.name; }
  const gltf = await new Promise((resolve, reject) => loader.parse(buf, '', resolve, reject));
  await bvhReady;
  return prepare(gltf, name, buf.byteLength);
}

async function readWithProgress(res, onProgress) {
  const total = +res.headers.get('content-length') || 0;
  if (!res.body || !total) return res.arrayBuffer();
  const reader = res.body.getReader(), out = new Uint8Array(total); let got = 0;
  for (;;) {
    const { done, value } = await reader.read(); if (done) break;
    out.set(value, got); got += value.length; onProgress?.(got / total);
  }
  return out.buffer;
}

function prepare(gltf, fileName, bytes) {
  const scene = gltf.scene || gltf.scenes[0];
  const native = analyzeModel(scene);

  const root = new THREE.Group(); root.name = 'REAL GLB MODEL';
  const offset = new THREE.Vector3(-native.center.x, -native.ground, -native.center.z);
  root.position.copy(offset); root.add(scene); root.updateMatrixWorld(true);

  const meshes = [];
  scene.traverse(o => {
    if (!o.isMesh) return;
    o.castShadow = o.receiveShadow = true;
    if (bvh && !o.isSkinnedMesh) { o.geometry.computeBoundsTree = bvh.computeBoundsTree; o.geometry.computeBoundsTree(); o.raycast = bvh.acceleratedRaycast; }
    meshes.push(o);
  });

  // everything below in viewer coordinates (after the translation)
  const box = native.box.clone().translate(offset);
  const shift = (l) => ({ y: l.y - native.ground, area: l.area });
  return {
    root, scene, gltf, fileName, bytes, offset, meshes, bvh: !!bvh,
    native,                                   // native GLB coordinates, untouched
    box, size: native.size.clone(), height: native.size.y,
    levels: native.levels.map(shift), surfaces: native.surfaces.map(shift),
    key: `${fileName}|${native.size.toArray().map(v => v.toFixed(2)).join('x')}`,
  };
}
