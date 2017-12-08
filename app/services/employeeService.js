/**
 * @fileOverview EmployeeService for employee module 
 */

 var app = app || {};
 
 (function (app){
    
    // Employee service function 
    
    app.service('employeeService', function ($http,$location,appUrls){
        
        this.addNewDesignation = function (designation,action,id,disable){
            return $http({
                url     : appUrls.apiUrl + 'saveDesignation.php',
                method  : 'POST',
                data    : {
                    designation : designation,
                    action  : action, // action could be add/edit 
                    id      : id,
                    disable : (disable ? 1 : null)
                }
            });
        };
        
        this.getAllDesignation = function (){
            return $http({
                url     : appUrls.apiUrl + 'getDesignation.php',
                method  : 'GET'
            });
        };
        
        this.deleteDesignation = function (designation_id,status){
            return $http({
                url     : appUrls.apiUrl + 'deleteDesignation.php',
                method  : 'POST',
                data    : {
                    id : designation_id,
                    mark_as_delete : status
                }
            });
        };
        
        /* Service method to create new employee in DB*/
        this.saveNewEmployee = function (employee){
            
            return $http({
                url : appUrls.apiUrl + 'saveEmployee.php',
                method : 'POST',
                data : employee
            });
        };
        
		/**
		 * 
		 * @param {type} employee_filter
		 * @returns {unresolved}
		 */
		
        this.getAllEmployees = function (employee_filter){
			
            return $http({
                url : appUrls.apiUrl + 'employees.php',
                method: 'GET'
            });
        };
        
        this.getEmployee = function (emp_id){
            
            return $http({
                url : appUrls.apiUrl + 'employees.php?employee_id='+ emp_id,
                method : 'GET'
            });
        };
        
		this.filterEmployeeByDesignation = function (designation_id){
            
            return $http({
                url : appUrls.apiUrl + 'employees.php?designation_id='+ designation_id,
                method : 'GET'
            });
        };
        
        this.updateEmployee = function (employee){
            
            return $http({
                url     : appUrls.apiUrl + 'updateEmployee.php',
                method  : 'POST',
                data    : employee
            });
        };
		
		this.deleteEmployee = function (emp_id){
			var url = appUrls.apiUrl + 'deleteEmployee.php';
			return $http.post(url, {employee_id : emp_id});
		};
		
		this.employeeLeadCount = function (emp_id){
			var url = appUrls.apiUrl + 'employeeLeadCount.php';
			return $http.post(url, {employee_id : emp_id});
		};
    });
     
 }) (app);
