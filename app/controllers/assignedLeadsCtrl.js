/**
 * Assigned Leads Controller 
 */

var app = app || {};

(function (app, $){
	
	app.controller('assignedLeadsCtrl', function ($scope, $log, $http, baseUrl, disposition_status_list, $filter, Session){
		
		$scope.loginUser = Session.getUser(); // Container for logged in user data
	
		/* Assigned Leads modal property */
		$scope.assigned_leads = [];
		
		$scope.disposition_status_list = disposition_status_list;
		
		/* Get assigned leads of logged in user */
		
		$scope.getAssignedLeads = function (){
			
			$http({
				url : baseUrl + 'apis/get_assigned_leads.php',
				method : 'POST',
				data : $.param({user_id : $scope.loginUser.id}),
				headers : {'Content-Type' : 'application/x-www-form-urlencoded; charset=utf-8'}
			}).then(function (success){
				
				if(parseInt(success.data.success) === 1){
					
					$scope.assigned_leads = success.data.data;
				}else{
					$scope.no_lead_found = success.data.message;
				}
			});
		};
		
		$scope.getAssignedLeads();
		
		/**
		 * 
		 * @returns {undefined}
		 */
		$scope.getStatusTitle = function (status_id){
		
			var status_data = $filter('filter')($scope.disposition_status_list, {id : status_id}, true);
		
			if(status_data[0].parent_status === null){
				return (status_data[0].status_title);
			}else{
				return (status_data[0].sub_status_title);
			}
		};
		
	});
	
} (app,jQuery));