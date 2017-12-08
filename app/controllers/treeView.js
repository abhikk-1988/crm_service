/**
 * 
 */

var app = app || {};

(function (app,$){
    
    app.controller('treeView', ['$scope','utilityService','modalService', function ($scope,utilityService,modalService){
        
        
        $scope.$watch('treeViewData', function (val){
            angular.element('#tree').treeview({data: val});
            
        });
        
        console.log('hello');
        
        
    }]);
})(app,jQuery);