<li class="booking-list-item<%= ' status-'+status %><%= selected ? ' day-selected' : '' %>">
  <input type="radio" name="highlight" value="" data-id="<%= id %>"<%= selected ? ' checked' : '' %>>
  <form method="post" action="">
    <div>
      <div class="details">
        <% _.each(meta, function(value, key){ %>
          <span class="<%- key %>"><%= value %></span>
        <% }); %>
      </div>
      <div class="dates">
        <ul>
        <% _.each(dates, function(value){ %>
          <li><%= value.date %></li>
        <% }); %>
        </ul>
      </div>
      <div class="actions">
        <input type="hidden" name="booking_id" value="<%= id %>">
        <select name="status">
          <% _.each(valid_statuses, function(valid_status, code){ %>
            <option value="<%= code %>"<%= (code == status) ? ' selected' : '' %>><%= (code == status) ? valid_status.labels.general : valid_status.labels.action %></option>
          <% }); %>
        </select>
        <button type="submit" name="action" value="change_status"><?php _e( 'Update', 'bop-bookings' ) ?></button>
      </div>
    </div>
  </form>
</li>
