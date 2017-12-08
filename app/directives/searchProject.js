/**
 * Custom directive to search project 
 */

var app = app || {};

(function (app, $){
	
	app.directive('searchProject', function (baseUrl, projectFilters,utilityService, httpService, $http, $filter,$compile){
		
		
		var search_project = {};
		
		search_project.restrict			= 'EA';
		
		search_project.replace			= false;
		
		search_project.templateUrl		= baseUrl + 'partials/search_project.html';
		
		search_project.link = function ( scope, element, attributes ){
			
			if(attributes.enquiryId){
				scope.enquiry_id = attributes.enquiryId;
			}
			
			if(attributes.leadNumber != ''){
				scope.lead_number = attributes.leadNumber;
			}
			
			$('#find_project_modal').on('hidden.bs.modal', function (e) {
				scope.reloadRoute();
			});
			
		};
        
		search_project.controller = function ($scope){
			
			$scope.popup_title		= 'Find new project';
			
			$scope.bhk			= [];
			
            $scope.pre_loading_content = '<span class="danger">No Project Found</span>';
            
			$scope.property_state	= '';
			
			$scope.selected_property_types	= [];
			
			$scope.property_types	= projectFilters.property_types;

			$scope.budget_range		= projectFilters.budget_range;
			
			$scope.checkMaxBudget	= function (max_price_value){
				
				if(parseInt(max_price_value) < parseInt($scope.min_budget_range)){
					alert('Please select max price greater than min price');
					$scope.max_budget_range = '';
					return false;
				}
			};
		
			$scope.checkMinBudget = function (min_price_value){
				
				if(parseInt(min_price_value) > parseInt($scope.max_budget_range)){
					alert('Please select min price less than max price');
					$scope.min_budget_range = '';
					return false;
				}
			};
			
			$scope.cities = [];
			
			var cities = utilityService.getBMHProjectCities(); // Calling BMH API to get list of project cities 
			
			cities.then(function (success){
				$scope.cities = success.data.city_list;
			});
			
			$scope.crm_projects			= [];
			$scope.selected_projects	= [];
			
			$scope.showProjects		= function (city){
					
                    $scope.pre_loading_content = '<i class="fa fa-circle-o faa-burst animated"></i>';
                
                    var config = {
						url: baseUrl + 'apis/fetchCRMProjects.php',
						method: 'POST',
						data: $.param ( {
							city: city,
							ptype: $scope.selected_property_types,
							status_data: $scope.selected_property_status,
							bhk1: $scope.bhk,
							min_price: $scope.min_budget_range,
							max_price: $scope.max_budget_range
						} ),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
						}
					};
					
					var projects = $http ( config );

					projects.then ( function ( success ) {
						if ( success.data.success ) {
                            
                            // If no project found then change the content of pre_loading_content scope variable to some thing like No Project Found
                            if(success.data.data.length === 0){
                                $scope.pre_loading_content = '<span class="danger">No Project Found</span>';
                            }else{
                                $scope.pre_loading_content = null;
                            }
                            
							$scope.crm_projects = success.data.data;
						}
					} );
					
			};
		
			/**
			 * Function to collect/ remove projects on checked/ unchecked state
			 * @param {type} p_id
			 * @param {type} event
			 * @returns {undefined}
			 */
			$scope.select_p			= function (p_id, event){
                
                event.stopPropagation();
                
				var is_checked = $ ( event.currentTarget ).prop ( 'checked' );
                
				if(is_checked){
					$scope.selected_projects.unshift (p_id);
				}else{
					var project_id_index = $scope.selected_projects.indexOf ( p_id );
					$scope.selected_projects.splice ( project_id_index, 1 );
				}
			};
			
			
			/**
			 * Function to save checked projects 
			 * @returns {undefined}
			 */
			
			$scope.saveCheckedProjects	= function (){
				
				var filtered_project = [];
				
				if($scope.enquiry_id){
				
					angular.forEach ($scope.selected_projects, function (val){
						
						var temp = {project_id : val};
						var filtered_project_temp = $filter('filter')($scope.crm_projects,temp,true);
						filtered_project.push(filtered_project_temp[0]);
					});
					
					
					if(filtered_project.length > 0){
						
						var save_project = httpService.makeRequest({
							url : baseUrl + 'apis/save_enquiry_projects.php',
							method : 'POST',
							data : {
								projects : filtered_project,
								enquiry_id : $scope.enquiry_id,
								lead_number : $scope.lead_number
							}
						});
						
						save_project.then(function (success){
							
							if(parseInt(success.data.success) === 1){
                                
							}else{
								alert('There were some errors in saving your selected projects. Please try again');
							}
							
							$('#find_project_modal').modal('hide');
							
						});
					}	
				}
			};
						
			/**
			 * Model property to collect selected BHK values 
			 */
			
			$scope.bhk = [];
			
			
			/**
			 * Function to select property status from filter 
			 * @returns {undefined}
			 */
			
			$scope.select_property_status = function (status){
				
				if(status !== null){
						$scope.selected_property_status = $filter('lowercase')(status);
				}else{
						$scope.selected_property_status = null;
				}
				
			};
			
		};
		
		return search_project;
		
	});
	
}(app, jQuery));
