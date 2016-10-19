<?php 

//Reject if accessed directly
defined( 'ABSPATH' ) || die( 'Our survey says: ... X.' );

//Plugin code
function bop_bookings_plugin_url( $path = '' ){
  return plugin_dir_url( __FILE__ ) . ltrim( $path, '/' );
}

require_once bop_bookings_plugin_path( 'class-bop-booking.php' );

add_action( 'admin_menu', function(){
  add_menu_page(
    __( 'Bop Bookings', 'bop-bookings' ),
    __( 'Bop Bookings', 'bop-bookings' ),
    'edit_pages',
    'bop-bookings',
    function(){
      if( isset( $_POST['action'] ) ){
        switch( $_POST['action'] ){
          case 'change_status':
            if( isset( $_POST['status'] ) && isset( $_POST['booking_id'] ) && is_numeric( $_POST['booking_id'] ) ){
              $booking = new Bop_Booking( $_POST['booking_id'] );
              $booking->update_status( $_POST['status'] );
            }
          break;
        }
      }
      require bop_bookings_plugin_path( 'templates/admin/list.php' );
    },
    'dashicons-calendar-alt',
    30 );
} );

function _bop_bookings_register_scripts(){
  wp_register_script( 'moment', bop_bookings_plugin_url( 'assets/js/moment.js' ), [], '2.15.1', true );
  wp_register_script( 'clndr', bop_bookings_plugin_url( 'assets/js/clndr.js' ), ['underscore', 'moment'], '1.4.6', true );
  wp_register_script( 'bop-bookings-admin', bop_bookings_plugin_url( 'assets/js/admin.js' ), ['jquery', 'underscore', 'moment', 'clndr'], '0.1.0', true );
}

add_action( 'admin_enqueue_scripts', function( $hook ){
  _bop_bookings_register_scripts();
  if( $hook == 'toplevel_page_bop-bookings' ){
    wp_enqueue_script( 'bop-bookings-admin' );
  }
} );

add_action( 'wp_enqueue_scripts', '_bop_bookings_register_scripts' );

require_once bop_bookings_plugin_path( 'cf-bespoke.php' );
