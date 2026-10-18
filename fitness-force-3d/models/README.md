# models/

Put the real architectural model here as **`men_gym_3d.glb`**. It is the master
architectural source for the viewer: the app only translates it (plan centre → origin,
ground floor → 0.00) and never scales, rotates or edits it.

If the file is missing, the app shows a load screen where the GLB can be chosen or
dragged in instead.
