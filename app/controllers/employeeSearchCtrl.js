/**
 * @fileOverview To list and search employee 
 * @author Abhishek Agrawal
 * @version 1.0
 */

var app = app || {};

( function ( app , $) {

	app.controller ( 'employeeSearchCtrl', ['$scope', '$routeParams','$route', '$location', '$http', '$log', 'Session', 'employeeService', 'user_auth','$filter','user_designation', 'baseUrl',function ( $scope, $routeParams, $route, $location, $http, $log, Session, employeeService, user_auth, $filter, user_designation, baseUrl ) {
		
			$scope.user = Session.getUser(); // current user 
			
			$scope.employees = new Array ();

			$scope.designation_list = user_designation;
			
			$scope.getAllEmployees = function (){
				
				var res = employeeService.getAllEmployees ();
					res.then ( function ( response ) {
						$scope.employees = response.data; // Employees data
					}, function ( error ) {
				});
			};
			
			$scope.getAllEmployees ();
			
			// Pagination Data 
			$scope.pagination = {
				current_page	: 1,
				pagination_size : 4,
				page_size		: 10,
				show_boundary_links : true,
				total_page		: 0,
				changePage : function (page){
					this.current_page = page;
				}
			};
        
			// End pagination
			
			$scope.pages = new Array($scope.pagination.page_size, $scope.pagination.page_size + 10, $scope.pagination.page_size + 20, 'All');
			
			$scope.changePageSize = function (page_size){
				
				if(typeof page_size === 'string'){
					if( 'all' === $filter('lowercase')(page_size)){
						$scope.pagination.page_size = $scope.employees.length;
					}
				}
				else if (typeof page_size === 'object' && !page_size){
					$scope.pagination.page_size = 10; // default value of page size
				}else{
					$scope.pagination.page_size = page_size;
				}
			};
			
			/**
			 * Filter employee data set by designation 
			 * @returns {undesfined}
			 */
			$scope.filterEmployeeByDesignation = function (designation_id){
				
				if(designation_id){
					employeeService.filterEmployeeByDesignation(designation_id).then(function (res){
						
						$scope.employees = res.data;
					});
				}else{
					$scope.getAllEmployees ();
				}
			};
			
			/**
			 * Function to mark employee as delete from system
			 * @returns {undefined}
			 */
			// Go for re-assing lead from user to other user 
			$scope.reassignOnDelete = function (){
				var emp_id = $("#emp_id").val();
				
				var designation = $("#designation").val().toLowerCase();
				
				designation = designation.split(' ').join('_');
				
				if(emp_id && (designation == 'agent' || designation == 'area_sales_manager')){
					
					$('#delete_emp_popup').modal('hide');
					
					$location.path('re-assign-lead/'+emp_id+"-"+designation);  
				
				}else if(emp_id && designation=='sales_person'){									
						
					$('#delete_emp_popup').modal('hide');
					
					$location.path('re-assign-to-sp/'+emp_id);  
					
				}else{
					
					$('#delete_emp_popup').modal('hide');
				}

			};
			
			// delete user force fully 
			$scope.forceDeleteEmp = function (){
				
				if(confirm("Are you sure you want to delete this user?")){
					
					var emp_id = $("#emp_id").val();
				
					if(emp_id){
						
						var res = employeeService.deleteEmployee(emp_id);
						
						res.then(function (success){
							
							if( parseInt(success.data.success) === 1){
								
								$('#delete_emp_popup').modal('hide');
								
								alert(success.data.message);
								
								$route.reload();
								
							}else{
								alert(success.data.message);
								$scope.logout();
							}
						});
						
					}else{
						$('#delete_emp_popup').modal('hide');
						alert("Emp Id Not Found");
					}
				}else{
					$('#delete_emp_popup').modal('hide');
				}			
			};
			
			 		 
			$scope.deleteEmployeePopup = function (emp_id, designation){
				$('#emp_id').remove();			
				$('#designation').remove();	
				$('#reassignId').show();
				$('#designation').remove();	
				$("#ignoreDeleteBtn").text('IGNORE AND DELETE');
				$('#msg').remove();	
				
				if(emp_id){
					
					$('#bodyPopup').append('<input type="hidden" id="emp_id" value="'+emp_id+'" />');
					
					$('#bodyPopup').append('<input type="hidden" id="designation" value="'+designation+'" />');
					var result = employeeService.employeeLeadCount(emp_id);
						
					result.then(function (success){
						
						if(parseInt(success.data.success) === 1){
							
							var count = parseInt(success.data.count);
							
							if(count > 0){
								
								$('#bodyPopup').append('<p id="msg">Total ('+count+') leads are assign to selected user. if you want re-assing these leads click on REASSIGN button!</p>');
							}else{
								$('#reassignId').hide();
								$('#bodyPopup').append('<p id="msg">There are no leads assign to selected user</p>');
								$("#ignoreDeleteBtn").text('DELETE');
							}
						}
					});
					$('#delete_emp_popup').modal('show');
				}else{
					alert("Employee id missing");
				}
			};
	}]); // End of controller 

    
    app.directive('formatDoj', function ($filter){
    
        return {
          
            restrict : 'A',
            scope : {
                doj : '@'
            },
            link : function ($scope, tEle, tAttr){
                
                var formated_date = $filter('date')(new Date($scope.doj),'mediumDate','+0530');
                tEle.text(formated_date);
            }
        };
    });
    
    
} ) ( app , jQuery);