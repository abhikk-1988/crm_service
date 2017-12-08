/**
 * View Lead Controller 
 */

var app = app || {};

(function (app, $){
	
	app.controller('viewLeadCtrl', function ($scope, Session, $log, httpService, $route, $routeParams, $rootScope, $state, baseUrl, user_session, $filter, enquiry_status, sales_persons, is_lead_accepted, $location, leadsService, officeAddress,is_lead_closed,is_editable,mandatoryRemarksActivity){
        
		// route params 
		$scope.enquiry_id = $routeParams.enquiry_id;
		
		// Is Lead editable
		$scope.is_lead_editable = is_editable;

		// current enquiry status 
		$scope.current_enquiry_status = enquiry_status; 
		
        $scope.hwc_status = '';
        
        $scope.isRemarksMandatory = 0;
        
        leadsService.getEnquiryStatus($scope.enquiry_id).then(function (response){
            $scope.hwc_status = response.data;
            $scope.hwc_status_class = $scope.hwc_status+'-status';
        });
        
		// Getting resolved user session

		if(typeof user_session === 'object'){
			$scope.currentUser = user_session;
		}else{
			$scope.logout();
		}
        
        // To toggle sms box 
		$scope.show_message_box = false;
		$scope.show_email_box 	= false;

		$scope.toggleSmsBox = function (e){
			if(e) e.stopPropagation();
			$scope.show_message_box = !$scope.show_message_box;
			$scope.show_email_box = false;
            
		};

		// To open or close email box to send user an email
		$scope.toggleEmailBox = function (e){
			
            if(e) e.stopPropagation();

			$scope.show_email_box = !$scope.show_email_box;
            
            if($scope.show_email_box){
                $('#sendMailModal').modal('show');
                $scope.show_email_box = false;
            }else{
                $('#sendMailModal').modal('hide');
            }
			$scope.show_message_box = false;
		}
        
        
		// lead meeting id 
		$scope.meeting_id         = '';
        $scope.site_visit_id      = '';
        
		var meeting_id_response = leadsService.getLeadMeetingId($scope.enquiry_id,'both');
		meeting_id_response.then(function (success){
            
            var data = success.data;
            if(data){
                $scope.meeting_id       = data.meeting_id;
                $scope.site_visit_id    = data.site_visit_id;
            }    
		});
		
        $scope.is_open = true;
        

		// Hiding right side panel of update lead status on view lead page for agents 
		// as they must change/ update lead satus via edit lead page for their own leads	
        if(is_lead_closed === 'closed' || Number($scope.currentUser.designation) === 9){
            $scope.is_open = false;
        }
       
		// lead site visit id 
		
		/**
		 * Admin Role Flag 
		 */
		if(parseInt($scope.currentUser.role )=== 1){
			$scope.is_admin_role = true;
		}else{
			$scope.is_admin_role = false;
		}
	
		/**
		 * TL CRM Flag
		 */
		
		if($scope.currentUser.designation_slug === 'tl_crm'){
			$scope.is_tl_crm = true;
		}else{
			$scope.is_tl_crm = false;
		}
		
		/**
		 * is lead accepted 
		 */
		
		$scope.is_lead_accepted  = parseInt (is_lead_accepted);
		
		// Call default state
		$state.go('view_lead_customer_info',{}, { reload: true });
		
		// If lead is available
		if($routeParams.lead_id){
			if($routeParams.lead_id.toLowerCase() !== 'null'){
				$scope.lead_id = $routeParams.lead_id;
			}else{
				$scope.lead_id = '';
			}
		}
	
		// get sub page 
		if($routeParams.sub_page){
			$scope.sub_page = $routeParams.sub_page;
		}
		
		/**
		 * Sales Persons List 
		 */
		$scope.sales_persons = sales_persons;
		
		// modal variable to keep site visit projects 
		$scope.site_visit_projects		= [];
		$scope.clientEnquiryProjects	= []; // Projects which client has made enquiries 
		
		/*
		 * Function to get project for site visit 
		 * @param {type} event
		 * @returns {undefined}
		 */
		$scope.getsiteVisitProject = function (event){
			
			var project_response = httpService.makeRequest ({
				url : baseUrl + 'apis/helper.php?method=getSiteVisitProject&params=enquiry_id:'+ $scope.enquiry_id,
				method : 'GET'
			});
			
			project_response.then(function (successResponse){
				
				if( parseInt (successResponse.data.success) === 1 ){
					$scope.site_visit_projects = $scope.clientEnquiryProjects = successResponse.data.projects;
				}
				
			}, function (errorResponse){
				
			});
			
		};
		
		$scope.getsiteVisitProject ();
		/**
		 * Function to toggle childs list items in lead action block
		 * @param {type} event
		 * @returns {undefined}
		 */
		$scope.toggleSubList = function (event){
			
			var target = event.currentTarget;
			
			$(target).parent().siblings('.sub_list').toggle();
			
		};
		
		// Lead actions to update 
		$scope.lead_actions = [];
		
		
		/**
		 * function to get disposition status assigned to logged-in user 
		 * @returns {undefined}
		 */
		$scope.getLeadActionStatus = function (){
			
			httpService.makeRequest ( {
				url: baseUrl + 'apis/get_employee_disposition_group_status.php?employee_id=' + $scope.currentUser.id
			}).then ( function ( response ) {
			
					if(response.data.parent_status){
						
						angular.forEach (response.data.parent_status, function (val, key){
							
							var temp = {};
							temp.parent_id		= val.id;
							temp.parent_title	= val.title;
							
							var childs = $filter('filter')(response.data.sub_status,{group_id : val.id}, true);
							
							if(angular.isDefined (childs[0])){
								temp.childs = childs[0].childs;
							}else{
								temp.childs = [];
							}
				
							$scope.lead_actions.push(temp);
						});
						
					}
			});
		};
		
		$scope.getLeadActionStatus ();
		
		angular.element('.action-panels').css({display : 'none'});

		/**
		 * Modal property for new lead status data
		 */
		$scope.lead_status = {
			enquiry_id	: $scope.enquiry_id,
			lead_id		: $scope.lead_id,
			status_id	: null,
			sub_status_id : null,
            hot_warm_cold_status : null
		};
		
		/**
		 * Function to update lead status values on select from lead action panel 
		 * @param {integer} parent_status_id
		 * @param {integer} sub_status_id
		 * @returns {undefined}
		 */
		$scope.changeLeadStatus = function (parent_status_id , sub_status_id){
			
			$scope.lead_status.status_id		= parent_status_id;
			$scope.lead_status.sub_status_id	= sub_status_id;
		};
		
		/**
		 * Function to validate update lead status form
		 * @returns {undefined}
		 */
		
		$scope.updateLeadFormValidation = function (data){
			
			switch( parseInt(data.status_id) ){
				
				case 3 : 
					// Case of meeting status 
					// sub status will be Done, schedule, re-scheduled
					
					// meeting done event
					if(parseInt(data.sub_status_id) === 11){ // meeting done 
						// only remark from user is required 
						
						if(angular.isDefined (data.remark) && data.remark){
							return true;
						}else if($scope.isRemarksMandatory){
                            alert('Please enter remark');   
                            return false;
                        }
				    
                        return true;
					}
					
					// Meeting scheduled and re-scheduled with client
					if(parseInt(data.sub_status_id) === 22 || parseInt(data.sub_status_id) === 12){ // meeting scheduled or rescheduled
						
						if( angular.isUndefined (data.callback_date) ){
							alert('Please set date of meeting');
							return false;
						}else{
							if(data.callback_date === ''){
								alert('Please set date of meeting'); return false;
							}
						}
						
						if( angular.isUndefined (data.callback_time) ){
							alert('Please set time for meeting');
							return false;
						}else{
							if(data.callback_time === ''){
								alert('Please set time for meeting'); return false;
							}
						}
						
						if( angular.isUndefined (data.meeting_location_type) ){
							alert('Please select meeting location'); return false;
						}else{
							if( data.meeting_location_type === ''){
								alert('Please select meeting location'); return false;
							}
						}
						
						if( angular.isUndefined (data.meeting_address) ){
							alert('Please enter meeting address'); return false;
						}else{
							if(data.meeting_address === ''){
								alert('Please enter meeting address'); return false;
							}
						}
						
						if( angular.isUndefined (data.remark) && $scope.isRemarksMandatory){
							alert('Please enter remark '); return false;
						}else if(data.remark === '' && $scope.isRemarksMandatory){
							alert('Please enter remark '); return false;
                        }
                        
						// meeting project 
						if(angular.isUndefined (data.meeting_project) || angular.equals(data.meeting_project, {})){
							alert('Please select project');  return false;
						}
						
						return true;
					}
					
					// Remark validation for meeting not done
					if(parseInt(data.sub_status_id) === 44){
						if(angular.isDefined (data.remark) && data.remark){
							return true;
						}else{
                            if($scope.isRemarksMandatory){
                                alert('Please enter remark');
                                return false;
                            }
						}
                        
                        return true;
					}

					break;
					
				case 6 : // case of site visit 
				
					// Scheduled and Re-scheduled
					if( parseInt (data.sub_status_id) === 23 || parseInt (data.sub_status_id) === 15){
						
						if( angular.isUndefined (data.callback_date) ){
							alert('Please set site visit date'); return false;
						}else{
							if(data.callback_date === ''){
								alert('Please set site visit date'); return false;
							}
						}
						
						if( angular.isUndefined (data.callback_time)){
							alert('Please set site visit time'); return false;
						}else{
							if(data.callback_time === ''){
								alert('Please set site visit time'); return false;
							}
						}
						
						if( angular.isUndefined (data.site_visit_address)){
							alert('Please enter site visit address'); return false;
						}else{
							if(data.site_visit_address === ''){
								alert('Please enter site visit address'); return false;
							}
						}
						
						if( angular.isUndefined (data.remark) && $scope.isRemarksMandatory){
							alert('Please enter remark'); return false;
						}else{
							if(data.remark === '' && $scope.isRemarksMandatory){
								alert('Please enter remark'); return false;
							}
						}
						
						if( angular.isUndefined (data.site_visit_project) ){
							alert('Please select project'); return false;
						}else{
							if( !data.site_visit_project ){
								alert('Please select project'); return false;
							}
						}
						
                        if(!$scope.checkTimeForSiteVisit($scope.lead_status.callback_time)){
                            return false;
                        }
                        
                        return true;
					}
					
					// SiteVisit Done/ Not Done
					if( parseInt(data.sub_status_id) === 14 || parseInt(data.sub_status_id) === 45){
						
						if(angular.isDefined (data.remark) && data.remark){
							return true;
						}else if($scope.isRemarksMandatory){
                                alert('Please enter remark'); return false;    
                        }
                        return true;
					}
				break;
				
				case 1: // not interested
					
					if( angular.isUndefined (data.remark) && $scope.isRemarksMandatory){
						alert('Please enter remark'); return false;
					}
					else if(data.remark === '' && $scope.isRemarksMandatory){
						alert('Please enter remark'); return false;
					}
					else{
						return true;
					}
                    
					break;
					
				case 5:
					if( angular.isUndefined (data.remark) && $scope.isRemarksMandatory ){
						alert('Please enter remark'); return false;
					}
					else if(data.remark === '' && $scope.isRemarksMandatory){
						alert('Please enter remark'); return false;
					}
					else{
						return true;
					}
                    
					break;
                    
                case 34: // Just enquiry
                    
                    if( angular.isUndefined (data.remark) && $scope.isRemarksMandatory){
						alert('Please enter remark'); return false;
					}
					else if(data.remark === '' && $scope.isRemarksMandatory){
						alert('Please enter remark'); return false;
					}
					else{
						return true;
					}
                    
					break;
                    
                case 4:
                    
                    if( angular.isUndefined (data.remark) && $scope.isRemarksMandatory){
						alert('Please enter remark'); return false;
					}
					else if(data.remark === '' && $scope.isRemarksMandatory){
						alert('Please enter remark'); return false;
					}
                    
                    if( angular.isUndefined (data.callback_date) ){
							alert('Please set callback date'); return false;
				    }else{
				        if(data.callback_date === ''){
								alert('Please set callback date'); return false;
				        }
				    }
						
				    if( angular.isUndefined (data.callback_time)){
							alert('Please set callback time'); return false;
				    }else{
							if(data.callback_time === ''){
								alert('Please set callback time'); return false;
							}
				    }
                    
                    return true;
                    break;
                    
                case 47:
                    if( angular.isUndefined (data.callback_date) ){
							alert('Please set callback date'); return false;
				    }else{
				        if(data.callback_date === ''){
								alert('Please set callback date'); return false;
				        }
				    }
						
				    if( angular.isUndefined (data.callback_time)){
							alert('Please set callback time'); return false;
				    }else{
							if(data.callback_time === ''){
								alert('Please set callback time'); return false;
							}
				    }
                    
                    if( angular.isUndefined (data.remark) && $scope.isRemarksMandatory ){
						alert('Please enter remark'); return false;
					}
					else if(data.remark === '' && $scope.isRemarksMandatory){
						alert('Please enter remark'); return false;
					}
                    
                    return true;
                   break;
                    
				case 38:
                    if( angular.isUndefined (data.remark) && $scope.isRemarksMandatory){
						alert('Please enter remark'); return false;
					}
					else if(data.remark === '' && $scope.isRemarksMandatory){
						alert('Please enter remark'); return false;
					}
					else{
						return true;
					}
                    break;
                    
                // Reject Status    
                case 48:
                    
                    if( angular.isUndefined (data.remark) && $scope.isRemarksMandatory){
						alert('Please enter remark'); return false;
					}
					else if(data.remark === '' && $scope.isRemarksMandatory){
						alert('Please enter remark'); return false;
					}
					else{
						return true;
					}
                    
                    break;
                    
                case 46:
                    return true;
                    break;    
                    
                default:
                    break;
			}
		};
		
        $scope.errors = [];
        
		/**
		 * function to update lead status 
		 * @returns {undefined}
		 */
		
		$scope.updateLeadStatus = function (status_data){
		
			if(! $scope.updateLeadFormValidation(status_data)){
               
                return false;
			}

            
			status_data.employee_id		= $scope.currentUser.id; // current logged in user id 
			
            if($scope.meeting_id){
                status_data.meeting_id		= $scope.meeting_id;    
            }
            
            if($scope.site_visit_id){
                status_data.site_visit_id   = $scope.site_visit_id;    
            }
			
            // Append remark with status_data
            status_data.isRemarksMandatory = $scope.isRemarksMandatory;
            
            httpService.makeRequest({
				url : baseUrl + 'apis/update_lead_status.php',
				method : 'POST',
				data : status_data
			}).then(function (success){
		
				if(parseInt(success.data.success) === 1) {
					alert('Lead status updated successfully');
				}else{
					
                    if(angular.isDefined(success.data.errors)){
                        angular.forEach(success.data.errors, function (val){
                            $scope.errors.push(val);
                        });
                    }
                    
					alert('Something went wrong. Lead couldn\'t be updated');
                    return false;
				}
		
				// Hiding the modal and then reload the route 
				$('#action_modal').modal('hide');
				
				//$scope.reloadRoute ();
				
			}, function (error){
				
			});	
		};

		/**
		 * Function to change lead status wiht no child status 
		 * @returns {undefined}
		 */
		$scope.changeParentLeadAction = function (action_id){
			
            $scope.isRemarksMandatory = 0; // make it disable for every action selection
            
            mandatoryRemarksActivity.findIndex(function(element){
                
                if(element == action_id){
                    $scope.isRemarksMandatory = 1;
                }
            });
            
			$scope.lead_status.status_id		= action_id;
			$scope.lead_status.sub_status_id	= 'NULL';
            
            if(parseInt(action_id) === 1){
                $scope.lead_status.is_not_intrested = true;
            }
            else if(parseInt(action_id) == 48){
                $scope.lead_status.lead_reject = true;    
            }
            else if( parseInt(action_id) === 34){
                $scope.lead_status.just_enquiry = true;
            }
            else if(parseInt(action_id) === 47){
                $scope.lead_status.is_call_back = true;
            }
            else if(parseInt(action_id) === 46){
                
                $scope.lead_status.is_send_mail = true;
                
                $('#sendMailModal').modal('show');
                
                // Set lead status as <send mail>
                $scope.updateLeadStatus($scope.lead_status);
                return true;
                
            }
            else if(parseInt(action_id) === 5){
                $scope.lead_status.is_technical_issue = true;
            }
            
            $('#action-panel-'+action_id).show();
			$('#action_modal').modal('show');
		};

		/**
		 * function to reload current route 
		 */
		
		$scope.reloadRoute = function (state){
				$route.reload();
		};
		

		// Event on close of action panel 
		$('#action_modal').on('hidden.bs.modal', function (e) {
			
			// Hide all panels in leadAction Popup modal
			$('.action-panels').css({display : 'none'});
			
			// Reset lead_status modal property on close of popup
			var reset_lead_status = {
				enquiry_id		: $scope.enquiry_id,
				lead_id			: $scope.lead_id,
				status_id		: null,
				sub_status_id	: null
			};
			
            $scope.errors = [];
			$scope.lead_status           = reset_lead_status;
			$scope.meeting_location_type = null;
            $scope.lead_status.meeting_address = '';
            
		});


		/**
		 * Function to assign lead to sales person
		 * @returns {undefined}
		 */
		$scope.assign_lead = function (sales_person_id){
			
		};
		
		/**
		 * Function to change location 
		 * @returns {undefined}
		 */
		$scope.changePath = function (path){
			
			$location.path('/cheque-collection/20947/7/16');			
		};
		
		/**
		 * Function to set meeting project on selection
		 * @returns {undefined}
		 */
		$scope.setMeetingProject	= function (p){
			
			$scope.lead_status.meeting_project = {};
			
			if(p){
				$scope.lead_status.meeting_project = {
					id		: p.project_id,
					name	: p.project_name,
					url		: p.project_url,
					city	: p.project_city
				};
			}
		};
        
        // set default office address 
        $scope.setOfficeAddress = function (address_type){
           
            if(address_type === 'office'){
                $scope.lead_status.meeting_address = officeAddress;
            }
            else if(address_type === 'client'){
                $scope.$broadcast('sendClientAddress', {});   
            }
            else{
                $scope.lead_status.meeting_address = '';
            }
        };
        
        
        $scope.getClientAddress = function (address){
            $scope.lead_status.meeting_address = address;
        };
        
        
        
        // Site visit booking logic
        // Old function for checking site visit time 
        // do not use this function as this function has some flaws
        $scope.checkTimeForSiteVisitOld = function (time){
          
           $scope.site_visit_booking_error = '';    
            
           if(angular.isUndefined($scope.lead_status.callback_date)){
               $scope.lead_status.callback_time = '';    
               alert('Please select site visit date');
               return false;
           }
        
           var current_date      = new Date().getDate();
           var current_timestamp = new Date().getTime(); // unix time in miliseconds   
           var current_time      = new Date().getHours(); 
           var current_month     = new Date().getMonth() + 1;     
           var selected_date     = new Date($scope.lead_status.callback_date).getDate(); 
           var selected_month    = new Date($scope.lead_status.callback_date).getMonth();
            
           try{
                var selected_time     = parseInt(time.toString().slice(0,2));    
           }
           catch(e){
                alert('Please select site visit time');
                return false;
            }
            
            var selected_date_timestamp = new Date($scope.lead_status.callback_date + ' ' + time).getTime();    
            
           // if selected date is today
           if(selected_date === current_date){
               
                if(selected_time < current_time){
                    
                  $scope.site_visit_booking_error  = 'Site visit booking is not allowed for the selected time';
                  $scope.lead_status.callback_time = '';    
                  return false;
                    
                }else{

                    //var time_diff = selected_time - current_time; 

                    // Time difference in minutes          
                    var time_diff = Math.floor( (selected_date_timestamp - current_timestamp) /1000/60);  
                    
                    if(time_diff < 90){
                        
                        $scope.lead_status.callback_time = null;
                        $scope.site_visit_booking_error = 'Site visit can be set 90 minutes later from now or choose next day between 7 AM to 5:30 PM.';
                        return false;
                    }
                    else{
                        $scope.site_visit_booking_error  = '';
                        $scope.lead_status.callback_time = time; 
                        return true;
                    }
                }    
            }
            // If selected date is greater than today date
            else if(selected_date > current_date){
                $scope.site_visit_booking_error  = '';
                $scope.lead_status.callback_time = time;
                return true;
            }
            // If selected date is less than today date
            else if(selected_date < current_date){
                $scope.site_visit_booking_error  = 'Site Visit is not allowed back date';
                $scope.lead_status.callback_time = '';
                return false;
            }
        };
        
        // New function for booking site visit
        $scope.checkTimeForSiteVisit = function (time){
          
           $scope.site_visit_booking_error = '';    
            
           if(angular.isUndefined($scope.lead_status.callback_date)){
               $scope.lead_status.callback_time = '';    
               alert('Please select site visit date');
               return false;
           }
        
           var current_date      = new Date().getDate();
           var current_timestamp = new Date().getTime(); // unix time in miliseconds   
           var current_time      = new Date().getHours(); 
           var current_month     = new Date().getMonth() + 1; 
           var current_year      = new Date().getFullYear();    
           
           var selected_date     = new Date($scope.lead_status.callback_date).getDate();
           var selected_month    = new Date($scope.lead_status.callback_date).getMonth() + 1; 
           var selected_year     = new Date($scope.lead_status.callback_date).getFullYear();         
   
           try{
                var selected_time     = parseInt(time.toString().slice(0,2));    
           }
           catch(e){
                alert('Please select site visit time');
                return false;
            }
            
            var selected_date_timestamp = new Date($scope.lead_status.callback_date + ' ' + time).getTime();    
            
            // When seelcted year is greater then current year 
            if(Number(selected_year) > current_year){
               
                $scope.site_visit_booking_error  = '';
                $scope.lead_status.callback_time = time;
                return true;
            }
            // When selected year is same as current year 
            else if(Number(selected_year) === current_year){
                // When selected month is greater then current month
                
                if(Number(selected_month) > current_month){
                       $scope.site_visit_booking_error  = '';
                       $scope.lead_status.callback_time = time;
                       return true;
                }
                else if(Number(selected_month) < current_month){
                    // Site visit is not allowed for past month of current year
                     $scope.site_visit_booking_error  = 'Site Visit is not allowed back date';
                     $scope.lead_status.callback_time = '';
                     return false;
                }
                else{
            
                    // Case when booking is for same month
                    // if selected date is today
                    if(selected_date === current_date){
               
                        if(selected_time < current_time){
                    
                            $scope.site_visit_booking_error  = 'Site visit booking is not allowed for the selected time';
                            $scope.lead_status.callback_time = '';    
                            return false;
                        }else{

                            //var time_diff = selected_time - current_time; 

                            // Time difference in minutes          
                            var time_diff = Math.floor( (selected_date_timestamp - current_timestamp) /1000/60);  
                    
                            if(time_diff < 90){
                        
                                $scope.lead_status.callback_time = null;
                                $scope.site_visit_booking_error = 'Site visit can be set 90 minutes later from now or choose next day between 7 AM to 5:30 PM.';
                                return false;
                            }
                            else{
                                $scope.site_visit_booking_error  = '';
                                $scope.lead_status.callback_time = time; 
                                return true;
                            }
                        }    
                    }
                    
                    // If selected date is greater than today date
                    else if(selected_date > current_date){
                        $scope.site_visit_booking_error  = '';
                        $scope.lead_status.callback_time = time;
                        return true;
                    }
                    // If selected date is less than today date
                    else if(selected_date < current_date){
                        $scope.site_visit_booking_error  = 'Site Visit is not allowed back date';
                        $scope.lead_status.callback_time = '';
                        return false;
                    }
                }
            }
            else if(Number(selected_year) < current_year){
                // Site visit is not allowed for past year
                $scope.site_visit_booking_error  = 'Site Visit is not allowed back date';
                $scope.lead_status.callback_time = '';
                return false;
            }    
        };
        

		// redirect to edit lead page
		$scope.editLead = function (enquiry_id, lead_number){
			$location.path('/edit-lead/'+enquiry_id+'/'+lead_number);
		}; 
        
        // Set lead status
        $scope.setLeadHotWarmColdStatus = function (status){
            $scope.lead_status.hot_warm_cold_status = status;
        }
        
        /**
         * Function to change remarksMandatory flag value
         */
        $scope.changeRemarksMandatoryValue = function (flag_value){
            $scope.isRemarksMandatory = flag_value;
        }
        
	}); // End of controller function
	
	
	/**
	 * Custom directive to save lead action attributes  
	 */
	
	
	app.directive('actionPopupDialog', function (baseUrl, httpService, $filter, utilityService, $location,mandatoryRemarksActivity){
		
			return {
			
				strict : 'A',
				
				link : function (scope, iElement, iAttr){
				
					iElement.click(function (){
						
						var action		= iAttr.action; // lead action id
						var sub_action	= iAttr.subAction;

                        mandatoryRemarksActivity.findIndex(function (element){
                            
                            if(sub_action == element){
                                // call parent function to change remarkMandatory flag value
                                scope.changeRemarksMandatoryValue(1);
                            }
                        });
                        
						scope.setSubStatusType(sub_action);
						
						scope.changeLeadStatus(action, sub_action); // updating lead status id and sub statusId in parent modal property
						
						scope.callActionTemplate(action, sub_action);
					});
				},
				
				controller : function ($scope){		
					
					$scope.setSubStatusType		= function (val){
						
                        
						if(parseInt(val) === 11){
							$scope.lead_status.is_meeting_done = true;
						}else{
							$scope.lead_status.is_meeting_done = false;
						}
						
						if(parseInt(val) === 12){
							// meeting re-schedule
							$scope.lead_status.is_meeting_rescheduled = true;
						}else{
							$scope.lead_status.is_meeting_rescheduled = false;
						}
						
						if(parseInt(val) === 22){
							$scope.lead_status.is_meeting_scheduled = true;
						}else{
							$scope.lead_status.is_meeting_scheduled = false;
						}
						
						if(parseInt(val) === 14){
							$scope.lead_status.site_visit_done = true;
						}else{
							$scope.lead_status.site_visit_done = false;
						}
						
						if(parseInt(val) === 15){
							$scope.lead_status.is_site_visit_rescheduled = true;
						}else{
							$scope.lead_status.is_site_visit_rescheduled = false;
						}
						
						if(parseInt(val) === 23){
							$scope.lead_status.is_site_visit_scheduled = true;
						}else{
							$scope.lead_status.is_site_visit_scheduled = false;
						}
						
						if(parseInt(val) === 10){
							$scope.lead_status.is_call_back = true;
						}else{
							$scope.lead_status.is_call_back = false;
						}
						
						if(parseInt(val) === 19 || parseInt(val) === 20 || parseInt(val) === 21){
							$scope.lead_status.is_technical_issue = true;
						}else{
							$scope.lead_status.is_technical_issue = false;
						}
						
						if( parseInt(val) === 16){
							
							var path = {
								url : 'cheque-collection',
								data : {
									enquiry_number : $scope.enquiry_id,
									status_id : 7,
									sub_status_id : val
								}
							};
							
							$scope.changePath(path);
							
						}
                        
                        if(Number(val) === 37){
                            $scope.lead_status.is_follow_up = true;
                        }else{
                            $scope.lead_status.is_follow_up = false;
                        }
						
						// Meeting Not Done
						if(Number(val) === 44){
							$scope.lead_status.is_meeting_not_done = true;
						}else{
							$scope.lead_status.is_meeting_not_done = 0;
						}
                        
                        // For Not Interested
                        if(Number(val) === 50 || Number(val) === 51 || Number(val) === 52){
                            $scope.lead_status.is_not_intrested = true;
                        }else{
                            $scope.lead_status.is_not_intrested = false;
                        }
					};
                    
					$scope.callActionTemplate	= function (action_id, sub_action_id){
					
					
						switch(parseInt(action_id)){
								
							case 5: // Technical Issue 
								$('#action-panel-5').show();
								$('#action_modal').modal('show');
							break;

							case 3: // Meeting 
								if(parseInt(sub_action_id) == 12 || parseInt(sub_action_id) === 22){
									$('#action-panel-3').show();
								}

								// Meeting Done
								if(parseInt(sub_action_id) === 11){
									$('#action-panel-11').show();
								}

								// Meeting Not Done
								if(parseInt(sub_action_id) === 44){
									$('#action-panel-44').show();
								}
								
								$('#action_modal').modal('show');
							break;

							case 4: // Future reference
								if(parseInt(sub_action_id) !== 18){
									$('#action-panel-4').show();
									$('#action_modal').modal('show');
								}else{

									httpService.makeRequest({
										url : baseUrl + 'apis/helper.php?method=setAsColdLead&params=enquiry_id:'+$scope.enquiry_id+'/lead_id:'+$scope.lead_id+'/emp_id:'+$scope.currentUser.id,
										method : 'GET'
									}).then(function (success){

										if(parseInt(success.data.success) === 1){
											alert('success');
										}else{
											alert('failure');
										}

										$scope.reloadRoute();

									}, function (error){

									});
								}
							break;

							case 6: // site visit
								
								if(parseInt (sub_action_id) === 23){
								    $('#action-panel-6').show();
								}
                                if(parseInt(sub_action_id) === 14){
									$('#action-panel-14').show();  
								}
                                if(parseInt(sub_action_id) === 45){
									$('#action-panel-45').show(); 
								}
								$('#action_modal').modal('show');
							break;
                                
                            case 38:
                                $('#action_modal').modal('show');
                                $scope.lead_status.is_no_response = true;
                                var sub_status = [39,40,41,42,43];
                                if(sub_status.indexOf(parseInt(sub_action_id)) > -1){
                                   $('#action-panel-40').show();
                                }
                                
                            break;
                                
                            case 1:
                                $('#action_modal').modal('show');
                                $('#action-panel-1').show();
                                break;
						}
					};
						
				}
			};
	}); // end of directive 
	
	app.directive('eventTime', function ($filter){
		
		return {
			restrict : 'E',
			replace: false,
			scope : {
				data : '='
			},
			link : function (scope, element, attr){
                
				var event = scope.data.status_slug;
				
				if(angular.isDefined(event) && scope.data[event]){
                
					var event_data = scope.data[event];    
                    
                    var timest = scope.data.event_timestamp;
                    
                    if(event == 'site_visit'){
                        timest = event_data.site_visit_timestamp;
                    }
					else if(event == 'meeting'){
						timest = event_data.meeting_timestamp;
					}
                    
					// 1494324182811
					var formated_date = $filter('date')(timest,'dd MMM, yyyy HH:mm a','+0530');

					var template = '<span style="font-weight:normal; color:lightseagreen">('+formated_date+')</span> ';
					element.html(template);
				}else{
                    
                    if(scope.data.disposition_status_id == 47 && scope.data.future_followup_date && scope.data.future_followup_time){
						var template = ' <span style="font-weight:normal; color:lightseagreen">('+$filter('date')(scope.data.future_followup_date,'dd/MMM/yyyy','+0530')+' '+ scope.data.future_followup_time+')</span> ';
                    }else if(scope.data.lead_closure_date != null){
                       
                        var template = ' <span style="font-weight:normal; color:lightseagreen">('+$filter('date')(scope.data.lead_closure_date,'dd/MMM/yyyy HH:mm a','+0530')+')</span> ';
                    }
                    else{
                        var template = '';
                    }
					
					element.html(template);
				}
			}
		};
		
	});

}(app, jQuery));