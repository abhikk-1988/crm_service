/**
 * Add new message communication template 
 */

var app = app || {};

(function (app, $){

	app.controller('addMessageTemplateCtrl', ['$scope','message_template_events','$compile','httpService','user_session','baseUrl','$route','$location', function ($scope,message_template_events, $compile, httpService, user_session, baseUrl, $route, $location){
			
		$scope.user = user_session;
			
		$scope.page_title = 'Add new messaage template';

		$scope.message_event_templates = message_template_events;	
			
		$scope.message_template = {};
		
		$scope.default_message_recipient_count = 1;
		
		$scope.message_template.numbers = [];
		
		$scope.submit_btn_label = 'Add Template';
		
		$scope.addNumber = function (number){
		
			if(number){
				// split string from comma
				$scope.message_template.numbers = number.split(',');
			}else{
				$scope.message_template.numbers = [];			
			}			
		};
		
		$scope.clearError = function (element_id){
			var dom_element = '#' + element_id;
			var help_block = '#' + element_id + '_help_block';
			
			angular.element(dom_element).removeClass ('parsley_error');
			angular.element(help_block).html('');
		};
		
		$scope.saveTemplate = function (){
		
			// Validations 
			
			var error_flag = false;
			
			if($scope.message_template.category === undefined){
				angular.element('#message_category').addClass('parsley_error');
				angular.element('#message_category_help_block').html('Please select message category');
				error_flag = true;
			}
			
			if($scope.message_template.event === undefined){
				angular.element('#message_event').addClass('parsley_error');
				angular.element('#message_event_help_block').html('Please select message event');
				error_flag = true;
			}
			
			if($scope.message_template.message_text === undefined){
				angular.element('#message_text').addClass('parsley_error');
				angular.element('#message_text_help_block').html('Message text is required');
				error_flag = true;
			}
			
			if(error_flag){
				return false;
			}else{
				
				var save_template = httpService.makeRequest({
					
					url : baseUrl + 'apis/add_message_template.php',
					method : 'POST',
					data : {
						message_data	: $scope.message_template,
						user_id			: $scope.user.id,
						mode : 'add'
					}
				});
				
				save_template.then(function (succcessCallback){
					
					if(parseInt(succcessCallback.data.success === 1)){
						
						$scope.notify({
							class:['alert','alert-success','center-aligned'],
							message : succcessCallback.data.message
						});
						
						// redirect user to message templates page 
                        $location.path('message-communication-templates');
						
					}else{
						$scope.notify({
							class:['alert','alert-warning','center-aligned'],
							message : succcessCallback.data.message
						});
					}
				});
				
			}
			
		};
	
		$scope.back_to_list = function (){
			$location.path('/message-communication-templates');
		};
	}]);
	
}(app, jQuery));