# slabcostcalculator

Custom WordPress code for https://slabcostcalculator.com.

This repository is deployed automatically by Hostinger's Git deployment.
Every push to `main` is pulled onto the server into the install path configured
in Hostinger (a folder under `public_html`, for example
`wp-content/plugins/slabcostcalculator`).

Only custom theme/plugin code lives here. WordPress core, uploads and
`wp-config.php` stay on the server and are not tracked in this repo.

## Theme

The active theme (`slab-cost-calculator`) is kept in `theme/slab-cost-calculator/`.
Hostinger's Git deployment only updates this plugin's folder, so the plugin points
WordPress at that copy (see `slabcostcalculator.php`). Edit theme files here and push
to `main`; the copy in `wp-content/themes/` on the server is only a fallback and is used
again if the plugin is deactivated or `SCC_DISABLE_BUNDLED_THEME` is defined in
`wp-config.php`.
