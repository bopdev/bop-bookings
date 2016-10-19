<div class="clndr-controls">
  <span class="clndr-previous-button">‹ Prev</span>
  <span class="clndr-next-button">Next ›</span>
  <div class="month"><%= month %></div>
  <div class="clearfix"></div>
</div>
<div class="days-of-the-week clearfix">
  <% _.each(daysOfTheWeek, function(day) { %>
    <div class="header-day"><%= day %></div>
  <% }); %>
</div>
<div class="days clearfix">
  <% _.each(days, function(day){ %>
    <% day.classes += extras.isEventSelected(day) ? ' day-selected' : '' %>
    <div class="<%= day.classes %><% _.each(day.events, function(event){print( ' status-'+event.status );}); %>" id="<%= day.id %>">
      <span class="day-number"><%= day.day %></span>
    </div>
  <% }); %>
</div>
