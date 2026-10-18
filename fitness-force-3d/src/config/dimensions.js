// ============================================================================
// FITNESS FORCE — CENTRAL DIMENSIONS FILE  (single source of truth)
// Master unit: metres.  Plan coordinates: x = east, y = north, z = up.
// Origin (0,0) = front-left corner of the Reception lounge platform.
//
// Every room size, wall, opening and height lives here. The shell, floor
// finishes, plan annotations, walk-mode collisions and camera presets are
// all generated from these numbers — change a value, reload, done.
//
// Tags:  [BRIEF] value from the written brief
//        [PHOTO] value read from the dimension lines on the site photos
//        [A#]    assumption — see ASSUMPTIONS at the bottom
// ============================================================================

// ---- Heights ----------------------------------------------------------------
export const HEIGHTS = {
  eye: 1.65,                 // walk mode / interior cameras (1.60–1.70 m)
  frontHall: 7.00,           // [A3] double-height reception hall (photos 1–3)
  receptionBulkhead: 3.60,   // [A3] back wall / bulkhead behind reception
  platformRise: 0.30,        // [A2] raised wood platform with LED edge (photos 1–3, 7)
  corridor: 3.20,            // [A3] corridor ceiling (photo 5)
  gymHall: 7.50,             // [A3] double-height training hall, exposed black ceiling (photo 4)
  hyroxFrame: 3.60,          // [A3] ceiling frame over the HYROX runway (photo 8)
  balustrade: 1.10,          // handrail height on HYROX glass sides (photo 8)
  railing: 1.00,             // platform railing beside the entrance (photo 7)
  doorHead: 2.60,
};

// ---- Zones --------------------------------------------------------------
// rect zones: x,y = south-west corner, w = east-west, d = north-south.
export const ZONES = {
  reception: {
    label: 'MAIN RECEPTION', short: 'RECEPTION', floor: 'wood',
    briefSize: [7.80, 6.00], briefArea: 46.80,                  // [BRIEF]
    // Raised lounge platform, shape from photo 3 [PHOTO]:
    // straight back edge 8.00, left side 4.60, right side 9.08, curved front.
    platform: { width: 8.00, left: 4.60, right: 9.08, cornerR: 1.60, frontCtrl: [2.6, 0.5] },
    x: 0, y: 0,
  },
  entrance: {
    label: 'INTERNAL CLUB ENTRANCE', short: 'ENTRANCE', floor: 'tile',
    x: 8.00, y: 3.93, w: 4.70, d: 5.15,                          // [BRIEF] + [PHOTO 7]
  },
  corridor: {
    label: 'CLUB CORRIDOR', short: 'CORRIDOR', floor: 'woodGrey',
    x: 8.00, y: 9.08, w: 4.26, d: 17.00,                         // [PHOTO 5]
  },
  cardio: {
    label: 'CARDIO AREA', short: 'CARDIO', floor: 'rubber',
    x: 13.00, y: 29.00, w: 12.00, d: 15.80, briefArea: 189.60,   // [BRIEF] + [PHOTO 4]
  },
  hyrox: {
    label: 'HYROX / FUNCTIONAL', short: 'HYROX', floor: 'rubberRed',
    x: 4.50, y: 29.00, w: 5.78, d: 17.69, briefArea: 102.25,     // [BRIEF] + [PHOTO 8]
  },
};

// ---- Building envelopes (floor slabs + ceiling heights) --------------------
export const HALLS = {
  front: { x: -6.0, y: -3.0, w: 22.0, d: 12.08, height: 'frontHall', floor: 'concrete' },
  gym:   { x: -2.0, y: 26.08, w: 29.0, d: 21.92, height: 'gymHall', floor: 'concrete' },
};

// ---- Elevator core (beside reception) [A4] ---------------------------------
export const ELEVATORS = {
  core: { x: -8.6, y: 3.0, w: 2.6, d: 5.6 },
  doors: [ { y: 4.40, w: 1.10 }, { y: 7.00, w: 1.10 } ],   // on the core's east face (x = -6.0)
  doorHeight: 2.30,
};

// ---- Wall types ---------------------------------------------------------
export const WALL_TYPES = {
  solid:      { t: 0.20, mat: 'wallCharcoal' },
  red:        { t: 0.20, mat: 'wallRed' },
  black:      { t: 0.25, mat: 'wallBlack' },
  ext:        { t: 0.35, mat: 'wallCharcoal' },
  glass:      { t: 0.05, mat: 'glass', mullion: 1.50, frosted: [1.40, 2.10] },  // corridor partitions (photo 5)
  storefront: { t: 0.06, mat: 'glass', mullion: 1.10 },                         // sliding entrance (photo 7)
  windows:    { t: 0.10, mat: 'glass', mullion: 1.25, pier: 3.00 },             // tall gym windows (photo 4)
  balustrade: { t: 0.05, mat: 'glass', mullion: 1.80, frosted: [1.60, 2.30], kick: 0.60 }, // HYROX sides (photo 8)
};

// ---- Walls ----------------------------------------------------------------
// a → b in plan. h = height (number, or a HEIGHTS key). openings measured
// from 'a' to the opening centre. kind: portal | slidingDoor | glassDoor | elevator
export const WALLS = [
  // Front hall — reception back wall + elevator lobby
  { id: 'REC-back',  type: 'black',   a: [-6.0, 9.08], b: [8.0, 9.08], h: 'receptionBulkhead' },
  { id: 'LOBBY-W',   type: 'solid', a: [-6.0, -3.0], b: [-6.0, 9.08], h: 'frontHall',
    openings: [ { at: 7.40, w: 1.10, kind: 'elevator' }, { at: 10.00, w: 1.10, kind: 'elevator' } ] },

  // Club front: glazed sliding entrance + marble / green-wall pier (photo 7)
  { id: 'ENT-glass', type: 'storefront', a: [8.0, 9.08], b: [11.3, 9.08], h: 'doorHead',
    openings: [ { at: 1.65, w: 2.40, kind: 'slidingDoor' } ] },
  { id: 'ENT-pier',  type: 'black', a: [11.3, 9.08], b: [12.7, 9.08], h: 'receptionBulkhead' },

  // Corridor (photo 5): red walls at the start, then glass partitions both sides
  { id: 'COR-W-red', type: 'red',   a: [8.0, 9.08],   b: [8.0, 11.08],  h: 'corridor' },
  { id: 'COR-W',     type: 'glass', a: [8.0, 11.08],  b: [8.0, 26.08],  h: 'corridor' },
  { id: 'COR-E-red', type: 'red',   a: [12.26, 9.08], b: [12.26, 11.08], h: 'corridor' },
  { id: 'COR-E',     type: 'glass', a: [12.26, 11.08], b: [12.26, 26.08], h: 'corridor' },

  // Gym hall perimeter
  { id: 'GYM-S-w', type: 'ext', a: [-2.0, 26.08], b: [8.0, 26.08],  h: 'gymHall' },
  { id: 'GYM-S-door', type: 'storefront', a: [8.0, 26.08], b: [12.26, 26.08], h: 'doorHead',
    openings: [ { at: 2.13, w: 2.00, kind: 'glassDoor' } ] },
  { id: 'GYM-S-e', type: 'ext', a: [12.26, 26.08], b: [27.0, 26.08], h: 'gymHall' },
  { id: 'GYM-E-win', type: 'windows', a: [27.0, 26.08], b: [27.0, 48.0], h: 'gymHall' },  // [A1] treadmills face these
  { id: 'GYM-N', type: 'ext', a: [27.0, 48.0], b: [-2.0, 48.0], h: 'gymHall' },
  { id: 'GYM-W', type: 'ext', a: [-2.0, 48.0], b: [-2.0, 26.08], h: 'gymHall' },

  // HYROX runway (photo 8): glass balustrade sides, black logo end wall
  { id: 'HYX-W', type: 'balustrade', a: [4.5, 29.0],   b: [4.5, 46.69],   h: 'hyroxFrame' },
  { id: 'HYX-E', type: 'balustrade', a: [10.28, 29.0], b: [10.28, 46.69], h: 'hyroxFrame' },
  { id: 'HYX-N', type: 'black',      a: [4.5, 46.69],  b: [10.28, 46.69], h: 'hyroxFrame' },
];

// ---- Areas next to the club that the brief does not describe --------------
// Drawn as dashed outlines only. No walls or finishes are invented for them.
export const UNSPECIFIED = [
  { label: 'ROOMS BEHIND GLASS', x: 3.0,  y: 11.08, w: 5.0, d: 15.0 },
  { label: 'ROOMS BEHIND GLASS', x: 12.26, y: 11.08, w: 5.0, d: 15.0 },
  { label: 'WOOD AREA (PHOTO 7)', x: 12.7, y: 3.93, w: 3.3, d: 5.15 },
  { label: 'OTHER TRAINING', x: -2.0, y: 26.08, w: 6.5, d: 21.92 },
];

// ---- Columns ----------------------------------------------------------------
// None yet: the structural grid is not visible on a plan. Add when known.
// Example: { x: 18.5, y: 37.0, w: 0.6, d: 0.6 }
export const COLUMNS = [];

// ---- Assumptions (shown in the app and in DIMENSIONS.md) -------------------
export const ASSUMPTIONS = [
  ['A1', 'Zone positions', 'The photos show each zone but not how they connect. Assumed route: Elevators → Reception platform → Entrance → glass sliding doors → 17 m corridor → training hall. In the hall, HYROX runs north–south beside Cardio, with a 2.72 m aisle between them. Each zone is placed with one x / y pair in this file, so moving it is a one-line change.'],
  ['A2', 'Reception size', 'The brief gives 7.80 × 6.00 m (46.80 m²). Photo 3 measures a curved platform 8.00 m wide, 4.60 m deep on the left and 9.08 m on the right. The platform follows the photo and is raised 0.30 m.'],
  ['A3', 'Heights', 'Front hall 7.00 m, training hall 7.50 m (double height, exposed black ceiling), reception back wall 3.60 m, corridor 3.20 m, HYROX ceiling frame 3.60 m. These are read from proportions in the photos, not measured.'],
  ['A4', 'Elevators', 'Two elevators on a core west of the reception lounge. The photos do not show where the real elevators are.'],
  ['A5', 'Windows', 'One wall of tall windows on the training hall\'s east side, 2.00 m from the Cardio zone. Treadmills will face it.'],
  ['A6', 'Columns', 'No columns drawn. The structural grid is needed from a plan.'],
  ['A7', 'Logo', 'Drawn from the Fitness Force mark in photos 2, 6 and 8. Replace it with the vector logo file when available.'],
];
