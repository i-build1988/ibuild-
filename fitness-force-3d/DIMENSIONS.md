# Fitness Force — Dimension Map (Phase 1, REFERENCE SHELL)

> **Superseded.** The real architectural model `models/men_gym_3d.glb` is now the master
> source of truth. Everything below describes the assumed shell built from photographs,
> which is kept only as the optional REFERENCE SHELL debug layer. Real dimensions, floor
> levels and the brief-vs-GLB comparison are measured in the app (REPORT tab, downloadable
> as Markdown).

All sizes in metres. Source of the reference shell: `src/config/dimensions.js`.

## Zones

| Zone | Size (m) | Area (m²) | Source |
|---|---|---:|---|
| Main Reception (brief) | 7.80 × 6.00 | 46.80 | Brief |
| ↳ Reception platform as built | 8.00 wide · 4.60 left / 9.08 right, curved front | ≈ 62.3 | Photo 3 — **differs from brief, see A2** |
| Internal Club Entrance | 4.70 × 5.15 | 24.21 | Brief + photo 7 |
| Club Corridor | 4.26 × 17.00 | 72.42 | Photo 5 (not in brief) |
| Cardio Area | 12.00 × 15.80 | 189.60 | Brief + photo 4 |
| HYROX / Functional | 5.78 × 17.69 | 102.25 | Brief + photo 8 |

## Heights (estimated from photos)

| Element | Height |
|---|---:|
| Front hall (double height) | 7.00 |
| Training hall (double height, exposed black ceiling) | 7.50 |
| Reception back wall / bulkhead | 3.60 |
| Corridor ceiling | 3.20 |
| HYROX ceiling frame | 3.60 |
| Reception platform rise | 0.30 |
| Eye level (cameras, walk) | 1.65 |

## Circulation (assumed)

Elevators → Reception platform → Internal Entrance → glass sliding doors → 17 m corridor → training hall → HYROX runway (west) and Cardio (east), with a 2.72 m aisle between them. Cardio sits 2.00 m from the window wall.

## Assumptions to confirm

- **A1 · Zone positions**: The photos show each zone but not how they connect. Assumed route: Elevators → Reception platform → Entrance → glass sliding doors → 17 m corridor → training hall. In the hall, HYROX runs north–south beside Cardio, with a 2.72 m aisle between them. Each zone is placed with one x / y pair in this file, so moving it is a one-line change.
- **A2 · Reception size**: The brief gives 7.80 × 6.00 m (46.80 m²). Photo 3 measures a curved platform 8.00 m wide, 4.60 m deep on the left and 9.08 m on the right. The platform follows the photo and is raised 0.30 m.
- **A3 · Heights**: Front hall 7.00 m, training hall 7.50 m (double height, exposed black ceiling), reception back wall 3.60 m, corridor 3.20 m, HYROX ceiling frame 3.60 m. These are read from proportions in the photos, not measured.
- **A4 · Elevators**: Two elevators on a core west of the reception lounge. The photos do not show where the real elevators are.
- **A5 · Windows**: One wall of tall windows on the training hall's east side, 2.00 m from the Cardio zone. Treadmills will face it.
- **A6 · Columns**: No columns drawn. The structural grid is needed from a plan.
- **A7 · Logo**: Drawn from the Fitness Force mark in photos 2, 6 and 8. Replace it with the vector logo file when available.
