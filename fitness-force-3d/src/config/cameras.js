// ============================================================================
// REFERENCE SHELL camera presets (Phase 1, assumed shell). Used only when no
// GLB is loaded. With the real GLB, presets are generated from its geometry
// in src/model/presets.js.
// Camera presets. Plan coordinates (x east, y north), z = height in metres.
// Interior cameras sit at eye level (1.60–1.70 m, +0.30 m on the platform).
// fov is vertical degrees — kept at 50–58 to avoid fisheye distortion.
// ============================================================================
export const CAMERAS = [
  { id: 'CAM01', name: 'Main Entrance',          zone: 'entrance',  pos: [10.6, 4.4, 1.65],  target: [9.6, 9.08, 1.55],  fov: 56 },
  { id: 'CAM02', name: 'Reception Hero View',    zone: 'reception', pos: [7.4, -3.4, 1.65],  target: [2.4, 7.2, 1.0],    fov: 52 },
  { id: 'CAM03', name: 'Reception toward Club',  zone: 'reception', pos: [0.9, 7.6, 1.95],   target: [11.0, 7.4, 1.5],   fov: 56 },
  { id: 'CAM04', name: 'Cardio Wide View',       zone: 'cardio',    pos: [12.4, 27.4, 1.70], target: [24.0, 42.0, 1.3],  fov: 58 },
  { id: 'CAM05', name: 'Cardio Equipment View',  zone: 'cardio',    pos: [14.2, 36.9, 1.65], target: [27.0, 36.9, 2.2],  fov: 54 },
  { id: 'CAM06', name: 'HYROX Entrance',         zone: 'hyrox',     pos: [11.6, 27.2, 1.65], target: [6.8, 37.0, 1.4],   fov: 54 },
  { id: 'CAM07', name: 'HYROX Full Runway',      zone: 'hyrox',     pos: [7.39, 29.3, 1.68], target: [7.39, 46.69, 1.5], fov: 54 },
  { id: 'CAM08', name: 'Overall Club View',      zone: 'overview',  pos: [-20, -8, 38],      target: [11, 22, 0],        fov: 40, orbit: true },
  { id: 'CAM09', name: 'Top Plan',               zone: 'plan',      top: true },
];

// Left navigation → camera
export const NAV = [
  { key: 'overview',  label: 'Overview',  cam: 'CAM08' },
  { key: 'reception', label: 'Reception', cam: 'CAM02' },
  { key: 'cardio',    label: 'Cardio',    cam: 'CAM04' },
  { key: 'hyrox',     label: 'HYROX',     cam: 'CAM07' },
  { key: 'entrance',  label: 'Entrance',  cam: 'CAM01' },
];

export const WALK_START = { pos: [4.0, -1.5], heading: 70 };  // degrees, 0 = east, 90 = north
