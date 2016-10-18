<?php 

//Reject if accessed directly
defined( 'ABSPATH' ) || die( 'Our survey says: ... X.' );

//Plugin code
function bop_bookings_plugin_url( $path = '' ){
  return plugin_dir_url( __FILE__ ) . ltrim( $path, '/' );
}

require_once bop_bookings_plugin_path( 'bop-booking.php' );

add_action( 'admin_menu', function(){
  add_menu_page(
    __( 'Bop Bookings', 'bop-bookings' ),
    __( 'Bop Bookings', 'bop-bookings' ),
    'edit_pages',
    'bop-bookings',
    function(){
      require bop_bookings_plugin_path( 'templates/admin/list.php' );
    },
    'dashicons-calendar-alt',
    30 );
} );

add_action( 'wp', function(){
  wp_register_script( 'moment', bop_bookings_plugin_url( 'assets/js/moment.js' ), [], '2.15.1', true );
  wp_register_script( 'clndr', bop_bookings_plugin_url( 'assets/js/clndr.js' ), ['underscore', 'moment'], '1.4.6', true );
  wp_register_script( 'bop-bookings-admin', bop_bookings_plugin_url( 'assets/js/admin.js' ), ['jquery', 'underscore', 'moment', 'clndr'], '0.1.0', true );
} );

add_action( 'admin_enqueue_scripts', function( $hook ){
  if( $hook == 'bop-bookings' ){
    wp_enqueue_script( 'bop-bookings-admin' );
  }
} );
