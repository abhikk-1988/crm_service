/**
 * @fileOverview disposition master controller to manage disposition group 
 * @author Abhishek agrawal
 * @version 1.0
 */

var app = app || {};

(function (app,$){
    
    app.controller('dispositionGroupCtrl', ['$scope','utilityService','Session','$rootScope','baseUrl','AuthService','modalService','user_auth',function ($scope,utilityService,Session,$rootScope,baseUrl,AuthService,modalService,user_auth){
        
        $scope.title = 'Disposition Groups';
        
        $scope.disposition_groups = [];
        
        // Flags to switch add/ edit group block 
        $scope.addGroupBlock    = true;
        $scope.editGroupBlock   = false;
        
        $scope.getDispositionGroups = function (){
            
            var request = utilityService.getDisposition_groups();
            
            request.then(function (request){
                
                if(request.data.success == 1){
                    $scope.disposition_groups = request.data.data;
                }
            }, function (requestError){
                
            });
            
        };
        
        $scope.getDispositionGroups();
        
        $scope.swapBlock = function (flag){
            
            if(flag === 'add'){
                $scope.editGroupBlock = false;
                $scope.addGroupBlock = true;
            }else{
                $scope.addGroupBlock = false;
                $scope.editGroupBlock = true;
            }
        };
        
        
        $scope.newDisposition = {
            title : '',
            error : '',
            errorClass : ''
        };
        
        $scope.editExistingDisposition = {
            title       : '',
            error       : '',
            errorClass  : ''
        };
        
        $scope.saveGroup = function (group){
            
            if(group.title === ''){
                $scope.newDisposition.error = 'Disposition Group Title is required';
                $scope.newDisposition.errorClass = 'parsley_error';
                return false;
            }
            
            $scope.newDisposition.error         = '';
            $scope.newDisposition.errorClass    = '';
            
            group.action = 'add';
            
            var response = utilityService.saveDispositionGroup(group);
            
            response.then(function (request){
                
                if(request.data.success == -1){
                    
                    // Form error 
                    
                    var error = request.data.errors;
                    $scope.newDisposition.error = error.title;
                    $scope.newDisposition.errorClass = 'parsley_error';
                }else if(request.data.success == 1){
                    $scope.getDispositionGroups();
                    $scope.newDisposition.title = '';
                    alert('Group added successfully');
                }else{
                    if(angular.isDefined(request.data.message)){
                        alert(request.data.message);
                    }
                }
                
            },function (requestError){
                
            });
            
        };
        
        $scope.editGroup = function (data){
            
            $scope.swapBlock('edit');
            
            $scope.editExistingDisposition.title = data.group_title;
            $scope.editExistingDisposition.id = data.id;
            $scope.editExistingDisposition.action = 'edit';
        };
        
        $scope.updateGroup = function (group_data){
            
            if(group_data.title === ''){
                $scope.editExistingDisposition.error = 'Disposition Group Title is required';
                $scope.editExistingDisposition.errorClass = 'parsley_error';
                return false;
            }
            
            $scope.editExistingDisposition.error         = '';
            $scope.editExistingDisposition.errorClass    = '';
            
            var response = utilityService.updateDispositionGroup(group_data);
            
            response.then(function (request){
                
                if(request.data.success == -1){
                    
                    // Form error 
                    
                    var error = request.data.errors;
                    $scope.editExistingDisposition.error = error.title;
                    $scope.editExistingDisposition.errorClass = 'parsley_error';
                }else if(request.data.success == 1){
                    $scope.getDispositionGroups();
                    $scope.editExistingDisposition.title = '';
                    alert('Group updated successfully');
                }else{
                    if(angular.isDefined(request.data.message)){
                        alert(request.data.message);
                    }
                }
                
            }, function (requestError){
                
            });
            
        };
        
        $scope.showStatusHeirarchy = function (status_data){
            
            if(status_data.length === 0){
                return false;
            }
            
            $scope.treeViewData = [];
            
            angular.forEach(status_data, function (val,key){
               $scope.treeViewData.push($scope.makeHeirarchyData(val)); 
            });
            
            modalService.title          = 'Assigned Group Status';
            modalService.size           = 'modal-lg';
            modalService.showFooter     = false;
            modalService.templateUrl    = baseUrl + 'templates/treeView.html';
            $('#my-modal').modal('show');
            angular.element('#tree').treeview({data: $scope.treeViewData});
        };
        
        $scope.makeHeirarchyData = function (obj){
            
            var heirarchy_data = {};
            heirarchy_data.text = obj.status_title;
            heirarchy_data.selectable = false;
            heirarchy_data.backColor = "#3BB4C8";
            heirarchy_data.icon = "glyphicon glyphicon-folder-open";
            heirarchy_data.state = {
                expanded : false,
                checked:true
            };
            heirarchy_data.nodes = [];
            
            if(obj.childs.length > 0){
                
                for(var i=0;i<obj.childs.length;i++){
                    
                    var nodes = {text : obj.childs[i].status};
                    
                    heirarchy_data.nodes.push(nodes);
                }
            }
            return heirarchy_data;
        };
        
    }]);
    
})(app,jQuery);