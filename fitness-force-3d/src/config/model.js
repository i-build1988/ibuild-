// ============================================================================
// REAL ARCHITECTURAL MODEL — configuration
// The GLB is the master source of truth for all geometry. Nothing here scales,
// moves or edits it: the loader only translates the whole model so its plan
// centre sits on the origin and its ground floor sits at height 0.
// ============================================================================
export const MODEL = {
  url: 'models/men_gym_3d.glb',     // default location, relative to index.html
  file: 'men_gym_3d.glb',
  // DRACO-compressed GLBs need the decoder (loaded only if the file uses it)
  dracoPath: 'https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/libs/draco/gltf/',
};

// Zones the client can identify on the TOP VIEW. `brief` = written brief
// dimensions [w, d] in metres, used only for comparison — never applied.
// `match` = mesh-name patterns used to suggest a mesh for that zone.
export const ZONE_DEFS = [
  { key: 'reception',   label: 'RECEPTION',     color: '#e0a458', brief: [7.80, 6.00],  match: /recep|lobby|front.?desk/i },
  { key: 'entrance',    label: 'ENTRANCE',      color: '#7fb2e5', brief: [4.70, 5.15],  match: /entran|entry|vestib/i },
  { key: 'cardio',      label: 'CARDIO',        color: '#e5484d', brief: [12.00, 15.80], match: /cardio|treadmill/i },
  { key: 'hyrox',       label: 'HYROX',         color: '#f5d90a', brief: [17.69, 5.78], match: /hyrox|functional|turf/i },
  { key: 'corridor',    label: 'CORRIDOR',      color: '#a78bfa', brief: [17.00, 4.26], match: /corrid|passage|hallway/i },
  { key: 'pool',        label: 'POOL',          color: '#3fb6c6', match: /pool|swim/i },
  { key: 'jacuzzi',     label: 'JACUZZI',       color: '#5ad1b8', match: /jacuz|whirl|hot.?tub|\bspa\b/i },
  { key: 'freeweights', label: 'FREE WEIGHTS',  color: '#f08c3c', match: /free.?weight|dumbb/i },
  { key: 'strength',    label: 'STRENGTH',      color: '#c084fc', match: /strength|machine|resist/i },
  { key: 'lockers',     label: 'LOCKER ROOMS',  color: '#94a3b8', match: /locker|chang|shower|wc\b|toilet/i },
];

// Difference tolerances for the brief comparison (metres)
export const TOLERANCE = { ok: 0.10, warn: 0.50 };
