/**
 * External service to start Pace.js loading bar on route change 
 */

var app     = app || {};
var Pace    = Pace || {};

(function (app,Pace){
    
    app.service('paceLoading', function ($http,appUrls){
        
        this.start = function (msg){
             // Just make get request to base url to get pace loading visible 
            $http.get(appUrls.baseUrl + 'apis/pace.php');
        };
        
    });
    
})(app,Pace);