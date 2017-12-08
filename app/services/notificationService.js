/**
 * Notification service service for notificaion directive 
 */

var app = app || {};

(function (app){
    
          app.service('notificationService', function (){
            
                this.show = function (config){
                        this.popup_display = true;
                };

                this.popup_display = false;

                this.notificationConfig = {};

                this.cancel = function (){
                        this.popup_display = false;
                };

          });
    
}) (app);