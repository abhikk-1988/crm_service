/**
 * package CRM
 * 
 * @fileOverview directive roundRobinSwitch.js 
 */

var app = app || {};

(function (app, $){
	
	app.directive('roundRobinSwitch', function (httpService, baseUrl){
		
		var button = {};
		
		button.restrict		= 'EA';
		
		button.replace		= false;
		
		button.scope		= {};
		
		button.templateUrl	= baseUrl + 'app/directives/templates/round_robin_switch.html';
		
		button.link			= function (scope, tElement, tAttr){
			tElement.addClass('pull-right');
		};
		
		button.controller = function ($scope){
			
			$scope.status = false; // default button state is Off
			
			$scope.init = function(){
				
				httpService.makeRequest({
					url		: baseUrl + 'apis/getAppSettings.php',
					method	: 'POST',
					data	: {
						setting : 'is_round_robin_enable'
					}
				}).then(function (successCallback){
					$scope.status = successCallback.data;
				}, function (errorCallback){
					$scope.status = false;
				});
			};
			
			$scope.init();
  
			$scope.changeStatus = function(){
				
				httpService.makeRequest({
					url : baseUrl + 'apis/changeAppSettings.php',
					method : 'POST',
					data : {
						setting : 'is_round_robin_enable',
						state	: $scope.status
					}
				}).then(function(success){
					
					if(parseInt (success.data.success)){
						$scope.init ();
					}
				}, function (error){
					console.log(error);
				});
			};
			
		};
	
		return button;
	});
	
	
	app.directive('autoAssignSplLeadSwitch', function (httpService, baseUrl){
		
		var widget = {};
		
		widget.restrict = 'EA';
		
		widget.replace = false;
		
		widget.scope	= {};
		
		widget.templateUrl = baseUrl + 'app/directives/templates/auto_assign_spl_lead_switch.html';
		
		widget.link = function (scope, tElement, tAttr){
			tElement.addClass('pull-right');
		};
		
		widget.controller = function ($scope){
			
			$scope.status = false; // default button state is Off
			
			$scope.init = function(){
				
				httpService.makeRequest({
					url		: baseUrl + 'apis/getAppSettings.php',
					method	: 'POST',
					data	: {
						setting : 'auto_assign_spl_lead'
					}
				}).then(function (successCallback){
					$scope.status = successCallback.data;
				}, function (errorCallback){
					$scope.status = false;
				});
			};
		
			$scope.init();
			
			$scope.changeStatus = function(){
				
				httpService.makeRequest({
					url : baseUrl + 'apis/changeAppSettings.php',
					method : 'POST',
					data : {
						setting : 'auto_assign_spl_lead',
						state	: $scope.status
					}
				}).then(function(success){
					
					if(parseInt (success.data.success)){
						$scope.init ();
					}
				}, function (error){
					console.log(error);
				});
			};
		};
		
		return widget;
	});
	
} (app, jQuery));
