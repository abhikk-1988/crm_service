/**
 * @version 1.0
 * @fileOverview This js file is a directive for settings popup on header at top right side 
 */

var app = app || {};

(function (app,$){
    
    app.directive('userSetting',function ($http,appUrls){
       
        var setting = {};
      
        setting.restrict = 'AE';
        
        setting.templateUrl = appUrls.appUrl + 'directive/template/settings_popup.html';
        
        return setting ;
       
    });
    
})(app,jQuery);