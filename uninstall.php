<?php
/**
 * Uninstall cleanup for alt自動挿入.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'alt_auto_insert_settings' );
