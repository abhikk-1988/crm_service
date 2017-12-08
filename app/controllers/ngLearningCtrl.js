
/**
 * @file: app controller
 * 
 */

var app = app || {};
var Pace = Pace || {};

(function (app,Pace){
    
    // Register a controller
    
    app.controller('ngLearningCtrl', function ($scope,paceLoading,AuthService,Session){
        
        paceLoading.start();
        $scope.controller  = 'NG Learning';
        
    });
    
})(app,Pace);
