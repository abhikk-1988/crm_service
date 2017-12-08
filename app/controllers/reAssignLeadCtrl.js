/**
 * 
 */

//var app = app || {};

(function (app, $) {

    app.controller('reAssignLeadCtrl', function ($scope, user_auth, $http, httpService, $location, baseUrl, $filter, $routeParams) {
    	
    	
    	if($routeParams.user_id){
		
			$scope.string = $routeParams.user_id.split("-");
			
			$scope.userId = $scope.string[0];
			
			$scope.designation = $scope.string[1];
			
		}else{
		
			$scope.userId = '';
			
			$scope.designation = '';
		}
		
        $scope.leadsData = [];
        $scope.backupLeads = [];
        $scope.selectedLeads = [];
        $scope.selectedSingleLeads = [];
        $scope.area_sales_managers = null;
        $scope.agent_list = null;
        
        //$scope.isAgentDisabled = true;
        $("#isAgentDisabled").show();
        $("#isAsmDisabled").hide();
        
        $scope.isCheckBox = false;
	    
	    
		//console.log(lead_response);
        $scope.filterData = function(item, sourceStatus, LeadUpdatedDate, selectedUserType, searchKeyword, projectFilter){
        	
        	if(item){
				var statusId = item;
			}else{
				var statusId = 3;
			}
			
    		var leads_config = {
	            url: baseUrl + 'apis/fetchReAssignLeads.php',
	            method: 'POST',
	            data : {status : statusId, source_status : sourceStatus, lead_updated_date: LeadUpdatedDate, start: $scope.pagination.current_page, limit : $scope.pagination.page_size, user_id : $scope.userId, user_type : selectedUserType, keyword : searchKeyword, project: projectFilter}
	        }; 
    		var lead_response = httpService.makeRequest(leads_config);
			lead_response.then(function (response) {

	            if (response.data.success == 1 && response.data.http_status_code == 200) {
	                $scope.backupLeads = $scope.leadsData = response.data.data;
	                $scope.total_row =  response.data.total_row;
	                 
					$scope.isCheckBox = false;
					//console.log($scope.leadsData);
	            }else if (response.data.http_status_codes == 401) {
	                $location.path('/');
	            }
	        });
		};
		//$scope.filterData();
        $scope.modal = {size: 'sm', title: 'Projects'};
        $scope.view_projects = function (data) {
            $scope.client_enquiry_projects = data;
            $('.bd-example-modal-sm').modal('show'); // Opening modal
        };
        
        // Check All Checkbox
        $scope.checkAll = function () {
        	$scope.selectedLeads = [];
        	var i = 0; 
        	angular.forEach($scope.leadsData, function (item) {
        		if(i < $scope.pagination.page_size){
					if(item.Selected){
		         	   item.Selected = false;
		            }else{
						item.Selected = true;
						$scope.selectedLeads.push(item.enquiry_id);
					}					
				}
				i++;

	        });
	        //console.log($scope.selectedLeads);
		};
		
		$scope.checkOneByOne = function () {
        	$scope.selectedLeads = [];
	        angular.forEach($scope.leadsData, function (item) {
	        	if(item.Selected){
	         	   $scope.selectedLeads.push(item.enquiry_id);
	            }
	        });
	        //console.log($scope.selectedLeads);
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
        
        // Search Data
        $scope.searchData = function (keyword){
        	if(keyword){
        		$scope.keyword = keyword;
				 $scope.filterLeads($scope.selectedItem.id, $scope.selectedSourceItem.id, $scope.updateLeadFilter, $scope.selectedUser.id, $scope.keyword, $scope.selectedProjectItem.id);
        	}else{
				alert("Please enter search keyword");
			}
        };
        //End Search
        
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
							//console.log(success);
							if(parseInt(success.data.success) === 1){
								dom_element.target.innerHTML   = 'Assigned';
			                    dom_element.target.disabled    = true;
			                    $('#asm_users_list_popup').modal('hide'); // hide asm popup modal
			                    //$scope.getLeads (); // get updated leads   
			                    alert(success.data.message);  
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
    	
    	
    	$scope.filterLeads  = function (a, b, c=null,d, e=null, f=null){
    		$scope.filterData(a, b, c, d, e, f);
        };
        
        $scope.meetingStatus = [];
        if($scope.designation=='area_sales_manager'){				// Used for on ASM Delete
			$scope.selectedItem = {id:3,label:"Meeting Schedule"};
			$scope.meetingStatus.push({id:3,label:"Meeting Schedule"});
        	$scope.meetingStatus.push({id:6,label:"Site Visit Schedule"});
        	
		}else if($scope.designation=='agent'){						// Used for on Agent Delete
			$scope.selectedItem = {id:1,label:"Not Interested"};
			$scope.meetingStatus.push({id:1,label:"Not Interested"});
	        $scope.meetingStatus.push({id:38,label:"No response"});
	        $scope.meetingStatus.push({id:47,label:"Call Back"});
	        $scope.meetingStatus.push({id:34,label:"Just Enquiry"});
	        $scope.meetingStatus.push({id:4,label:"Future Refernce"});
		
		}else{														// Used for on re-assing process
			$scope.selectedItem = {id:1,label:"Not Interested"};
			$scope.meetingStatus.push({id:1,label:"Not Interested"});
	        $scope.meetingStatus.push({id:38,label:"No response"});
	        $scope.meetingStatus.push({id:47,label:"Call Back"});
	        $scope.meetingStatus.push({id:34,label:"Just Enquiry"});
	        $scope.meetingStatus.push({id:4,label:"Future Refernce"});
//	        $scope.meetingStatus.push({id:3,label:"Meeting Schedule"});
//        	$scope.meetingStatus.push({id:6,label:"Site Visit Schedule"});
		}
		
		$scope.userType = [];
    	$scope.selectedUser = {id:1,label:"CRM"};
    	$scope.userType.push({id:1,label:"CRM"});
    	$scope.userType.push({id:2,label:"Sales Person"});
    	$scope.userType.push({id:3,label:"N/A"});
        
        
        // Secondary Source filter
        $scope.sourceStatus = [] ;
        var Sources = {
            url: baseUrl + 'apis/helper.php?method=getSecondaryCampiagns',
            method: 'GET',
        };
		var Sources_response = httpService.makeRequest(Sources);
		
		$scope.sourceStatus.push({id : "NA", label: "N/A"});	
		
		$scope.selectedSourceItem = {id : "NA", label: "N/A"};
		
		Sources_response.then(function (response) {
            if(response.data){
            	
            	angular.forEach(response.data, function (key, val) {
            		
					$scope.sourceStatus.push({id : response.data[val].title, label: response.data[val].title});
				});
            }
        });
        
        //// Get Total Enquiried Project 
        $scope.enq_project = [] ;
        var ENQ_Project = {
            url: baseUrl + 'apis/enquiryProjects.php',
          	method: 'POST',
          	data : {status : "successs"}
        };
		var ENQ_Project_Response = httpService.makeRequest(ENQ_Project);
		
		$scope.enq_project.push({id : "NA", label: "N/A"});	
		
		$scope.selectedProjectItem = {id : "NA", label: "N/A"};
		
		ENQ_Project_Response.then(function (response) {
            if(response.data){
            	if(response.data.success==1){
					angular.forEach(response.data.data, function (key, val) {
						var project_name = $.trim(response.data.data[val]);
            			$scope.enq_project.push({id : project_name, label: project_name});
					});
				}else{
					alert("No Enquiry project available!");
				}
            }
        });
		
        // Source Status Change
	    $scope.$watch('selectedProjectItem', function(newval,oldval) {
	        if($scope.selectedProjectItem.id!=''){
	        	
        		$scope.filterLeads($scope.selectedItem.id, $scope.selectedSourceItem.id, $scope.updateLeadFilter, $scope.selectedUser.id, null, $scope.selectedProjectItem.id);
				$scope.selectedPage = {id:10, label:"10"};
				$scope.pagination.current_page = 1;
			}
	    });
        //End Enq Projects list
        
        
        //console.log($scope.selectedItem);
        $scope.$watch('selectedItem', function() {
        	
        	$(".search-input").val('');
        	if($scope.selectedItem.id==3 || $scope.selectedItem.id==6){
        		$scope.filterLeads($scope.selectedItem.id, $scope.selectedSourceItem.id, $scope.updateLeadFilter, $scope.selectedUser.id,null, $scope.selectedProjectItem.id);
//				$scope.isAgentDisabled = false;
				$("#isAgentDisabled").hide();
				//$scope.isAsmDisabled = true;
				$("#isAsmDisabled").show();
				$scope.selectedPage = {id:10, label:"10"};
				$scope.pagination.current_page = 1;
				// get List of ASM
		        httpService.makeRequest({
		            url		: baseUrl + 'apis/get_asm_capacities.php',
		            method	: 'GET'
		        }).then(function (successCallback){
		            $scope.area_sales_managers = successCallback.data;
		            $scope.agent_list = null;
		        });
			}else{
				$scope.filterLeads($scope.selectedItem.id, $scope.selectedSourceItem.id, $scope.updateLeadFilter, $scope.selectedUser.id, null, $scope.selectedProjectItem.id);
//				$scope.isAsmDisabled = false;
				$("#isAsmDisabled").hide();
//				$scope.isAgentDisabled = true;
				$("#isAgentDisabled").show();
				$scope.selectedPage = {id:10, label:"10"};
				$scope.pagination.current_page = 1;
				// get List of Agent
				httpService.makeRequest({
		            url		: baseUrl + 'apis/getAgentList.php',
		            method	: 'GET'
		        }).then(function (successCallback){
		        	$scope.agent_list = successCallback.data.agents;
		        	//console.log($scope.agent_list);
		        	$scope.area_sales_managers = null;
		        });
			}
	    });

		$scope.initialize = $scope.selectedSourceItem.id;
	    
	    // Source Status Change
	    $scope.$watch('selectedSourceItem', function() {
	    	
        	$(".search-input").val('');
        	if($scope.initialize !=  $scope.selectedSourceItem.id){
				
	        	if($scope.selectedItem.id==3 || $scope.selectedItem.id==6){
	        		$scope.filterLeads($scope.selectedItem.id, $scope.selectedSourceItem.id, $scope.updateLeadFilter, $scope.selectedUser.id,null,$scope.selectedProjectItem.id);
					$scope.selectedPage = {id:10, label:"10"};
					$scope.pagination.current_page = 1;
					// get List of ASM
			        httpService.makeRequest({
			            url		: baseUrl + 'apis/get_asm_capacities.php',
			            method	: 'GET'
			        }).then(function (successCallback){
			            $scope.area_sales_managers = successCallback.data;
			            $scope.agent_list = null;
			        });
				}else{
					$scope.filterLeads($scope.selectedItem.id, $scope.selectedSourceItem.id, $scope.updateLeadFilter, $scope.selectedUser.id,null, $scope.selectedProjectItem.id);
					$scope.selectedPage = {id:10, label:"10"};
					$scope.pagination.current_page = 1;
					// get List of Agent
					httpService.makeRequest({
			            url		: baseUrl + 'apis/getAgentList.php',
			            method	: 'GET'
			        }).then(function (successCallback){
			        	$scope.agent_list = successCallback.data.agents;
			        	//console.log($scope.agent_list);
			        	$scope.area_sales_managers = null;
			        });
				}
			}
	    });

		// Source Status Change
	    $scope.$watch('selectedUser', function() {
	    	
			if($scope.selectedUser.id=='2'){
				$scope.meetingStatus = [];
				$scope.meetingStatus.push({id:1,label:"Not Interested"});
		        $scope.meetingStatus.push({id:34,label:"Just Enquiry"});
			}else{
				$scope.meetingStatus = [];
				$scope.meetingStatus.push({id:1,label:"Not Interested"});
		        $scope.meetingStatus.push({id:34,label:"Just Enquiry"});
				$scope.meetingStatus.push({id:38,label:"No response"});
	       		$scope.meetingStatus.push({id:47,label:"Call Back"});
	       		$scope.meetingStatus.push({id:4,label:"Future Refernce"});
			}
        	$(".search-input").val('');
        	if($scope.selectedUser.id){
        	
				$scope.filterLeads($scope.selectedItem.id, $scope.selectedSourceItem.id, $scope.updateLeadFilter, $scope.selectedUser.id, null, $scope.selectedProjectItem.id);
				
				$scope.selectedPage = {id:10, label:"10"};
				$scope.pagination.current_page = 1;
				// get List of Agent
				httpService.makeRequest({
		            url		: baseUrl + 'apis/getAgentList.php',
		            method	: 'GET'
		        }).then(function (successCallback){
		        	$scope.agent_list = successCallback.data.agents;
		        	//console.log($scope.agent_list);
		        	$scope.area_sales_managers = null;
		        });
			}
	    });
	    
	    // Updated Date Filter
	    //$scope.updateLeadFilter = null;
	    
        $scope.filterByDateRange = function (){
        	$(".search-input").val('');
        	if($scope.selectedItem.id==3 || $scope.selectedItem.id==6){
        		$scope.filterLeads($scope.selectedItem.id, $scope.selectedSourceItem.id, $scope.updateLeadFilter, $scope.selectedUser.id, null, $scope.selectedProjectItem.id);
				$scope.selectedPage = {id:10, label:"10"};
				$scope.pagination.current_page = 1;
				// get List of ASM
		        httpService.makeRequest({
		            url		: baseUrl + 'apis/get_asm_capacities.php',
		            method	: 'GET'
		        }).then(function (successCallback){
		            $scope.area_sales_managers = successCallback.data;
		            $scope.agent_list = null;
		        });
			}else{
				$scope.filterLeads($scope.selectedItem.id, $scope.selectedSourceItem.id, $scope.updateLeadFilter, $scope.selectedUser.id, null, $scope.selectedProjectItem.id);
				$scope.selectedPage = {id:10, label:"10"};
				$scope.pagination.current_page = 1;
				// get List of Agent
				httpService.makeRequest({
		            url		: baseUrl + 'apis/getAgentList.php',
		            method	: 'GET'
		        }).then(function (successCallback){
		        	$scope.agent_list = successCallback.data.agents;
		        	//console.log($scope.agent_list);
		        	$scope.area_sales_managers = null;
		        });
			}
        };
    
    
    	// Lead re-assign to Agent
    	$scope.manualLeadAssignToAgent = function (dom_element, agent_id){
    		
            $scope.lead_category_for_agent_assignment = '';
            
            // Start button animation 
			var button_innerHTML = dom_element.target.innerHTML; // existing button html
			dom_element.target.innerHTML = 'Assigning <i class="fa fa-spinner faa-spin animated"></i>';	
			dom_element.target.disabled = true;
			if(confirm("Are you sure you want to re-assign lead(s)?")){
				if($scope.selectedLeads.length > 0){
					$http({
						url : baseUrl + 'apis/reassign_manual_lead_assign_to_agent.php',
						method : 'POST',
						data : {enquiry_id: $scope.selectedLeads, agent_id : agent_id},
						headers : {'Content-Type' : 'application/x-www-form-urlencoded; charset=utf-8'}
					}).then(function (success){
						if(parseInt(success.data.success) === 1){
		                    dom_element.target.innerHTML   = 'Assigned';
		                    dom_element.target.disabled    = true;
		                    $('#agent_users_list_popup').modal('hide'); // hide asm popup modal
		                    alert(success.data.message);
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
				}else{
					alert("Please check at least one lead!");
					dom_element.target.innerHTML = button_innerHTML;
					dom_element.target.disabled = false;
					$('#agent_users_list_popup').modal('hide'); 
				}
			}else{
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
        	
        	$scope.filterLeads($scope.selectedItem.id, $scope.selectedSourceItem.id, $scope.updateLeadFilter, $scope.selectedUser.id, null, $scope.selectedProjectItem.id);
        });
        
        $scope.$watch('search_lead_query',function(item){
        	$scope.pagination.current_page = 1;
        	$scope.leadsData = $filter('filter')($scope.backupLeads, {$:$scope.search_lead_query});
        	
        });
        
        // pagination data 
		$scope.pagination = {
			current_page	: 1,
			pagination_size : 4,
			page_size		: 10,
			show_boundary_links : true,
			total_page		: 0,
			changePage : function (page){
				this.current_page = page;
				
				$scope.keyword = $scope.search_lead_query;
				
				$scope.filterLeads($scope.selectedItem.id, $scope.selectedSourceItem.id, $scope.updateLeadFilter, $scope.selectedUser.id, $scope.keyword, $scope.selectedProjectItem.id);
			}
		};
		// End pagination
		
		//reset update date filter
		
		$scope.resetDateFilters = function (){
            $scope.updateLeadFilter = null;
            $scope.search_lead_query = '';
            $scope.selectedSourceItem = {id : "NA", label: "N/A"};
            $scope.filterLeads($scope.selectedItem.id, $scope.selectedSourceItem.id, $scope.updateLeadFilter, $scope.selectedUser.id, null, $scope.selectedProjectItem.id);
        }
		//end 
		
    });

    
    // Bulk upload controller

    app.controller('bulkUploadLeads', ['$scope','$http','$q','baseUrl', function ($scope,$http,$q,baseUrl){
        
        
        var file = '';
        
        $scope.file_error = '';
        
        $scope.file_details = {};
        
        $scope.parseCsv = function (){
          
            $scope.file_parsing_start = true;
            
            $scope.file_error = '';
          
            $scope.rawfile = angular.element('input[name="bulk_upload_csv_file"]');
            
            file = $scope.rawfile[0].files[0];
            
            if(!file){
                $scope.file_error = 'No file uploaded';
                $scope.csvParseSuccess = '';
                $scope.file_parsing_start = false;
                return;
            }else if(parseFloat(file.size) < 0){
                $scope.file_parsing_start = false;
                $scope.file_error = 'Either your uploaded file is corrupted or has no size.';
                $scope.csvParseSuccess = '';
                return;
            }
            
            // Print file name
            $scope.file_details.name = file.name;
            
            Papa.parse(file, {
                    delimiter: '',
                    newline: '',
                    header: false,
                    dynamicTyping: false,
                    preview: 0,
                    step: '',
                    encoding: '',
                    worker: '',
                    comments: false,
                    complete: fileParseSuccess,
                    error: fileParseError,
                    download: false,
                    fastMode: '',
                    skipEmptyLines: true,
                    chunk: '',
                    beforeFirstChunk: '',
                });
        };
        
        var fileParseSuccess = function (r, f){
          
            var csv_headers = [];
            
            if(!validateCsvKeys(r.data[0])){
                
                $scope.csvParseSuccess      = '';
                $scope.file_details.name    = '';
                $scope.file_parsing_start   = false;
                $scope.file_error           = `CSV headers are not correct. Please use <strong style="color:#000 !important;">enquiry_id</strong> and <strong style="color:#000 !important;">mobile_number</strong> and <strong style="color:#000 !important;">agent_id</strong> as headers`;
                return;
            }
            
            $scope.csvParseSuccess = 'Processing CSV ...';
            
            if(r.data && r.data.length){
                           
                var post = {};
                
                post.csv    = r.data;
               
                // Remove first index of csv data array of headers 
                post.csv.splice(0, 1);
                
                var promise = $http.post(baseUrl + 'apis/reassign_manual_lead_assign_to_agent_bulk_assign.php',{post});
                            
                promise.then(function (s){
                    
                    var log = s.data.log_data;
                    
                    var log_time = new Date().getTime();
                    
                    var csv = new CSVExport(log, 'log'+ log_time);
            
                    $scope.csvParseSuccess = 'CSV file has been processed. Please Check downloaded log csv ';
                    
                    return false;
                    
                    
                }, function (e){
                    
                });
                
            } // end IF
            
            $scope.file_parsing_start = false;
        };
    
        var fileParseError = function(err, f){
            
            console.log(err);
            
        };
    
        $scope.downloadSampleCsv = function (){
            
            var example = [
                {enquiry_id: "XXX", mobile_number: 'XXXXXXXXX', agent_id: 22 }, 
                {enquiry_id: "XXX", mobile_number: 'XXXXXXXXX', agent_id: 22 }, 
                {enquiry_id: "XXX", mobile_number: 'XXXXXXXXX', agent_id: 22 },
                {enquiry_id: 'XXX', mobile_number: 'XXXXXXXXX', agent_id: 22 }
            ];
            
            var x = new CSVExport(example,'sample');
            return false;

        };
        
    
        /**
         * Function for Validating CSV headers 
         */
        function validateCsvKeys(o){
    
//            console.log(o); 
//            return false;
//            
            var keys_ok = true;
            
            if(Object.keys(o).length > 3){
                return false;
            }
            
//            for(key in o){
//                
//                if(o[key] == 'enquiry_id' || o[key] == 'agent_id' || o[key] == 'mobile_number'){
//                    keys_ok = true;
//                }else{
//                    keys_ok = false;
//                }
//            }
//            
            
            // check orders
            if(o[0]  != 'enquiry_id'){
                return false;
            }else{
                console.log('header 1 ok');
            }
            
            if(o[1] != 'mobile_number'){
                return false;
            }else{
                console.log('header 2 ok');
            }
            
            if(o[2] != 'agent_id'){
                console.log('header agent_id is missing');
                return false;
            }else{
                console.log('header 3 is ok');
            }
             
            return true;
        }
        
        function clearFileInput (){
            $scope.file_error           = '';
            $scope.csvParseSuccess      = '';
            $scope.file_details.name    = '';
            angular.element('input[name="bulk_upload_csv_file"]').val('');
        }
        
        $scope.onCloseBulkUpload  = function (){
            clearFileInput();
        };
        
        $('#bulk_upload_leads').on('hidden.bs.modal', function (e) {
            clearFileInput();
        })
        
    }]);
    // END Bulk upload controller
    
    
    
    
})(app, jQuery);