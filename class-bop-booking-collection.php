<?php 

class Bop_Booking_Collection{
  
  public $query_vars;
  
  public $default_vars = [];
  
  protected $_parsed_vars = [
    'select'=>[],
    'join'=>[],
    'where'=>['relationship'=>'AND'],
    'groupby'=>[],
    'having'=>[],
    'orderby'=>[],
    'offset'=>0,
    'limit'=>0,
    'class'=>'bop_booking'
  ];

  protected $_collection_hash = '';
  
  protected $_queried = false;

  public $cache = true;

  public $overwrite_cache = false;

  public $collection;
  
  public $collection_ids;
  
  public function __construct( $query = [] ){
    if ( ! empty( $query ) ) {
      $this->query( $query );
    }
  }
  
  public function init(){}
  
  public function query( $query ){
    $this->init();
    
    $this->query_vars = $query;
    $this->parse_query();
  }
  
  public function parse_query(){
    global $wpdb;
    
    $this->_queried = false;
    
    $q = $this->query_vars;
    $pq = &$this->_parsed_vars;
    
    
    $pq['class'] = strtolower( isset( $q['class'] ) && class_exists( $q['class'] ) ? $q['class'] : 'Bop_Booking' );
    
    if( isset( $q['from_date'] ) ){
      $pq['join'][] = ['type'=>'INNER', 'table'=>$wpdb->bop_bookings_dates, 'alias'=>'from_d', 'native_field'=>'from_d.booking_id', 'foreign_field'=>'t.booking_id']
      $pq['where'][] = ['relation'=>'AND', ['table'=>'from_d', 'field'=>'start', 'compare'=>'>=', 'value'=>$q['from_date'], 'cast'=>'DATETIME']];
    }
    if( isset( $q['to_date'] ) ){
      $pq['join'][] = ['type'=>'INNER', 'table'=>$wpdb->bop_bookings_dates, 'alias'=>'to_d', 'native_field'=>'to_d.booking_id', 'foreign_field'=>'t.booking_id']
      $pq['where'][] = ['relation'=>'AND', ['table'=>'to_d', 'field'=>'start', 'compare'=>'<=', 'value'=>$q['to_date'], 'cast'=>'DATETIME']];
    }
    if( isset( $q['status'] ) ){
      $where_statuses = ['relation'=>'OR'];
      foreach( (array)$q['status'] as $status ){
        $where_statuses[] = ['table'=>'t', 'field'=>'status', 'compare'=>'=', 'value'=>$status, 'cast'=>'CHAR']
      }
      $pq['where'][] = $where_statuses;
    }
    if( isset( $q['type'] ) ){
      $where_types = ['relation'=>'OR'];
      foreach( (array)$q['type'] as $type ){
        $where_types[] = ['table'=>'t', 'field'=>'type', 'compare'=>'=', 'value'=>$type, 'cast'=>'CHAR']
      }
      $pq['where'][] = $where_types;
    }
  }
  
  public function get_collection(){
    if( $this->_queried )
      return $this->collection;
    
    $this->_queried = true;
    
    $current_hash = md5( serialize( $this->_parsed_vars ) );
    if( ! $this->overwrite_cache && $this->cache ){
      $cache_result = wp_cache_get( $current_hash, 'bop_booking_collection' );  
      
      if( $cache_result ){
        $this->collection_ids = $cache_result;
        $class = $this->_parsed_vars['class'];
        foreach( $this->collection_ids as $id ){
          if( $row = wp_cache_get( $id, 'bop_booking_data' ) ){
            $this->collection[] = new $class()->fill_object( $row );
          }else{
            $this->collection[] = new $class( $id );
          }
        }
        return $this->collection;
      }
    }

    $this->_get_collection();

    $this->_collection_hash = $current_hash;
    $this->overwrite_cache = false;
    
    if( $this->cache )
      wp_cache_set( $current_hash, $this->collection_ids, 'bop_booking_collection' );
      
    return $this->collection;
  }
  
  protected function _get_collection(){
    global $wpdb;
    
    $this->collection = [];
    $this->collection_ids = [];
    
    $v = &$this->_parsed_vars;
    
    $select = "";
    foreach( $v['select'] as $col ){
      $select .= "t.{$col} AS {$col}";
    }
    $q = "SELECT $select";
    $q .= "FROM {$wpdb->bop_bookings} AS t";
    
    $joins = "";
    foreach( $v['join'] as $j ){
      $joins .= "\n{$j['type']} JOIN {$j['table']} AS {$j['alias']} ON ({$j['native_field'] = $j['foreign_field']})";
    }
    $q .= $joins;
    
    $wheres = $this->_fill_where_clause( $v['where'] );
    if( $wheres )
      $q .= "\nWHERE 1=1 " . $wheres;
    
    $groupbys = implode( ", ", $v['groupby'] );
    if( $groupbys )
      $q .= "\nGROUP BY {$groupbys}";
      
    //!having
    
    if( $v['orderby'] ){
      $obs = [];
      foreach( $v['orderby'] as $ob ){
        $obs[] = "{$ob['field']} {$ob['dir']}";
      }
      $q .= "\nORDER BY " . implode( ", ", $obs );
    }
    
    if( $v['limit'] )
      $q .= "\nLIMIT {$v['limit']}";
      
    if( $v['offset'] )
      $q .= "\nOFFSET {$v['offset']}";
    
    $rows = $wpdb->get_results( $q, ARRAY_A );
    
    foreach( $rows as $row ){
      $this->collection_ids[] = $row['id'];
    }
    
    $class = $this->class;
    foreach( $rows as $row ){
      $this->collection[] = new $class()->fill_object( $row );
      
      if( $this->cache )
        wp_cache_set( $booking->id, $row, 'bop_booking_data' );
    }
  }
  
  protected function _fill_where_clause( $clause ){
    $output = "";
    foreach( $clause as $w ){
      if( isset( $w['field'] ) ){
        $output .= "\n{$clause['relation']} CAST({$w['table']}.{$w['field']} AS {$w['cast']}) {$w['compare']} CAST({$w['value']} AS {$w['cast']})";
      }else{
        $ouput .= "\n{$clause['relation']} (" . $this->_fill_where_clause( $w ) . ")";
      }
    }
    return $output;
  }
  
}
