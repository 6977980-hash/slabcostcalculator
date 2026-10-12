<?php
/**
 * Plugin Name: Slab Cost Calculator Fixes
 * Description: Custom fixes for slabcostcalculator.com, deployed from GitHub via Hostinger Git. Copies the theme in this plugin's theme/ folder into wp-content/themes/ so theme fixes deploy with Git and stay in the real theme folder.
 * Version: 0.4.0
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
 * Files removed from theme/ in the repo are removed from the theme folder
 * too, and a sync that fails part-way changes nothing (see scc_sync_theme()).
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

// Copies every source file whose content differs into the theme folder, in
// two passes so a failed sync never leaves the theme half-updated:
//  1. every changed file is copied next to its target as "<name>.scc-tmp";
//     if any copy fails, all temp files are removed and nothing changed;
//  2. only then is each temp file renamed over its target (a rename is
//     atomic, so a visitor never sees a half-written file).
// Returns false if the theme could not be updated.
function scc_sync_theme( $files, $target ) {
	$staged = array();
	foreach ( $files as $rel ) {
		$src  = SCC_THEME_SRC . '/' . $rel;
		$dest = $target . '/' . $rel;
		if ( file_exists( $dest ) && md5_file( $dest ) === md5_file( $src ) ) {
			continue;
		}
		$tmp = $dest . '.scc-tmp';
		if ( ! wp_mkdir_p( dirname( $dest ) ) || ! @copy( $src, $tmp ) ) {
			@unlink( $tmp );
			foreach ( $staged as $staged_tmp => $unused ) {
				@unlink( $staged_tmp );
			}
			return false;
		}
		$staged[ $tmp ] = $dest;
	}

	foreach ( $staged as $tmp => $dest ) {
		if ( ! @rename( $tmp, $dest ) ) {
			@unlink( $tmp );
			return false;
		}
		if ( function_exists( 'opcache_invalidate' ) && '.php' === substr( $dest, -4 ) ) {
			opcache_invalidate( $dest, true );
		}
	}
	return true;
}

// Removes files that were deleted from the repo, so they don't stay
// web-reachable (or loadable) in the live theme forever. Only files this
// plugin deployed before (the saved manifest) are removed. On the first
// sync there is no manifest yet, so only stray code files (.php, .js, .css)
// that are not in the repo are removed; images and anything else that may
// have been added to the theme folder by hand stay.
function scc_remove_deleted_files( $files, $target ) {
	$keep     = array_flip( $files );
	$manifest = get_option( 'scc_theme_manifest' );
	$removed  = array();

	foreach ( scc_theme_files( $target ) as $rel ) {
		if ( isset( $keep[ $rel ] ) ) {
			continue;
		}
		$deployed_before = is_array( $manifest ) ? in_array( $rel, $manifest, true ) : (bool) preg_match( '/\.(php|js|css)$/i', $rel );
		$leftover_tmp    = '.scc-tmp' === substr( $rel, -8 );
		if ( ! $deployed_before && ! $leftover_tmp ) {
			continue;
		}
		$path = $target . '/' . $rel;
		if ( @unlink( $path ) ) {
			$removed[] = dirname( $path );
			if ( function_exists( 'opcache_invalidate' ) && '.php' === substr( $path, -4 ) ) {
				opcache_invalidate( $path, true );
			}
		}
	}

	// Drop folders left empty (rmdir fails harmlessly on non-empty ones).
	$dirs = array_unique( $removed );
	rsort( $dirs );
	foreach ( $dirs as $dir ) {
		while ( strlen( $dir ) > strlen( $target ) && @rmdir( $dir ) ) {
			$dir = dirname( $dir );
		}
	}

	update_option( 'scc_theme_manifest', $files, false );
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

	// After a failed sync of this same version, wait 10 minutes before
	// trying again instead of rescanning and rewriting on every request.
	$failed = get_option( 'scc_theme_sync_failed' );
	if ( is_array( $failed ) && $failed['fingerprint'] === $fingerprint && time() - (int) $failed['time'] < 600 ) {
		scc_serve_bundled_theme();
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
		scc_remove_deleted_files( $files, $target );
		update_option( 'scc_theme_synced', $fingerprint, true );
		delete_option( 'scc_theme_sync_failed' );
	} else {
		update_option( 'scc_theme_sync_failed', array( 'fingerprint' => $fingerprint, 'time' => time() ), false );
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
