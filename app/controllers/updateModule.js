/**
 * @fileOverview update module 
 * 
 */

var app = app || {};

(function (app){
    
    app.controller('updateModule', ['$scope','modalService','utilityService','$filter', function ($scope,modalService,utilityService,$filter){
       
        $scope.module_id = null;
        
        // Edit module object
        $scope.editmodule = [];
        
        $scope.$watch(function (){
            
            $scope.editmodule = modalService.data;
            
        },true);  
        
        $scope.parentModules = [];
        
        $scope.getParentModules = function (){
            
            var response =  utilityService.getParentModules();
            
            response.then(function (response){
                
                if(response.data.success == 1){
                    
                    $scope.parentModules = response.data.data;
                }
            });
        };
        
        $scope.getParentModules();
        
        $scope.updateParent = function (selected_parent){
            
            if(selected_parent === null){
                $scope.editmodule.parent = null;
                $scope.editmodule.parent_title = 'Select Module';
                return false;
            }
            
            $scope.editmodule.parent = selected_parent.id;
            $scope.editmodule.parent_title = selected_parent.title;
        };
        
        $scope.$watch('editmodule.title', function (val){           
            $scope.editmodule.link = $filter('lowercase')(val).replace(/\s/g, "-");
            angular.element('.title_help_block').text('');
            angular.element('.link_help_block').text('');
        });
        
        $scope.resetForm = function (){
            $scope.editodule.title = '';
            $scope.editmodule.link = '';
            $scope.editmodule.parent = null;
            $scope.editmodule.parent_title = '';
        };
        
        $scope.submitForm = function (data){
            
            var create_module = utilityService.createModule(data);
            
            angular.element('.help-block').text('');
            
            create_module.then(function (response){
                
                if(response.data.success == 1){
                    $scope.response_message = response.data.message;
                    $scope.resetForm();
                }else if(response.data.success == -1){
                    
                    var errors = response.data.errors;
                    
                    angular.forEach(errors, function(val,key){
                       
                        var error_block_element = '.'+key+'_help_block';
                        angular.element(error_block_element).text(val);
                    });
                    $scope.response_message = response.data.message;
                }else{
                    $scope.response_message = response.data.message;
                }
                
            }, function (error){
                
            });
        };
        
    }]);
    
})(app);