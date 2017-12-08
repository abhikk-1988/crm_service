
var flatPicker = flatPicker || {};

var validateTime = function (day, hour, minute){
            
    var current_hour     = new Date().getHours();    
    var current_minute   = new Date().getMinutes();
                
                
//    if(day === 1){
//        if(hour < current_hour){
//            alert('You have selected a past time');
//            clearCalender();
//        }else if(hour > 17){
//            alert('Selected Time is Not allowed');
//            clearCalender();
//        }else{
//                    
//            if(hour === 17){
//                if(minute > 30){
//                    alert('Selcted Time is mot allowed');
//                    clearCalender();
//                }
//            }
//        }
//    }else{
//                    
//    }
};

flatPicker.config1 = {
        defaultDate : 'today',
        dateFormat : 'Y-m-d',
        inline: false,
        altInput : false,
        enableTime : false,
        enableSeconds : false,
        mode : 'single',
        noCalendar : false,
        time_24hr : false,
        utc : true,
        minDate: 'today',
        onChange : function (e){
        },
        onOpen: function (){
        }
};

flatPicker.config2 = {
    defaultDate : 'today',
    dateFormat : 'Y-m-d',
    inline: false,
    altInput : false,
    enableTime : false,
    enableSeconds : false,
    mode : 'single',
    noCalendar : false,
    time_24hr : false,
    utc : true,
    minDate: 'today',
    onChange: function (e){
    },
    onOpen : function (){
    }
};

flatPicker.config3 = {
        dateFormat : 'H:i K',
        enableTime : true,
        mode : 'single',
        noCalendar : true,
        minuteIncrement : 30,
        time_24hr : true,
        utc : true,
        onChange : function (e){
        }
};

    (function ($, flatPicker){
    
         var normal_calender     =  $("#normal_calender").flatpickr(flatPicker.config1);
         var site_visit_calender =  $("#site_visit_calender").flatpickr(flatPicker.config2);
         var event_timepicker    =  $('#event_timepicker').flatpickr(flatPicker.config3); 
        
         // make timepicker input readonly  
         $('.numInput').attr({readonly: true});
        
    }( jQuery,flatPicker));

   