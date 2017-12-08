/**
 * Custom directive to send internal/ external email communication email
 */

var app = app || {};

(function(app,$){
	
	app.directive('sendMail', function (baseUrl, httpService, $http){

		return {
			restrict : 'EA',
			templateUrl : baseUrl + 'app/directives/templates/send_mail.html',
			scope : {
				data : '='
			},
			link : function (iElement, iAttr, scope){
				
			},
			controller : function ($scope, $filter, $compile){
				
				$scope.email_list		= [];
				
				$scope.email			= {};
				$scope.email.subject	= '';
				$scope.template_id		= '';
				
				$scope.to_email_users	= [];
				$scope.cc_email_users	= [];
				$scope.bcc_email_users	= [];
				
				$scope.getEmailList = function (){
					 httpService.makeRequest({
						url : baseUrl + 'apis/get_email_templates.php',
						method : 'GET'
					}).then(function (successCallback){
						$scope.email_list = successCallback.data;
						
						console.log($scope.email_list);
						
					});
				};
				
				$scope.getEmailList ();
				
				$scope.resetEmailUsers = function (){
					
					$scope.to_email_users	= [];
					$scope.cc_email_users	= [];
					$scope.bcc_email_users	= [];	
					$scope.to_user_email	= '';
					$scope.cc_user_email	= '';
					$scope.bcc_user_email	= '';
					
					var span_container_elements = ['to_input_tags','cc_input_tags','bcc_input_tags'];
					
					angular.forEach (span_container_elements, function (id_string){
						
						var tags = 	$('#'+id_string).find ('span');
						$(tags).remove (); // Remove all tags at all
					});
					
				};
				
				$scope.fetchMail = function (id){
					
					$scope.resetEmailUsers ();
					
					if(id === null){
						
						$('#summernote_editor').summernote('code','');
					}
					
					var $mail = $filter('filter')($scope.email_list,{template_id : id}, true);
					
					if($mail[0].to_users.length > 0){
						
						angular.forEach ($mail[0].to_users, function (val, key){
							$scope.to_email_users.push(val.email);
						});
					
						var target = angular.element('#to_input_tags');
						$scope.createEmailTags (target, $scope.to_email_users,'to');
					}else{
						$scope.resetEmailUsers();
					}
					
					if($mail[0].cc_users.length > 0){
						angular.forEach ($mail[0].cc_users, function (val, key){
							$scope.cc_email_users.push(val.email);
						});
					
						var target = angular.element('#cc_input_tags');
						$scope.createEmailTags (target, $scope.cc_email_users,'cc');
					}else{
						$scope.resetEmailUsers ();
					}
					
					if($mail[0].bcc_users.length > 0){
						angular.forEach ($mail[0].bcc_users, function (val, key){
							$scope.bcc_email_users.push(val.email);
						});
					
						var target = angular.element('#bcc_input_tags');
						$scope.createEmailTags (target, $scope.bcc_email_users,'bcc');
					}else{
						$scope.resetEmailUsers ();
					}
					
					// Insert mail message in editor 
					// Compile message string so that we can use interpolation in this 
					var $compiled_messsage = $compile($mail[0].message_body)($scope);
					
					$('#summernote_editor').summernote('code', $compiled_messsage);
					
					// Insert mail subject 
					$scope.email.subject = $mail[0].subject;
					
				};
				
				$scope.add_to_tag = function (email_id, element){
					
					var target = element.currentTarget;
					
					if($(target).val() === ''){
						return false;
					}
					
					var user_type = $(target).data('user_type');
					
					var $tags_container = angular.element('#to_input_tags');
					
					var $tag = $compile('<span class="tag"><span>'+email_id+'</span> <a href="#" ng-click="remove_email_user(\''+user_type+'\',\''+email_id+'\',$event)" title="Removing tag">x</a> </span>')($scope);
					
					$($tag).insertBefore (target);
					
					$scope.to_user_email = '';
					
					$scope.to_email_users.push(email_id);
					
				};
				
				$scope.add_cc_tag = function (email_id, element){
					
					var target = element.currentTarget;
					
					if($(target).val() === ''){
						return false;
					}
					
					var user_type = $(target).data('user_type');
					
					var $tags_container = angular.element('#cc_input_tags');
					
					var $tag = $compile('<span class="tag"><span>'+email_id+'</span> <a href="#" ng-click="remove_email_user(\''+user_type+'\',\''+email_id+'\',$event)" title="Remove Email">x</a> </span>')($scope);
					
					$($tag).insertBefore (target);
					
					$scope.cc_user_email = '';
					
					console.log( 'CC user email - ' + $scope.cc_user_email);
					
					$scope.cc_email_users.push(email_id);
					
				};
				
				$scope.add_bcc_tag = function (email_id, element){
					
					var target = element.currentTarget;
					
					if($(target).val() === ''){
						return false;
					}
					
					var user_type = $(target).data('user_type');
					
					var $tags_container = angular.element('#bcc_input_tags');
					
					var $tag = $compile('<span class="tag"><span>'+email_id+'</span> <a href="#" ng-click="remove_email_user(\''+user_type+'\',\''+email_id+'\',$event)" title="Remove Email">x</a> </span>')($scope);
					
					$($tag).insertBefore (target);
					
					$scope.bcc_user_email = '';
					
					$scope.bcc_email_users.push(email_id);
					
				};
				
				$scope.remove_email_user = function (user_type, email, element){
					
					var target = element.target;
					var target_parent = target.parentElement;
					
					if(user_type === 'to'){
						var index_value  = $scope.to_email_users.indexOf (email);
						$scope.to_email_users.splice (index_value,1);
					}
					
					if(user_type === 'cc'){
						var index_value  = $scope.cc_email_users.indexOf (email);
						$scope.cc_email_users.splice (index_value,1);
					}
					
					if(user_type === 'bcc'){
						var index_value  = $scope.bcc_email_users.indexOf (email);
						$scope.bcc_email_users.splice (index_value,1);
					}
					
					$(target_parent).remove();
				};
				
				$('#send_mail_interface').on('hidden.bs.modal', function (e){	
					
					$scope.resetEmailUsers ();
					$('#summernote_editor').summernote('reset');
					$scope.email.subject	= '';
					$scope.template_id		= '';
				});
				
				/**
				 * Function to create email tags in respective category
				 * @param {Object} target
				 * @param {Array} tag_collection
				 * @param {String} user_type
				 * @returns {undefined}
				 */
				$scope.createEmailTags = function (target, tag_collection, user_type){
					
					angular.forEach (tag_collection, function (val){
						var $tag = $compile('<span class="tag"><span>'+val+'</span> <a href="#" ng-click="remove_email_user(\''+user_type+'\',\''+val+'\',$event)" title="Removing tag">x</a> </span>')($scope);
						$( target ).prepend( $tag );
					});
			
				};
				
				/**
				 * Function to send mail
				 * @returns {undefined}
				 */
				$scope.send = function (){
					
					if($scope.to_email_users.length < 1){
						alert('Please specify to email users');
						return false;
					}
					
					if($scope.email.subject === undefined || $scope.email.subject === ''){
						
						var confirm_blank_subject = confirm('Are you sure to continue with blank subject');
						
						if(!confirm_blank_subject){
							$('#email_subject').focus ();
							return false;
						}
					}
					
					var $emptyMessage = $('#summernote_editor').summernote('isEmpty');
					if($emptyMessage){
						alert('Please write your email message');
						$('#summernote_editor').summernote('focus');
						return false;
					}
					
					var request_data = {};
					
					// Email message 
					request_data.message_body	= $('#summernote_editor').summernote('code');
					request_data.to_users		= $scope.to_email_users;
					request_data.cc_users		= $scope.cc_email_users;
					request_data.bcc_users		= $scope.bcc_email_users;
					request_data.subject		= $scope.email.subject; 
					
					var send_mail = $http({
						url : baseUrl + 'apis/send_communication_mail.php',
						method : 'POST',
						data : $.param(request_data),
						headers : {'Content-Type': 'application/x-www-form-urlencoded'}
					});
					
					send_mail.then(function (successCallback){
						
						alert(successCallback.data.message);
						
						$( document.body ).trigger( "click" ); // force triggering click on document to close popup modal
						
						$('#send_mail_interface').modal('hide');
						
					});
				};
			}
		};
	});
	
}(app,jQuery)) ;