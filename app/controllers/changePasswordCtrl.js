/**
 * Package : CRM
 * 
 * Controller : changePasswordCtrl
 */

var app = app || {};

(function (app, $){
	
	app.controller ('changePasswordCtrl', function ($scope, $route, httpService, $location, $window, baseUrl, $timeout){
		
		if(!$scope.user.id || !$scope.user.email){
			$scope.logout(); // logout user if session is not there
		};
		
		/**
		 * User password object 
		 */
		$scope.userPassword = {
			
			current_password : {
				value : '',
				is_required : true,
				is_error : true,
				error : ''
			},
			new_password : {
				value : '',
				is_required : true,
				is_error : true,
				error : ''
			},
			confirm_new_password : {
				value : '',
				is_required : true,
				is_matched : false,
				is_error : true,
				error : ''
			}
			
		};
		
		/**
		 * Watch on current password
		 */
		$scope.$watch('userPassword.current_password.value', function (val){
			
			if(val !== ''){
				$scope.userPassword.current_password.is_error = false;
			}else{
				$scope.userPassword.current_password.is_error = true;
			}
		});
		
		/**
		 * Watch on new password
		 */
		$scope.$watch('userPassword.new_password.value', function (val){
			
			if(val !== ''){
				
				if($scope.userPassword.confirm_new_password.value === val){
					$scope.userPassword.confirm_new_password.error = '';
				}
				
				$scope.userPassword.new_password.is_error = false;
			}
			else{
				$scope.userPassword.new_password.is_error = true;
			}
		});
		
		/**
		 * Watch on confirm new password
		 */
		$scope.$watch('userPassword.confirm_new_password.value', function (val){
			
			if(val !== ''){
				
				// Check if confirm new password value is not exactly equal to new password 
				
				if(val !== $scope.userPassword.new_password.value){
					$scope.userPassword.confirm_new_password.error = 'Confirm password is not same as new password';
					$scope.userPassword.confirm_new_password.is_error = true;
				}else{
					$scope.userPassword.confirm_new_password.error = '';
					$scope.userPassword.confirm_new_password.is_error = false;
				}
			}else{
				$scope.userPassword.confirm_new_password.is_error = true;
			}
		});
		
		/**
		 * Function to set new password 
		 * @returns {undefined}
		 */
		$scope.setNewPassword = function (password){
			
			var data = {
				current_password : password.current_password.value,
				new_password : password.new_password.value,
				confirm_password : password.confirm_new_password.value,
				user_id : $scope.user.id,
				user_email: $scope.user.email
			};
			
			var change_password = httpService.makeRequest({
				url : baseUrl + 'apis/change_password.php',
				method : 'POST',
				data: data
			});

			change_password.then(function (response){
				
				if(parseInt(response.data.success) === 1){
//					$scope.server_message = response.data.message;
					
					$scope.notify({
						class : ['alert','alert-success','bottom-right'],
						message : response.data.message
					});
					
					// logging out user after 5 seconds 
					$timeout(function (){
						alert('You are logging out from session');
						$scope.logout();
					}, 5000);
					
				}else{
					
					// Show errors to user  
					$scope.server_message = response.data.message;
					angular.forEach (response.data.errors, function (val, key){
						
						$scope.userPassword[key].error = val;
						
						if(key === 'account_error'){
							alert('Unautorized Error');
							$scope.logout(); // Logout user immediately
						}
					});
				}
			});
		};
		
		/**
		 * Function to check password strength 
		 * 
		 */
		
		$scope.checkPasswordStrength = function (password){
			
			/**
			 * Password strength will be checked against some paramaeters like "legnth" , "not inclusion of atleast 1 special character", "not inclusion of atleast 1 Alphabet in combination of numberic numbers" 
			 * But currently we only check length of passsord as strength 
			 **/
			
			if( parseInt(password.length) < 4){
				
				$scope.userPassword.new_password.error = 'Passwod too short. New password should be 4 characters long.';
				$scope.userPassword.new_password.is_error = true;
				return false;
			}
			
			$scope.userPassword.new_password.is_error = false;
			$scope.userPassword.new_password.error = '';
			
		};
	});
	
} (app, jQuery));