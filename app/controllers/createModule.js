/**
 * @fileOverview To create a new module
 */

var app = app || {};


(function (app){
    
    app.controller('createModule', ['$scope','Session','utilityService','$filter', function ($scope,Session,utilityService,$filter){
        
        $scope.newModule = {
            title : '',
            parent_module : '',
            parent_module_title : '',
            link : ''
        };
          
        $scope.response_message = '';    
            
        $scope.parentModules = [];
        
        $scope.getParentModules = function (){
            
            var response =  utilityService.getParentModules();
            
            response.then(function (response){
                
                if(response.data.success == 1){
                    
                    $scope.parentModules = response.data.data;
                }
            });
        };
        
        $scope.setParent = function (parent){
            
            if(parent === null){
                $scope.newModule.parent_module = null;
                $scope.newModule.parent_module_title = 'Select Parent Module';
                return false;
            }
            
            $scope.newModule.parent_module = parent.id;
            $scope.newModule.parent_module_title = parent.title;
            angular.element('.parent_module_help_block').text('');
        };
        
        $scope.$watch('newModule.title', function (val){           
            $scope.newModule.link = $filter('lowercase')(val).replace(/\s/g, "-");
            angular.element('.title_help_block').text('');
            angular.element('.link_help_block').text('');
        });
        
        $scope.resetForm = function (){
            $scope.newModule.title = '';
            $scope.newModule.link = '';
            $scope.newModule.parent_module = null;
            $scope.newModule.parent_module_title = '';
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
        
        $scope.getParentModules();
        
    }]);
    
})(app);