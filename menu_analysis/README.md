# Menu Analysis and Fix

## Identified Issues
1. **Multiple Bootstrap Instances**: The Gob.mx framework (`gobmx.js`) loads `bootstrap.bundle.js` from a CDN. The Symfony application also loads Bootstrap via AssetMapper. This causes conflicts in event listeners for `data-bs-toggle` attributes, leading to random failures in expanding/collapsing the menu.
2. **DOM Injection**: `main.js` prepends a full navbar/header to the `<body>`, which overlaps with the application's navbar.
3. **CSS Overrides**: Gob.mx styles and the application's styles compete for the navbar appearance and positioning.

## Isolated Code
- `menu_template.html.twig`: The original application menu template.
- `menu_logic.js`: Extracted JavaScript logic from `main.js` and `gobmx.js` that handles navbar injection and loading of external dependencies.
- `menu_styles_raw.css`: Relevant CSS rules found in `main.css`.

## Applied Fix
1. **Stimulus Controller**: Created `assets/controllers/menu_fix_controller.js` to manually handle the navbar toggle using the Bootstrap API. This avoids the conflict between multiple Bootstrap versions by opting out of the automatic `data-bs-toggle` behavior.
2. **Template Update**: Modified `templates/menu.html.twig` to use `data-action="click->menu-fix#toggle"` and removed `data-bs-toggle="collapse"`.
3. **CSS Fixes**: Created `assets/styles/menu_fix.css` to ensure the application navbar has the correct z-index and display properties, overriding any interference from the Gob.mx framework.
