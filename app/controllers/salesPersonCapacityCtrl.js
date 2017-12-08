/**
 * Package: CRM
 * 
 * @fileOverview Controller salesPersonController
 */

var app = app || {};

(function (app, $){
	
	app.controller ('salesPersonCapacityCtrl', ['$scope','user_session','sales_person_capacities','$location','dateUtility', function($scope, user_sesison, sales_person_capacities, $location,dateUtility){
        
			$scope.user = user_sesison;
        
			$scope.sales_person_capacities = sales_person_capacities;
        
            console.log('SALES PERSON CAPACITIES');
            console.log($scope.sales_person_capacities);
			
			$scope.current_month	= dateUtility.current_month_in_textual_representation('full');
        
			$scope.current_year		= dateUtility.current_year; 
			
			$scope.addSPCapacity= function (sp){
			
				var url = '/add_sales_person_capacity/'+sp.id+'/'+sp.manager.id+'/'+sp.manager.capacity+'/add';
				$location.path(url);
			};
			
			/**
			 * function to redirect user to go on edit page of sales person capacity
			 */
			
			$scope.editSpCapacity = function (sp){
				var url = '/edit_sales_person_capacity/'+sp.sales_person_id+'/'+sp.manager.id+'/'+sp.manager.capacity;
				$location.path(url);
			};
	}]);
	
	app.filter('handleNull', function (){
		
		return function (value){
			
			if(value === undefined || value === null){
				return 'NA';
			}else{
				return value;
				
			}
		};
		
	});
	
	app.directive('isDisabled', function (){
		
		return {
			restrict : 'A',
			scope: {
				capacity : '@',
				usercapacity : '@'
			},
			link : function (scope, ielement, $attr){
				if(scope.capacity === ''){
					ielement.prop('disabled', true);
					ielement.prop('title','Manager\'s capacity is not defined for this month');
				}
				
				if((scope.usercapacity !== '') && scope.capacity != ''){
					ielement.prop('disabled', true);
					ielement.prop('title','Already added capacity');
				}
			}
		};
	});
	
	/**
	 * Custom function for edit button 
	 */
	
	app.directive('canEdit', function (){
		return {
		
			restrict : 'A',
			scope :{
				user_capacity : '@capacity1',
				manager_capacity : '@capacity2'
			},
			link: function (scope, tElement, tAttr){
				
				if(scope.manager_capacity === ''){
					tElement.prop({
						disabled : true,
						title : 'Manager\'s capacity is not defined for this month'
					});
				}
				
				if(scope.manager_capacity !== '' && scope.user_capacity === ''){
					tElement.prop({
						disabled : true,
						title : 'Add capacity for this month'
					});
				}
				
			}
		};
	});
	
}(app, jQuery));