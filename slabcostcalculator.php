<?php
/**
 * Plugin Name: Slab Cost Calculator Fixes
 * Description: Custom fixes for slabcostcalculator.com, deployed from GitHub via Hostinger Git. Copies the theme in this plugin's theme/ folder into wp-content/themes/ so theme fixes deploy with Git and stay in the real theme folder.
 * Version: 0.3.0
 * Author: Ali Ahmad
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * GIT-DEPLOYED THEME
 *
 * Hostinger's Git deployment only writes to this plugin's folder, not to
 * wp-content/themes/. The theme's source lives in theme/slab-cost-calculator/
 * here, and whenever a push changes it, this plugin copies the changed
 * files into the real wp-content/themes/slab-cost-calculator/ folder on the
 * next page load. The fixes then live in the theme itself: they stay even if
 * this plugin is later deactivated or deleted.
 *
 * The sync runs on plugins_loaded, before WordPress loads the theme, so the
 * request that performs it already runs the new files. If the theme folder
 * can't be written, the site is served from the copy in this plugin instead
 * (same slug and settings), so a push never leaves the site on old code.
 */
define( 'SCC_THEME_SLUG', 'slab-cost-calculator' );
define( 'SCC_THEME_SRC', __DIR__ . '/theme/' . SCC_THEME_SLUG );

function scc_theme_files( $dir ) {
	$files = array();
	$iter  = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) );
	foreach ( $iter as $file ) {
		if ( $file->isFile() ) {
			$files[] = substr( wp_normalize_path( $file->getPathname() ), strlen( wp_normalize_path( $dir ) ) + 1 );
		}
	}
	sort( $files );
	return $files;
}

// Changes whenever Git adds, removes or rewrites a file in theme/.
function scc_theme_fingerprint( $files ) {
	$parts = array();
	foreach ( $files as $rel ) {
		$path    = SCC_THEME_SRC . '/' . $rel;
		$parts[] = $rel . ':' . filesize( $path ) . ':' . filemtime( $path );
	}
	return md5( implode( '|', $parts ) );
}

// Copies every source file whose content differs into the theme folder.
// Each file is written to a temp name and renamed, so a visitor never sees
// a half-written file. Returns false if anything could not be written.
function scc_sync_theme( $files, $target ) {
	foreach ( $files as $rel ) {
		$src  = SCC_THEME_SRC . '/' . $rel;
		$dest = $target . '/' . $rel;
		if ( file_exists( $dest ) && md5_file( $dest ) === md5_file( $src ) ) {
			continue;
		}
		if ( ! wp_mkdir_p( dirname( $dest ) ) ) {
			return false;
		}
		$tmp = $dest . '.scc-tmp';
		if ( ! @copy( $src, $tmp ) || ! @rename( $tmp, $dest ) ) {
			@unlink( $tmp );
			return false;
		}
		if ( function_exists( 'opcache_invalidate' ) && '.php' === substr( $dest, -4 ) ) {
			opcache_invalidate( $dest, true );
		}
	}
	return true;
}

function scc_maybe_sync_theme() {
	if ( ! is_dir( SCC_THEME_SRC ) || ( defined( 'SCC_DISABLE_THEME_SYNC' ) && SCC_DISABLE_THEME_SYNC ) ) {
		return;
	}
	$files       = scc_theme_files( SCC_THEME_SRC );
	$fingerprint = scc_theme_fingerprint( $files );
	if ( get_option( 'scc_theme_synced' ) === $fingerprint ) {
		return;
	}

	// One request syncs at a time; others keep serving the bundled copy.
	if ( ! add_option( 'scc_theme_sync_lock', time(), '', false ) ) {
		if ( time() - (int) get_option( 'scc_theme_sync_lock' ) < 60 ) {
			scc_serve_bundled_theme();
			return;
		}
		update_option( 'scc_theme_sync_lock', time(), false );
	}

	$target = get_theme_root( SCC_THEME_SLUG ) . '/' . SCC_THEME_SLUG;
	if ( is_dir( $target ) && scc_sync_theme( $files, $target ) ) {
		update_option( 'scc_theme_synced', $fingerprint, true );
	} else {
		scc_serve_bundled_theme();
	}
	delete_option( 'scc_theme_sync_lock' );
}
add_action( 'plugins_loaded', 'scc_maybe_sync_theme', 1 );

/*
 * Fallback only: point WordPress at the copy in this plugin for this
 * request when the theme folder could not be updated.
 */
function scc_serve_bundled_theme() {
	$dir = function ( $value, $slug ) {
		return SCC_THEME_SLUG === $slug ? SCC_THEME_SRC : $value;
	};
	$uri = function ( $value, $slug ) {
		return SCC_THEME_SLUG === $slug ? plugins_url( 'theme/' . SCC_THEME_SLUG, __FILE__ ) : $value;
	};
	add_filter( 'template_directory', $dir, 10, 2 );
	add_filter( 'stylesheet_directory', $dir, 10, 2 );
	add_filter( 'template_directory_uri', $uri, 10, 2 );
	add_filter( 'stylesheet_directory_uri', $uri, 10, 2 );
}
