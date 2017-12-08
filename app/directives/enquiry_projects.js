/**
 * custom directive to enquiry projects in bootstrap modal 
 */

var app = app || {};

(function (app,$){

    app.directive('enquiryProjects', function ($compile,baseUrl){
        
        return {
            
            restrict : 'EA',
            
            replace : false,
            
            templateUrl : baseUrl + 'app/directives/templates/lead_enquiry_projects.htm',
            
//            template : '<div>Abhishek</div>',
            scope : {
                projects : '='
            }, // isolating the scope 
            
            compile : function (tElement, tAttributes){
                   
                   // link function 
                   return function (scope, iElement, iAttributes){
                       
                            iElement.bind('click', function (){
                                console.log(scope.projects);
                                $('.bd-example-modal-lg').modal('show');
                            });
                   };
            },
            
            controller : function ($scope,$element,$attrs){
                    $scope.id = $scope.$id;
                    console.log($scope.projects);
            }
            
        };
        
    });
    
}) (app,jQuery);
