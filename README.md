# slabcostcalculator

Custom WordPress code for https://slabcostcalculator.com.

This repository is deployed automatically by Hostinger's Git deployment.
Every push to `main` is pulled onto the server into the install path configured
in Hostinger (a folder under `public_html`, for example
`wp-content/plugins/slabcostcalculator`).

Only custom theme/plugin code lives here. WordPress core, uploads and
`wp-config.php` stay on the server and are not tracked in this repo.

## Theme

The source of the active theme (`slab-cost-calculator`) is kept in
`theme/slab-cost-calculator/`. Hostinger's Git deployment only updates this plugin's
folder, so after each push the plugin copies any changed theme files into the real
`wp-content/themes/slab-cost-calculator/` folder on the next page load (see
`slabcostcalculator.php`). Edit theme files here and push to `main`; the fixes then live
in the theme folder itself and stay even if the plugin is deactivated or deleted.
Bump `Version` in the theme's `style.css` to bust browser caches for CSS/JS changes.
