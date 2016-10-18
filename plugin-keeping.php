<?php 

//Reject if accessed directly
defined( 'ABSPATH' ) || die( 'Our survey says: ... X.' );


//Name any additional tables in the database (take care with multisite)

global $wpdb;
$wpdb->bop_bookings = $wpdb->prefix . 'bop_bookings';
$wpdb->bop_bookings_dates = $wpdb->prefix . 'bop_bookings_dates';
$wpdb->bop_bookingmeta = $wpdb->prefix . 'bop_bookings_meta';



/* Activation hook 
 * 
 * Uses the database to determine the version number and checks against
 * the current code version number. It then runs through all outstanding
 * update scripts in order of version number. If this is a fresh
 * install, it will run through all the update scripts (consider the
 * first update script as an install script).
 */
register_activation_hook( bop_bookings_plugin_path( 'init.php' ), function(){
	
	define( 'BOP_PLUGIN_ACTIVATING', true );
	
	$db_version = get_site_option( 'bop_bookings_version', '0.0.0', false );
	$pd = get_plugin_data( bop_bookings_plugin_path( 'init.php' ), false, false );
	
	if( version_compare( $db_version, $pd['Version'], '<' ) ){
		
		if( $handle = opendir( bop_bookings_plugin_path( 'updates' ) ) ){
			
			$updates = array();
			
			while( false !== ( $entry = readdir( $handle ) ) ){
				if( $entry != '.' && $entry != '..' ) {
					if( version_compare( $db_version, $entry, '<' ) ){
						$updates[] = $entry;
					}
					
				}
			}
			
			if( ! empty( $updates ) ){
				
				define( 'BOP_PLUGIN_UPDATING', true );
				
				usort( $updates, 'version_compare' );
				
				foreach( $updates as $update ){
					require_once( bop_bookings_plugin_path( "updates/{$update}/update.php" ) );
				}
				
			}
			
			closedir($handle);
			
		}
		
		update_option( 'bop_bookings_version', $pd['Version'], false );
	}
} );


/** Deactivation hook
 * 
 * Runs deactivate.php
 * 
 */
register_deactivation_hook( bop_bookings_plugin_path( 'init.php' ), function(){
	
	define( 'BOP_PLUGIN_DEACTIVATING', true );
	
	require_once( bop_bookings_plugin_path( 'deactivate.php' ) );
} );


/* Set up translations */
add_action( 'plugins_loaded', function(){
    load_plugin_textdomain( 'bop-bookings', false, basename( bop_bookings_plugin_path() ) . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR );
} );
