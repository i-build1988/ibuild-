// Renderer, scene, image-based reflections and the frame loop.
import * as THREE from 'three';
import { RoomEnvironment } from 'three/addons/environments/RoomEnvironment.js';

export function createStage(container) {
  const renderer = new THREE.WebGLRenderer({ antialias: true, powerPreference: 'high-performance' });
  renderer.setPixelRatio(Math.min(devicePixelRatio, 1.75));
  renderer.outputColorSpace = THREE.SRGBColorSpace;
  renderer.toneMapping = THREE.ACESFilmicToneMapping;
  renderer.shadowMap.enabled = true; renderer.shadowMap.type = THREE.PCFSoftShadowMap;
  renderer.localClippingEnabled = true;
  container.appendChild(renderer.domElement);

  const scene = new THREE.Scene();
  scene.background = new THREE.Color('#2b2d31');
  const pmrem = new THREE.PMREMGenerator(renderer);
  scene.environment = pmrem.fromScene(new RoomEnvironment(renderer), 0.04).texture;   // soft reflections for glass / steel / stone

  const tickers = new Set();
  let last = performance.now();
  function start(renderFn) {
    renderer.setAnimationLoop((now) => {
      const dt = Math.min(0.05, (now - last) / 1000); last = now;
      for (const t of tickers) t(dt, now);
      renderFn();
    });
  }
  return { renderer, scene, tickers, start };
}
