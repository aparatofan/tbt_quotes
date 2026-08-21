<?php
/**
 * Removes per-user quote state when the plugin is deleted.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_metadata( 'user', 0, '_tbt_quotes_current_quote_id', '', true );
delete_metadata( 'user', 0, '_tbt_quotes_recent_quote_ids', '', true );
