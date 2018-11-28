// Global $
jQuery(document).ready(function($) {

  var clicked = false;
  //Instantiate Fullcalendar
  $('#calendar').fullCalendar({
    minTime: "07:00:00",
    maxTime: "21:00:00",
    selectable: false,
    selectHelper: true,
    selectOverlap: false,
    height: 600,
    header: { center: 'month,agendaDay' },
    dayClick: function(date, jsEvent, view, resourceObj) {
      $('#calendar').fullCalendar( 'gotoDate', date );
      $('#calendar').fullCalendar( 'changeView', 'agendaDay' );
      jsEvent.preventDefault();
      if (view.name == 'agendaDay') {
        var varighed = $('#vaelg-en-ydelse option:selected').data('varighed');
        var titel = $('#vaelg-en-ydelse option:selected').text();
        $('#hiddenbehandling').val($('#vaelg-en-ydelse option:selected').val());
                
        var kundeid = $('#vaelg-en-kunde option:selected').val();
        var kundenavn = $('#vaelg-en-kunde option:selected').text();
        $('#hiddenkundeid').val(kundeid);
        
        $('#starthidden').val(date.format());

        var varighedTimer = varighed.slice(0, 1);
        var varighedMinutter = varighed.slice(2, 4);
        
        var enddate = $.fullCalendar.moment(date.toObject());
//        enddate.set(date.toObject());
        
        console.log(date.format());
        enddate.add(varighedTimer, 'hours');
        enddate.add(varighedMinutter, 'minutes');
        console.log(date.format());
        console.log(enddate.format());

        
        $('#sluthidden').val(enddate.format());
        
        var alertTxt = 'Vil du booke:\n'+titel+'\nTil denne kunde:\n'+kundenavn+'\n\nFra kl: '+date.format()+' til kl: '+enddate.format();
        alert(alertTxt);
        
        $('#calendar').fullCalendar( 'changeView', 'month' );
      }      
    },
    //events: bookingURL.pluginurl
  });
});
  