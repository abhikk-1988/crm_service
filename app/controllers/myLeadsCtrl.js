/**
 * package CRM
 * 
 * @fileOverview controller myLeadsCtrl.js
 * @author Abhishek Agrawal
 */

var app = app || {};

(function (app,$){
	
	app.controller('myLeadsCtrl' , function ($scope,$sce, $log, httpService, baseUrl, $http, disposition_status_list, $filter, user_session, $route, asm_users,leadsService){
		
		$scope.loginUser = user_session; // Container for logged in user data
        
		$scope.user_designation_slug = $scope.loginUser.designation_slug; 
        
		if($scope.loginUser.designation_slug === 'agent' || $scope.loginUser.designation_slug === 'executive'){
			$scope.hideLeadAddedByCol = true;
		}
        
        $scope.recordings = null;
	
    $scope.trustSrc = function(src) {
        return $sce.trustAsResourceUrl(src);
      }
    $scope.stopAllAudio = function(src){
        var sounds = document.getElementsByTagName('audio');
        for(i=0; i<sounds.length; i++) sounds[i].pause();
        }
	  /*
     * getting recordings
     */ 
     $scope.show_logger = function(mobileno){
		$scope.recordings = null;
		var http_logger = {
				url : baseUrl + 'apis/get_voice_logger.php',
				method : 'POST',
				data : {
					mobno : mobileno
				}
			};
			
			var logger_response = httpService.makeRequest(http_logger);
			
			logger_response.then(function (success){
				
				if(success.data){
					$scope.recordings = success.data;
				}
		
	
			});
	}

          
		/**
		 * Enquiry Status Filter List 
		 */
        $scope.enquiry_status_list = disposition_status_list;
        $scope.primary_enquiry_status_list = [];
        $scope.enquiry_filter_status = null;
        if($scope.enquiry_status_list){
            
            $scope.primary_enquiry_status_list = $filter('filter')($scope.enquiry_status_list,{parent_status : null},true);

			// Finding index number of status "Future Ref" in list
			var status_filter_list_item_to_remove = $scope.primary_enquiry_status_list.findIndex(function (item){

				var el_index ;

				if(item.id === '4'){
					return true;
				}

				return false;
			});

			// removing status from list by index number
			$scope.primary_enquiry_status_list.splice(status_filter_list_item_to_remove,1);

			// Add new filters FollowUp and Callback into the list
			$scope.primary_enquiry_status_list.push({
				id: '37',
				parent_status: 4,
				parent_status_title : 'Future References',
				status_title : 'Follow Up',
				sub_status_title : ''
			});

			$scope.primary_enquiry_status_list.push({
				id: '10',
				parent_status: 4,
				parent_status_title : 'Future References',
				status_title : 'Callback',
				sub_status_title : ''
			});
        }
    
		// Ares Sales Managers 
		$scope.area_sales_managers = asm_users;
	
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
	
		$scope.round_robin_switch_enable = false;
		
		
		// Flag to enable/ hide assign leads functionality 
		$scope.assign_leads = false;
		
		$scope.view = 'view1'; // default view 
		
		/**
		 * Switch staement to toggle multiple views according to user login
		 */
		
		switch($scope.loginUser.designation_slug.toString()){
			
			case 'sr_team_leader':
				$scope.view = 'view1';
				break;
			
			case 'area_sales_manager':
				$scope.view = 'view2';
				break;
			
			case 'sales_person':
				$scope.view = 'view3';
				break;
				
			case 'agent':
				$scope.view = 'view4';
				break;
                
            case 'executive':
				$scope.view = 'view4';
				break;
			
            case 'team_leader':
                $scope.view = 'view5';
                break;
		};

		$scope.sales_persons = [];
		
		$scope.getAreaManagerSalesPerson = function (){
			
			httpService.makeRequest({
				url : baseUrl + 'apis/get_area_manager_sales_person.php',
				method : 'POST',
				data : {
					asm_id : $scope.loginUser.id
				}
			}).then(function (successCallback){
				if(successCallback.data.length > 0){
					
                    // List of sales person reporting to logged in area sales manager
                    $scope.sales_persons = successCallback.data;
                    
                    // inclucde area sales manager in the list of sales persons 
                    
                    var _asm_self = {
                        id: $scope.loginUser.id, 
                        sales_person_name: $scope.loginUser.firstname +' '+$scope.loginUser.lastname,
                        capacity: $scope.loginUser.current_month_capacity.capacity,
                        remaining_capacity: $scope.loginUser.current_month_capacity.remaining_capacity
                    };
                    
                    // Push area sales manager
                    $scope.sales_persons.push(_asm_self);
				}
			});
		};
		
		
		if($scope.loginUser.designation_slug === 'area_sales_manager'){
			$scope.assign_leads = true;
			$scope.round_robin_switch_enable = true;
			$scope.getAreaManagerSalesPerson();
		} 
		
		$scope.disposition_status_list = disposition_status_list;
                
                
		// Login user full name 
		 
		$scope.login_user_fullname = $scope.loginUser.firstname +' ' +$scope.loginUser.lastname;

		$scope.leads = [];
	
		/**
		 * Fetch Leads from server
		 */

		$scope.getLeads = function (){
			
			var url = 'apis/getMyLeads.php';
			
			if($scope.loginUser.designation_slug === 'area_sales_manager'){
				url = 'apis/asm_leads.php';
			}
			
			if($scope.user_designation_slug === 'sales_person'){
				url = 'apis/sales_persons_lead.php'
			}
			
			var get_my_leads_config = {

				url : baseUrl + url,
				method : 'POST',
				data : $.param ({
					user_id : $scope.loginUser.id,
					designation_id : $scope.loginUser.designation,
					designation_slug : $scope.loginUser.designation_slug,
                    enquiry_filter : $scope.enquiry_filter_status,
                    date_range_filter 	: $scope.lead_creation_date_filter,
					lead_update_date_filter : $scope.lead_updation_date_filter
				}),
				headers : {'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'}

			};

			$http(get_my_leads_config).then(function (success){

				if(parseInt(success.data.success) === 1){
					
					$scope.leads = success.data.data;
                    
					// calculate total pages for paginations
					$scope.pagination.total_page = Math.ceil ( Object.keys($scope.leads).length/$scope.pagination.page_size );
					
				}else{
					if(parseInt(success.data.http_status_code) === 401){
						alert('unauthorized access..! Logout user immediate');
					}
				}
			}, function (error){
			});
		};
		
		$scope.getLeads ();
		

		/**
		 * Filter Leads by status 
		 */

                $scope.filterLeads  = function (){
                    $scope.getLeads ();
                };
        
		/**
		 * 
		 * @returns {undefined}
		 */
		$scope.getStatusTitle = function (status_id, sub_status_id){
                        
                    var status_data             = [];
                    var sub_status_data         = [];
                    var primary_disposition     = '';
                    var secondary_disposition   = '';
                        
                        
                    // If primary status or secondary status id in undefined then return NA
                    if( typeof status_id === 'undefined'){
                        return 'NA';
                    }
                        
			
                    // Primary Disposition
                    status_data = $filter('filter')($scope.disposition_status_list, {id : status_id}, true);

                    if(status_data[0]){

                        var primary_disposition = status_data[0].status_title;

                        if(sub_status_id){

                            sub_status_data  = $filter('filter')($scope.disposition_status_list, {id : sub_status_id}, true);

                            if(sub_status_data[0]){
                                secondary_disposition = sub_status_data[0].sub_status_title;
                            }
                        }
                        return primary_disposition + ' ' + secondary_disposition;
                    }else{
                        return 'NA';
                    }
                };
		
		/**
		 * Function to assign lead to area sales managers 
		 * @param <object> lead_data
		 * @param <object> dom element
		 * @returns <bool>
		 */
		
		$scope.manualLeadAssignToAsm = function (dom_element,enquiry_id,category, asm_id){
            
			/**
			 * For MPL category type leads and for leads having no category assigned
			 * we have to assign them manually to ASMs by selecting one of the ASM from popup 
			 */

            $scope.enquiry_id_for_asm_assignment 	= enquiry_id;
            $scope.lead_category_for_asm_assignment = '';
            
			// Start button animation 
			var button_innerHTML 			= dom_element.target.innerHTML; // existing button html
			dom_element.target.innerHTML 	= 'Assigning <i class="fa fa-spinner faa-spin animated"></i>';	
			dom_element.target.disabled 	= true;
		
			var http_call_data = {
				enquiry_id		: enquiry_id,
                asm_id          : asm_id
			};
		
			var assign_lead_config = {	
				url : baseUrl + 'apis/manual_lead_assign_to_asm.php',
				method : 'POST',
				data : $.param (http_call_data),
				headers : {'Content-Type' : 'application/x-www-form-urlencoded; charset=utf-8'}
			};
		
			$http(assign_lead_config).then(function (success){
				
				if(Number(success.data.success) === 1){

					showToast(success.data.message,success.data.title,'success');
                    dom_element.target.disabled    	= false;
					dom_element.target.innerHTML 	= button_innerHTML;
                    $('#asm_users_list_popup').modal('hide'); // hide asm popup modal
                    $scope.getLeads (); // get updated leads                     
				}else{
					// restore original button text 
					dom_element.target.disabled 	= false;
					dom_element.target.innerHTML 	= button_innerHTML;
					showToast(success.data.message,success.data.title,'error');
				}
			}, function (error){
				dom_element.target.innerHTML 	= button_innerHTML;
				dom_element.target.disabled 	= false;	
			});
		};
		
		
        /**
          * Function to popup asm list for lead assignment
         /**/
        
        $scope.popUpAsmList = function (enquiry_id, category){
         
            $scope.enquiry_id_for_asm_assignment    = enquiry_id;
            $scope.lead_category_for_asm_assignment = category;
            
            // open popup of asm users list 
            $('#asm_users_list_popup').modal('show');
        }
        
		/**
		 * Function: To manually assign lead to sales person
		 */
		
		$scope.showLeadAssignDialog = function (enq_id){	
			$scope.lead_assign.enquiry_id = enq_id;
			$('#sales_person_modal').modal('show');
		};
	
		$scope.assignment_method;
		
		/**
		 * Click event handler
		 * @param {object} element  DOM ELEMENT
		 * @returns {undefined}
		 */
		$scope.getAssignmentMethod = function (element){
			$scope.assignment_method = element.currentTarget.value;
		};
		
		// Lead assign scope variable
		$scope.lead_assign = {sales_person : null, enquiry_id : null};
		
		/**
		 * BS Modal hidden event handling 
		 */
		$('#sales_person_modal').on('hidden.bs.modal', function (e) { 
			e.stopPropagation();
			$scope.lead_assign.sales_person = null;
			$scope.lead_assign.enquiry_id	= null;
			
			// reload current route 
			$route.reload();
		});
		
		/**
		 * Function to assign lead to sales person if any selected
		 */
		
		$scope.assignLeadToSalesPerson = function (sales_person_id){
			
			if($scope.lead_assign.enquiry_id === null){
				alert('Please select lead to assign');
				return false;
			}
			
			$scope.lead_assign.sales_person = sales_person_id;
			
			// Assign lead 
			
			$http({
				url : baseUrl + 'apis/assign_lead_to_sales_person.php',
				method : 'POST',
				data : $.param ($scope.lead_assign),
				headers : {
					'Content-Type' : 'application/x-www-form-urlencoded; charset=utf-8'
				}
			}).then(function (successCallback){
				
				$('#sales_person_modal').modal('hide');
				
				if(parseInt(successCallback.data.success) === 1){
					
					showToast(successCallback.data.message,'Lead Assignent','success');
				}else{
					showToast(successCallback.data.error,'Lead Assignent','error');
				}
			});
		};
		
		/**
		 * 
		 * @param {type} enquiry_id
		 * @returns {undefined}
		 */
		$scope.rejectLeadAction = function (enquiry_id){
			
			$scope.reject_lead_enquiry_id = enquiry_id;
			
			// open BS modal to capture lead rejection explnation againt enquiry id 
			$('#lead_reject_modal').modal('show');
		};
		
		/*
		 * Function to reject lead by sales person
		 * @param <string> reason text of lead reason
		 * @param <number> enquiry id
		 * @returns {undefined}
		 */
		$scope.rejectLead = function (reason, eID){
		
			var lead_reject_modal = {
				reject_reason : reason,
				enquiry_id : eID,
				sales_person_id : $scope.loginUser.id
			};
			
			$http({
				url : baseUrl + 'apis/reject_lead.php',
				method : 'POST',
				data : $.param(lead_reject_modal),
				headers : {
					'Content-Type' : 'application/x-www-form-urlencoded; charset=utf-8'
				}
			}).then(function (successCallback){
				
				if(parseInt(successCallback.data) === 1){
					alert('Lead has been rejected');
				}else{
					alert('Lead could\'nt be rejected at this time.');
				}
				
				$('#lead_reject_modal').modal('hide');
			});
		};
		
		$('#lead_reject_modal').on('hidden.bs.modal', function (e) { 
			e.stopPropagation();
			$scope.lead_reject_reason		= '';
			$scope.reject_lead_enquiry_id	= null;
			$route.reload();
		});
		
		/**
		 * Function to accept lead by sales person
		 * @param {number} enquiryId
		 * @returns {undefined}
		 */
		$scope.acceptLead  = function (enquiryId){
			var lead_accept_modal = {
				enquiry_id : enquiryId,
				sales_person_id : $scope.loginUser.id
			};
			
			$http({
				url : baseUrl + 'apis/acceptLead.php',
				method : 'POST',
				data : $.param(lead_accept_modal),
				headers : {
					'Content-Type' : 'application/x-www-form-urlencoded; charset=utf-8'
				}
			}).then(function (successCallback){
				
				if(parseInt(successCallback.data) === 1){
					alert('Lead has been accepted succesfully');
				}else{
					alert('Lead could\'nt be accepted at this time');
				}
				
				$route.reload();
				
			});
		};
		
		$scope.lead_status_data = {};
		
		$scope.getLeadStatusAndDetail = function (enquiry_id){
		
			leadsService.getLeadStatus(enquiry_id).then(function (res){
            
				if(res.data){
					
					var lead_data = res.data;
					
					if( parseInt(lead_data.disposition_status_id) === 3 ){
						$scope.lead_status_data.event_date  = new Date( parseInt(lead_data.meeting.meeting_timestamp));
						$scope.lead_status_data.project		= JSON.parse(lead_data.meeting.project);
						$scope.lead_status_data.status		= 'Meeting';
                        $scope.lead_status_data.status_id = 3;
                     
					}
					else if ( parseInt(lead_data.disposition_status_id) === 6){
						$scope.lead_status_data.event_date  = new Date( parseInt(lead_data.site_visit.site_visit_timestamp));
						$scope.lead_status_data.project		= JSON.parse(lead_data.site_visit.project);
						$scope.lead_status_data.status		= 'Site Visit';
                        $scope.lead_status_data.status_id = 6;
					}
				}
			});
		};
        
        // Date Range Filter 
        $scope.filterByDateRange = function (date_range){
            $scope.getLeads();
        };
        
		/**
		 * Function to reset create lead date filter 
		 */
		$scope.resetCreateLeadDateFilter = function() {
			$scope.lead_creation_date_filter = null;
			$scope.getLeads();
		};

		/**
		 * Function to reset update lead date filter 
		 */
	
		 $scope.resetUpdateLeadDateFilter = function() {
			$scope.lead_updation_date_filter = null;
			$scope.getLeads();
		};

		/*
		 * @function: To Change Page size dynamically
		 * 
		 */
		$scope.changePageSize = function (page_size){
				
				if(typeof page_size === 'string'){
					if( 'all' === $filter('lowercase')(page_size)){
						$scope.pagination.page_size = $scope.leads.length;
					}
				}
				else if (typeof page_size === 'object' && !page_size){
					$scope.pagination.page_size = 10; // default value of page size
				}else{
					$scope.pagination.page_size = page_size;
				}
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
		
		
	});
	
	/**
	 *  callback leads 
	 */
	app.controller('callbackLeads', function($scope, $route, $log, httpService, Session, baseUrl){
		
		$scope.callback_leads = [];
		
		$scope.getCallbackLeads = function (){
		
			var http_config = {
				url : baseUrl + 'apis/callback_leads.php',
				method : 'POST',
				data : {
					user_id : $scope.loginUser.id
				}
			};
			
			var leads_response = httpService.makeRequest(http_config);
			
			leads_response.then(function (success){
				
				if(success.data){
					$scope.callback_leads = success.data;
				}
			});	
		};
		
		$scope.getCallbackLeads ();
	});
	
	/**
	 * technical issue leads 
	 */
	
	app.controller('technicalIssueLeads', function ($scope, httpService, baseUrl){
		
		$scope.technical_issues_leads = [];
		
		$scope.getTechnicalIssueLeads = function (){
			
			var http_config = {
				url : baseUrl + 'apis/technical_issue_leads.php',
				method : 'POST',
				data : {
					user_id : $scope.loginUser.id
				}
			};
			
			var leads_response = httpService.makeRequest(http_config);
			
			leads_response.then(function (success){
				
				if(success.data){
					$scope.technical_issues_leads = success.data;
				}
			});
		};
		
		$scope.getTechnicalIssueLeads ();
	});
	
	// custom directive isValue

	app.directive('isValue', function(){
		
		return {
			restrict : 'A',
			scope : {
				value : '@'
			},
			link : function (scope, tElement, tAttr){
				if(scope.value === ''){
					tElement.innerText = 'NA';
				}
			}
		};
	});

	/**
 * Directive: assign
 * 
 */

app.directive('assignBtn', function (){

	return {
	
		restrict : 'EA',
		scope : {
			emp : '=',
			enquiry_id : '@'
		},
		link : function (scope, tElement, tAttr){
			
		},
		controller : function ($scope){
			
		}
	};
});


app.directive('isEnableForAssign', function (lead_assign_for_status){
    
    return {
        restrict : 'EA',
		scope : {
			leadStatus : '@'
		},
		link : function (scope, tElement, tAttr){
			
            var is_disable = false;	
			if( lead_assign_for_status.indexOf(parseInt(scope.leadStatus)) <= -1){
			   is_disable = true;
			}
			
            if(is_disable === true){
               tElement.attr({disabled : is_disable, title : 'Can\'t assign lead at this time'});
            }
		}
    };
    
});


app.controller('todaysWorkout', function ($scope,baseUrl,$http){

	$scope.workout_leads = [];
	$scope.active_workout_tab = 1; // default active first tab
	 
	$scope.tabs = [
		{
			title: 'FollowUp', 
			disabled: false,
//			show :($scope.view == 'view44' ? true : false), 
            show :false, 
			status: {
				primary:4,
				secondary:37
			},
			view: $scope.view
		},
		
		{
			title: 'Callback',
			disabled: false, 
			show :($scope.view == 'view4' ? true : false),
			status: {
				primary:4,
				secondary:10
			},
			view: $scope.view},
		
		{
			title: 'Meeting', 
			disabled: false, 
			show :($scope.view != 'view4' ? true : false), 
			status: {
				primary:3,
				secondary:''
			}
		},
		{
			title: 'Site Visit',
			disabled: false, 
			show :($scope.view != 'view4' ? true : false),
			status: {
				primary:6,
				secondary:''
			},
			view: $scope.view
		},
		{
			title: 'Just Enquiry',
			disabled: false, 
			show:false,
			status: {
				primary:34,
				secondary:''
			},
			view: $scope.view
		},
		{
			title: 'Not Interested',
			disabled: true, 
			show: false,
			status: {
				primary:1,
				secondary:''
			}
		},
		{
			title: 'Technical Issue', 
			disabled: true, 
			show: false,
			status: {
				primary:5,
				secondary:''
			},
			view:$scope.view
		},
		{
			title: 'No Response', 
			disabled: false, 
			show:false,
			status: {
				primary:38,
				secondary:''
			},
			view : $scope.view
		}
	];
	
	
	$scope.activeTab = function (tab_index, tab){
		$scope.active_workout_tab = tab_index + 1;
		$scope.getTabContent(tab.status,$scope.loginUser.id);
	};
	
	$scope.getTabContent = function (status, userid){
	
		// api end points
		var end_point = '';
		if($scope.view === 'view4'){
		   end_point = 'getTodayWorkoutLeads.php';
		}
		else if($scope.view == 'view1'){
			end_point = 'getTLCRMTodaysAssignment.php';	
		}else if($scope.view == 'view2'){
			end_point = 'getASMtodaysAssignment.php';	 
		}else if($scope.view == 'view3'){
			end_point = 'getSalesPersonTodaysAssignment.php';
		}
		
		
		$http.post(baseUrl+'apis/'+end_point,{lead_type: status, user: userid}).then(function (response){
			$scope.workout_leads = response.data;
		});
        
	};
});
    
} (app, jQuery));


