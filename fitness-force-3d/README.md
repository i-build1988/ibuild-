# Fitness Force — Interactive 3D Club (Phase 1 correction: real GLB model)

> **The real architectural model `men_gym_3d.glb` is the master source of truth.**
> The assumed shell built from photos is kept only as the optional **REFERENCE SHELL**
> debug layer (off by default). No equipment, no wall changes, no decoration: Phase 2
> waits until the real model is approved.

Three.js / WebGL project. Opens in any modern browser, Mac included.

## Run it
ES modules need a local web server (opening `index.html` by double-click will not work):

```bash
cd fitness-force-3d
npx serve .            # or: python3 -m http.server 8000
```
Then open the printed address (e.g. http://localhost:3000).

### The real model
Put the GLB at **`models/men_gym_3d.glb`**. If it is missing, the app shows a load screen:
choose the file or drag it onto the window. It is read in the browser and is not uploaded anywhere.

On load the app:
- computes the exact bounding box, overall dimensions, centre, overall height and **floor levels**
  (from the area of horizontal surfaces at each height);
- **translates** the whole model so its plan centre is at the origin and its ground floor is at 0.00.
  No scaling, no rotation. The glTF hierarchy, node names, mesh names and materials are left exactly as exported;
- builds camera presets, walk start points, top-view framing and the light rig from the real geometry.

`dist/preview.html` is the same app bundled into one file (three.js from the CDN).
Rebuild it after edits with `npm install && node build.mjs`.

## Controls
| | |
|---|---|
| Left nav | Overview · zones (once labelled; unlabelled ones open the zone tool) · Top plan |
| Cameras panel | presets generated from the GLB: overall views, elevations, bird's-eye, eye-level interiors per floor level, one view per labelled zone, top plan |
| **Orbit** | drag = rotate · right-drag / two fingers = pan · wheel / pinch = zoom |
| **Walk** | drag = look · W A S D / arrows = move · Shift = faster · ▲▼ pad on touch |
| **Top view** | orthographic floor plan with dimensions (drag = pan, wheel = zoom) |
| **Day / Night** | daylight ↔ cinematic night (3200 K reception, 4000 K training) |
| **Reset** | back to CAM01, day mode |
| **Inspect** (debug) | click any object: mesh name, path, material, size X/Y/Z, world position, bounding box, native GLB coordinates. Focus · Hide · Isolate · Copy JSON |
| **Measure** (debug) | click point A, click point B → distance in metres, plus plan distance and ΔX/ΔY/ΔZ. Snaps to vertices |
| **Label zones** (debug) | on the top view, outline RECEPTION, ENTRANCE, CARDIO, HYROX, CORRIDOR, POOL, JACUZZI, FREE WEIGHTS, STRENGTH, LOCKER ROOMS. Drag for a rectangle, click corners for any shape, or use a mesh's footprint |
| **Layers** (debug) | REAL GLB MODEL / REFERENCE SHELL / zone labels / measurements / edge lines / section cut · level · full GLB hierarchy with show/hide, filter, isolate |
| **Report** | overall dimensions, floor levels, brief vs real GLB comparison. Download as Markdown; zones export/import as JSON |

## Project structure
```
index.html                  page shell + import map (three@0.160.0, three-mesh-bvh@0.7.8)
models/men_gym_3d.glb       ★ the real architectural model (master)
build.mjs                   bundles dist/preview.html
src/
  main.js                   wires everything together
  config/
    model.js                ★ GLB path, zone list, brief sizes used for comparison
    dimensions.js           REFERENCE SHELL only (Phase 1 assumptions)
    cameras.js              REFERENCE SHELL presets (used only without a GLB)
  model/
    loadModel.js            GLTFLoader (+DRACO/Meshopt), centring, BVH acceleration
    analyze.js              bounding box, counts, floor-level detection
    world.js                walk-mode floors + collisions by raycasting the GLB
    presets.js              camera presets from the real geometry
  tools/
    inspect.js · measure.js · zones.js · planGlb.js · overlay.js · geom2d.js
    brand.js                Fitness Force colours, colour temperatures
  core/
    stage.js                renderer, scene, reflections, frame loop
    cameraRig.js            perspective + plan cameras, smooth fly-to
  controls/
    walk.js                 first-person walk with wall collisions
  lighting/
    lighting.js             day / night rig
  materials/
    textures.js             procedural wood, stone tile, rubber, concrete…
    materials.js            PBR material library
  zones/                    REFERENCE SHELL generators (Phase 1)
    geometry.js             plan ↔ 3D helpers, platform outline
    walls.js                solid walls, glazing systems, openings, colliders
    shell.js                slabs, zone floors, platform, ceilings, elevator core
    planAnnotations.js      top-view labels, dimension lines, wall poché
  equipment/
    equipment.js            phase 2 (instancing helper ready, empty for now)
  ui/
    ui.js · panels.js · ui.css · logo.js
```

## Brief vs real GLB
The brief sizes (Reception 7.80 × 6.00, Entrance 4.70 × 5.15, Cardio 12.00 × 15.80,
HYROX 17.69 × 5.78, Corridor 17.00 × 4.26) are **never applied** to the model. Label each
zone on the top view; the REPORT tab measures it (minimum enclosing rectangle and true area,
so rotated rooms measure correctly) and lists the difference with the brief: long side against
long side, short side against short side, and area.

Zone outlines are saved in the browser per model. Use **Export zones (.json)** to share them.

## Phase plan
1. ✅ Structure · dimension map · shell · top view · navigation
1b. ✅ **Correction: real GLB as master model** · inspect · measure · zone labelling · brief comparison  ← **this delivery**
2. Zone build-out, one at a time — **waits for approval of the real architectural model**
3. Equipment (instanced), branding, lighting detail, materials polish
