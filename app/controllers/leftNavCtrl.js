
/**
 * Left Sidebar Controller
 * version 1.0
 * @author Abhishek Agrawal
 *  
 */

var app = app || '';

(function (app){
    
	app.controller('leftNavCtrl', ['$scope','$rootScope','AuthService','Session','$route','$location',function ($scope,$rootScope,AuthService,Session,$route,$location){
    
		$rootScope.$on('userSession', function (event,data){
			$scope.currentUser = data.user_session;        
		});
        
		$scope.currentUser = Session.getUser();
        
        /**
         * This function is not operational
         */
		$scope.displayMenu = function (menu,element){
            
				if(angular.isUndefined(menu)){
					return true;
				}
				
				var parent_container = angular.element('.navigation');
				var lists = angular.element(parent_container).children();
				
				angular.forEach ( lists, function (list_element){
					
					if( angular.element(list_element).hasClass('active_menu') ){
						
						$scope.closeOpenMenu (list_element);
						
					}
					
				});
				
				// add active class to current target 
				angular.element(element.currentTarget).addClass('active_menu');
				
				/*Creating submenu html template */
				var submenu_template = '<div class="sub-list"> ';         

				angular.forEach (menu, function(val,key){

					for(var i in val){
						submenu_template += '<div><a style="color:#000;" href="'+val[i].link+'">'+val[i].title+'<a/></div>';
					}
				});
		
				submenu_template += '</div>';
				/* submenu template end */
            
				var subMenuHideFlag = angular.element(element.currentTarget).attr('data-hide-sublist');
//				angular.element(element.currentTarget).find('span').after('<i class="fa fa-caret-up pull-right" ng-click="close_menu($event)"></i>');
				if(angular.isUndefined(subMenuHideFlag)){
					angular.element(element.currentTarget).after(submenu_template).attr('data-hide-sublist',true);
				}else{
					angular.element(element.currentTarget).next().remove();
					angular.element(element.currentTarget).removeAttr('data-hide-sublist');
				}
		};
        
        
		/*
		 * Function to close already open menus
		 */
		$scope.closeOpenMenu = function (element){
			angular.element(element).next('div .sub-list').remove();
			angular.element(element).removeAttr('data-hide-sublist');
		};
		
        
        $scope.noEvent = function (e,route_path){
            // Prevent click event raise to upto the dom tree
            $location.path(route_path);
            e.preventDefault();
        };
        
	}]);
})(app);




