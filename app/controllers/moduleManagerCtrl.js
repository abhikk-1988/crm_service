/**
 * @fileOverview To manage application modules 
 * @author : Abhishek Agrawal
 * @version: 1.0
 */

var app = app || {};

(function (app,$){
    
    app.controller('moduleManagerCtrl', ['$scope','$rootScope','$location','Session','utilityService','$filter','modalService','baseUrl','user_auth', function ($scope,$rootScope,$location,Session,utilityService,$filter,modalService,baseUrl,user_auth){
           
        $scope.modules = [];
        
        $scope.addNew = {
            title: 'Add New Module',
            size : 'sm',
            templateUrl : baseUrl + 'templates/add_new_module.html',
            showFooter : false
        };
        
        $scope.getAllModules = function (){
            
            var promise = utilityService.getAllModules();
            
            promise.then(function (response){
                
                if(response.data.success == 1){
                    $scope.modules = response.data.data;
                }
            });
        };
        
        $scope.getAllModules();
        
        
        $scope.getParentModuleTitle = function (parent_id,modules){
          
            if(parent_id === null || parent_id == 0){
                return 'NA';
            }else{
                
				
                var parent_module = $filter('filter')(modules,{id : parent_id}, true);
				
				if( parent_module.length > 0){
					return parent_module[0].title;
				}
            }
        };
        
        $scope.setEnableDisable = function (module,event){
            
            if(module.markAsDisable == 0){
                
                // Make it disable 
                var disable = utilityService.enableDisableModule(module.id,1);
                
                disable.then(function (response){
                    
                    if(response.data.success == 1){
                        angular.element(event.currentTarget).removeClass('checkbox_success').addClass('checkbox_danger');
                    }
                    $scope.getAllModules();
                });
                
            }else{
                
                // Make it enable 
                var enable = utilityService.enableDisableModule(module.id,0);
                
                enable.then(function (response){
                    
                    if(response.data.success == 1){
                        angular.element(event.currentTarget).removeClass('checkbox_danger').addClass('checkbox_success');
                    }
                    $scope.getAllModules();
                });
            }
            
        };
        
        $scope.deleteModule = function (module,action){
            
            var mark_as_delete = null; 
            
            if(module.markAsDelete == 0){
                mark_as_delete = 1;
            }else{
                mark_as_delete = 0;
            }
            
            var reply = confirm('Are you sure you wish to '+action+' the module ' + module.title+'?');
            
            if(reply){
                var promise = utilityService.deleteModule(module.id,mark_as_delete);
                
                promise.then(function (response){
                    
                    if(parseInt(response.data.success) === 1){
                        $scope.getAllModules();   
                    }
					
					alert(response.data.message);
					
                });
            }
        };
        
        /* To Display tree view */
        
        $scope.displayHeirarchyView = function (module){
            
            modalService.title = 'Tree View';
            modalService.templateUrl = baseUrl + 'templates/treeView.html';
            modalService.size = 'modal-sm';
            modalService.showFooter = false;
            
            $scope.treeViewData = [];
            
            var treeViewPromise = utilityService.getTreeView(module.id);
            treeViewPromise.then(function (response){

                $scope.treeViewData.push(response.data.data);
                angular.element('#tree').treeview({data: $scope.treeViewData});
            }); 
            
            $('#my-modal').modal('show');
        };

        // function to edit module
        
        $scope.editModule = function (module){
            
            modalService.title = 'Edit Module';
            modalService.templateUrl = baseUrl + 'templates/edit_module.html';
            modalService.size = 'modal-md';
            modalService.showFooter = false;
            module.parent_title = $scope.getParentModuleTitle(module.parent, $scope.modules);
            modalService.data = module;
            $('#my-modal').modal('show');
        };
        
    }]);    
})(app,jQuery);