<?php

//Reject if accessed directly or when not uninstalling
defined( 'WP_UNINSTALL_PLUGIN' ) || die( 'Our survey says: ... X.' );

delete_site_option( 'bop_bookings_version' ); //change this

//Uninstall code - remove everything with wiping
