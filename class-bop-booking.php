<?php 

//Reject if accessed directly
defined( 'ABSPATH' ) || die( 'Our survey says: ... X.' );

class Bop_Booking implements JsonSerializable{
  
  public $id = 0;
  
  public $created;
  
  public $type = '';
  
  public $status = '';
  
  public $dates = [];
  
  protected $_meta_type = 'bop_booking';
  
  public function __construct( $id_or_data = 0 ){
    if( $id_or_data ){
      if( is_array( $id_or_data ) || is_object( $id_or_data ) ){
        $this->fill_object( (array)$id_or_data );
      }else{
        $this->load( $id_or_data );
      }
    }
    return $this;
  }

  public function load( $id ){
    $fields = $this->_fetch_from_db( $id );
    $this->fill_object( $fields );
    return $this;
  }
  
  public function fill_object( $data ){
    if( isset( $data['id'] ) ){
      $this->id = $data['id'];
    }
    
    if( isset( $data['created'] ) ){
      if( is_object( $data['created'] ) && is_a( $data['created'], 'Datetime' ) ){
        $this->created = $data['created'];
      }elseif( is_string( $this->created ) ){
        $this->created = new Datetime( $data['created'] );
      }
    }
    
    if( isset( $data['type'] ) ){
      $this->type = in_array( $data['type'], array_keys( $this->get_valid_types() ) ) ? $data['type'] : $this->get_default_type();
    }
    
    if( isset( $data['status'] ) ){
      $this->status = in_array( $data['status'], array_keys( $this->get_valid_statuses() ) ) ? $data['status'] : $this->get_default_status();
    }
    
    if( isset( $data['dates'] ) ){
      foreach( $data['dates'] as $date ){
        if( is_object( $date ) && is_a( $date, 'Datetime' ) ){
          $this->dates[] = $date;
        }elseif( is_string( $date ) ){
          $this->dates[] = new Datetime( $date );
        }
      }
    }
    
    return $this;
  }
  
  protected function _fetch_from_db( $id ){
    global $wpdb;
    $fields = $wpdb->get_row(
      $wpdb->prepare(
        "SELECT t.booking_id AS id, t.created AS created, t.type AS type, t.status AS status
        FROM {$wpdb->bop_bookings} AS t
        WHERE t.booking_id = %d
        LIMIT 1",
        $id
      ),
      ARRAY_A
    );
    return $fields;
  }
  
  public function get_valid_types(){
    return apply_filters(
      'valid_types.bop_bookings',
      [
        'default'=>[
          'labels'=>['general'=>__( 'Default', 'bop-bookings' )]
        ]
      ],
      $this
    );
  }
  
  public function get_default_type(){
    return apply_filters( 'default_type.bop_bookings', 'default', $this );
  }
  
  public function get_valid_statuses(){
    return apply_filters(
      'valid_statuses.bop_bookings',
      [
        'pending'=>[
          'labels'=>[
            'general'=>__( 'Pending', 'bop-bookings' ),
            'action'=>__( 'Pending', 'bop-bookings' )
          ]
        ],
        'approved'=>[
          'labels'=>[
            'general'=>__( 'Approved', 'bop-bookings' ),
            'action'=>__( 'Approve', 'bop-bookings' )
          ]
        ],
        'dismissed'=>[
          'labels'=>[
            'general'=>__( 'Dismissed', 'bop-bookings' ),
            'action'=>__( 'Dismiss', 'bop-bookings' )
          ]
        ]
      ],
      $this
    );
  }
  
  public function get_default_status(){
    return apply_filters( 'default_status.bop_bookings', 'pending', $this );
  }
  
  public function get_meta( $k = '', $single = false ){
    if( ! $this->id ) return false;
    return get_metadata( $this->_meta_type, $this->id, $k, $single );
  }
  
  public function update_meta( $k, $v, $prev = '' ){
    if( ! $this->id ) return false;
    return update_metadata( $this->_meta_type, $this->id, $k, $v, $prev );
  }
  
  public function add_meta( $k, $v, $unique = false ){
    if( ! $this->id ) return false;
    return add_metadata( $this->_meta_type, $this->id, $k, $v, $unique );
  }
  
  public function delete_meta( $k, $v = '' ){
    if( ! $this->id ) return false;
    return delete_metadata( $this->_meta_type, $this->id, $k, $v );
  }
  
  public function update_multi_meta( $k, $new_items ){
    $old_items = $this->get_meta( $k );
    
    //clean input before comparison
    for( $i = 0; $i < count( $new_items ); $i++ ){
      $new_items[$i] = trim( $new_items[$i] );
    }
    
    //check what's new
    $to_add = [];
    foreach( $new_items as $new_item ){
      if( ! in_array( $new_item, $old_items ) ){
        $to_add[] = $new_item;
      }
    }
    
    //replace expired with new or, if no more new, simply delete expired
    $i = 0;
    foreach( $old_items as $old_item ){
      if( ! in_array( $old_item, $new_items ) ){
        if( isset( $to_add[$i] ) ){
          $this->update_meta( $k, $to_add[$i], $old_item );
          ++$i;
        }else{
          $this->delete_meta( $k, $old_item );
        }
      }
    }
    
    //add any remaining new
    while( $i < count( $to_add ) ){
      $this->add_meta( $k, $to_add[$i] );
      ++$i;
    }
  }
  
  public function get_dates(){
    global $wpdb;
    
    if( $this->dates )
      return $this->dates;
    
    $dates = $wpdb->get_col(
      $wpdb->prepare(
        "SELECT start
        FROM {$wpdb->bop_bookings_dates} AS t
        WHERE booking_id = %d
        ORDER BY start ASC",
        $this->id
      )
    );
    
    $this->fill_object( ['dates'=>$dates] );
    
    return $this->dates;
  }
  
  public function update_status( $status ){
    global $wpdb;
    
    if( $this->id && in_array( $status, array_keys( $this->get_valid_statuses() ) ) && $this->status != $status ){
      
      do_action( 'before_update_status.bop_bookings', $status, $this );
      
      $success = $wpdb->update( $wpdb->bop_bookings, ['status'=>$status], ['booking_id'=>$this->id], ['%s'], ['%d'] );
      if( $success ){
        $this->fill_object( ['status'=>$status] );
        do_action( 'after_update_status.bop_bookings', $this );
      }
    }
  }
  
  public function update_dates( $dates = [] ){
    global $wpdb;
    
    if( $dates ){
      $new_dates = new Bop_Booking();
      $new_dates = $new_dates->fill_object(['dates'=>$dates])->get_dates();
      $old_dates = $this->get_dates();
    }else{
      $new_dates = $this->get_dates();
      $old_dates = new Bop_Booking( $this->id );
      $old_dates = $old_dates->get_dates();
    }
    
    do_action( 'before_update_dates.bop_bookings', $new_dates, $old_dates, $this );
    
    $diff = count( $new_dates ) - count( $old_dates );
    
    if( $diff < 0 ){
      $wpdb->query( $wpdb->prepare(
        "DELETE FROM {$wpdb->bop_bookings_dates}
        WHERE booking_id = %d
        LIMIT %d",
        $this->id,
        -$diff
      ) );
      $old_dates = $this->get_dates();
    }
    
    $i=0;
    while( isset( $old_dates[$i] ) ){
      $wpdb->update( $wpdb->bop_bookings_dates, ['start'=>$new_dates[$i]->format("Y-m-d H:i:s")], ['booking_id'=>$this->id, 'start'=>$old_dates[$i]->format("Y-m-d H:i:s")], ['%s'], ['%d', '%s'] );
      ++$i;
    }
    
    while( isset( $new_dates[$i] ) ){
      $wpdb->insert( $wpdb->bop_bookings_dates, ['booking_id'=>$this->id, 'start'=>$new_dates[$i]->format("Y-m-d H:i:s")], ['%s'] );
      ++$i;
    }
    
    $this->get_dates();
    
    do_action( 'after_update_dates.bop_bookings', $this );
    
    return $this;
  }
  
  public function insert(){
    global $wpdb;
    
    do_action( 'before_insert.bop_bookings', $this );
    
    $wpdb->query( $wpdb->prepare(
      "INSERT INTO {$wpdb->bop_bookings} 
        (created, type, status)
        VALUES (NOW(), %s, %s)
      ",
      $this->type,
      $this->status
    ) );
    
    $this->id = $id = $wpdb->insert_id;
    
    do_action( 'after_insert.bop_bookings', $this );
    
    $this->update_dates();
    
    do_action( 'after_insert_dates.bop_bookings', $this );
    
    return $this;
  }
  
  public function jsonSerialize(){
    $output = new StdClass();
    $output->id = $this->id;
    $output->dates = $this->get_dates();
    $output->type = $this->type;
    $output->status = $this->status;
    $output->valid_statuses = $this->get_valid_statuses();
    $output->meta = $this->get_meta();
    
    $output = apply_filters( 'json_serialize.bop_bookings', $output, $this );
    
    return $output;
  }
}
