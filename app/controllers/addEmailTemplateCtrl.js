/**
 * Controller : Add New Email Template Ctrl
 */

var app = app || {};

(function (app, $){
	
	app.controller ('addEmailTemplateCtrl', function ($scope, user_session, email_users, email_template_events, httpService, baseUrl, $route, $window, $filter, $location){
		
		$scope.page_title				= 'Add New Email Template';
		$scope.email_users				= email_users;
		$scope.email_template_events	= email_template_events;
		$scope.hide_user_list			= false;
		$scope.user						= user_session; 
		$scope.submit_btn_label			= 'Save';
		$scope.to						= [];
		$scope.cc						= [];
		$scope.bcc						= [];
		
		
		$scope.email_template = {};
		
		$scope.email_template.toUsers = [];
		$scope.email_template.ccUsers = [];
		$scope.email_template.bccUsers = [];
		
		// summernote editor options propertyIsEnumerable
		$scope.summernote_options = {
			height: 300,
			focus: true,
			airMode: true,
			toolbar: [
				['edit',['undo','redo']],
				['headline', ['style']],
				['style', ['bold', 'italic', 'underline', 'superscript', 'subscript', 'strikethrough', 'clear']],
				['fontface', ['fontname']],
				['textsize', ['fontsize']],
				['fontclr', ['color']],
				['alignment', ['ul', 'ol', 'paragraph', 'lineheight']],
				['height', ['height']],
				['table', ['table']],
				['insert', ['link','picture','video','hr']],
				['view', ['fullscreen', 'codeview']],
				['help', ['help']]
			]
		};
		
		
		$scope.clearError = function (element_id){
			var dom_element = '#' + element_id;
			angular.element(dom_element).removeClass ('parsley_error').next().html('');
		};
		
		$scope.resetFormErrors = function (){
			var element_classes = ['email_category','email_event','email_subject'];			
			angular.forEach (element_classes, function(val){
				var element_id = '#' + val;
				angular.element(element_id + '_help_block').html('');
				angular.element(element_id).removeClass('parsley_error');
			});
		};
		
		$scope.saveTemplate = function (){
			
			$scope.resetFormErrors();
			var error_flag	= false;
			var markupStr	= $('#summernote').summernote('code');
			$scope.email_template.message = markupStr;
			
			// email category validation
			if(!$scope.email_template.category){
				angular.element('#email_category').addClass('parsley_error');
				angular.element('#email_category_help_block').html('Email category is required');
				error_flag = true;
			}
			
			// email event validation
			if(!$scope.email_template.event){
				angular.element('#email_event').addClass('parsley_error');
				angular.element('#email_event_help_block').html('Email event is required');
				error_flag = true;
			}
			
			// email subject validation
			if(!$scope.email_template.subject){
				angular.element('#email_subject').addClass('parsley_error');
				angular.element('#email_subject_help_block').html('Email subject is required');
				error_flag = true;
			}
			
			if(error_flag){
				return false;
			}else{
				
				if($scope.to.length){
					$scope.email_template.toUsers = [];
					
					angular.forEach($scope.to, function (val){
						var temp_to_user = $filter('filter')($scope.email_users, {id : val}, true);
						$scope.email_template.toUsers.push( temp_to_user[0] );
					});
				}else{
					$scope.email_template.toUsers = [];
				}
				
				if($scope.cc.length){	
					
					$scope.email_template.ccUsers = [];
					
					angular.forEach($scope.cc, function (val){
						var temp_cc_user = $filter('filter')($scope.email_users, {id : val}, true);
						$scope.email_template.ccUsers.push( temp_cc_user[0] );
					});
				}else{
					$scope.email_template.ccUsers = [];
				}
				
				if($scope.bcc.length){					
					
					$scope.email_template.bccUsers = [];
					
					angular.forEach($scope.bcc, function (val){
						var temp_bcc_user = $filter('filter')($scope.email_users, {id : val}, true);
						$scope.email_template.bccUsers.push( temp_bcc_user[0] );
					});
				}else{
					$scope.email_template.bccUsers = [];
				}
				
				var save_email_template = httpService.makeRequest({
					
					url : baseUrl + 'apis/save_email_template.php',
					method : 'POST',
					data : {
						template_data	: $scope.email_template,
						user_id			: $scope.user.id,
						mode			: 'add'
					}
				});
				
				save_email_template.then(function (successCallback){
					
					if(parseInt(successCallback.data.success === 1)){
						
						$scope.notify({
							message : successCallback.data.message,
							class : ['alert alert-success center-aligned']
						});
				
						$window.location.reload();		
					}else{
						$scope.notify({
							message : successCallback.data.message,
							class : ['alert alert-warning center-aligned']
						});
					}
				}, function (errorCallback){
					alert('Some error has been occurred.');
				});
			}
		};
		
		$scope.back_to_list = function (){
			$location.path('/email-template-system');
		};
	});
	
} (app,jQuery));