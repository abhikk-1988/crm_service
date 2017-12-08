/**
 * @fileOverview Bootstrap popup modals 
 */

var app = app || {};

(function (app,$){
    
    app.directive('popupModals',function (modalService){
        
        var modal = {};
        modal.restrict  = 'EA';
        modal.replace   = true;
        modal.scope     = {
            modal : '=', // Configuration object of modal popup
            data  : '@'
        };
        modal.link = function ($scope,element,attr){
            
            element.bind('click', function (){
                
                $scope.$apply(function (){
                    
                    if(angular.isDefined($scope.modal.size)){
                        modalService.size = $scope.modal.size;
                    }else{
                        modalService.size = '';
                    }
                    
                    if(angular.isDefined($scope.modal.title)){
                        modalService.title = $scope.modal.title;
                    }else{
                        modalService.title = '';
                    }
                    
                    if(angular.isDefined($scope.modal.template)){
                        modalService.template = $scope.modal.template;
                    }else{
                        modalService.template = '';
                    }
                    
                    if(angular.isDefined($scope.modal.templateUrl)){
                        modalService.templateUrl = $scope.modal.templateUrl;
                    }else{
                        modalService.templateUrl = '';
                    }
                    
                    if(angular.isDefined($scope.modal.showFooter)){
                        modalService.showFooter = $scope.modal.showFooter;
                    }else{
                        modalService.showFooter = false;
                    }
                    
                    if(angular.isDefined($scope.modal.showCloseBtn)){
                        modalService.showCloseBtn = $scope.modal.showCloseBtn;
                    }else{
                        modalService.showCloseBtn = false;
                    }
                    
                    if(angular.isDefined($scope.modal.showSaveBtn)){
                        modalService.showSaveBtn = $scope.modal.showSaveBtn;
                    }else{
                        modalService.showSaveBtn = false;
                    }
                    
                    if(angular.isDefined($scope.data)){
                        modalService.data = $scope.data;
                    }
                    
                    $('#my-modal').modal('show');
                });
            });
        };
        return modal;
    });
    
    app.directive('myModals', function (modalService,appUrls){
        
        var modal = {};
        
        modal.restrict = 'E';
        
        modal.controller = function ($scope){
            
            $scope.$watch(function (){
                $scope.title        = modalService.title;
                $scope.sizeClass    = [modalService.size];
                $scope.template     = modalService.template;
                $scope.showCloseBtn = modalService.showCloseBtn;
                $scope.showSaveBtn  = modalService.showSaveBtn;
                $scope.showFooter   = modalService.showFooter;
                $scope.templateUrl  = modalService.templateUrl; 
            });

            $scope.modalMessage = function (msg){
                $scope.$emit('messageFromModal', {
                    message : msg
                });
            };
            
            $('#my-modal').on('hidden.bs.modal', function (event) {
                modalService.clearModal();
            });
            
        };
        
//        modal.scope = {
//            
//        };
        
        modal.link = function (scope,element,attr){
            
            scope.$watch(function (){
                scope.data = modalService.data;
            });
            
        };
        
        modal.templateUrl = appUrls.appUrl + 'directives/templates/modal.html';
        
        return modal;
    });
    
})(app,jQuery);