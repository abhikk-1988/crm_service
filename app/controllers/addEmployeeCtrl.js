/**
 * @fileOverview Contorller to add new employee
 */

var app = app || {};

( function ( app ) {

	app.controller ( 'addEmployeeCtrl', ['$scope', '$http', '$location', 'utilityService', 'validationService', 'employeeService', 'user_auth', 'assign_to_employees_list','$timeout', function ( $scope, $http, $location, utilityService, validationService, employeeService, user_auth, assign_to_employees_list, $timeout ) {

			$scope.reporting_to_employees  = assign_to_employees_list;

			/*Add New employee form object*/
			$scope.employee = {};

			$scope.employee.designation = {};

			$scope.employee.state = {};

			$scope.employee.city = {};

			$scope.cities = new Array ();

			$scope.states = new Array ();

			$scope.designation = new Array ();

			$scope.employee.isCreateLogin	= 1;
			$scope.employee.isSendSMS		= 1;

			var designation_req = utilityService.getDesignationList ();

			designation_req.then ( function ( response ) {

				if ( response.data.success === 1 ) {
					$scope.designation = response.data.designations;
				}
			} );

			var state_req = utilityService.getStateList ();
			state_req.then ( function ( response ) {
				$scope.states = response.data;
			} );

			// Fetch city list by state id 
			$scope.selectState = function ( state ) {

				if ( state === null ) {
					$scope.resetState ();
					return true;
				}

				var city_req = utilityService.getCityList ( state.state_id );
				city_req.then ( function ( response ) {
					$scope.cities = response.data;
				} );

				$scope.employee.state.label = state.state_name;
				$scope.employee.state.id = state.state_id;

				$scope.resetCity ();
				$scope.state_query = '';
			};

			$scope.resetState = function () {
				$scope.empoyee.state.label = '';
				$scope.employee.state.id = null;
				$scope.cities = [];
			};

			$scope.selectCity = function ( city ) {

				if ( city === null ) {
					$scope.resetCity ();
					return true;
				}

				$scope.employee.city.id = city.city_id;
				$scope.employee.city.label = city.city_name;
				$scope.city_query = '';
			};

			$scope.resetCity = function () {
				$scope.employee.city.id = null;
				$scope.employee.city.label = '';
			};

			$scope.selectDesignation = function ( data ) {

				$scope.employee.designation.text = data.designation;
				$scope.employee.designation.id = data.id;
				$scope.designation_query = '';
			};

			$scope.getUsernameAvailibility = function ( username ) {

				var username_avail = utilityService.checkAvailibility ( username );

				username_avail.then ( function ( response ) {

					if ( response.data.is_available === 1 ) {

						angular.element ( '#loginusername' ).css ( {
							color: 'green'
						} ).text ( 'Username is available' );
					}
					else {
						angular.element ( '#loginusername' ).addClass ( 'danger' ).text ( 'Username is not available' );
					}

				}, function ( error ) {

				} );

			};

			$scope.removeAttachCreateLogin = function ( el ) {

				var check_value = angular.element ( el.currentTarget ).prop ( 'checked' );
				if ( check_value ) {
					$scope.employee.isCreateLogin = 1;
				}
				else {
					$scope.employee.isCreateLogin = 0;
					$scope.employee.username = '';
				}
			};

			// set create login and send SMS checkboxes checked
			$scope.checkDefault = function () {
				angular.element ( '#sendsms_chk' ).attr ( 'checked', 'checked' );
			};

			$scope.generatePassword = function ()
			{
				var text = "";
				var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%&*()";

				for ( var i = 0; i < 7; i++ )
					text += possible.charAt ( Math.floor ( Math.random () * possible.length ) );

				$scope.employee.password = text;
			};

			$scope.validateEmail = function ( email, el ) {

				var email_validation_response = validationService.email ( email );

				if ( email_validation_response ) {
				
                    // Call to server to check availability of email id 
                    
                    angular.element(el.currentTarget).next().html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i> Please Wait...');
                    
                    var email_response = validationService.checkEmailAvailibility(email);
                    email_response.then(function (response){
                        
                        if( parseInt(response.data.success) === 1){
                            
                            if(response.data.is_email_exists){
                                var input = angular.element ( el.currentTarget );
                                angular.element ( input ).addClass ( 'red-border' ).next ().html('').text ( 'Email address already in use' ).addClass ( 'danger' );
                                return false;
                            }
                            else{
                                angular.element(el.currentTarget).next().html('');
                                return true;
                            }
                        }else{
                            angular.element(el.currentTarget).next().html('');
                        }
                    });
                        
                    return true;
				}
				else {

					var input = angular.element ( el.currentTarget );
					angular.element ( input ).addClass ( 'red-border' ).next ().text ( 'Invalid email id entered' ).addClass ( 'danger' );
					return false;
				}
			};

			$scope.validateNumber = function ( number, el ) {

				if ( ! angular.isNumber ( parseInt ( number ) ) || ! isFinite ( parseInt ( number ) ) ) {
					var input = angular.element ( el.currentTarget );
					angular.element ( input ).addClass ( 'red-border' );
					angular.element ( input ).next ().text ( 'Phone no. must be a valid number' ).addClass ( 'danger' );
					return false;
				}
				else{
                    
                    angular.element(el.currentTarget).next().html('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i> Please Wait...');
                    
                    // check existence of mobile number from server
                    
                    var is_mobile_exists = validationService.checkMobileNumberAvailibility(number);
                    is_mobile_exists.then(function(response){
                        
                        if( parseInt(response.data.is_exists) === 1){
                                angular.element(el.currentTarget).next().html('Mobile number already in use.').css({color: '#F00'});
                                angular.element(el.currentTarget).focus();
                        }
                        else{
                                angular.element(el.currentTarget).next().html('');
                                return false;
                        }
                    });
				}
			};

			/* Watcher on form inputs */

			$scope.$watch ( 'employee.email', function ( val ) {

				angular.element ( '#email-help-block' ).text ( '' ).removeClass ( 'danger' );
			} );

			$scope.$watch ( 'employee.phone', function ( val ) {

				angular.element ( '#phone-help-block' ).text ( '' ).removeClass ( 'danger' );
			} );

			/* ./*/

			$scope.saveEmployee = function ( employee ) {
				
            
				var res = employeeService.saveNewEmployee ( employee );

				res.then ( function ( response ) {

					if ( response.data.success === - 1 ) {
						// Handle form errors
						$scope.FormResponse = response.data.message;
						$scope.errors = response.data.errors;

						angular.element ( '.help-block' ).text ( '' );
						angular.forEach ( $scope.errors, function ( val, key ) {

							if ( ! angular.isUndefined ( key ) ) {
								var element = '';
								if ( key === 'username' ) {
									element = '.' + key + '-help-block';
								}
								else {
									element = '#' + key + '-help-block';
								}

								angular.element ( element ).text ( val ).addClass ( 'danger' );
							}

						} );

					}
					else if ( response.data.success === 0 ) {
						// Error handling 
                        alert(response.data.message);
						$scope.FormResponse = response.data.message;
						$scope.errors = null;
					}
					else {
						// Form is Ok
						$scope.resetEmployee ();
						$scope.FormResponse = response.data.message;
						if ( parseInt(response.data.email_sent) === 1 ) {
							$scope.emailSent = 'An Email has been sent to user of with login credentials ';
						}
						else {
							$scope.emailSent = 'Email can\'t be send';
						}

						// Alert notification 
						$scope.notify({
							class : ['alert','alert-success','center-aligned'], message : 'Employee has been successfully created.'
						});
					}
				}, function ( error ) {

				} );
			};

			/* Reset employee object */
			$scope.resetEmployee = function () {

				$scope.employee.firstname = null;
				$scope.employee.lastname = null;
				$scope.employee.email = null;
				$scope.employee.phone = null;
				$scope.employee.joining_date = null;
				$scope.employee.city = {};
				$scope.employee.state = {};
				$scope.employee.designation = {};
				$scope.employee.address1 = null;
				$scope.employee.address2 = null;
				$scope.employee.username = null;
				angular.element ( '#loginusername' ).text ( '' ).removeClass ( 'danger' );
			};

			$scope.checkDefault ();
			$scope.generatePassword ();

			$scope.disposition_group_list = [];
			
			/**
			 * Function to get disposition groups 
			 */
			$scope.getDispositionGroups = function (){
				
				var disposition_group = utilityService.getDisposition_groups();
				
				disposition_group.then(function (successResponse){
					
					if( parseInt(successResponse.data.success) === 1){
						$scope.disposition_group_list = successResponse.data.data;
					}
					
				});
			};
			
			$scope.getDispositionGroups ();

        
            $scope.checkEmail = function (email){
                
                if(typeof email === 'object' || !email){
                    alert('Email is should not be blank');
                    return false;
                }
                
            }    
        
		}] );

} ) ( app );