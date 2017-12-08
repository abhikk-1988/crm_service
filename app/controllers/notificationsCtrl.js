/**
 *
 * Notifications controller 
 * @author: Abhishek Agrawal
 * @created at: 12th April, 2017
 */


var app = app || {};

(function (app , $){
    
    app.controller('notificationCtrl', function ($scope, $interval,pushNotification,Session){
    
        var bgColors = ['bg-primary','bg-info','bg-warning','bg-danger'];

        // Default notification message count to fetch
        $scope.notification_count = 0;
        
        var d = new Date();

        var month = parseInt(d.getMonth()) + 1;
        
        if(parseInt(month) < 10){
           month = '0' + month;
        }
        
        var current_date = d.getFullYear() + '-'+ month + '-'+d.getDate(); 
        
        $scope.notifications = [];

        // modal varibale to hold all notifications messages
        pushNotification.getReadMessages(current_date).then(function (resp){

            $scope.notifications = resp.data;

            $scope.startNotificationTimer();
        }, function (resp){

        });

        // Function to genearte a random number between minimum value 1 and max value passed as an argument to the function
        $scope.getRandomInt = function(max) {
            var min = 1;
            min = Math.ceil(min);
            max = Math.floor(max);
            return Math.floor(Math.random() * (max - min)) + min;
        };
        
        // Function to pull notification messages from server
        $scope.getNotifications = function (){
         
            var temp            = [];

            pushNotification.getUnreadMessages(current_date).then(function (resp){
            
                if(Object.keys(resp.data).length > 0){

                    angular.element('#notification_counter').css({backgroundColor: '#F00'});
                    $scope.notification_count = Object.keys(resp.data).length;
                    
                    if(resp.data){
                        angular.forEach(resp.data, function (o){
                            $scope.notifications.unshift(o);
                        });
                    }
                    
                }

            }, function (){

            });

            // for(var i=0; i< $scope.notification_count; i++){
                
            //     var notification    = {};

            //     var random_number = $scope.getRandomInt(4); // to select background color from colors array by specific index number 
                
            //     notification.html   = 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum';
            //     notification.color  = '#000';
            //     notification.type   = bgColors[random_number];
            //     notification.time   = new Date().getTime();

            //     temp.push(notification);
            // }

            // Pushing new notifications to the notifications array 
            angular.forEach(temp, function (o){
                $scope.notifications.unshift(o);
            });

        };
        
        // Function to open/hide notification messages window
        $scope.fetchNotifications = function (e){
          
            e.stopPropagation();
                
            var parent = e.currentTarget;
            
            $scope.notification_count = 0;

            // TOGGLE (SHOW OR HIDE) NOTIFICATION WINDOW.
            $('#notifications').fadeToggle('fast', 'linear', function () {
                angular.element(parent).css({backgroundColor : '#777'});
            });
        };
    
        // Notification Timer to fetch notification in every 10 seconds 

        $scope.startNotificationTimer = function (){
            $scope.startNotification = $interval(function (){
                
                var session_user = Session.getUser();
                
                if(Object.keys(session_user).length > 0 && session_user.id){
                    $scope.getNotifications();
                }
                else{
                    console.log('out of session');
                }
                
            },10000); // 10 seconds    
        };
    }); // end controller
    
    
}(app, jQuery));