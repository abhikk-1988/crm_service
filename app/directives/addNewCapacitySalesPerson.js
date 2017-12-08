/* *
 Custom directive of a button to add new capacity for sales persons
*/

var app = app || {};

(function (app, $) {

    // directive definition
    
    app.directive('addNewCapacitySalesPerson', function($http, $compile, $filter, baseUrl, $location){
        
        // return DDO
        
        return {
            restrict    : 'EA',
            replace     : false,
            template    : '<button class="btn btn-xs btn-success">Add new capacity for sales person</button>',
            link        : function (scope, element, attr){
                
                // here element is the container DIV DOM element
             
                element.bind('click', function (){
                    $location.path('/add_sales_person_capacity');
                    scope.$apply();
                });
                
                // send request to server to get information of all asm has assigned capacities or not.
             
                $http.get(baseUrl + 'apis/helper.php?method=is_all_asm_users_has_capacity&params=scope:sp').
                then(function (response){
                    
                    if(response.data.btn_state === 'enable'){
                        element.find('button').attr({disabled : false, title : 'Click to add new sales person capacity'});
                    }
                    else{
                        element.find('button').attr({disabled : true, title : 'No Area Sales Managers has assigned this month capacity'});
                    }
                    
                });
                
                
            }
        }; 
    });    
}(app,jQuery));

