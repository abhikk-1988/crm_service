/**
 * Message Template controller 
 */

var app = app || {};

(function (app, $){
	
	app.controller('messageTemplatesCtrl', ['$scope','$location','$route','message_templates','user_session','httpService','baseUrl', function ($scope, $location, $route, message_templates, user_session, httpService, baseUrl){
	
		$scope.user = user_session;
		
		$scope.page_title = 'Message Communication Templates';
		
		$scope.message_templates = message_templates;
		
		$scope.delete_template = function (template_id, value){
			
			var status_text = '';
			
			if(parseInt(value) === 0){
				status_text = 'delete';
			}else{
				status_text = 'restore';
			}
			
			value = !parseInt(value); 
			
			var confirm_delete = confirm('Are you sure you want to '+ status_text +' this template?');
			
			if(confirm_delete){
				var delete_template = httpService.makeRequest({
				
					url : baseUrl + 'apis/helper.php?method=changeDeleteStatusOfMessageTemplate&params=template_id:'+ template_id+'/value:'+value,
					method: 'GET'
				});
				
				delete_template.then(function (successCallback){
					
					if(parseInt(successCallback.data)){
						
						alert('Template '+status_text+'d successfully');
					}else{
						alert('Template could not be '+status_text+'d at this time. Try again later');
					}
					
					$route.reload();
				});
			}else{
				return false;
			}
		};
		
		$scope.editMessageTemplate = function (template_id){
			
			$location.path('/edit_message_template/'+template_id);
		};
		
	}]);
}(app, jQuery));