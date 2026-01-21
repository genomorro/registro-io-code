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
1. **Removed Bootstrap CDN**: Modified `assets/styles/gobmx.js` to stop loading the Bootstrap and jQuery CDNs. Instead, the application uses versions managed by Symfony AssetMapper.
2. **Global Exposure**: Updated `assets/app.js` to expose jQuery and Bootstrap to the `window` object. This ensures that framework scripts (like `main.js`) that expect global dependencies continue to function.
3. **Transition to Local Assets**: Updated `templates/base.html.twig` to use local copies of the Gob.mx framework files instead of remote CDN links.
4. **Stimulus Controller**: Maintained the `menu_fix_controller.js` as a robust backup to handle navbar toggles manually, ensuring reliability even if other framework scripts interfere with the DOM.
5. **UX-Autocomplete Support**: By consolidating into a single Bootstrap instance, conflicts with `UX-Autocomplete` (TomSelect) are resolved.
