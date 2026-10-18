// PBR material library. Floors take metric tiling via floorMaterial(key, w, d).
// Nothing is glossy by default: roughness is set per finish.
import * as THREE from 'three';
import { BRAND } from '../config/brand.js';
import { woodTexture, stoneTileTexture, rubberTexture, concreteTexture, slatTexture, diamondPlateTexture } from './textures.js';

// Floor finishes: texture factory + tile size in metres + surface response
const FLOORS = {
  wood:      { tex: () => woodTexture({ base: [112, 84, 60] }),                                  tile: 2.4, roughness: 0.42, metalness: 0 },
  woodGrey:  { tex: () => woodTexture({ base: [118, 104, 92], grey: 0.45, seed: 21 }), tile: 2.4, roughness: 0.45, metalness: 0 },
  tile:      { tex: () => stoneTileTexture(),                             tile: 2.4, roughness: 0.18, metalness: 0 },
  rubber:    { tex: () => rubberTexture({ base: 20 }),                    tile: 1.0, roughness: 0.92, metalness: 0 },
  rubberRed: { tex: () => rubberTexture({ base: 19, fleck: '#b0141c', density: 1600, seed: 4 }), tile: 1.0, roughness: 0.9, metalness: 0 },
  concrete:  { tex: () => concreteTexture(),                              tile: 4.0, roughness: 0.35, metalness: 0 },
};

const texCache = {};
export function floorMaterial(key, w = 1, d = 1) {
  const f = FLOORS[key];
  texCache[key] ??= f.tex();
  const map = texCache[key].clone(); map.needsUpdate = true;
  map.repeat.set(w / f.tile, d / f.tile);
  return new THREE.MeshStandardMaterial({ map, roughness: f.roughness, metalness: f.metalness });
}

// Shared surface materials
export const MAT = {
  wallCharcoal: new THREE.MeshStandardMaterial({ color: BRAND.charcoal, roughness: 0.85 }),
  wallRed:      new THREE.MeshStandardMaterial({ color: '#7d0a12', roughness: 0.55 }),   // deep red so black stays dominant
  wallBlack:    new THREE.MeshStandardMaterial({ color: BRAND.matte, roughness: 0.55 }),
  ceilingBlack: new THREE.MeshStandardMaterial({ color: '#101113', roughness: 0.95, side: THREE.DoubleSide }),
  ceilingLight: new THREE.MeshStandardMaterial({ color: '#d9d6d1', roughness: 0.9, side: THREE.DoubleSide }),
  glass: new THREE.MeshPhysicalMaterial({
    color: '#cfdde0', roughness: 0.04, metalness: 0, transparent: true, opacity: 0.16,
    envMapIntensity: 1.4, side: THREE.DoubleSide, depthWrite: false,
  }),
  frosted: new THREE.MeshStandardMaterial({ color: '#e9ecec', roughness: 0.9, transparent: true, opacity: 0.72, side: THREE.DoubleSide }),
  frame:   new THREE.MeshStandardMaterial({ color: '#161718', roughness: 0.45, metalness: 0.6 }),
  steel:   new THREE.MeshStandardMaterial({ color: BRAND.steel, roughness: 0.32, metalness: 1 }),
  platformEdge: new THREE.MeshStandardMaterial({ color: '#141414', roughness: 0.5, metalness: 0.3 }),
  ledWarm: new THREE.MeshStandardMaterial({ color: '#000', emissive: '#ffb35c', emissiveIntensity: 2.2 }),
  ledRed:  new THREE.MeshStandardMaterial({ color: '#000', emissive: BRAND.red, emissiveIntensity: 2.4 }),
  ledCool: new THREE.MeshStandardMaterial({ color: '#000', emissive: '#f4f6ff', emissiveIntensity: 2.0 }),
  elevator: new THREE.MeshStandardMaterial({ color: '#8d9196', roughness: 0.28, metalness: 1 }),
  laneWhite: new THREE.MeshStandardMaterial({ color: '#e9e9e6', roughness: 0.8 }),
};

const slat = slatTexture(); slat.repeat.set(1, 1);
export const slatMaterial = (w, h) => { const m = slat.clone(); m.needsUpdate = true; m.repeat.set(w / 1.2, h / 1.2);
  return new THREE.MeshStandardMaterial({ map: m, roughness: 0.6 }); };
const plate = diamondPlateTexture();
export const kickMaterial = (w, h) => { const m = plate.clone(); m.needsUpdate = true; m.repeat.set(w / 0.6, h / 0.6);
  return new THREE.MeshStandardMaterial({ map: m, roughness: 0.4, metalness: 0.8 }); };

// Day/night emissive scaling for every LED strip
export function setLedLevel(k) {
  MAT.ledWarm.emissiveIntensity = 2.2 * k;
  MAT.ledRed.emissiveIntensity = 2.4 * k;
  MAT.ledCool.emissiveIntensity = 2.0 * k;
}
