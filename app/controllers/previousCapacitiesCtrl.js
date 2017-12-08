/**
 * package CRM
 * 
 * @fileOverview controller previousCapacitiesCtrl.js
 */


var app = app || {};

( function (app, $) {
	
	app.controller('previousCapacitiesCtrl', function ($scope, $routeParams, $log, httpService, user_session, $filter, baseUrl, user_name, $http, dateUtility){
	
		$scope.user						= user_session; // Login user session data 
		$scope.previous_capacities		= []; 
		$scope.showAssignedProjectsCol	= true;
		
		if($routeParams.designation_slug === 'sales_person'){
			$scope.showAssignedProjectsCol = false;
		}
		
		$scope.current_month	=  new Date().getMonth ();
		$scope.current_year		= new Date().getFullYear ();
		$scope.previous_year;
		$scope.previous_month;
		
		// previous month calculation 
		if( parseInt($scope.current_month) === 0 ){
			
			$scope.previous_month = 11;
			$scope.previous_year = $scope.current_year - 1;
		}else{
			$scope.previous_month = $scope.current_month  - 1;
			$scope.previous_year  = $scope.current_year;
		}
		
		// making designation text from designation slug
		$scope.user_for_showing_previous_capacites = {
			id : $routeParams.user_id,
			designation_slug : $routeParams.designation_slug,
			getDesignationFromSlug : function (){
				
				return this.designation_slug.split('_').map(function (words){
					var first_char = words.charAt(0).toUpperCase();
					var str; 
					for(var i=0;i<words.length;i++){
						if(i===0){
							str = first_char;
						}else{
							str += words[i];
						}
					}
					return str;
				}).join(' ');
				
			},
			
			name : user_name
		};
		
		/**
		 * HTTP call to previous months capacities 
		 * @type type
		 */
		var capacity_response = $http({
			url : baseUrl + 'apis/get_all_previous_month_capacities.php',
			method : 'POST',
			data : $.param({
				user_id : $scope.user_for_showing_previous_capacites.id,
				designation_slug : $scope.user_for_showing_previous_capacites.designation_slug,
				previous_month : $scope.previous_month,
				previous_year : $scope.previous_year
			}),
			headers : {
				'Content-Type': 'application/x-www-form-urlencoded'
			}
		});
		
		capacity_response.then(function (successResponse){
			$scope.previous_capacities = successResponse.data;
		});
		
		
		$scope.openProjectsPopup = function (data){
			
			if(Object.keys(data).length === 0){
				alert('No Projects to show'); return false;
			}
			
			$scope.project_with_capacities = data;
			// open modal to show up projects with capacities 
			$('.project_wise_capacity_model').modal('show');
			
		};
		
		// on hidden of bsmodal
		$('.project_wise_capacity_model').on('hidden.bs.modal', function (e) { 
			
			$scope.project_with_capacities = [];
		});
		
	});
	
} (app, jQuery));
