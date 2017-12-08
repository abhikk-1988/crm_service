/**
 * Notifications Directive to generate dicersified popups for showing information to user 
 * Types: alerts notification, success messages, warning messages
 */

var app = app || {};

(function (appModule,$){
    
    appModule.directive('notificationMessage', function ($timeout,notificationService){
        
            var notification = {};
        
            // This directive will in shared scope with the controller 
            // Can be used as attribute or element
            
            notification.restrict = 'EA';
            
            notification.replace = true;
            
            notification.template = '<div class="notification-container  position" title="Click to dismis" ng-class="notification_class"><div class="message" ng-bind-html="message"> <span class="space pull-right">&times;</span></div></div>';
            
            notification.link = function ($scope,element,attr){
                
                    $scope.showNotification = function (){
                            $scope.message				= $scope.notificationConfig.message;
                            $scope.notification_class   = $scope.notificationConfig.class;
                            $(element).fadeIn('slow');
                    };
                   
                   $scope.showNotification();
                
                   $scope.timeout_promise = {};
                   timeout_promise = $timeout(timeoutCallback,10000, true);
                   timeout_promise.then(function (success){

                   }, function (failure){
                   });
                   
                    
                    /**
                     * Click function to dismiss notification popup 
                     */
                    $(element).click(function (e){
                             $(element).fadeOut('slow');
                    });
                  
                   /**
                    * timer callback function
                    * @returns {undefined}
                    */ 
                   function timeoutCallback(){
                            $(element).fadeOut('slow');
                   }
                   
            };
            
            return notification;
    });
    
}) (app,jQuery);