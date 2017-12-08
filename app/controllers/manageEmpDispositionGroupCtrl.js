/**
 * Controller to manage disposition status assigned to employee 
 * 
 * 
 */

var app = app || {};

(function(app){
	
	app.controller('manageEmpDispositionGroupCtrl', function ($scope, $routeParams, Session, disposition_group, $filter, httpService, baseUrl, $route, employee_name){
		
		$scope.employee_name = employee_name; 
		
		$scope.employee_id = $routeParams.employee_id;
		
		$scope.disposition_group = disposition_group.data; // group data

		$scope.user = Session.getUser(); // current user 
		
		$scope.group_id = $routeParams.group_id; // Group id 		
		
		$scope.toggleContent = function (data){
			data.show_panel = !data.show_panel;
		};
		
		$scope.setParent = function (check_value, id){
			
			var check_status = check_value; 
			var child_element = angular.element('.child-'+id);

			angular.forEach ($scope.disposition_group.group_status, function (val,key){

				var child_obj_length = Object.keys(val.childs).length;
				if(parseInt(val.id) === parseInt(id) ){

					for(var i =0; i < child_obj_length; i++){
						val.childs[i].assigned = check_value; // assigning false state to child checkboxes 
					}
				}

			});			
		};
		
		$scope.setAssigned = function (assigned, clicked_by,id){
			
			var check_status_of_parent; 
			
			assigned = check_status_of_parent = !assigned;
		
			if(clicked_by === 'parent'){
				$scope.setParent (!check_status_of_parent, id);
			}
		};
		
		// function to save/ update employee disposition status 
		
		$scope.updateEmployeeDispositionStatus = function (data){
			
			// call to server and pass this data 
			var config = {
				url : baseUrl + 'apis/update_employee_disposition_group.php',
				method : 'POST',
				data : {
					status_data : data,
					employee_id : $scope.employee_id
				}
			};
			
			var promise = httpService.makeRequest(config);
			
			promise.then(function (success){
				
				if(parseInt(success.data.success) === 1){
					var notification = {message : success.data.message, class :  ['alert', 'alert-success', 'bottom-right']};
					$scope.notify(notification);
				}else{
					var notification = {message : success.data.message, class :  ['alert', 'alert-warning', 'bottom-right']};
					$scope.notify(notification);
				}
				
				// Reload current route
				$route.reload();
			});
		};
	});
	
} 
(app));