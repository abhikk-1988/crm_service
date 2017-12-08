/**
 * @fileOverview designation controller to add/ manage emplyee designation 
 * @author Abhishek Agrawal
 * @version 1.0
 */

var app = app || {};

(function (app){
    
    app.controller('designationCtrl',['$scope','$http','$location','$rootScope','employeeService','$filter','user_auth','$route', function ($scope,$htpp,$location,$rootScope,employeeService,$filter,user_auth, $route){
       
        $scope.page_title = 'Add New Designation';
        
        $scope.designations = new Array();
        
        // Flags to show or hide designation panel elements 
        $scope.enableEdit = false;
        $scope.enableAdd = true;
        $scope.enableListing = true;
        
        $scope.fetchDesignations = function (){
            var designations_response = employeeService.getAllDesignation();
        
            designations_response.then(function (response){

                if(response.data.success === 1){
                    $scope.designations = response.data.designations;
                }else{
                    $scope.designations = response.data.designations;
                }
            }, function (error){

            });
        };
        
        $scope.fetchDesignations();
        
        $scope.$watch('designation', function (val){
            
            if(val !== undefined || val !== ''){
                $scope.error_template = null;
                angular.element('.input_text').removeClass('red-border');
            }
        });
        
		/***
		 * Add new designation
		 * @param {type} value
		 * @param {type} element
		 * @returns {undefined}
		 */
		
        $scope.saveDesignation = function (value,element){
            
            if(value === undefined || value === ''){
                
                angular.element('.input_text').addClass('red-border');
                $scope.error_template = '<div class="danger mt">Please enter designation</div>';
            }else{
                
                var add_emp_promise =  employeeService.addNewDesignation(value,'add',null,null);
               
                add_emp_promise.then(function (response){
                   
                    var response_data = response.data;
                   
                    if(response_data.success === 1){
                       
                        $scope.response_message = '<div>'+response_data.message+'</div>';
                        $scope.designation = '';
                        $scope.fetchDesignations();
						$route.reload();
                    }else{
                        $scope.response_message = '<div>'+response_data.message+'</div>';
                        $scope.designation = '';
                    }
               }, function(error){
                   
               });
               
            }
        };
        
		
        $scope.switchForEdit = function (designation,el){
            
            var tbody = angular.element('.tlist tr').removeClass('active-row');
            angular.element(tbody).removeClass('active-row');
            var parent_tr = angular.element(el.currentTarget).closest('tr');
            angular.element(parent_tr).addClass('active-row');
            
            
            if(!$scope.enableEdit){
                
                $scope.enableAdd    =   false;
                $scope.enableEdit   =   true;
             
            }
             
            $scope.designation = designation.designation;
            $scope.designation_instance_id = designation.id;
        };
        
        $scope.resetForm = function (){
            
            $scope.designation              = null;
            $scope.designation_instance_id  = null;
            $scope.enableEdit               = false;
            $scope.enableAdd                = true;
        };
        
        $scope.editDesignation = function (designation_id,new_designation,disable){
          
            var edit_emp_promise =  employeeService.addNewDesignation(new_designation,'edit',designation_id,disable);
               
            edit_emp_promise.then(function (response){
                   
                var response_data = response.data;
                 
                 
                if(response_data.success === 1){

                    $scope.response_message = '<div>'+response_data.message+'</div>';
                    $scope.designation  = null;
                    $scope.designation_instance_id = null;
                    $scope.fetchDesignations();
                    $scope.resetForm();
                }else{
                    $scope.response_message = '<div>'+response_data.message+'</div>';
                    $scope.designation = '';
                   
                }
            }, function(error){

            });
        };
        
        $scope.managePrviledge = function (designation_id){
            
            //$location.path('priviledge/' + designation_id);
        };
        
        $scope.deleteDesignation = function (designation_id){
            
            delete_promise = employeeService.deleteDesignation(designation_id);
            
            delete_promise.then(function (response){
                alert(response.data.message);
                $scope.fetchDesignations();
            }, function (error){
                
            });
        };
    }]);
    
})(app);


