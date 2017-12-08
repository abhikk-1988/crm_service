/**
 * Disposition group assignment to user (employee)
 */

var app = app || {};

( function ( app, $ ) {

	var controller = function ( $scope, $http, $log, utilityService, $routeParams, $route ) {

		$scope.group_list = [];

		$scope.employee_id = $routeParams.id;

		$scope.role = $routeParams.role;

		$scope.group = {
			id: null,
			text: ''
		};

		$scope.getGroupList = function () {

			var promise = utilityService.get_disposition_group_only ();
			promise.then ( function ( response ) {
				$scope.group_list = response.data;
				console.log ( $scope.group_list );
			} );
		};

		$scope.getGroupList ();

		$scope.getAssignedGroup = function () {

			var group_response = utilityService.getEmployeeDispositionGroup ( $scope.employee_id );

			group_response.then ( function ( response ) {

				if ( angular.isDefined ( response.data.group_id ) ) {

					$scope.group.id = response.data.group_id;
				}
			} );
		};

		$scope.getAssignedGroup ();

		$scope.save_group = function ( selected_group ) {

			if ( selected_group.id === null ) {
				$scope.group.error = 'parsley_error';
				alert ( 'Please select disposition group' );
				return false;
			}

			var request = utilityService.saveEmployeeDispositionGroup ( selected_group.id, $scope.employee_id );

			request.then ( function ( response ) {

				if ( parseInt(response.data.success) === 1 ) {

					var notification_config = {
						message: 'Group has been added successfully',
						class: ['alert', 'alert-success', 'bottom-right']
					};
					
					$scope.notify ( notification_config ); // user notification calling 
				}
				else {

					if ( parseInt(response.data.success) === - 1 ) {

						var notification_config = {
							message: 'Insufficient data provided',
							class: ['alert', 'alert-danger', 'center-aligned']
						};
						$scope.notify ( notification_config );
					}
					else {
						var notification_config = {
							message: 'Group can\'t be added. Please check if there is some errors',
							class: ['alert', 'alert-danger', 'center-aligned']
						};
						$scope.notify ( notification_config );
					}
				}
			} );
		};
	};

	app.controller ( 'dispositionGroupAssignmentCtrl', controller );
} ) ( app, jQuery );