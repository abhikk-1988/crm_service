/**
 * Common script file for application header 
 * @version 1.0
 * @author Abhishek Agrawal
 */

(function ($){
    
    $(document).ready(function (){
        
        console.log('setting tray', $('#logout_setting_tray'));
        
        $('#logout_setting_tray').click(clickHandler);
        
        $(document).click(domClickHandler);
        
        function clickHandler(e){
           // e.stopPropagation();
        }
        
        function domClickHandler(e){
          
            e.stopPropagation();
            
            var target = $(e.target);
            
            if(typeof target[0].offsetParent == 'object' && !target[0].offsetParent){
                $('#logout_setting_tray').hide();
                $(target[0].offsetParent).css({backgroundColor:'ghostwhite'});
                return;
            }
            else if(typeof target[0].offsetParent == 'object' && target[0].offsetParent.id == 'account_setting_dialog'){
                $('#logout_setting_tray').show();
            }
            else{
                $('#logout_setting_tray').hide();
            }            
        }
        
        
        console.log('account name container',$('#account_name_container'));
        
        $('#account_name_container').hover(function (){
            
            alert('on hover in');
        }, function (){
            
            console('on hover out');
        });
    });
})(jQuery);