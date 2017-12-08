/**
 *  @fileOverview Priviledge controller to manage modules with employee designations 
 **/

 var app = app || {};
 
 (function (app){
     
     app.controller('managePriviledgeCtrl', ['$scope','Session','$rootScope','$routeParams','utilityService','$location','designationService','user_auth', function ($scope,Session,$rootScope,$routeParams,utilityService,$location,designationService,user_auth){
        
        /* When no designation id provided */
        if($routeParams.designation_id === undefined){
            $location.path('home');
        }     
             
        /* Model properties */
        $scope.designation_id = $routeParams.designation_id; 
        
        // hold collections of modules assigned to selected designation 
        $scope.designation_modules  = [];
        $scope.unassigned_modules   = [];     
        
        $scope.assigned = false;     
             
        var name_promise = utilityService.getDesignationName($routeParams.designation_id);    
        // Resolve name of the designation from designation id 
        name_promise.then(function (response){
            
            if(response.data.success == 1){
                $scope.designation_name = response.data.data;
            }else{
                $scope.designation_name = '';
            }
        });
        
        $scope.getDesignationModules = function (){
            var designation_module_promise = utilityService.getDesignationModules($routeParams.designation_id);
        
            designation_module_promise.then(function (response){

                if(response.data.success == 1){
                    $scope.designation_modules =  response.data.data;
                }
            });
        };
        
        $scope.getDesignationModules();
        
        // Change permissions
        $scope.setPermission = function (event,module){
            
            var permission_value = angular.element(event.currentTarget).val();
            var is_checked = angular.element(event.currentTarget).prop('checked');
            
            if(is_checked){
                
                // Add the checkbox value in the module permission
                module.permission = parseInt(module.permission) + parseInt(permission_value);
            }else{
                // Decrease the module permission value on unchecked state of checkbox by the value of the checkbox 
                module.permission = parseInt(module.permission) - parseInt(permission_value);
            }
            
        };
        
        
        // Update assignment of module to designation 
        $scope.updateModuleAssignment = function (event,module){
            
            var is_checked = angular.element(event.currentTarget).prop('checked');
            
            if(is_checked){
                module.assign = 1;
            }else{
                module.assign = 0;
            }
        };
        
        // Update the modules changes for designation 
        $scope.updatePermission = function (modules){
            
           var promise =   designationService.updateDesignationModules(modules);
           
           promise.then(function(response){
               
                if(response.data.success == 1){
                    $scope.updateResponse = '<div class="alert alert-success">'+response.data.message+'</div>';
                }else{
                    $scope.updateResponse = '<div class="alert alert-danger">'+response.data.message+'</div>';
                }
                $scope.getDesignationModules();
           });
           
        };
        
        $scope.dismiss = function (event){
            
            angular.element(event.currentTarget).remove();
        };
        
        $scope.openAddPanel = function (){
            $scope.getUnassignedDesignationModules();
            
        };
        
        $scope.getUnassignedDesignationModules = function (){
            
            var promise =  utilityService.getUnAssignedModules($routeParams.designation_id);
            promise.then(function (response){
                
                if(response.data.data.length === 0){
                    $scope.assigned = 0;
                    alert('No modules to add');
                    return false;
                }
                $scope.assigned = !$scope.assigned;
                $scope.unassigned_modules = response.data.data;
            });
        };
        
        $scope.addNew = function (module){
            
            if(!module.assign){
                alert('Please assign the module');
                return false;
            }
            
            var save_module_promise = designationService.addNewModule(module);
            save_module_promise.then(function (response){
                
                if(response.data.success == 1){
                    
                    $scope.getDesignationModules();
                    $scope.getUnassignedDesignationModules();
                }else{
                    alert('Module could not be added'); return false;
                }
            });
        };
        
     }]);
     
 })(app);