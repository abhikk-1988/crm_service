/**
 * Package CRM
 * 
 * @fileOverview controller addSalesPersonCapacityCtrl
 * @author Abhishek Agrawal
 */

var app = app || {};

(function(app, $){
	
	app.controller ('addSalesPersonCapacityCtrl', ['$scope','user_session','dateUtility','sales_person_list','httpService','baseUrl','$http','already_assigned_capacity_users', function ($scope,user_session,dateUtility, sales_person_list, httpService, baseUrl,$http,already_assigned_capacity_users){
		
        
		$scope.user = user_session;
		
		$scope.sales_person_max_capacity = 0; // default 
		
		$scope.current_month  = dateUtility.current_month_in_textual_representation('full');
		
		$scope.current_year   = dateUtility.current_year;
		
		$scope.sales_person_list = sales_person_list.data;
		
		$scope.sales_person = {}; // main object 
		
		$scope.sales_person.capacity_month = new Date().getMonth ();
		
		$scope.sales_person.capacity_year = new Date().getFullYear ();
		
        // Sales person user to skip from selection
        $scope.skipSalesPersonUsers = already_assigned_capacity_users;
        
		$scope.checkMaxCapacityLimit = function (event, capacity_value){
			
			if(capacity_value === ''){
				$scope.sales_person_details.sales_person_capacity = null;
			}
			
			if(parseInt(capacity_value) > $scope.sales_person_max_capacity){
				alert('Cannot assign capacity beyond maximum limit');
				$scope.sales_person.sales_person_capacity = null;
				return false;
			}
			
		};
		
        /**
            Fucntion to save sales person capacity
        */
		$scope.saveCapacity = function (data){
            
			if(!data.sales_person_capacity){
				alert('Please enter sales person capacity');
				return false;
			}
			
			var save_capacity = httpService.makeRequest({
				url     : baseUrl + 'apis/save_sales_person_capacity.php',
				method  : 'POST',
				data    : data
			});
			
			save_capacity.then(function (response){
				
				if(parseInt(response.data.success) === 1){
					
					$scope.notify({
						class	: ['alert','alert-success', 'center-aligned'],
						message : response.data.message
					});
				}else{
					$scope.notify({
						class	: ['alert','alert-warning', 'center-aligned'],
						message : response.data.message
					});
				}
				
			}, function (error){
				
			});
			
		};
		
		// sales person manager details
		$scope.sales_person.manager = {};
		
		/**
		 * Function to get sales person manager details 
		 * @returns {undefined}
		 * 
		 */
		$scope.getSalesPersonManager = function (sp){
		
			var req = $http.get(baseUrl + 'apis/helper.php?method=get_sales_person_manager_details&params=user_id:'+sp.id);
			
			req.then(function (response){
			
					if( response.data){
						
						$scope.sales_person.manager = {
                            name        : response.data.manager_name, 
                            id          : response.data.manager_id, 
                            capacity    : response.data.manager_capacity
                        }; 

						var manager_remaining_capacity	= httpService.makeRequest({
								url : baseUrl + 'apis/helper.php?method=getSalesPersonMaxCapacity&params=asm_id:'+$scope.sales_person.manager.id+'/sales_person_id:'+sp.id+'/asm_capacity:'+$scope.sales_person.manager.capacity,
								method : 'GET'
						});
						
						manager_remaining_capacity.then(function (response){
							
							if(response.data){
								$scope.sales_person.manager.remaining_capacity = response.data;
							}else{
								$scope.sales_person.manager.remaining_capacity  = 0;
							}
                            
                                var mpl_capacity = httpService.makeRequest({
								    url : baseUrl + 'apis/helper.php?method=get_mpl_capacity&params=user_id:'+$scope.sales_person.manager.id,
								    method : 'GET'
						          });
                            
                                mpl_capacity.then(function (response){
							
                                    if( parseInt(response.data.success) === 1){
                                        $scope.sales_person.manager.mpl_capacity = response.data.capacity;
                                    }
                                    else{
                                        $scope.sales_person.manager.mpl_capacity = 0 ; // default 0
                                    }

							        // prepare sales person max capacity
							        $scope.sales_person_max_capacity = parseInt($scope.sales_person.manager.remaining_capacity) + parseInt($scope.sales_person.manager.mpl_capacity);
                            
                                    console.log($scope.sales_person_max_capacity);
						          });    
						      });
					   }
			}); // end of then function
		};
		
	}]);
	
}(app, jQuery));