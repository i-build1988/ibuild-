// FITNESS FORCE — interactive 3D club · entry point
// The REAL GLB (men_gym_3d.glb) is the master architectural model. The
// Phase 1 assumed shell is kept only as the optional REFERENCE SHELL layer.
// Wires stage, model, lighting, cameras, controls, debug tools and UI together.
import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { createStage } from './core/stage.js';
import { createCameraRig } from './core/cameraRig.js';
import { buildShell } from './zones/shell.js';
import { buildPlanAnnotations } from './zones/planAnnotations.js';
import { buildEquipment } from './equipment/equipment.js';
import { buildLighting } from './lighting/lighting.js';
import { createWalk } from './controls/walk.js';
import { createUI } from './ui/ui.js';
import { createPanels, reportMarkdown } from './ui/panels.js';
import { CAMERAS as REF_CAMERAS, NAV as REF_NAV, WALK_START as REF_WALK } from './config/cameras.js';
import { MODEL, ZONE_DEFS } from './config/model.js';
import { V } from './zones/geometry.js';
import { loadGLB } from './model/loadModel.js';
import { createGlbWorld, createSegmentWorld } from './model/world.js';
import { buildPresets, zoneCamera } from './model/presets.js';
import { createInspector, info } from './tools/inspect.js';
import { createMeasure } from './tools/measure.js';
import { createZones } from './tools/zones.js';
import { snapHit, disposeTree } from './tools/overlay.js';
import { buildPlanLayer } from './tools/planGlb.js';

await Promise.race([document.fonts?.ready, new Promise(r => setTimeout(r, 2500))]);   // canvas labels need the fonts

const container = document.getElementById('viewport');
const { renderer, scene, tickers, start } = createStage(container);
const dom = renderer.domElement;
const rig = createCameraRig();

// ---- REFERENCE SHELL (Phase 1 assumptions) — debug layer, OFF by default ----
const shell = buildShell();
const refPlan = buildPlanAnnotations();
const reference = new THREE.Group(); reference.name = 'REFERENCE SHELL'; reference.add(shell.root); reference.visible = false;
scene.add(reference, refPlan, buildEquipment());
const segWorld = createSegmentWorld(shell.colliders, shell.floorAt);

// viewer ground under the GLB (not part of the model, never picked)
const ground = new THREE.Mesh(new THREE.PlaneGeometry(1, 1), new THREE.MeshStandardMaterial({ color: '#1c1d1f', roughness: 1 }));
ground.rotation.x = -Math.PI / 2; ground.receiveShadow = true; ground.visible = false; ground.name = 'viewer ground'; scene.add(ground);

const lights = buildLighting(scene, renderer);

// controls
const orbit = new OrbitControls(rig.persp, dom);
Object.assign(orbit, { enableDamping: true, dampingFactor: 0.08, maxPolarAngle: Math.PI * 0.49, minDistance: 0.4, maxDistance: 160, screenSpacePanning: true });
const planCtl = new OrbitControls(rig.ortho, dom);
Object.assign(planCtl, { enableRotate: false, enableDamping: true, screenSpacePanning: true, minZoom: 0.4, maxZoom: 14, enabled: false });
const PAN_LEFT = () => { planCtl.mouseButtons.LEFT = THREE.MOUSE.PAN; planCtl.touches.ONE = THREE.TOUCH.PAN; };
PAN_LEFT(); centrePlan();
const walk = createWalk(rig.persp, dom, segWorld);

// section planes: cut the model so the plan / overview reads from above
const cutPlane = new THREE.Plane(new THREE.Vector3(0, -1, 0), 0);

const app = {
  mode: 'orbit', day: true, cam: null, tool: null, model: null,
  glbOn: true, refOn: false, zonesOn: true, measureOn: true, edgesOn: false, sectionOn: true,
  level: 0, cutTop: 3.4, snap: true, clipY: null,
  cams: REF_CAMERAS, nav: REF_NAV, baseCams: [], walkStart: null, suggestions: {},
};

// GLB walk physics + picking only see visible GLB meshes
let pickDirty = true, pickList = [], glbList = [];
function refreshPickables() {
  pickList = []; glbList = [];
  if (app.model?.root.visible) app.model.root.traverseVisible(o => o.isMesh && glbList.push(o));
  pickList.push(...glbList);
  if (reference.visible) shell.root.traverseVisible(o => o.isMesh && o.geometry.type !== 'PlaneGeometry' && pickList.push(o));
  pickDirty = false;
}
const pickables = () => (pickDirty && refreshPickables(), pickList);
const glbWorld = createGlbWorld(() => (pickDirty && refreshPickables(), glbList));
const useWorld = () => walk.setWorld(app.model && app.glbOn ? glbWorld : segWorld);

// ---- tools ------------------------------------------------------------------
const inspector = createInspector(scene);
const measure = createMeasure(scene, () => app.tool === 'measure' && showMeasure());
const zones = createZones(scene, onZonesChanged);
let planLayer = null, edges = null;

const ui = createUI({
  onCamera: (id) => goCamera(id),
  onMode: (m) => setMode(m),
  onToggleDay: () => { app.day = !app.day; lights.setMode(app.day ? 'day' : 'night'); ui.setDay(app.day); },
  onReset: () => { if (!app.day) { app.day = true; lights.setMode('day'); ui.setDay(true); } goCamera(app.model ? 'CAM01' : 'CAM08'); },
  onPush: (v) => walk.setPush(v),
  onInfo: () => panels.openTab('report'),
  onLabelZone: (key) => { setTool('zones', true); zones.setActive(key); },
});

const panels = createPanels({
  onTool: (k) => setTool(k),
  onDrawer: (tab) => { if (tab) refreshPanels(); },
  onFile: (f) => loadModel(f),
  onSkipModel: () => { setRef(true); },
  onToggleGlb: (on) => { app.glbOn = on; if (app.model) app.model.root.visible = on; pickDirty = true; useWorld(); syncEdges(); syncLayers(); },
  onToggleRef: (on) => setRef(on),
  onToggleZoneLabels: (on) => { app.zonesOn = on; zones.group.visible = on; },
  onToggleMeasure: (on) => { app.measureOn = on; measure.group.visible = on; },
  onToggleEdges: (on) => { app.edgesOn = on; if (on && !edges) buildEdges(); syncEdges(); },
  onToggleSection: (on) => { app.sectionOn = on; },
  onLevel: (i) => setLevel(i),
  onCut: (v) => { app.cutTop = v; },
  onShowAll: () => { app.model?.scene.traverse(o => (o.visible = true)); visibilityChanged(); },
  onHideSelected: () => { if (inspector.selected) { inspector.selected.visible = false; visibilityChanged(); } },
  onIsolateSelected: () => isolate(inspector.selected),
  onNodeVisible: (o, v) => { o.visible = v; visibilityChanged(false); },
  onNodeSelect: (o) => { if (app.tool !== 'inspect' && app.tool !== 'zones') setTool('inspect'); select(o); },
  onZonePick: (key) => { if (app.tool !== 'zones') setTool('zones', true); zones.setActive(key); },
  onZoneFromSelected: (key) => inspector.selected && zones.setFromObject(key, inspector.selected, levelY()),
  onZoneFromObject: (key, o) => zones.setFromObject(key, o, levelY()),
  onZoneView: (key) => { setTool(null); const c = zoneCamera(zones.zones[key], app.model && app.glbOn ? glbWorld : null); if (c) goCamera(c.id); },
  onZoneDelete: (key) => zones.remove(key),
  onZonesClear: () => { if (confirm('Remove all zone labels for this model?')) zones.clearAll(); },
  onZonesExport: () => download('fitness-force-zones.json', JSON.stringify({ model: app.model?.key ?? null, file: app.model?.fileName ?? null, zones: zones.toJSON() }, null, 2), 'application/json'),
  onZonesImport: async (f) => { try { const j = JSON.parse(await f.text()); zones.load(j.zones ?? j); } catch (e) { alert(`Could not read zones: ${e.message}`); } },
  onReportExport: () => app.model && download('fitness-force-glb-report.md', reportMarkdown(panelState()), 'text/markdown'),
});

const panelState = () => ({
  model: app.model, glbOn: app.glbOn, refOn: app.refOn, zonesOn: app.zonesOn, measureOn: app.measureOn, edgesOn: app.edgesOn,
  sectionOn: app.sectionOn, level: app.level, cutTop: app.cutTop, selected: inspector.selected, zones, suggestions: app.suggestions, measures: measure.list,
});
const refreshPanels = () => panels.refresh(panelState());
const syncLayers = () => panels.tab === 'layers' && refreshPanels();

// ---- modes & cameras --------------------------------------------------------------
const levelY = () => app.model?.levels[app.level]?.y ?? 0;
const activeCam = () => (app.mode === 'top' ? rig.ortho : rig.persp);

function setMode(mode, { keepPose = false } = {}) {
  const prev = app.mode; app.mode = mode;
  orbit.enabled = mode === 'orbit'; planCtl.enabled = mode === 'top';
  if (mode === 'walk') {
    if (prev === 'orbit' && rig.persp.position.y < levelY() + 3) walk.enterFromCamera();
    else if (app.model && app.glbOn && app.walkStart) { const w = app.walkStart; walk.enter(w.pos[0], w.pos[1], w.heading, 0, w.feet); }
    else walk.enter(REF_WALK.pos[0], REF_WALK.pos[1], REF_WALK.heading);
    app.cam = null; ui.setCamera(null);
  } else walk.exit();
  if (mode === 'orbit' && prev === 'walk' && !keepPose) {   // orbit around a point 3 m ahead
    const d = new THREE.Vector3(); rig.persp.getWorldDirection(d); orbit.target.copy(rig.persp.position).addScaledVector(d, 3);
  }
  if (mode === 'top') { app.cam = app.cams.find(c => c.top); ui.setCamera(app.cam); }
  if (mode !== 'top' && app.tool === 'zones') setTool(null);
  ui.setMode(mode);
}

function goCamera(id, instant = false) {
  const c = app.cams.find(k => k.id === id); if (!c) return;
  app.cam = c; ui.setCamera(c);
  if (c.top) {
    setMode('top'); centrePlan(); return;
  }
  setMode('orbit', { keepPose: true });
  rig.flyTo(c.pos, c.target, c.fov, orbit, { instant });
  ui.setCamera(c);
}

// top view: plan camera straight down over the plan centre, zoom 1
function centrePlan() {
  planCtl.target.copy(V(rig.plan.cx, rig.plan.cy, 0));
  rig.ortho.position.copy(V(rig.plan.cx, rig.plan.cy, rig.ortho.position.y)); rig.ortho.zoom = 1; rig.ortho.updateProjectionMatrix();
  planCtl.update(); planCtl.saveState();
}

function refreshCams() {
  if (!app.model) { app.cams = REF_CAMERAS; app.nav = REF_NAV; ui.setCameras(app.cams, app.nav); return; }
  const world = app.glbOn ? glbWorld : null;
  const zc = ZONE_DEFS.filter(d => zones.zones[d.key]).map(d => zoneCamera(zones.zones[d.key], world)).filter(Boolean);
  const top = app.baseCams.filter(c => c.top), rest = app.baseCams.filter(c => !c.top);
  app.cams = [...rest, ...zc, ...top];
  app.nav = [{ key: 'overview', label: 'Overview', cam: 'CAM01' },
    ...ZONE_DEFS.filter(d => d.brief || zones.zones[d.key]).map(d => ({ key: d.key, label: d.label.replace(' ROOMS', ''), cam: `ZONE-${d.key}`, pending: !zones.zones[d.key] })),
    { key: 'plan', label: 'Top plan', cam: top[0]?.id }];
  ui.setCameras(app.cams, app.nav);
}

// ---- layers -------------------------------------------------------------------
function setRef(on) {
  app.refOn = on; reference.visible = on; pickDirty = true;
  if (!app.model) { app.cams = REF_CAMERAS; refreshCams(); if (on) { const b = shellBounds(); rig.setPlanBounds(b.x0, b.x1, b.y0, b.y1, 8); centrePlan(); } }
  syncLayers();
}
function shellBounds() { const b = new THREE.Box3().setFromObject(shell.root.getObjectByName('walls') ?? shell.root); return { x0: b.min.x, x1: b.max.x, y0: -b.max.z, y1: -b.min.z }; }

function visibilityChanged(rerender = true) { pickDirty = true; syncEdges(); if (rerender) syncLayers(); }
function isolate(obj) {
  if (!obj || !app.model) return;
  const keep = new Set(); obj.traverse(o => keep.add(o)); for (let p = obj; p; p = p.parent) keep.add(p);
  app.model.scene.traverse(o => { if (o.isMesh) o.visible = keep.has(o); else if (keep.has(o)) o.visible = true; });
  visibilityChanged();
}
function setLevel(i) {
  app.level = i;
  if (planLayer) { scene.remove(planLayer); disposeTree(planLayer); }
  planLayer = buildPlanLayer(app.model, i, ground.position.y); planLayer.visible = app.mode === 'top'; scene.add(planLayer);
  syncLayers();
}

// edge lines: drawing outlines of every mesh, built on first use (separate group, hierarchy untouched)
function buildEdges() {
  edges = new THREE.Group(); edges.name = 'EDGE LINES'; edges.userData.map = new Map();
  const mat = new THREE.LineBasicMaterial({ color: '#0d0d0f', transparent: true, opacity: 0.85 });
  for (const m of app.model?.meshes ?? []) {
    const l = new THREE.LineSegments(new THREE.EdgesGeometry(m.geometry, 30), mat);
    l.matrixAutoUpdate = false; l.matrix.copy(m.matrixWorld); edges.add(l); edges.userData.map.set(m, l);
  }
  scene.add(edges);
}
function shown(o) { for (; o; o = o.parent) if (!o.visible) return false; return true; }
function syncEdges() {
  if (!edges) return; edges.visible = app.edgesOn;
  if (app.edgesOn) for (const [m, l] of edges.userData.map) l.visible = shown(m);
}

// ---- model loading --------------------------------------------------------------
async function loadModel(src) {
  panels.hideDrop(); panels.progress('Loading model…');
  try {
    const M = await loadGLB(src, (p) => panels.progress(p == null ? 'Loading model…' : `Loading model · ${Math.round(p * 100)} %`));
    panels.progress('Analysing geometry…'); await new Promise(r => setTimeout(r, 0));
    installModel(M);
  } catch (err) {
    console.warn(err);
    const where = typeof src === 'string' ? `<code>${src}</code>` : 'that file';
    panels.showDrop(err.notFound ? `No model found at ${where}.` : `Could not load ${where}: ${String(err.message || err).replace(/</g, '&lt;')}`);
  } finally { panels.progress(null); hideLoading(); }
}

function installModel(M) {
  if (app.model) { select(null); scene.remove(app.model.root); disposeTree(app.model.root); }
  if (edges) { scene.remove(edges); disposeTree(edges); edges = null; }
  app.model = M; app.glbOn = true; app.level = 0; scene.add(M.root); pickDirty = true;
  app.refOn = false; reference.visible = false;

  const b = M.box, R = Math.max(M.size.x, M.size.z, 10);
  ground.visible = true; ground.scale.set(R * 8, R * 8, 1); ground.position.y = b.min.y - 0.02;
  rig.persp.far = Math.max(600, R * 10); rig.persp.updateProjectionMatrix();
  orbit.maxDistance = R * 4;
  rig.setPlanBounds(b.min.x, b.max.x, -b.max.z, -b.min.z, b.max.y); centrePlan();
  lights.fitTo({ x0: b.min.x, x1: b.max.x, y0: -b.max.z, y1: -b.min.z, height: M.height, levels: M.levels });
  useWorld();

  const { cams, walkStart } = buildPresets(M, glbWorld);
  app.baseCams = cams; app.walkStart = walkStart;
  setLevel(0);

  // zone suggestions from mesh / node names
  app.suggestions = {};
  for (const d of ZONE_DEFS) { const hits = []; M.scene.traverse(o => o !== M.scene && o.name && d.match.test(o.name) && hits.push(o)); app.suggestions[d.key] = hits; }

  zones.load(store.get(`ff-zones:${M.key}`) ?? []);
  refreshCams();
  goCamera('CAM01', true);
  refreshPanels();
  console.info('[GLB]', M.fileName, { size: M.size, levels: M.levels, meshes: M.meshes.length, bvh: M.bvh });
}

function onZonesChanged() {
  if (app.model) store.set(`ff-zones:${app.model.key}`, zones.toJSON());
  refreshCams(); if (panels.tab === 'zones' || panels.tab === 'report') refreshPanels();
}

// ---- tools: pointer handling ------------------------------------------------------
const ray = new THREE.Raycaster(), ndc = new THREE.Vector2();
function setRay(e) {
  const r = dom.getBoundingClientRect(); ndc.set(((e.clientX - r.left) / r.width) * 2 - 1, -((e.clientY - r.top) / r.height) * 2 + 1);
  ray.setFromCamera(ndc, activeCam()); ray.far = Infinity; ray.firstHitOnly = false;
}
function pickAt(e) {
  setRay(e);
  return ray.intersectObjects(pickables(), false).find(h => app.clipY == null || h.point.y <= app.clipY + 1e-4) || null;
}
// metres per screen pixel at a hit (for snapping tolerances)
function mPerPx(hit) {
  const h = dom.clientHeight || 1;
  if (app.mode === 'top') return (rig.ortho.top - rig.ortho.bottom) / rig.ortho.zoom / h;
  return (hit ? hit.distance : 10) * 2 * Math.tan(THREE.MathUtils.degToRad(rig.persp.fov / 2)) / h;
}
function surfacePoint(hit) { return app.snap ? snapHit(hit, mPerPx(hit) * 12).point : hit.point.clone(); }

// plan point on the active level for zone drawing, snapped to model vertices and zone corners
function planPoint(e) {
  setRay(e);
  const p = ray.ray.intersectPlane(new THREE.Plane(new THREE.Vector3(0, 1, 0), -levelY()), new THREE.Vector3()); if (!p) return null;
  let pt = [p.x, -p.z]; const tol = mPerPx(null) * 12;
  const hit = pickAt(e);
  if (hit) { const s = snapHit(hit, Infinity).point; if (Math.hypot(s.x - p.x, s.z - p.z) <= tol) pt = [s.x, -s.z]; }
  for (const c of zones.corners()) if (Math.hypot(c[0] - pt[0], c[1] - pt[1]) <= tol) { pt = [c[0], c[1]]; break; }
  return pt;
}

let down = null, hoverEvt = null;
dom.addEventListener('pointerdown', (e) => {
  if (e.button !== 0) return;
  down = { x: e.clientX, y: e.clientY, t: performance.now(), zone: false, dragged: false };
  if (app.tool === 'zones' && app.mode === 'top' && zones.active) { const p = planPoint(e); if (p) { zones.down(p, levelY()); down.zone = true; } }
});
dom.addEventListener('pointermove', (e) => {
  if (down?.zone) {
    if (!down.dragged && Math.hypot(e.clientX - down.x, e.clientY - down.y) > 6) { down.dragged = true; zones.beginDrag(); }
    const p = planPoint(e); if (p) zones.move(p); return;
  }
  if (!down) hoverEvt = e;
});
dom.addEventListener('pointerup', (e) => {
  if (!down) return; const d = down; down = null;
  const moved = Math.hypot(e.clientX - d.x, e.clientY - d.y) > 5;
  if (d.zone) { const p = planPoint(e); if (p) zones.up(p, moved, mPerPx(null) * 12); return; }
  if (moved || performance.now() - d.t > 700) return;
  if (app.tool === 'inspect') { const hit = pickAt(e); select(hit?.object ?? null); }
  else if (app.tool === 'measure') { const hit = pickAt(e); if (hit) measure.click(surfacePoint(hit)); }
});
dom.addEventListener('dblclick', () => app.tool === 'zones' && zones.finish());
addEventListener('keydown', (e) => {
  if (e.target.closest?.('input, select, textarea')) return;
  if (e.key === 'Escape') { if (app.tool === 'zones') zones.cancel(); else if (app.tool === 'measure') measure.cancel(); else if (app.tool === 'inspect') select(null); }
  if (app.tool === 'zones' && zones.drawing) {
    if (e.key === 'Enter') zones.finish();
    if (e.key === 'Backspace') { e.preventDefault(); zones.undoPoint(); }
  }
});

function select(obj) {
  inspector.select(obj);
  if (!obj) { if (app.tool === 'inspect') panels.showHint('Inspect', inspectHint); syncLayers(); return; }
  const i = info(obj, app.model?.offset, inspector.originalMaterial());
  panels.showInspect(i, {
    clear: () => select(null),
    focus: () => focusOn(obj),
    hide: () => { obj.visible = false; select(null); visibilityChanged(); },
    isolate: () => isolate(obj),
    copy: () => navigator.clipboard?.writeText(JSON.stringify(i, (k, v) => (v?.isVector3 ? v.toArray().map(n => +n.toFixed(4)) : v?.isBox3 ? { min: v.min, max: v.max } : v), 2)).catch(() => {}),
  });
  syncLayers();
  if (panels.tab === 'zones') refreshPanels();
}
function focusOn(obj) {
  const b = new THREE.Box3().setFromObject(obj, true), c = b.getCenter(new THREE.Vector3()), r = Math.max(b.getSize(new THREE.Vector3()).length(), 1.5);
  const p = c.clone().add(new THREE.Vector3(0.7, 0.6, 0.9).normalize().multiplyScalar(r * 1.3));
  if (app.mode !== 'orbit') setMode('orbit', { keepPose: true });
  app.cam = null; ui.setCamera({ id: 'FOCUS', name: obj.name || 'Selection' });
  rig.flyTo([p.x, -p.z, p.y], [c.x, -c.z, c.y], 50, orbit);
}

const inspectHint = 'Click any object to read its <b>name, size, position, material</b> and <b>bounding box</b>. Esc clears the selection.';
function showMeasure() {
  panels.showMeasure(measure, { snap: app.snap, onSnap: (v) => (app.snap = v), remove: (i) => measure.remove(i), clear: () => measure.clear() });
}

function setTool(k, force = false) {
  if (app.tool === k && !force) k = null;
  if (app.tool === 'measure' && k !== 'measure') measure.cancel();
  if (app.tool === 'zones' && k !== 'zones') { zones.cancel(); zones.setActive(null); PAN_LEFT(); }
  if (app.tool === 'inspect' && k !== 'inspect' && k !== 'zones') inspector.select(null);
  app.tool = k; panels.setTool(k);
  dom.style.cursor = k ? 'crosshair' : '';
  if (k === 'inspect') { if (inspector.selected) select(inspector.selected); else panels.showHint('Inspect', inspectHint); }
  if (k === 'measure') { measure.group.visible = app.measureOn = true; showMeasure(); }
  if (k === 'zones') {
    if (app.mode !== 'top') goCamera(app.cams.find(c => c.top)?.id);
    planCtl.mouseButtons.LEFT = -1; planCtl.touches.ONE = -1;        // left button / one finger draw; right / two fingers pan
    zones.group.visible = app.zonesOn = true;
    panels.openTab('zones');
    panels.showHint('Label zones', 'Choose a zone in the panel, then <b>drag</b> a rectangle or <b>click corners</b> on the plan. Right-drag or two fingers to pan, wheel or pinch to zoom.');
  }
}

// ---- per-frame --------------------------------------------------------------------
tickers.add((dt, now) => {
  rig.update(now);
  if (app.mode === 'orbit') orbit.update();
  if (app.mode === 'top') planCtl.update();
  walk.update(dt);
  const ly = levelY(), top = app.model ? app.model.box.max.y : 4.5;
  const eye = app.mode !== 'top' && rig.persp.position.y < ly + 3.0;
  shell.ceilings.visible = eye;
  app.clipY = !app.sectionOn ? null : app.mode === 'top' ? ly + 1.25 : (app.mode === 'orbit' && rig.persp.position.y > top + 0.5 ? ly + app.cutTop : null);
  if (app.clipY != null) { cutPlane.constant = app.clipY; renderer.clippingPlanes = [cutPlane]; } else renderer.clippingPlanes = [];
  refPlan.visible = app.mode === 'top' && app.refOn;
  zones.group.visible = app.zonesOn && (app.mode === 'top' || (app.mode === 'orbit' && !eye));   // floor labels read from above only
  if (planLayer) planLayer.visible = app.mode === 'top' && app.glbOn;
  if (hoverEvt && app.tool === 'measure' && measure.pending) { const hit = pickAt(hoverEvt); measure.hover(hit ? surfacePoint(hit) : null); }
  hoverEvt = null;
});

function resize() { const w = innerWidth, h = innerHeight; renderer.setSize(w, h, false); rig.resize(w, h); }
addEventListener('resize', resize); resize();

lights.setMode('day'); ui.setDay(true);
refreshCams();
goCamera('CAM08', true);
start(() => renderer.render(scene, app.mode === 'top' ? rig.ortho : rig.persp));
function hideLoading() { const l = document.getElementById('ff-loading'); if (!l || l.hidden) return; l.style.opacity = 0; setTimeout(() => (l.hidden = true), 500); }

// small helpers
function download(name, text, type) {
  const a = document.createElement('a'); a.href = URL.createObjectURL(new Blob([text], { type })); a.download = name;
  document.body.append(a); a.click(); setTimeout(() => { URL.revokeObjectURL(a.href); a.remove(); }, 1000);
}
const store = {
  get(k) { try { return JSON.parse(localStorage.getItem(k)); } catch { return null; } },
  set(k, v) { try { localStorage.setItem(k, JSON.stringify(v)); } catch { /* storage unavailable: zones still work for this session */ } },
};

// the real model: default location, then file picker / drag-and-drop
loadModel(window.FF_MODEL_URL || MODEL.url);

// expose for quick checks in the console
window.FF = { goCamera, setMode, setTool, scene, rig, app, orbit, planCtl, zones, measure, loadModel, get model() { return app.model; } };
