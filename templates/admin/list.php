<h2><?php _e( 'Bop Bookings', 'bop-bookings' ) ?></h2>

<?php 
global $wpdb;
$rows = $wpdb->get_results(
  "SELECT t.booking_id AS id, t.created AS created, t.type AS type, t.status AS status
  FROM {$wpdb->bop_bookings} AS t
  INNER JOIN {$wpdb->bop_bookings_dates} AS d ON (d.booking_id = t.booking_id)
  WHERE CAST(d.start AS DATETIME) >= CAST(NOW() AS DATETIME)
  AND t.status IN ('pending', 'approved')
  AND t.type IN ('schools_groups', 'private_hire')
  ORDER BY type, status, id",
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
<div class="clndr-container">
</div>
<div class="bookings-list">
  <ul></ul>
</div>
<script type="application/json" id="booking-data"><?php echo json_encode( $collection ) ?></script>
<script type="text/template" id="clndr-booking-template">
</script>
<script type="text/template" id="booking-list-item-template">
  <li class="booking-list-item">
    <form method="post" action="#">
      <div>
        <div class="details">
          <span class="name"><%= meta.name %></span>
          <span class="email"><%= meta.email %></span>
        </div>
        <div class="actions">
          <input type="hidden" name="booking_id" value="<%= id %>">
          <select name="status">
            <% _.each(valid_statuses, function(valid_status, code){ %>
              <option value="<%= code %>"<%= valid_status == status ? ' selected' : '' %>><%= valid_status.labels.action %></option>
            <% }); %>
          </select>
          <button type="submit" name="action" value="change_status"><?php _e( 'Update', 'bop-bookings' ) ?></button>
        </div>
      </div>
    </form>
  </li>
</script>
