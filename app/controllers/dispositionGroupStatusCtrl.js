/**
 * @fileOverview disposition master controller to manage disposition group 
 * @author Abhishek agrawal
 * @version 1.0
 */

var app = app || {};

(function (app,$){
    
    app.controller('dispositionGroupStatusCtrl', ['$scope','utilityService','Session','$rootScope','baseUrl','AuthService','modalService','user_auth',function ($scope,utilityService,Session,$rootScope,baseUrl,AuthService,modalService,user_auth){
        
        $scope.title = 'Disposition Group Status';
        
        $scope.topStatusList = [];
       
        $scope.statuses = [];
        
        $scope.sub_statuses = [];
        
        $scope.addGroupStatus = true;
        $scope.editGroupStatus = false;
        
        $scope.fetchStatusList = function (){
            var response = utilityService.getDispositionStatusList();
            response.then(function (promise){$scope.statuses = promise.data;});
        };
        
        $scope.fetchSubStatusList = function (){
            var response = utilityService.getDispositionSubStatusList();
            response.then(function (promise){$scope.sub_statuses = promise.data;});
        };
        
        $scope.fetchStatusList();
        $scope.fetchSubStatusList();
        
        // Add status 
       
        $scope.add_status = {
            title       :   '',
            error       :   '',
            parent_id   :   null,
            errorClass  :   ''
        };
       
        $scope.parentStatus = function (){
           
            var request = utilityService.getParentDispositionStatus();
            request.then(function (request){
                
                if(request.data.success == 1){
                    $scope.topStatusList = request.data.data;
                    $scope.topStatusList.unshift({
                        id : null, status_title : 'Select Parent Status'
                    });
                    
                }
            }, function (requestError){
                
            });
        };
        
        $scope.parentStatus();
        
        $scope.savestatus = function (data){
            
            if(data.title == ''){
                data.error = 'Status Title is required';
                data.errorClass = 'parsley_error';
                return false;
            }
            
            $scope.add_status.error = '';
            $scope.add_status.errorClass = '';
            
            var req_promise = utilityService.saveDispositionStatus(data);
            
            req_promise.then(function (promise){
                
                if(promise.data.success == 1){
                    $scope.add_status.title = '';
                    $scope.add_status.parent_id = null;
                    $scope.fetchStatusList();
                    $scope.fetchSubStatusList();
                    alert('Status saved successfully');
                }else{
                    alert('Status couldn\'t be saved');
                }
                
            },function (promiseError){
                
            });
            
        };    
        
        $scope.showChilds = function (childs){
            modalService.title = 'Child Disposition Status';
            modalService.size = 'modal-sm';
            modalService.showFooter = false;
            modalService.data = childs;
            modalService.templateUrl = baseUrl + 'templates/child_disposition_status_list.html';
            $('#my-modal').modal('show');
        };
        
        /**
         * Scope modal property to hold editable status data
         */
        $scope.editStatus = {
            status_id : null,
            status_title : '',
            parent_status_id : null,
            error : '',
            errorClass : '',
            dropdownError : '',
            dropdownErrorClass : ''
        };
        
        $scope.resetEditStatus = function (){
          
            $scope.editStatus.id = null;
            $scope.editStatus.status_title = '';
            $scope.editStatus.parent_status_id = null;
            $scope.editStatus.error = '';
            $scope.editStatus.errorClass = '';
            $scope.editStatus.dropdownError = '';
            $scope.editStatus.dropdownErrorClass = '';
            
        };
        
        /**
         * Edit function common for both status and sub status items to edit
         * @param {object} data 
         * @param {string} relation parent child relation
         */
        
        $scope.edit =  function (data,relation){
            
            $scope.editGroupStatus = true;
            $scope.addGroupStatus = false;
            
            if(relation === 'parent'){
                $scope.editStatus.status_id = data.status_id;
                $scope.editStatus.status_title = data.status_title;
                $scope.editStatus.parent_status_id = null;
                $scope.editStatus.relation = relation;
            }else{
                $scope.editStatus.status_id = data.id;
                $scope.editStatus.parent_status_id = data.parent_id;
                $scope.editStatus.status_title = data.sub_status_title;
                $scope.editStatus.relation = relation;
            }
        };
        
        
        $scope.updateStatus = function (status_data){
            
            $scope.editStatus.error = '';
            $scope.editStatus.errorClass = '';
            $scope.editStatus.dropdownError = '';
            $scope.editStatus.dropdownErrorClass = '';
           
            if(status_data.status_title === '' && status_data.relation ==='parent'){
                $scope.editStatus.error = 'Status Title is required';
                $scope.editStatus.errorClass = 'parsley_error';
                return false;
            }
           
            if(status_data.relation === 'child' && status_data.status_title === ''){
                $scope.editStatus.error = 'Status Title is required';
                $scope.editStatus.errorClass = 'parsley_error';
                return false;
            }
           
            if(status_data.relation === 'child' && status_data.parent_status_id === null){
                $scope.editStatus.dropdownError         = 'Please select parent status';
                $scope.editStatus.dropdownErrorClass    = 'parsley_error';
                return false;
            }
            
            var update = utilityService.editDispositionStatus(status_data);
            
            update.then(function (promise){
                
                if(promise.data.success == 1){
                    alert('Status updated successfully');
                    $scope.fetchStatusList();
                    $scope.fetchSubStatusList();
                    $scope.parentStatus();
                    $scope.resetEditStatus();
                    $scope.editGroupStatus = false;
                    $scope.addGroupStatus = true;
                }else if(promise.data.success == 0){
                    if(angular.isDefined(promise.data.message)){
                        alert(promise.data.message);
                    }
                }else if(promise.data.success == -1){
                    
                    angular.forEach(promise.data.errors, function (value,key){
                        
                        switch(key){
                            case 'title':
                                $scope.editStatus.error = value;
                                $scope.editStatus.errorClass = 'parsley_error';
                                break;
                                
                            case 'parent_status_id':
                                $scope.editStatus.dropdownError = value;
                                $scope.editStatus.dropdownErrorClass = 'parsley_error';
                                break;
                                
                            case 'status_id':
                                break;
                        }
                    });   
                }
            }, function (promiseError){
                
            });
        };
        
        $scope.changePanel = function (mode){
            
            if(mode === 'add'){
                $scope.resetEditStatus();
                $scope.editGroupStatus  = false;
                $scope.addGroupStatus   = true;
            }else{
                $scope.editGroupStatus = true;
                $scope.addGroupStatus = false;
            }
        };
        
         /**
          * To change state of disposition active/Inactive
          */
        $scope.makeEnableDisable = function (disposition_id, new_state){
            
            utilityService.changeDispositionState(disposition_id, new_state).then(function (response){
                if(response.data == 1){
                    alert('Disposition state successfully updated');
                    $scope.fetchStatusList();
                }else{
                    alert('Could not change the disposition state at this time');
                    return false;
                }
            });
        };
        
        $scope.bindActivityStatus = function (disposition_id, activity_state){
                utilityService.changeDispositionActivityStatus(disposition_id, activity_state).then(function (response){
                if(response.data == 1){
                    alert('Disposition Activity state successfully updated');
                    $scope.fetchStatusList();
                }else{
                    alert('Could not change the disposition activity state at this time');
                    return false;
                }
            });
        };
        
    }]);
    
})(app,jQuery);