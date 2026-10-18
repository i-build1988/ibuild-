// Debug UI for the real GLB: tool strip (Inspect · Measure · Zones),
// right-hand drawer with LAYERS / ZONES / REPORT tabs, the inspector card,
// the measurement card and the GLB drop / load screen.
// Pure DOM: user intent goes out through the handlers object `h`.
import { ZONE_DEFS, TOLERANCE, MODEL } from '../config/model.js';

const el = (tag, attrs = {}, html = '') => { const e = document.createElement(tag); Object.assign(e, attrs); if (html) e.innerHTML = html; return e; };
const esc = (s) => String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
const f2 = (n) => (n == null || !isFinite(n) ? '—' : n.toFixed(2));
const f3 = (n) => (n == null || !isFinite(n) ? '—' : n.toFixed(3));
const v3 = (v) => `${f3(v.x)}, ${f3(v.y)}, ${f3(v.z)}`;
const sgn = (n) => (n > 0 ? '+' : n < 0 ? '−' : '±') + Math.abs(n).toFixed(2);

export function createPanels(h) {
  // ---- tool strip ----------------------------------------------------------
  const strip = el('div', { className: 'ff-tools', role: 'toolbar', ariaLabel: 'Debug tools' });
  strip.append(el('span', { className: 'ff-tools-tag' }, 'Debug'));
  const T = {};
  for (const [k, label] of [['inspect', 'Inspect'], ['measure', 'Measure'], ['zones', 'Label zones']]) {
    const b = el('button', { type: 'button' }, label); b.dataset.k = k; b.setAttribute('aria-pressed', 'false');
    b.onclick = () => h.onTool(k); strip.append(b); T[k] = b;
  }
  const layersBtn = el('button', { type: 'button' }, 'Layers'); layersBtn.onclick = () => openTab('layers'); strip.append(layersBtn);
  const reportBtn = el('button', { type: 'button' }, 'Report'); reportBtn.onclick = () => openTab('report'); strip.append(reportBtn);

  // ---- drawer --------------------------------------------------------------
  const drawer = el('aside', { className: 'ff-drawer', hidden: true, ariaLabel: 'Model panels' });
  const tabs = el('div', { className: 'ff-tabs', role: 'tablist' });
  const bodies = {};
  for (const [k, label] of [['layers', 'Layers'], ['zones', 'Zones'], ['report', 'Report']]) {
    const b = el('button', { type: 'button', role: 'tab' }, label); b.dataset.tab = k; b.onclick = () => openTab(k); tabs.append(b);
    bodies[k] = el('section', { className: 'ff-tabbody', hidden: true }); bodies[k].dataset.tab = k;
  }
  const close = el('button', { className: 'ff-close', type: 'button' }, 'Close ✕'); close.onclick = () => closeDrawer();
  drawer.append(close, tabs, ...Object.values(bodies));
  let tab = null;
  function openTab(k) {
    tab = k; drawer.hidden = false;
    tabs.querySelectorAll('button').forEach(b => b.setAttribute('aria-selected', String(b.dataset.tab === k)));
    for (const [n, b] of Object.entries(bodies)) b.hidden = n !== k;
    h.onDrawer?.(k);
  }
  function closeDrawer() { drawer.hidden = true; tab = null; h.onDrawer?.(null); }

  // ---- inspector + measure cards -------------------------------------------
  const card = el('aside', { className: 'ff-card', hidden: true, ariaLabel: 'Tool output' });

  // ---- drop / load screen ----------------------------------------------------
  const drop = el('div', { className: 'ff-drop', hidden: true });
  drop.innerHTML = `<div class="ff-drop-box">
    <h2>Load the real architectural model</h2>
    <p class="ff-drop-msg"></p>
    <p>Put <code>${MODEL.file}</code> in <code>models/</code> next to <code>index.html</code>, or load it here. It stays in your browser and is not uploaded anywhere.</p>
    <label class="ff-btn ff-btn-red">Choose GLB file<input type="file" accept=".glb,.gltf,model/gltf-binary" hidden></label>
    <p class="ff-drop-hint">or drag the file anywhere onto this window</p>
    <button type="button" class="ff-btn ff-drop-skip">Continue with REFERENCE SHELL only</button>
  </div>`;
  drop.querySelector('input').onchange = (e) => e.target.files[0] && h.onFile(e.target.files[0]);
  drop.querySelector('.ff-drop-skip').onclick = () => { drop.hidden = true; h.onSkipModel(); };
  addEventListener('dragover', (e) => { e.preventDefault(); document.body.classList.add('ff-dragging'); });
  addEventListener('dragleave', (e) => { if (!e.relatedTarget) document.body.classList.remove('ff-dragging'); });
  addEventListener('drop', (e) => {
    e.preventDefault(); document.body.classList.remove('ff-dragging');
    const f = [...(e.dataTransfer?.files || [])].find(x => /\.(glb|gltf)$/i.test(x.name)); if (f) h.onFile(f);
  });

  const progress = el('div', { className: 'ff-progress', hidden: true });

  document.body.append(strip, drawer, card, drop, progress);

  // ===========================================================================
  // LAYERS
  function renderLayers(state) {
    const b = bodies.layers; b.innerHTML = '';
    b.append(el('h2', {}, 'Layers'));
    const top = el('div', { className: 'ff-layer-main' });
    const chk = (label, on, fn, note = '') => {
      const l = el('label', { className: 'ff-check' }); const i = el('input', { type: 'checkbox', checked: on }); i.onchange = () => fn(i.checked);
      l.append(i, el('span', {}, `${label}${note ? `<em>${note}</em>` : ''}`)); return l;
    };
    top.append(
      chk('REAL GLB MODEL', state.glbOn, h.onToggleGlb, state.model ? esc(state.model.fileName) : 'not loaded'),
      chk('REFERENCE SHELL', state.refOn, h.onToggleRef, 'Phase 1 assumptions · debug only'),
      chk('Zone labels', state.zonesOn, h.onToggleZoneLabels),
      chk('Measurements', state.measureOn, h.onToggleMeasure),
      chk('Edge lines', state.edgesOn, h.onToggleEdges, 'drawing outlines'),
      chk('Section cut', state.sectionOn, h.onToggleSection, 'removes ceilings above the cut in top / overview'),
    );
    b.append(top);
    if (state.model?.levels.length > 1) {
      const row = el('div', { className: 'ff-row' }, '<span class="ff-k">Level</span>');
      const sel = el('select');
      state.model.levels.forEach((l, i) => sel.append(el('option', { value: i, selected: i === state.level }, `Level ${i} · ${l.y >= 0 ? '+' : ''}${l.y.toFixed(2)} m`)));
      sel.onchange = () => h.onLevel(+sel.value); row.append(sel); b.append(row);
    }
    const row2 = el('div', { className: 'ff-row' }, `<span class="ff-k">Cut height</span>`);
    const rng = el('input', { type: 'range', min: 0.6, max: Math.max(4, (state.model?.height ?? 8)), step: 0.05, value: state.cutTop });
    const out = el('output', {}, `${(+state.cutTop).toFixed(2)} m`);
    rng.oninput = () => { out.textContent = `${(+rng.value).toFixed(2)} m`; h.onCut(+rng.value); };
    row2.append(rng, out); b.append(row2);
    b.append(el('p', { className: 'ff-note' }, 'Top view cuts at level + 1.25 m. Overview cuts at the height above (when the camera is high).'));

    if (!state.model) { b.append(el('p', { className: 'ff-note' }, 'Load the GLB to list its meshes.')); return; }
    b.append(el('h3', {}, `GLB hierarchy · ${state.model.meshes.length} meshes`));
    const tools = el('div', { className: 'ff-row ff-wrap' });
    const search = el('input', { type: 'search', placeholder: 'Filter by name…', className: 'ff-search' });
    const btn = (t, fn) => { const x = el('button', { type: 'button', className: 'ff-btn ff-sm' }, t); x.onclick = fn; return x; };
    tools.append(search, btn('Show all', h.onShowAll), btn('Hide selected', h.onHideSelected), btn('Isolate selected', h.onIsolateSelected));
    b.append(tools);
    const tree = el('div', { className: 'ff-tree' }); b.append(tree);
    const nodeRow = (o, depth) => {
      const row = el('div', { className: 'ff-node' + (o === state.selected ? ' sel' : '') }); row.style.paddingLeft = `${depth * 14}px`;
      const i = el('input', { type: 'checkbox', checked: o.visible, ariaLabel: `Show ${o.name || o.type}` });
      i.onchange = () => h.onNodeVisible(o, i.checked);
      const kids = o.children.filter(c => c.isMesh || c.children.length);
      const name = el('button', { type: 'button', className: 'ff-node-name' }, `${esc(o.name || `(${o.type})`)}${o.isMesh ? '' : ` <em>${countMeshes(o)}</em>`}`);
      name.onclick = () => h.onNodeSelect(o);
      if (kids.length) {
        const tg = el('button', { type: 'button', className: 'ff-twist', ariaExpanded: 'false', ariaLabel: 'Expand' }, '▸');
        let open = false, box = null;
        tg.onclick = () => {
          open = !open; tg.textContent = open ? '▾' : '▸'; tg.ariaExpanded = String(open);
          if (open) { box = el('div'); kids.forEach(c => box.append(nodeRow(c, depth + 1))); row.after(box); } else box?.remove();
        };
        row.append(tg);
      } else row.append(el('span', { className: 'ff-twist' }));
      row.append(i, name); return row;
    };
    const roots = state.model.scene.children;
    const renderTree = () => {
      tree.innerHTML = ''; const q = search.value.trim().toLowerCase();
      if (!q) { roots.forEach(c => tree.append(nodeRow(c, 0))); return; }
      let n = 0; state.model.scene.traverse(o => { if (o !== state.model.scene && (o.name || '').toLowerCase().includes(q) && n++ < 400) tree.append(nodeRow(o, 0)); });
      if (!n) tree.append(el('p', { className: 'ff-note' }, 'No match.'));
    };
    search.oninput = renderTree; renderTree();
  }
  const countMeshes = (o) => { let n = 0; o.traverse(c => c.isMesh && n++); return n; };

  // ===========================================================================
  // ZONES
  function renderZones(state) {
    const b = bodies.zones; b.innerHTML = '';
    b.append(el('h2', {}, 'Label zones'));
    b.append(el('p', { className: 'ff-note' }, 'Pick a zone, then outline it on the top view. <b>Drag</b> for a rectangle, or <b>click corners</b> for any shape (click the first corner or press Enter to close; Backspace removes a point; Esc cancels). Corners snap to model vertices. Or select a floor mesh with Inspect and press <b>Use selected mesh</b>.'));
    const list = el('div', { className: 'ff-zonelist' });
    for (const d of ZONE_DEFS) {
      const m = state.zones.measure(d.key), z = state.zones.zones[d.key], on = state.zones.active === d.key;
      const row = el('div', { className: 'ff-zone' + (on ? ' on' : '') });
      row.style.setProperty('--zc', d.color);
      const pick = el('button', { type: 'button', className: 'ff-zone-pick', ariaPressed: String(on) },
        `<i></i><b>${d.label}</b><span>${m ? `${f2(m.len)} × ${f2(m.wid)} m · ${f2(m.area)} m²` : 'not set'}</span>`);
      pick.onclick = () => h.onZonePick(on ? null : d.key);
      row.append(pick);
      if (on) {
        const act = el('div', { className: 'ff-row ff-wrap' });
        const bb = (t, fn, dis = false) => { const x = el('button', { type: 'button', className: 'ff-btn ff-sm', disabled: dis }, t); x.onclick = fn; return x; };
        act.append(bb('Use selected mesh', () => h.onZoneFromSelected(d.key), !state.selected));
        if (z) act.append(bb('View', () => h.onZoneView(d.key)), bb('Delete', () => h.onZoneDelete(d.key)));
        const sug = state.suggestions?.[d.key];
        if (sug?.length) act.append(el('span', { className: 'ff-note' }, 'Mesh names that match: '), ...sug.slice(0, 3).map(o => bb(esc(o.name), () => h.onZoneFromObject(d.key, o))));
        row.append(act);
        if (z) row.append(el('p', { className: 'ff-note' }, `Source: ${esc(z.source)} · level ${z.level >= 0 ? '+' : ''}${z.level.toFixed(2)} m · ${z.pts.length} corners`));
      }
      list.append(row);
    }
    b.append(list);
    const io = el('div', { className: 'ff-row ff-wrap' });
    const bb = (t, fn) => { const x = el('button', { type: 'button', className: 'ff-btn ff-sm' }, t); x.onclick = fn; return x; };
    const file = el('input', { type: 'file', accept: '.json,application/json', hidden: true }); file.onchange = () => file.files[0] && h.onZonesImport(file.files[0]);
    io.append(bb('Export zones (.json)', h.onZonesExport), bb('Import zones', () => file.click()), bb('Clear all', h.onZonesClear), file);
    b.append(el('h3', {}, 'Save / share'), io);
  }

  // ===========================================================================
  // REPORT
  function renderReport(state) {
    const b = bodies.report, M = state.model; b.innerHTML = '';
    b.append(el('h2', {}, 'Model report'));
    if (!M) { b.append(el('p', { className: 'ff-note' }, 'No GLB loaded. Load <code>' + MODEL.file + '</code> to measure the real model.')); return; }
    const n = M.native, unitWarn = Math.max(M.size.x, M.size.z) > 400 ? 'The model is over 400 m across. It may be exported in centimetres or millimetres. It has <b>not</b> been rescaled; check the export units.' :
      Math.max(M.size.x, M.size.z) < 5 ? 'The model is under 5 m across. It may be exported in the wrong units. It has <b>not</b> been rescaled.' : '';
    b.innerHTML += `
      <p class="ff-note">Measured directly from <code>${esc(M.fileName)}</code> (${(M.bytes / 1048576).toFixed(1)} MB). The model is only translated: no scaling, no rotation.</p>
      ${unitWarn ? `<p class="ff-warn">${unitWarn}</p>` : ''}
      <h3>Overall dimensions</h3>
      <table><tbody>
        <tr><td>Width · X</td><td class="num">${f3(M.size.x)} m</td></tr>
        <tr><td>Depth · Z (north–south on plan)</td><td class="num">${f3(M.size.z)} m</td></tr>
        <tr><td>Overall height · Y</td><td class="num">${f3(M.height)} m</td></tr>
        <tr><td>Plan footprint (bounding box)</td><td class="num">${f2(M.size.x * M.size.z)} m²</td></tr>
        <tr><td>Bounding box min (native)</td><td class="num">${v3(n.box.min)}</td></tr>
        <tr><td>Bounding box max (native)</td><td class="num">${v3(n.box.max)}</td></tr>
        <tr><td>Model centre (native)</td><td class="num">${v3(n.center)}</td></tr>
        <tr><td>Translation applied</td><td class="num">${v3(M.offset)}</td></tr>
        <tr><td>Meshes · triangles · materials</td><td class="num">${M.meshes.length} · ${M.native.tris.toLocaleString()} · ${n.materials}</td></tr>
      </tbody></table>
      <h3>Floor levels (detected)</h3>
      <table><thead><tr><th>Level</th><th class="num">Height</th><th class="num">Native Y</th><th class="num">Floor area</th></tr></thead><tbody>
        ${M.levels.map((l, i) => `<tr><td>Level ${i}</td><td class="num">${l.y >= 0 ? '+' : ''}${f3(l.y)} m</td><td class="num">${f3(l.y + n.ground)}</td><td class="num">${f2(l.area)} m²</td></tr>`).join('')}
      </tbody></table>
      <details class="ff-more"><summary>All horizontal surfaces (${M.surfaces.length})</summary>
      <table><tbody>${M.surfaces.map(s => `<tr><td>${s.y >= 0 ? '+' : ''}${f3(s.y)} m</td><td class="num">${f2(s.area)} m²</td></tr>`).join('')}</tbody></table></details>
      ${n.sampled ? '<p class="ff-note">Large model: floor levels were found from a sample of the triangles.</p>' : ''}
      <h3>Brief vs real GLB</h3>
      ${compareTable(state)}
      <p class="ff-note">Compares the long side with the long side and the short side with the short side. Tolerance: within ±${TOLERANCE.ok.toFixed(2)} m = match, up to ±${TOLERANCE.warn.toFixed(2)} m = check, more = differs. The GLB is never scaled to fit the brief.</p>`;
    const io = el('div', { className: 'ff-row ff-wrap' });
    const bb = (t, fn) => { const x = el('button', { type: 'button', className: 'ff-btn ff-sm' }, t); x.onclick = fn; return x; };
    io.append(bb('Download report (.md)', h.onReportExport), bb('Label zones…', () => h.onTool('zones')));
    b.append(io);
    const ref = el('details', { className: 'ff-more' });
    ref.innerHTML = `<summary>Reference shell (Phase 1 assumptions, replaced)</summary><p class="ff-note">The assumed shell is kept only as the REFERENCE SHELL debug layer. Its zone sizes came from the brief and photos, not from the GLB.</p>`;
    b.append(ref);
  }

  function compareTable(state) {
    const rows = ZONE_DEFS.filter(d => d.brief).map(d => {
      const m = state.zones.measure(d.key), [bw, bd] = d.brief, bl = Math.max(bw, bd), bs = Math.min(bw, bd), ba = bw * bd;
      const brief = `<td class="num">${f2(bw)} × ${f2(bd)}<br><small>${f2(ba)} m²</small></td>`;
      if (!m) return `<tr><td>${d.label}</td>${brief}<td class="src" colspan="2">not labelled yet</td></tr>`;
      const dl = m.len - bl, ds = m.wid - bs, worst = Math.max(Math.abs(dl), Math.abs(ds));
      const cls = worst <= TOLERANCE.ok ? 'ok' : worst <= TOLERANCE.warn ? 'warn' : 'bad';
      return `<tr><td>${d.label}</td>${brief}<td class="num">${f2(m.len)} × ${f2(m.wid)}<br><small>${f2(m.area)} m²</small></td>
        <td class="num ${cls}">${sgn(dl)} / ${sgn(ds)}<br><small>${sgn(m.area - ba)} m² · ${sgn((m.area / ba - 1) * 100)}%</small></td></tr>`;
    }).join('');
    return `<table class="ff-cmp"><thead><tr><th>Zone</th><th class="num">Brief</th><th class="num">Real GLB</th><th class="num">Δ long / short</th></tr></thead><tbody>${rows}</tbody></table>`;
  }

  // ===========================================================================
  // CARDS
  function showInspect(i, actions) {
    if (!i) { card.hidden = true; return; }
    card.hidden = false;
    card.innerHTML = `<header><span>Inspect</span><button type="button" class="ff-x" aria-label="Clear selection">✕</button></header>
      <h3 class="ff-name">${esc(i.name)}</h3>
      <dl class="ff-kv">
        <dt>Path</dt><dd>${esc(i.path)}</dd>
        <dt>Type</dt><dd>${esc(i.type)} · ${i.tris.toLocaleString()} tris · ${i.verts.toLocaleString()} verts</dd>
        <dt>Material</dt><dd>${i.materials.map(esc).join('<br>') || '—'}</dd>
        <dt>Size X</dt><dd class="num">${f3(i.size.x)} m</dd>
        <dt>Size Y</dt><dd class="num">${f3(i.size.y)} m <small>height</small></dd>
        <dt>Size Z</dt><dd class="num">${f3(i.size.z)} m</dd>
        <dt>Centre</dt><dd class="num">${v3(i.centre)}</dd>
        <dt>Node pos.</dt><dd class="num">${v3(i.node)}</dd>
        <dt>BBox min</dt><dd class="num">${v3(i.box.min)}</dd>
        <dt>BBox max</dt><dd class="num">${v3(i.box.max)}</dd>
        <dt>Native ctr</dt><dd class="num">${v3(i.nativeCentre)}</dd>
      </dl>
      <p class="ff-note">World positions in metres, viewer coordinates (Y up). Native = original GLB coordinates.</p>
      <div class="ff-row ff-wrap"></div>`;
    card.querySelector('.ff-x').onclick = actions.clear;
    const row = card.querySelector('.ff-row');
    for (const [t, fn] of [['Focus', actions.focus], ['Hide', actions.hide], ['Isolate', actions.isolate], ['Copy JSON', actions.copy]]) {
      const b = el('button', { type: 'button', className: 'ff-btn ff-sm' }, t); b.onclick = fn; row.append(b);
    }
  }
  function showHint(title, html) {
    card.hidden = false;
    card.innerHTML = `<header><span>${esc(title)}</span></header><div class="ff-hint">${html}</div>`;
  }
  function showMeasure(m, actions) {
    card.hidden = false;
    const rows = m.list.map((x, i) => `<tr><td>${i + 1}</td><td class="num"><b>${f3(x.dist)}</b></td><td class="num">${f2(x.plan)}</td><td class="num">${f2(x.dx)}</td><td class="num">${f2(x.dy)}</td><td class="num">${f2(x.dz)}</td><td><button type="button" class="ff-x" data-i="${i}" aria-label="Delete measurement ${i + 1}">✕</button></td></tr>`).reverse().join('');
    card.innerHTML = `<header><span>Measure</span></header>
      <p class="ff-hint">${m.pending ? 'Click <b>point B</b>. Esc cancels.' : 'Click <b>point A</b> on the model.'}</p>
      <label class="ff-check"><input type="checkbox" ${actions.snap ? 'checked' : ''}><span>Snap to vertices</span></label>
      ${m.list.length ? `<table class="ff-mtab"><thead><tr><th>#</th><th class="num">Distance</th><th class="num">Plan</th><th class="num">ΔX</th><th class="num">ΔY</th><th class="num">ΔZ</th><th></th></tr></thead><tbody>${rows}</tbody></table>
      <div class="ff-row"><button type="button" class="ff-btn ff-sm ff-clear">Clear all</button></div>` : ''}`;
    card.querySelector('input').onchange = (e) => actions.onSnap(e.target.checked);
    card.querySelectorAll('[data-i]').forEach(b => (b.onclick = () => actions.remove(+b.dataset.i)));
    card.querySelector('.ff-clear')?.addEventListener('click', actions.clear);
  }

  return {
    setTool(k) { for (const [n, b] of Object.entries(T)) b.setAttribute('aria-pressed', String(n === k)); if (!k) card.hidden = true; },
    openTab, closeDrawer, get tab() { return tab; },
    renderLayers, renderZones, renderReport,
    refresh(state) { if (tab === 'layers') renderLayers(state); if (tab === 'zones') renderZones(state); if (tab === 'report') renderReport(state); },
    showInspect, showMeasure, showHint, hideCard() { card.hidden = true; },
    showDrop(msg) { drop.hidden = false; drop.querySelector('.ff-drop-msg').innerHTML = msg || ''; },
    hideDrop() { drop.hidden = true; },
    progress(text) { progress.hidden = !text; progress.textContent = text || ''; },
  };
}

// Markdown report (downloaded by the client, shareable)
export function reportMarkdown(state) {
  const M = state.model, n = M.native, L = [];
  L.push(`# Fitness Force — real GLB model report`, '', `Source: \`${M.fileName}\` · generated ${new Date().toISOString().slice(0, 16).replace('T', ' ')}`, '',
    'The GLB is the master architectural source. It was only translated (centre → origin, ground floor → 0). No scaling, no rotation.', '',
    '## Overall', '', '| | |', '|---|---:|',
    `| Width (X) | ${f3(M.size.x)} m |`, `| Depth (Z) | ${f3(M.size.z)} m |`, `| Overall height (Y) | ${f3(M.height)} m |`,
    `| Bounding box min (native) | ${v3(n.box.min)} |`, `| Bounding box max (native) | ${v3(n.box.max)} |`, `| Centre (native) | ${v3(n.center)} |`,
    `| Translation applied | ${v3(M.offset)} |`, `| Meshes / triangles / materials | ${M.meshes.length} / ${n.tris} / ${n.materials} |`, '',
    '## Floor levels', '', '| Level | Height | Native Y | Floor area |', '|---|---:|---:|---:|',
    ...M.levels.map((l, i) => `| ${i} | ${f3(l.y)} m | ${f3(l.y + n.ground)} | ${f2(l.area)} m² |`), '',
    '## Brief vs real GLB', '', '| Zone | Brief (m) | Brief m² | GLB (m) | GLB m² | Δ long | Δ short | Δ area |', '|---|---:|---:|---:|---:|---:|---:|---:|');
  for (const d of ZONE_DEFS.filter(d => d.brief)) {
    const m = state.zones.measure(d.key), [bw, bd] = d.brief, ba = bw * bd;
    if (!m) { L.push(`| ${d.label} | ${f2(bw)} × ${f2(bd)} | ${f2(ba)} | not labelled | | | | |`); continue; }
    L.push(`| ${d.label} | ${f2(bw)} × ${f2(bd)} | ${f2(ba)} | ${f2(m.len)} × ${f2(m.wid)} | ${f2(m.area)} | ${sgn(m.len - Math.max(bw, bd))} | ${sgn(m.wid - Math.min(bw, bd))} | ${sgn(m.area - ba)} m² (${sgn((m.area / ba - 1) * 100)}%) |`);
  }
  L.push('', '## All labelled zones', '', '| Zone | Length × width (m) | Area m² | Angle | Source |', '|---|---:|---:|---:|---|');
  for (const d of ZONE_DEFS) { const m = state.zones.measure(d.key), z = state.zones.zones[d.key]; if (m) L.push(`| ${d.label} | ${f2(m.len)} × ${f2(m.wid)} | ${f2(m.area)} | ${m.angle.toFixed(1)}° | ${z.source} |`); }
  if (state.measures.length) {
    L.push('', '## Measurements', '', '| # | Distance | Plan | ΔX | ΔY | ΔZ |', '|---|---:|---:|---:|---:|---:|');
    state.measures.forEach((x, i) => L.push(`| ${i + 1} | ${f3(x.dist)} | ${f2(x.plan)} | ${f2(x.dx)} | ${f2(x.dy)} | ${f2(x.dz)} |`));
  }
  return L.join('\n') + '\n';
}
