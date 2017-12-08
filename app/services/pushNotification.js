/**
 * Service for push notifications
 */


var app = app || {};

(function(app){

    app.service('pushNotification', function ($http, baseUrl){

        var domain = window.location.host;

        this.getUnreadMessages = function (date)
        {
            return $http.get(baseUrl + 'apis/getNotificationMessages.php?mode=unread&date='+date+'&domain='+domain);
        };

        this.getReadMessages = function (date){
            return $http.get(baseUrl + 'apis/getNotificationMessages.php?mode=read&date='+date+'&domain='+domain);
        };


        this.markMessageAaRead = function (){

        };

        this.markallAsread = function (){

        };

    });

}(app));