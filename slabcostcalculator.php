<?php
/**
 * Plugin Name: Slab Cost Calculator Fixes
 * Description: Custom fixes for slabcostcalculator.com, deployed from GitHub via Hostinger Git. Serves the active "slab-cost-calculator" theme from this plugin's theme/ folder so theme fixes deploy with Git too.
 * Version: 0.2.0
 * Author: Ali Ahmad
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * GIT-DEPLOYED THEME
 *
 * Hostinger's Git deployment only writes to this plugin's folder, not to
 * wp-content/themes/. To get theme fixes onto the site by pushing to the
 * repo, a copy of the theme lives in theme/slab-cost-calculator/ and
 * WordPress is pointed at it through the template/stylesheet directory
 * filters. The theme keeps its slug, so its settings (menus, customizer)
 * are untouched, and the copy in wp-content/themes/ stays as the fallback:
 * deactivating this plugin, deleting theme/, or defining
 * SCC_DISABLE_BUNDLED_THEME in wp-config.php puts the site back on it.
 *
 * These filters must be added here (plugins load before the theme) so
 * they are in place when WordPress resolves the theme path and loads its
 * functions.php.
 */
define( 'SCC_THEME_SLUG', 'slab-cost-calculator' );
define( 'SCC_THEME_DIR', __DIR__ . '/theme/' . SCC_THEME_SLUG );

function scc_serves_theme( $slug ) {
	return SCC_THEME_SLUG === $slug
		&& ! ( defined( 'SCC_DISABLE_BUNDLED_THEME' ) && SCC_DISABLE_BUNDLED_THEME )
		&& file_exists( SCC_THEME_DIR . '/style.css' )
		&& file_exists( SCC_THEME_DIR . '/functions.php' );
}

function scc_theme_dir( $dir, $slug ) {
	return scc_serves_theme( $slug ) ? SCC_THEME_DIR : $dir;
}
add_filter( 'template_directory', 'scc_theme_dir', 10, 2 );
add_filter( 'stylesheet_directory', 'scc_theme_dir', 10, 2 );

function scc_theme_dir_uri( $uri, $slug ) {
	return scc_serves_theme( $slug ) ? plugins_url( 'theme/' . SCC_THEME_SLUG, __FILE__ ) : $uri;
}
add_filter( 'template_directory_uri', 'scc_theme_dir_uri', 10, 2 );
add_filter( 'stylesheet_directory_uri', 'scc_theme_dir_uri', 10, 2 );
