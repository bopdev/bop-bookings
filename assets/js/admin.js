(function($){
  
  $(document).ready(function(){
    
    var allBookings = $.parseJSON($('#booking-data').text());
    var bookings = [];
    
    $.each(allBookings, function(key, booking){
      booking.start = booking.dates[0].date;
      booking.end = booking.dates[booking.dates.length-1].date;
      selected = false;
    });
    
    var $list = $('.bookings-list ul');
    var $typeSelect = $('[name="booking-type"]');
    var $bookingContainer = $('.clndr-container');
    var extras = {
      selectedEventId: 0,
      isEventSelected: function(day){
        var selected = false;
        _.each(day.events, function(event){
          if(event.id == extras.selectedEventId){
            selected = true;
          }
        });
        return selected;
      },
      isEventFirstDay: function(day){
        if(!day.events.length) return false;
        
        var key = false;
        _.each(day.events, function(event, k){
          if(day.date.isSame(moment(event.start))){
            key = k;
          }
        });
        return key;
      }
    };
    
    var clndr = $bookingContainer.clndr({
      template: $('#clndr-template').html(),
      // start the week off on Sunday (0), Monday (1), etc. Sunday is the default.
      weekOffset: 1,
      // determines which month to start with using either a date string or a moment object.
      //startWithMonth: startConstraint,
      // an array of day abbreviations. If you have moment.js set to a different language,
      // it will guess these for you! If for some reason that doesn't work, use this...
      // the array MUST start with Sunday (use in conjunction with weekOffset to change the starting day to Monday)
      daysOfTheWeek: ['S', 'M', 'T', 'W', 'T', 'F', 'S'],
      //constraints: {
      //    startDate: startConstraint
      //},
      // callbacks!
      clickEvents: {
        // fired whenever a calendar box is clicked.
        // returns a 'target' object containing the DOM element, any events, and the date as a moment.js object.
        click: function(target){
          if(target.events.length){
            highlightEvent(target.events[0].id);
            refreshDisplay();
          }
        },
        // fired when a user goes forward a month. returns a moment.js object set to the correct month.
        nextMonth: function(month){ },
        // fired when a user goes back a month. returns a moment.js object set to the correct month.
        previousMonth: function(month){ },
        // fired when a user goes back OR forward a month. returns a moment.js object set to the correct month.
        onMonthChange: function(month){ },
        // fired when a user goes to the current month/year. returns a moment.js object set to the correct month.
        today: function(month){ },
      },
      // the target classnames that CLNDR will look for to bind events. these are the defaults.
      targets: {
        nextButton: 'clndr-next-button',
        previousButton: 'clndr-previous-button',
        todayButton: 'clndr-today-button',
        day: 'day',
        empty: 'empty'
      },
      // an array of event objects
      events: bookings,
      multiDayEvents: {
        endDate: 'end',
        startDate: 'start'
      },
      // if you're supplying an events array, dateParameter points to the field in your event object containing a date string. It's set to 'date' by default.
      dateParameter: 'date',
      // show the numbers of days in months adjacent to the current month (and populate them with their events). defaults to true.
      showAdjacentMonths: true,
      // when days from adjacent months are clicked, switch the current month.
      // fires nextMonth/previousMonth/onMonthChange click callbacks. defaults to false.
      adjacentDaysChangeMonth: false,
      // a callback when the calendar is done rendering. This is a good place to bind custom event handlers.
      doneRendering: function(){ },
      // anything you want access to in your template
      extras: extras
    });
    
    
    function refreshDisplay(){
      $list.render();
      clndr.setEvents(bookings);
      clndr.render();
    }
    
    function highlightEvent(id){
      extras.selectedEventId = id;
      _.each(bookings, function(booking){
        if(booking.id == id){
          booking.selected = true;
          
          //move to correct month
          var startDate = moment(booking.dates[0].date);
          clndr.setYear(startDate.year());
          clndr.setMonth(startDate.month());
        }else{
          booking.selected = false;
        }
      });
      refreshDisplay();
    }
    
    $list.render = function(){
      var $this = $(this);
      var $tplHtml = $('#booking-list-item-template').html();
      $this.empty();
      _.each(bookings, function(booking){
        var tpl = _.template($tplHtml);
        $this.append(tpl(booking));
      });
    }
    
    $typeSelect.on('change.bop-bookings', function(){
      var type = $(this).val();
      bookings = [];
      _.each(allBookings, function(booking){
        if(booking.type == type) bookings.push(booking);
      });
      window.location.hash = type;
      refreshDisplay();
    });
    
    if($typeSelect.val() != window.location.hash){
      $typeSelect.val(window.location.hash.substring(1));
    }
    
    $typeSelect.trigger('change.bop-bookings');
    
    $('.bookings-list').on('click.bop-bookings', '.booking-list-item input[type="radio"][name="highlight"]:checked', function(){
      highlightEvent($(this).data('id'));
    });
    
  });
  
})(jQuery);
