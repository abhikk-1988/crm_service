
jQuery.noConflict();
(function ($){
       
    // Detect click event on document 
    $('.modal-block').keydown(function (e){ 
        e.stopPropagation();
        var keyTriggered = e.keyCode;

        if(keyTriggered === 13){
            // Submit the login form 
            $(this).find('button').click();
        }
    });
    
})(jQuery);