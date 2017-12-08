
/**
 * LoginCtrl For App
 */

var app = app || {};

( function ( app, $ , bmh_pusher ) {

	app.controller ( 'loginCtrl', ['$scope', 'appUrls', 'application_blocks', '$window', '$location', 'validationService', '$http', 'AuthService', 'user_auth', '$route','$timeout','Session', function ( $scope, appUrls, application_blocks, $window, $location, validationService, $http, AuthService, user_auth, $route, $timeout , Session) {

        
            // Disconnect From Pusher
            bmh_pusher = new pusherClient();
            bmh_pusher.disconnectPusher();
			
			// Application layout configuration 
			angular.element ( '#content-layout' ).css ( {height: 'auto'} );

			$scope.userInfo = {};
			
			$scope.userInfo.loginType = 'email';

			$scope.login_loader_image_url = 'stuffs/images/ellipsis.gif';

			$scope.loginButton = {
				text: 'Login',
				loaderUrl: '<img src="stuffs/images/ellipsis.gif"/>'
			};

            // hide quick access on login page 
//            $scope.changeQuickAcessVisibility(show_quick_acces);
        
			$scope.disableLogin = {}; // Stores length of the userInfo object 

			$scope.$watch ( 'userInfo', function ( val ) {

				if ( val.username === undefined || val.username === '' || val.password === undefined || val.password === '' ) {
					$scope.disableLogin = true;
				}
				else {
					$scope.disableLogin = false;
				}
			}, true );

			$scope.$watch ( 'userInfo.username', function ( val ) {
				angular.element ( '#login-form-error' ).remove ();
			}, true );

			$scope.$watch ( 'userInfo.password', function () {
				angular.element ( '#login-form-error' ).remove ();
			}, true );


			/**
			 * User login function 
			 * @param {object} el
			 * @param {object} data
			 * @returns {Boolean}
			 */
			$scope.login = function ( el, data ) {
				
				// check user session 
				
				el.stopPropagation ();
				el.target.innerHTML = $scope.loginButton.loaderUrl;

				if ( Object.keys ( data ).length < 2 ) {
					$scope.loginError = 'Either username or password is missing';
					angular.element ( '#login-form-error' ).remove ();
					angular.element ( '#user-thumbnail-image' ).after ( '<span id="login-form-error">' + $scope.loginError + '</span>' );
					angular.element ( '#login-form-error' ).css ( {
						color: '#F00',
						position: 'relative',
						bottom: '0px',
						top: '0px',
						left: '1px',
						padding: '8px',
						backgroundColor: '#FFFFFF',
						width: '15%'
					} );
					el.target.innerHTML = $scope.loginButton.text;
				}
				else if ( data.username === '' || data.password === '' ) {
					$scope.loginError = 'Either username or password is missing';
					angular.element ( '#login-form-error' ).remove ();
					angular.element ( '#user-thumbnail-image' ).after ( '<span id="login-form-error">' + $scope.loginError + '</span>' );
					angular.element ( '#login-form-error' ).css ( {
						color: '#F00',
						position: 'relative',
						bottom: '3px',
						top: '0px',
						left: '10px',
						padding: '8px',
						backgroundColor: '#FFFFFF',
						width: '22%',
						border: '0.1em solid #000'
					} );
					el.target.innerHTML = $scope.loginButton.text;
				}
				else {
					
					if ( ! validationService.email ( $scope.userInfo.username ) && $scope.userInfo.loginType === 'email') {
						// Throw invalid email address error
						$scope.loginError = 'Please enter a valid email address';
						angular.element ( '#login-form-error' ).remove ();
						angular.element ( '#user-thumbnail-image' ).after ( '<span id="login-form-error">' + $scope.loginError + '</span>' );
						angular.element ( '#login-form-error' ).css ( {
							color: '#F00',
							position: 'relative',
							bottom: '3px',
							top: '0px',
							left: '20px',
							padding: '8px',
							backgroundColor: '#FFFFFF',
							width: '22%',
							border: '0.1em solid #000'
						} );
						el.target.innerHTML = $scope.loginButton.text;
						return false;
					}
					else {

						// send data to server for further authentication 

						var loginResponse = $http ( {
							url: appUrls.baseUrl + 'apis/login.php',
							method: 'POST',
							data: $scope.userInfo
						} );

						loginResponse.then ( function ( response ) {

							if ( response.data.success === 1 ) {
								var employee = response.data.employee;
                                
                                // start pusher
                                $scope.startPusher(employee.id);
                                
                                
                                // Store user in session service
                                Session.createUser(employee);
                                
								$location.path ( '/home' ); // Go to home page
							}
							else if ( response.data.success === - 1 ) {
								// form errors
							}
							else {

								$scope.loginError = response.data.errors;
								angular.element ( '#login-form-error' ).remove ();
								angular.element ( '#user-thumbnail-image' ).after ( '<span id="login-form-error">' + $scope.loginError + '</span>' );
								angular.element ( '#login-form-error' ).css ( {
									color: '#F00',
									position: 'relative',
									bottom: '3px',
									top: '0px',
									left: '19px',
									padding: '8px',
									backgroundColor: '#FFFFFF',
									width: '22%',
									border: '0.1em solid #000'
								} );
								el.target.innerHTML = $scope.loginButton.text;
								return false;
							}
						}, function ( error ) {

						} );
					}
				}
			};

			$scope.changeSidebarAppearence ( 'left', false ); // do not load left navigation panel/ block when login page is appeared

			$scope.left_nav_template_url = application_blocks.left_sidebar_path;

			// Hide main top header for login page
			$scope.toggleApplicationHeader ( false );

			// Make the content layout full width for login page 
			angular.element ( '#content-layout' ).css ( {
				width: '100%'
			} );
			
		}] );

} ) ( app, jQuery, bmh_pusher);