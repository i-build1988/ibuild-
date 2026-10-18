// UI layer: left zone navigation, camera list, bottom mode dock, caption and
// touch walk pad. Pure DOM — it only reports user intent through the handlers
// passed in from main.js. Camera presets and nav entries are generated from
// the real GLB, so they are set at runtime with setCameras().
import { LOGO_SVG } from './logo.js';

const el = (tag, attrs = {}, html = '') => { const e = document.createElement(tag); Object.assign(e, attrs); if (html) e.innerHTML = html; return e; };

export function createUI(h) {
  let CAMS = [], NAV = [];
  // left rail
  const rail = el('aside', { className: 'ff-rail' });
  rail.append(el('div', { className: 'ff-logo' }, LOGO_SVG));
  const nav = el('nav', { className: 'ff-nav', ariaLabel: 'Zones' });
  rail.append(nav);

  // camera list
  const cams = el('details', { className: 'ff-cams' });
  cams.innerHTML = `<summary>Cameras <b id="ff-cam-now">—</b></summary>`;
  const ol = el('ol');
  cams.append(ol);
  if (matchMedia('(min-width: 761px)').matches) cams.open = true;

  // dock
  const dock = el('div', { className: 'ff-dock', role: 'toolbar', ariaLabel: 'View controls' });
  const btn = (key, label, fn) => { const b = el('button', { type: 'button' }, label); b.dataset.k = key; b.onclick = fn; dock.append(b); return b; };
  const B = {
    walk: btn('walk', 'Walk', () => h.onMode('walk')),
    orbit: btn('orbit', 'Orbit', () => h.onMode('orbit')),
    top: btn('top', 'Top view', () => h.onMode('top')),
    day: btn('day', 'Day', () => h.onToggleDay()),
    reset: btn('reset', 'Reset', () => h.onReset()),
  };
  if (document.fullscreenEnabled) btn('fs', 'Full screen', () => (document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen()).catch(() => {}));

  // caption (current camera)
  const caption = el('div', { className: 'ff-caption' });

  // walk hint + touch pad
  const hint = el('div', { className: 'ff-walkhint', hidden: true }, 'Drag to look · W A S D to move · Shift = faster');
  const pad = el('div', { className: 'ff-pad', hidden: true });
  const fwd = el('button', { type: 'button', ariaLabel: 'Walk forward' }, '▲'), back = el('button', { type: 'button', ariaLabel: 'Walk back' }, '▼');
  for (const [b, v] of [[fwd, 1], [back, -1]]) {
    b.addEventListener('pointerdown', (e) => { e.preventDefault(); h.onPush(v); });
    for (const ev of ['pointerup', 'pointerleave', 'pointercancel']) b.addEventListener(ev, () => h.onPush(0));
  }
  pad.append(fwd, back);
  const touch = matchMedia('(pointer: coarse)').matches;

  // model report button (opens the REPORT tab)
  const infoBtn = el('button', { className: 'ff-info-btn', type: 'button' }, 'Model report');
  infoBtn.onclick = () => h.onInfo();

  document.body.append(rail, cams, dock, caption, hint, pad, infoBtn);

  let current = null;
  const api = {
    // cams: [{id, name, …}] · navItems: [{key, label, cam, pending?}]
    setCameras(cs, navItems) {
      CAMS = cs; NAV = navItems; ol.innerHTML = ''; nav.innerHTML = '';
      for (const c of CAMS) {
        const li = el('li'), b = el('button', { type: 'button' }, `<span>${c.id.startsWith('ZONE-') ? 'ZONE' : c.id}</span>${c.name}`);
        b.dataset.cam = c.id; b.onclick = () => h.onCamera(c.id); li.append(b); ol.append(li);
      }
      for (const n of NAV) {
        const b = el('button', { type: 'button' }, n.label); b.dataset.key = n.key;
        if (n.pending) { b.classList.add('pending'); b.title = 'Not labelled yet: click to outline it on the top view'; }
        b.onclick = () => (n.pending ? h.onLabelZone(n.key) : h.onCamera(n.cam)); nav.append(b);
      }
      api.setCamera(current);
    },
    setCamera(cam) {
      current = cam;
      document.getElementById('ff-cam-now').textContent = cam ? (cam.id.startsWith('ZONE-') ? 'ZONE' : cam.id) : '—';
      ol.querySelectorAll('button').forEach(b => b.setAttribute('aria-current', String(b.dataset.cam === cam?.id)));
      nav.querySelectorAll('button').forEach(b => b.setAttribute('aria-current', String(NAV.find(n => n.key === b.dataset.key)?.cam === cam?.id)));
      caption.innerHTML = cam ? `${cam.id.startsWith('ZONE-') ? 'ZONE' : cam.id}<b>${cam.name}</b>` : 'Walk mode<b>Free walk</b>';
    },
    setMode(mode) {
      for (const k of ['walk', 'orbit', 'top']) B[k].setAttribute('aria-pressed', String(mode === k));
      hint.hidden = mode !== 'walk' || touch; pad.hidden = mode !== 'walk' || !touch;
      if (mode === 'walk') { nav.querySelectorAll('button').forEach(b => b.setAttribute('aria-current', 'false')); }
    },
    setDay(day) { B.day.textContent = day ? 'Day' : 'Night'; B.day.setAttribute('aria-pressed', String(!day)); },
  };
  return api;
}
