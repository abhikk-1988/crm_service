/**
 * @fileOverview Edit an existing employee
 * @author Abhishek Agrawal
 * @version 1.0
 */

 var app = app || {};

(function (app){
    
    app.controller('editEmployeeCtrl', ['$scope','$routeParams','$http','Session','employeeService','utilityService','$filter','user_auth', function ($scope,$routeParams,$http,Session,employeeService,utilityService,$filter,user_auth){
       
        $scope.employee_id = $routeParams.employee_id;
        
        
        // Single employee to update 
        $scope.employee = {};
        
		// current reporting to user 
		
		var currentReportingToUser = {};
		
        var emp = employeeService.getEmployee($scope.employee_id);
        
        emp.then(function (response){
            
            $scope.employee = response.data[0];
            
			currentReportingToUser.user_id = $scope.employee.reportingTo;
			currentReportingToUser.user_name = $scope.employee.reportingToEmployee;
			
            /* Populate state when employee is prepared */
            var state_request = utilityService.getStateList();
            state_request.then(function(response){
                $scope.states				= response.data;
                $scope.employeeStateLabel	= $scope.getStateName($scope.states, $scope.employee.state);
            });
            
            /* Render city list */
            // We have to call setCity function to set label for city of employee 
            
            $scope.fetchCities($scope.employee.state, $scope.employee.city,1);
            
        }, function (){
            
        });
        
        $scope.states = [];
        $scope.cities = [];
        $scope.employeeStateLabel   = '';
        $scope.employeeCityLable    = '';
        
        /*Employee Designation list */
        $scope.emp_designations = [];
        
        $scope.getEmployee = function (){
            var emp_designation_req = employeeService.getAllDesignation();
            emp_designation_req.then(function (response){
                $scope.emp_designations = response.data.designations;
            });
        };
       
        // getting employee object from server 
        $scope.getEmployee();
		
		/**
		 * Fetching Reporting Persons list 
		 * @param {type} state_source
		 * @param {type} state_id
		 * @returns {unresolved}
		 */
		
		$scope.reportingToUsers = [];
		
		$scope.fetchReportingToEmployees = function (){
			
			// Exclude current edit employee user from list 
			
			var reportingEmployees = utilityService.getAllEmployees();
			
			reportingEmployees.then(function (response){
				$scope.reportingToUsers = response.data;
				console.log($scope.reportingToUsers);
			});
		};
		
		$scope.fetchReportingToEmployees ();
		
        /* Helper function to get state lable by state id */
        $scope.getStateName = function (state_source,state_id){
           
            var filtered_state =  $filter('filter')(state_source,{state_id : state_id});
            return filtered_state[0].state_name;
        };
        
        /* Helper function to get city lable by city id */
        $scope.getCityName = function (city_source,city_id){
            var filtered_city =  $filter('filter')(city_source,{$ : city_id});
            return filtered_city[0].city_name;
        };
        
        /* Update employee state id */
        $scope.updateStateId = function (state){
            
            if(state == null){
                $scope.employee.state       = null;
                $scope.employeeStateLabel   = 'Select State';
                $scope.employeeCityLable    = 'Select City';
                $scope.employee.city        = null;
                $scope.cities = [];
                return false;
            }
            
            $scope.employee.state       = state.state_id;
            $scope.employeeStateLabel   = state.state_name;
            
            $scope.fetchCities(state.state_id,null);
        };
       
        /* function to render list of cities in dropdown */
        $scope.fetchCities = function (state_id,city_id,blank_city_label){
            
            var cities_req = utilityService.getCityList(state_id);
            
            cities_req.then(function (response){
                $scope.cities = response.data;
                
                if(blank_city_label != null){
                    $scope.employeeCityLable = $scope.getCityName($scope.cities, city_id);
                }else{
                    $scope.employeeCityLable = '';
                }
                
            });
        };
       
        /* Function to call on change of city from cities dropdown to update employee city */
        $scope.setCity = function (city){
            
            if(city == null){
                $scope.employee.city = null;
                $scope.employeeCityLable = 'Select city';
                return false;
            }
            
            /* wrap city object in an array to search */
            
            var citySource = [];
            
            citySource.unshift(city);
            
            $scope.employeeCityLable = $scope.getCityName(citySource, city.city_id);
            $scope.employee.city = city.city_id;
        };
        
        $scope.setDesignation = function(designation){
        
            if(designation == null){
                $scope.employee.designation = null;
                $scope.employee.employee_designation = 'Select Designation';
                return false;
            }
            $scope.employee.designation = designation.id;
            $scope.employee.employee_designation = designation.designation;
        };
        
        /* Update Employee */
        $scope.updateEmployee = function (employee){
            
			// Validate mobile number 
			if(!$scope.validateMobileNumber (employee.contactNumber)){
				return false;
			}
			
            var update_promise = employeeService.updateEmployee(employee);
            
            update_promise.then(function (response){
                
                if(response.data.success === 1){
                    
					$scope.notify({class :['alert','alert-success','center-aligned'] , message: 'Employee has been successfully updated'});
                    angular.element('.help-block').text('');
                    $scope.getEmployee();
                }else if(response.data.success == -1){
                    
                    // Render form errors 
                    
                    var errors = response.data.errors; 
                    
                    angular.element('.help-block').text('');
                    
                    angular.forEach(errors, function (val,key,errors){
                        
                        if(!angular.isUndefined(key)){
                            
                            var element_help_block = '.' + key +'-help-block';
                            angular.element(element_help_block).text(val);
                        }
                    });
                    $scope.notify({class: ['alert','alert-warning','bottom-right'], message: 'Please correct the form error'});
                }else{
					
					$scope.notify({class: ['alert','alert-warning','bottom-right'], message: response.data.error});
                }
                
            }, function (error){
                
            });
            
        };
        
		
		/**
		 * Change Reporting User 
		 */
		
		$scope.changeReporting = function (reportingToUser){
			
			if(reportingToUser === null){
				$scope.employee.reportingTo = currentReportingToUser.user_id;
				$scope.employee.reportingToEmployee = currentReportingToUser.user_name;
			}else{
				$scope.employee.reportingToEmployee = reportingToUser.firstname +' '+reportingToUser.lastname;
				$scope.employee.reportingTo = reportingToUser.id;
			}
		};
		
		/**
		 * 
		 * @returns {undefined}
		 */
		$scope.validateMobileNumber = function (value,e){
			
			if(typeof e === 'object'){
				e.stopPropagation();
				if(isNaN (value)){
					angular.element(e.currentTarget).next().text('Please use only numbers in Contact Number'); return false;
				}else{
					angular.element(e.currentTarget).next().text(''); return true;
				}
			}else{
				
				if( isNaN (value) ){
					alert('Please use only numbers in Contact Number'); return false;
				}
				
				return true;
			}
		};
		
    }]);
    
})(app);


