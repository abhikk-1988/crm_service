/**
 * 
 */

var app = app || {};

(function (app, $){
	
	app.controller ('capacities_asm', function ($scope, $route, capacities,$filter, dateUtility, user_session, $location){
		
		$scope.user = user_session;
		
		$scope.capacities = capacities;
		
		$scope.showProjects = function (element){
			angular.element(element.currentTarget).next().toggle();
		};
		
		$scope.current_month	= dateUtility.current_month_in_textual_representation('full');
		$scope.current_year		= dateUtility.current_year; 
		
		/**
		 * 
		 * @param {type} user_id
		 * @returns {undefined}
		 */
		$scope.editAsmCapacity = function (user_id){
			
			$location.path('/edit_capacity_asm/'+ user_id);
		};
		
	});
	
	/**
	 * custom directive to disable unable add capacity button on basis of user current month capacity 
	 */
	app.directive('canAddCapacity', function (){
		
		return {
			
			restrict : 'A',
			scope : {
				data : '=',
			},
			link : function (scope,element,attr){
				
				var current_month = parseInt(new Date().getMonth ());
				
				if(scope.data.total_capacity > 0 && (current_month === parseInt(scope.data.capacity_month))){
					element.prop('disabled',true);
				}
				
				element.prop ('title','Capacity already added for this month');
			}
		};
	});
	
	/**
	 * Directive to check state of add new capacity button
	 */
	app.directive('isEnable', function (httpService, baseUrl, $location){
		
		return {
		
			restrict : 'A',
			scope : {
				
			},
			link : function (scope, element, attr){
				
				var button_state = httpService.makeRequest({
					url		: baseUrl + 'apis/helper.php?method=is_all_asm_users_has_capacity',
					method	: 'GET'
				});
				
				button_state.then(function (successCallback){
					
					if(successCallback.data.btn_state === 'enable'){
						element.prop('disabled', false);
						element.prop('title', 'Click to add new capacity');
					}else{
						element.prop('disabled', true);
						element.prop('title', 'All users has assigned capacities for this month. Continue to edit individually');
					}
					
					element.on('click', function (){
						$location.path('/capacity-area-sales-manager');
						scope.$apply();
					});
					
				});
			}
		};
		
	});
	
	
	/**
	 * custom directive
	 */
	app.directive('isEditable', function (){
		
		return {
		
			restrict:'A',
			scope : {
				month : '@',
				year : '@'
			},
			link: function(scope, tElement, tAttr){
				
				var date_obj = new Date();
				
				if(parseInt (scope.month) === parseInt(date_obj.getMonth ()) && parseInt(scope.year) === parseInt(date_obj.getFullYear ())){
					tElement.prop('disabled',false);
				}else{
					
					// we have to manipulate title according to the value recieved for month
					var title;
					
					if(scope.month === ''){
						title = 'Capacity not added for this month';
					}else{
						title = 'Can not edit capacity of past month';
					}
					
					tElement.prop({
						disabled: true,
						title: title
					});
//					tElement.css({display:'none'});
				}
			}
		};
		
	});
	
	app.filter('handleNull', function (){
		
		return function (value){
			
			if(value === undefined || value === null){
				return 'NA';
			}else{
				return value;
				
			}
		};
		
	});
} (app, jQuery));