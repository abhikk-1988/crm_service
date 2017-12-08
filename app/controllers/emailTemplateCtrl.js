/**
 * Controller : Email Temail_templatesemplate Ctrl
 * 
 * 
 */

var app = app || {};

(function (app, $){
	
	app.controller ('emailTemplateCtrl', function ($scope, user_session, templates, $location, httpService, baseUrl, $route){
	
		$scope.user				= user_session; // logged in user session data
		$scope.email_templates	= templates; // Collection of email templates 

		$scope.viewMessage = function (msg){
			$scope.popup_message = msg;
		};
		
		$('#email_msg_view_modal').on('hidden.bs.modal', function (e) { 
			$scope.popup_message = '';
		});
		
		$scope.editEmailTemplate = function (template_id){
			
			$location.path('edit_email_template/'+template_id);
		};
		
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
				
					url : baseUrl + 'apis/helper.php?method=changeDeleteStatusOfEmailTemplate&params=template_id:'+ template_id+'/value:'+value,
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
		
	});
	
} (app,jQuery));