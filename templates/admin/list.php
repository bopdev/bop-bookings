<h2><?php _e( 'Bop Bookings', 'bop-bookings' ) ?></h2>

<?php 
global $wpdb;
$rows = $wpdb->get_results(
  "SELECT t.booking_id AS id, t.created AS created, t.type AS type, t.status AS status
  FROM {$wpdb->bop_bookings} AS t
  INNER JOIN {$wpdb->bop_bookings_dates} AS d ON (d.booking_id = t.booking_id)
  WHERE CAST(d.start AS DATETIME) >= CAST(NOW() AS DATETIME)
  AND t.status IN ('pending', 'approved')
  GROUP BY t.booking_id
  ORDER BY type DESC, status DESC, id DESC",
  ARRAY_A
);

$collection = [];
foreach( $rows as $row ){
  $collection[] = new Bop_Booking( $row );
}
?>
<select name="booking-type">
  <?php $_bb = new Bop_Booking(); foreach( $_bb->get_valid_types() as $code=>$def ): ?>
    <option value="<?php echo $code ?>"<?php echo $_bb->get_default_type() == $code ? ' selected' : '' ?>><?php echo $def['labels']['general'] ?></option>
  <?php endforeach ?>
</select>
<style>.status-approved{background:red;} .status-pending{background:yellow;} .header-day, .day{float:left;width:14%;height:40px;} .day-selected{background:green;} .clearfix{clear:both;}</style>
<div class="clndr-container"></div>
<div class="clearfix"></div>
<div class="bookings-list">
  <ul></ul>
</div>
<script type="application/json" id="booking-data"><?php echo json_encode( $collection ) ?></script>
<script type="text/template" id="clndr-template">
  <?php include apply_filters( 'admin_list_clndr_template.bop_bookings', bop_bookings_plugin_path( 'templates/admin/list-clndr.php' ) ) ?>
</script>
<script type="text/template" id="booking-list-item-template">
  <?php include apply_filters( 'admin_list_item_template.bop_bookings', bop_bookings_plugin_path( 'templates/admin/list-item.php' ) ) ?>
</script>
