/**
 * 
 */

var app = app || {};

(function (app, $) {

    app.controller('getAgentReAssignedLeadCtrl', function ($scope, user_auth, $http, httpService, $location, baseUrl,$filter) {

        $scope.leadsData = [];
        $scope.backupLeads = [];
        $scope.selectedLeads = [];
        $scope.selectedSingleLeads = [];
        $scope.area_sales_managers = null;
        $scope.agent_list = null;
        
        $scope.isAgentDisabled = true;
        
        $scope.isCheckBox = false;
	    
	    
		// pagination data 
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
        
		//console.log(lead_response);
        $scope.filterData = function(item){
        	
    		if(item){
				var statusId = item;
			}else{
				var statusId = 1;
			}
			
    		var leads_config = {
	            url: baseUrl + 'apis/getReAssignedLeads.php',
	            method: 'POST',
	            data : {status : statusId}
	        };
    		var lead_response = httpService.makeRequest(leads_config);
			lead_response.then(function (response) {

	            if (response.data.success == 1 && response.data.http_status_code == 200) {
	                $scope.backupLeads = $scope.leadsData = response.data.data;
					$scope.isCheckBox = false;
					console.log($scope.leadsData);
	            } else if (response.data.http_status_codes == 401) {
	                $location.path('/');
	            }
	        });
		};
		$scope.filterData();
        $scope.modal = {size: 'sm', title: 'Projects'};
        $scope.view_projects = function (data) {
            $scope.client_enquiry_projects = data;
            $('.bd-example-modal-sm').modal('show'); // Opening modal
        };
        
        // Check All Checkbox
        $scope.checkAll = function () {
        	$scope.selectedLeads = [];
	        angular.forEach($scope.leadsData, function (item) {
	        	if(item.Selected){
	         	   item.Selected = false;
	            }else{
					item.Selected = true;
					$scope.selectedLeads.push(item.enquiry_id);
				}
	        });
	        console.log($scope.selectedLeads);
		};
		
		$scope.checkOneByOne = function () {
        	$scope.selectedLeads = [];
	        angular.forEach($scope.leadsData, function (item) {
	        	if(item.Selected){
	         	   $scope.selectedLeads.push(item.enquiry_id);
	            }
	        });
	        console.log($scope.selectedLeads);
		};
		 
		// Open popup Of ASM List
		$scope.popUpAsmList = function (enquiry_id, category){
         	$scope.enquiry_id_for_asm_assignment    = enquiry_id;
            $scope.lead_category_for_asm_assignment = category;
            
            // open popup of asm users list 
            $('#asm_users_list_popup').modal('show');
        };
        
        // Open popup Of Agent List
        $scope.popUpAgentList = function (enquiry_id, category){
         	$scope.enquiry_id_for_asm_assignment    = enquiry_id;
            $scope.lead_category_for_asm_assignment = category;
            
            // open popup of asm users list 
            $('#agent_users_list_popup').modal('show');
        };
        
        // Open popup Of History List
        $scope.popUpHistoryList = function (enquiry_id){
        	if(enquiry_id){
        		 $scope.history_enquiry_id = enquiry_id;
				var history_config = {
		            url: baseUrl + 'apis/getEnquiryHistory.php',
		            method: 'POST',
		            data : {enquiry_id : enquiry_id}
		        };
        		var history_response = httpService.makeRequest(history_config);
				history_response.then(function (response) {
					
		            if (response.data.length > 0) {
		                $scope.historyData = response.data;
		            }else{
						alert("No history available!");
		            }
		            // open popup of asm users list 
	           	 	$('#history_list_popup').modal('show');
				});
        	}
        };
        
        // Ressign lead to ASM
        $scope.manualLeadAssignToAsm = function (dom_element, asm_id){
           	
           // $scope.enquiry_id_for_asm_assignment = enquiry_id;
            $scope.lead_category_for_asm_assignment = '';
            
            // Start button animation 
			var button_innerHTML = dom_element.target.innerHTML; // existing button html
			dom_element.target.innerHTML = 'Assigning <i class="fa fa-spinner faa-spin animated"></i>';	
			dom_element.target.disabled = true;
			if(confirm("Are you sure you want to re-assign lead ?")){
				if($scope.selectedLeads.length > 0){
					angular.forEach($scope.selectedLeads, function (item) {
						$scope.enquiry_id_for_asm_assignment = item;
						
						//console.log(http_call_data);
						
						$http({
							url : baseUrl + 'apis/reassign_manual_lead_assign_to_asm.php',
							method : 'POST',
							data : {enquiry_id: item, asm_id : asm_id},
							headers : {'Content-Type' : 'application/x-www-form-urlencoded; charset=utf-8'}
						}).then(function (success){
							console.log(success);
							if(parseInt(success.data.success) === 1){
								
			                    dom_element.target.innerHTML   = 'Assigned';
			                    dom_element.target.disabled    = true;
			                    $('#asm_users_list_popup').modal('hide'); // hide asm popup modal
			                    $scope.getLeads (); // get updated leads     
			                    location.reload();                
							}else{
								// restore original button text 
								dom_element.target.innerHTML = button_innerHTML;
								dom_element.target.disabled = false;
								alert(success.data.message);
							}
						}, function (error){
							dom_element.target.innerHTML = button_innerHTML;
							dom_element.target.disabled = false;	
						});
					});
				}else{
					alert("Please check at least one lead!");
					dom_element.target.innerHTML = button_innerHTML;
					dom_element.target.disabled = false;
					$('#asm_users_list_popup').modal('hide'); 
				}
			}else{
				dom_element.target.innerHTML = button_innerHTML;
				dom_element.target.disabled = false;
				$('#asm_users_list_popup').modal('hide'); 
			}
		};
    	
    	
    	$scope.filterLeads  = function (item){
    		$scope.filterData(item);
        };
        
        $scope.selectedItem = {id:1,label:"Not Interested"};
        $scope.meetingStatus = [];
        $scope.meetingStatus.push({id:1,label:"Not Interested"});
        $scope.meetingStatus.push({id:38,label:"No response"});
        $scope.meetingStatus.push({id:4,label:"Future Refernce"});
        $scope.meetingStatus.push({id:34,label:"Just Enquiry"});
        
        //console.log($scope.selectedItem);
        $scope.$watch('selectedItem', function() {
        	$scope.filterLeads($scope.selectedItem.id);
			$scope.selectedPage = {id:10, label:"10"};
	    });
    
    
    	// Lead re-assign to Agent
    	$scope.manualLeadAssignToAgent = function (dom_element, agent_id){
    		//alert(agent_id);
           	
           // $scope.enquiry_id_for_asm_assignment = enquiry_id;
            $scope.lead_category_for_agent_assignment = '';
            
            // Start button animation 
			var button_innerHTML = dom_element.target.innerHTML; // existing button html
			dom_element.target.innerHTML = 'Assigning <i class="fa fa-spinner faa-spin animated"></i>';	
			dom_element.target.disabled = true;
			if(confirm("Are you sure you want to re-assign lead ?")){
				if($scope.selectedLeads.length > 0){
					angular.forEach($scope.selectedLeads, function (item) {
						//alert(item);
						$scope.enquiry_id_for_agent_assignment = item;
						
						//console.log(http_call_data);
						
						$http({
							url : baseUrl + 'apis/reassign_manual_lead_assign_to_agent.php',
							method : 'POST',
							data : {enquiry_id: item, agent_id : agent_id},
							headers : {'Content-Type' : 'application/x-www-form-urlencoded; charset=utf-8'}
						}).then(function (success){
							console.log(success);
							if(parseInt(success.data.success) === 1){
								
			                    dom_element.target.innerHTML   = 'Assigned';
			                    dom_element.target.disabled    = true;
			                    $('#agent_users_list_popup').modal('hide'); // hide asm popup modal
			                    $scope.getLeads (); // get updated leads     
			                    location.reload();                
							}else{
								// restore original button text 
								dom_element.target.innerHTML = button_innerHTML;
								dom_element.target.disabled = false;
								alert(success.data.message);
							}
						}, function (error){
							dom_element.target.innerHTML = button_innerHTML;
							dom_element.target.disabled = false;	
						});
					});
				}else{
					alert("Please check at least one lead!");
					dom_element.target.innerHTML = button_innerHTML;
					dom_element.target.disabled = false;
					$('#agent_users_list_popup').modal('hide'); 
				}
			}else{
				$scope.reloadRoute();
				dom_element.target.innerHTML = button_innerHTML;
				dom_element.target.disabled = false;
				$('#agent_users_list_popup').modal('hide'); 
			}
		};
   
   		
   		
   		
   		// Page Size
   		$scope.selectedPage = {id:10, label:"10"};
        $scope.PageSize = [];
        $scope.PageSize.push({id:10,label:"10"});
        $scope.PageSize.push({id:20,label:"20"});
        $scope.PageSize.push({id:30,label:"30"});
        $scope.PageSize.push({id:50,label:"50"});
        $scope.PageSize.push({id:100,label:"100"});
        $scope.PageSize.push({id:500,label:"500"});
        $scope.$watch('selectedPage', function() {
        	$scope.pagination.page_size = $scope.selectedPage.id;
        });
        
        $scope.$watch('search_lead_query',function(){
        	$scope.leadsData = $filter('filter')($scope.backupLeads, $scope.search_lead_query);
        });
    });

})(app, jQuery);