/**
 * Edit Lead Controller 
 */

var app = app || {};


(function (app, $){

    // controller definition
    app.controller('editLeadCtrl', function ($scope, $routeParams, utilityService, $filter, primaryLeadSource, httpService, baseUrl, $http, $location,user){

		
        $scope.user = user; // Logged in user

        $scope.enquiry_id = $routeParams.enquiry_id;

        $scope.lead_id = $routeParams.lead_id;

        $scope.profession_list = app_constant.profession_list;

        $scope.primary_lead_source = primaryLeadSource.data.data; // Primary lead source list

        $scope.secondary_lead_source = [];

        $scope.stateList = [];

        $scope.cityList  = [];

        var state_list_req = utilityService.getStateList();

        state_list_req.then(function(successCallback){
            $scope.stateList = successCallback.data;
        });
		
		// Umesh Open popup Of History List
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
        
        //$scope.popUpHistoryList("12345678");
		//End
		
		
         // Function to fetch city list 
         $scope.getStateCities = function (state_id) {

            if(typeof state_id === 'object' && !state_id){
                return true;
            }

            var state_obj = $filter ( 'filter' ) ( $scope.stateList, {state_id: state_id}, true );

            var city = utilityService.getCityList ( state_id );
            city.then ( function ( response ) {
                $scope.cityList = response.data;
            });

            return state_obj;   
        };

        /**
         * Lead Model
         */
        $scope.lead                 = [];

        // function to get lead information
        $scope.getLead = function (){

           var lead =  httpService.makeRequest({
                url : baseUrl + 'apis/getLeadData.php?enquiry_id='+$scope.enquiry_id+'&lead_id='+$scope.lead_id,
                method: 'GET'
            });

            lead.then(function (response){

                if(Number(response.data.success) === 1){
                    $scope.lead = response.data.data.lead;
                    $scope.lead.enquiry_projects = response.data.data.enquiry_projects;

                    $scope.client_pref = [];
                    // client prefrences
                    if($scope.lead.client_property_preferences){

                        var client_pref_object = JSON.parse($scope.lead.client_property_preferences);

                        angular.forEach(client_pref_object, function (val){
                            
                            angular.forEach(val, function (pref){
                                $scope.client_pref.push(pref);
                            });
                        });    
                    }

                    // broadcasting event to child controller 
                    $scope.$broadcast('fetchSecondarySources', { primary_source_id: $scope.lead.leadPrimarySource });

                }
            }, function (error){

            });
        };  

        $scope.getLead();

        // Remove enquiry project
        $scope.removeProject = function (p){

            // Confirm from user to delete project 

            var confirm_delete = confirm('Do you really want to delete this project?');

            if(confirm_delete){
                $http.post(baseUrl+'apis/remove_enquiry_project.php', {enquiry_id: $scope.enquiry_id, project : p}).then(function (response){
                
                if(response.data.http_status_code === 200){

                    if(response.data.success === 1){
                        showToast(response.data.message, 'Edit Lead','success');
                        $scope.getLead();
                    }else{
                        showToast(response.data.message, 'Edit Lead','error');
                    }
                }
                else if(response.data.http_status_code === 401){
                    // user session is out. Redirect user to login screen
                    $location.path('/');
                }

            });
            }else{
                return false;
            }
        };
    });

    /**
     * Client information controller
     */
    app.controller('editClientInfoCtrl', function ($scope, $http, httpService, baseUrl,validationService,$filter){

        var vm          = $scope;
        vm.client       = {};
        vm.client_copy  = {};

        vm.getClientData = function (){

            var request_config = {
                url : baseUrl + 'apis/helper.php?method=getLeadCustomerInfo&params=enquiry_id:'+vm.enquiry_id,
                method: 'GET'
            };

            var request = httpService.makeRequest(request_config);

            request.then(function (response){
                
                if(response.data){
                    vm.client = response.data;
                    console.log(vm.client);
                    if(vm.client){

                        // Copy client object 
                        vm.client_copy = $scope.deepObjectCopy(vm.client);

                        console.log('client copy');
                        console.log(vm.client_copy);

                        // populate city list 
                        vm.changeState(vm.client.customerStateId);
                    }
                }

            });

            /**
             * Function to change state
             */
            vm.changeState = function (state_id,reset)
            {
                var state =  vm.getStateCities(state_id);

                if(state && typeof state === 'object'){
                    vm.client.customerState = state[0].state_name;
                    if(reset){
                        vm.client.customerCity = '';
                    }
                }
            };

            // watch on alternate mobile number input for length 
            vm.$watch('client.customer_alternate_mobile', function (val){
                $scope.client.customer_alternate_mobile = $filter ( 'limitTo' ) ( val, 10 );
            });

            // Check for number only
            $scope.checkForNumberOnly = function (e,num){

                angular.element(e.currentTarget).next().text('');
                if(!isFinite(num)) {
                    angular.element(e.currentTarget).next().text('Please enter numbers only').css({color:'#F00'});
                }else if(num.toString().length > 10 || num.toString().length < 10){
                    angular.element(e.currentTarget).next().text('Please enter 10 digit mobile number').css({color:'#F00'});
                }
            }
    
            
            /**
             * Function to Change City
             */
            vm.changeCity = function (city_id){

                if(typeof city_id == 'undefined'){
                    return;
                }

                var city =  $filter('filter')(vm.cityList,{city_id : city_id},true);
                if(city){
                    vm.client.customerCity = city[0].city_name;     
                }
            };  

        };

        vm.getClientData();

        /**
         * Email Validation function
         */
        vm.validateEmail = function (email_id){
            $scope.email_error = '';
            if ( ! validationService.email ( email_id ) ) {
                $scope.email_error = 'Invaid email address provided';
                return false;
            }
            return true;
        };

        /**
         * Mobile number validation
         */
        vm.validateMobile = function (m_number){

            $scope.mobile_number_error = '';

            if(!m_number)
            {
                alert('Please enter mobile number');
                return false;
            }
            else if(isNaN(m_number))
            {
                alert('Please enter a valid mobile number');
            }else if(m_number.length < 10)
            {
                alert('Please enter 10 digit mobile number');
            }
        };

        /**
         * Function to edit client information
         */
        vm.editClient = function (client_data){


            // get information of only editable fields

            var differences = $scope.findObjDiff(vm.client, vm.client_copy);

            // console.log(changed_client_pproperties);
            var changed_columns = [];
            if(differences.length > 0){
                angular.forEach(differences, function (obj, key){
                    // var _property = obj.Property.split('.');
                    // change_in_properties.push(_property[1].split('_').join(' '));

                    changed_columns.push(obj.prop.split('_').join(' '));
                });
            } 

            // 
            client_data.altered_fields  = changed_columns;
            client_data.enquiry_id      = vm.enquiry_id;

            var edit_request = $http({
                url : baseUrl + 'apis/editClient.php',
                method : 'POST',
                data : $.param(client_data),
                headers : {
                    'Content-Type' : 'application/x-www-form-urlencoded'
                }
            });

            edit_request.then(function (response){

                if(Number(response.data.success) === 1){
                    showToast(response.data.message,response.data.message_title,'success');
                    vm.getClientData();
                    vm.getLead();
                }
                else if(Number(response.data.success) === 0)
                {
                    showToast(response.data.message,response.data.message_title,'error');
                }
                else
                {

                    var client_form_errors = [];

                    if(response.data.errors){
                        angular.forEach(response.data.errors, function (val, key){
                            client_form_errors.push(val);
                        });
                    }

                    showToast(client_form_errors.join('<br/>'),response.data.message_title,'error');
                }

            }, function (error_response){

            });
        };

    });

    /**
     * Lead Status Ctrl
     */
    app.controller('leadStatusCtrl', function($scope,httpService, baseUrl){

        var vm = $scope;
        vm.leadStatus = [];

        vm.getLeadCurrentStatus = function (){

            var lead_status = httpService.makeRequest({
                url : baseUrl + 'apis/helper.php?method=getLeadStatus&params=enquiry_id:'+vm.enquiry_id,
                method : 'GET'
            });

            lead_status.then(function(response){

                if(response.data.status_data){
                    var currentLeadStatus = response.data.status_data;

                    if(!currentLeadStatus.disposition_sub_status_id){
                        vm.leadStatus.status = currentLeadStatus.type;
                    }else{
                        vm.leadStatus.status = currentLeadStatus.type + ' '+ (currentLeadStatus.sub_type ? currentLeadStatus.sub_type : '');
                    }

                    if(currentLeadStatus.disposition_status_id == 3 || currentLeadStatus.disposition_status_id == 6){
                    
                        vm.leadStatus.remark    = currentLeadStatus.enquiry_remark;
                        vm.leadStatus.address   = currentLeadStatus.event_location;
                        var event_datetime      = currentLeadStatus.event_time.split(' ');
                        vm.leadStatus.callback_date = event_datetime[0];
                        vm.leadStatus.callback_time = event_datetime[1];
                        if(event_datetime[2]){
                            vm.leadStatus.callback_time +=  ' ' + event_datetime[2];
                        }
                    }else{
                        vm.leadStatus.remark = currentLeadStatus.remark;
                        vm.leadStatus.callback_date = currentLeadStatus.callback_date;
                        vm.leadStatus.callback_time = currentLeadStatus.callback_time;
                    }
                }
            });
        };

        vm.getLeadCurrentStatus();

    });

    app.controller('leadSourceCtrl', function ($scope, $filter, httpService, $http, baseUrl){

        var vm = $scope;

        vm.getSecondaryLeadSourceList = function (primary_source_id){
                var secondary_lead_source_object = $filter('filter')(vm.primary_lead_source, {id: primary_source_id},true);
                if (secondary_lead_source_object) {
                    vm.secondary_lead_source = secondary_lead_source_object[0];
                }
            };

        $scope.$on('fetchSecondarySources', function (event, args) {
            vm.getSecondaryLeadSourceList(args.primary_source_id);
        });     

       vm.updateLeadSource = function (){

           if(!vm.lead.leadPrimarySource){
               alert('Please select lead primary source');
               return;
           }
           
           if(vm.lead.leadSecondarySource == ''){
               alert('Please select lead secondary source');
               return;
           }
           
            $http({
                url : baseUrl + 'apis/updateLeadSource.php',
                method : 'POST',
                data : {
                    primary_source_id : vm.lead.leadPrimarySource,
                    secondary_source  : vm.lead.leadSecondarySource,
                    enquiry_id        : vm.enquiry_id, 
                }
            }).then(function (response){

                if(Number(response.data.success) == 1){
                    showToast(response.data.message,response.data.message_title,'success');
                    vm.getLead();
                }else{
                     showToast(response.data.message,response.data.message_title,'error');
                }
            });
       }

    });

    app.controller('searchProjectCtrl', function($scope, httpService, baseUrl, $http, projectFilters, $filter){

        var vm = $scope;

        vm.project_cities = [];

        vm.project_mode = 1;

        vm.builder_id = 40; // Default Builder Raheja

        vm.getProjectCities = function () {

            var config = {
                url: baseUrl + 'apis/getProjectCities.php',
                method: 'GET'
            };

            var response = httpService.makeRequest ( config );

            response.then ( function ( response ) {

                if(response.data.success){
                    $scope.project_cities = response.data.city_list;
                }
            });
        };

        vm.getProjectCities (); // Call to function 

        // BMH Projects model
        vm.projects = [];

        $scope.show_project_loader = false;

        vm.getProjects = function (city){

            $scope.show_project_loader = true;
            var property_status_filter_array = [];
			var bhk_filter 					 = [];
			var property_types 			     = [];
			
			if(vm.filters.property_status.length > 0){
			   
				angular.forEach(vm.filters.property_status, function(value){
					property_status_filter_array.push(value.value)
				});
			}
			
			if(vm.filters.bhk.length > 0){
				
				angular.forEach(vm.filters.bhk, function(value){
					bhk_filter.push(value.value)
				});
			}
			
			if(vm.filters.property_types.length > 0){
			   angular.forEach(vm.filters.property_types, function(value){
					property_types.push(value.value)
				});
			} 
            var config = {
                url: baseUrl + 'apis/fetchCRMProjects.php',
                method: 'POST',
                data: $.param ( {
                    city: city,
                    ptype: property_types,
                    status_data: property_status_filter_array,
                    bhk1: bhk_filter,
                    min_price: vm.filters.budget.min,
                    max_price: vm.filters.budget.max,
                    builder_id : vm.builder_id
                }),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
                }
                };

                var projects = $http ( config );

                projects.then(function (response){
                    vm.projects = response.data.data;
                    $scope.show_project_loader = false;
                    $('#filterModal').modal('hide')
                });  

        };

        vm.property_types   = projectFilters.property_types;
		vm.budget_range     = projectFilters.budget_range;
		vm.property_status  = projectFilters.property_status;
		
		// added on 04/04/2017
		vm.bhk_range = [
			{label : '1 BHK', value : 1},
			{label : '2 BHK', value : 2},
			{label : '3 BHK', value : 3},
			{label : '4 BHK', value : 4},
			{label : '5 BHK', value : 5},
		];

        vm.filters = {
            budget: {
				min: '',
				min_label: '',
				max: '',
				max_label: ''
			},
			bhk : [],
		    property_status: [],
			property_types: [],
            resetBudget: function () {
				this.budget.min = '';
				this.budget.max = '';
				this.budget.min_label = '';
				this.budget.max_label = '';
				angular.element ( '.filter-budget-container .min-list div' ).removeClass ( 'active' );
				angular.element ( '.filter-budget-container .max-list div' ).removeClass ( 'active' );
			},
        };

        /**
		 * Setting minimum budget filter 
		 * @param {type} budget
		 * @param {type} event
		 * @returns {undefined}
		 */
		vm.setMinBudget = function ( budget, event ) {
            
			// If min value is greater than max budget value then alert user and unselect min value
            // Apply a filter to check that min value should not greater then max value 
            
            if( angular.isDefined(vm.filters.budget.max) ){
                
                if(parseInt(budget.value) > parseInt(vm.filters.budget.max)){
                    alert('Min budget value should not greater than max budget value');
                    vm.filters.budget.min       = null;
                    vm.filters.budget.min_label = null;
                    angular.element ( '.filter-budget-container .min-list div' ).removeClass ( 'active' );
                    return false;
                }
            }
            
			vm.filters.budget.min = budget.value;
			vm.filters.budget.min_label = budget.label + ' ' + budget.currency_suffix;
			angular.element ( '.filter-budget-container .min-list div' ).removeClass ( 'active' );
			angular.element ( event.currentTarget ).addClass ( 'active' );
		};

        /**
		 * Setting maximum budget filter
		 * @param {type} budget
		 * @param {type} event
		 * @returns {undefined}
		 */
		vm.setMaxBudget = function ( budget, event ) {
			
            // If max value is less then minimum then alert user and unselect max value 
            if( angular.isDefined(vm.filters.budget.min) ){
                
                if(parseInt(budget.value) < parseInt(vm.filters.budget.min)){
                    alert('Max budget value should not less than min budget value');
                    vm.filters.budget.max       = null;
                    vm.filters.budget.max_label = null;
                    angular.element ( '.filter-budget-container .max-list div' ).removeClass ( 'active' );
                    return false;
                }
            }
            
			vm.filters.budget.max = budget.value;
			vm.filters.budget.max_label = budget.label + ' ' + budget.currency_suffix;
			angular.element ( '.filter-budget-container .max-list div' ).removeClass ( 'active' );
			angular.element ( event.currentTarget ).addClass ( 'active' );
		};

        /**
		 * To reset project filters
		 * @returns {undefined}
		 */
		vm.resetFilters = function () {
			vm.filters.bhk = [];
			vm.filters.property_status = [];
			vm.filters.property_types = [];
			vm.filters.resetBudget ();
			// vm.applyFilter ();
		};

        vm.selected_enquiry_projects = [];

        vm.selectProject = function (project, e){
        
            var _checkbox_state =   angular.element(e.currentTarget).prop('checked');

            if(_checkbox_state){
                // Select project

                vm.selected_enquiry_projects.push({
                    project_id : project.project_id,
                    project_name : project.project_name,
                    project_url : project.project_url,
                    project_status: project.status
                });
            } else{
                // Unselect project 
                // find project to unselet 
                var removable_item_index = vm.selected_enquiry_projects.findIndex(function (element){

                    if(element.project_id == project.project_id){
                        return true;
                    }
                });

                // Removing project by index number 
                vm.selected_enquiry_projects.splice(removable_item_index,1);

            }
        }

        /**
         * To keep checkbox state as they were while searching in projects
         */
        vm.isSelectedProject = function ( value ) {
            
			var is_exists = vm.selected_enquiry_projects.findIndex(function(element){
                if(element.project_id == value){
                    console.log('selected - ' + value);
                    return true;
                }
            });

            if(is_exists > -1 ){
                return true;
            }else{
                return false;
            }
		};

        /**
         * Function to add more enquiry projects to existing enquiry
         */
        vm.addMoreProject = function (){

            $http({
                url : baseUrl + 'apis/update_enquiry_projects.php',
                method: 'POST',
                data : {
                    projects    : vm.selected_enquiry_projects,
                    enquiry_id  : vm.enquiry_id,
                    filters     : {
                        budget  : vm.filters.budget,
					    bhk     : vm.filters.bhk,
					    property_status: vm.filters.property_status,
					    property_types: vm.filters.property_types
                    }
                }
            }).then(function (response){

                if(Number(response.data.success) === 1){
                    showToast('Projects added successfully','Edit Lead','success');
                    vm.selected_enquiry_projects = [];
                    vm.getProjects(vm.city_query);
                    vm.getLead();
                }else{
                    showToast('We couldn\'t add projects. Please try again later','Edit Lead','error');
                }

            });

        };

        vm.changeProjectState = function (state){
            if(state){
                vm.builder_id = 40;
            }else{
                vm.builder_id = null;
            }

            vm.getProjects(vm.city_query);
        }

    });

    /**
     * Lead Status Update Controller
     */
    app.controller('leadStatusUpdateCtrl', function ($scope, $http, httpService, baseUrl,$filter,officeAddress,mandatoryRemarksActivity){

        var employee_id = $scope.user.id

        $scope.showActivityStatusButtons = false; 
        
        // Flag to determine activity remark mandatory or not
        $scope.isActivityRemarkMandate = 0;
        
        // To disable update status button on form submit
        $scope.disable_status_update_button = false;
        
        $scope.disposition_status = [];

        $scope.disableDateInput     = true;
        $scope.disableTimeInput     = true;
        $scope.disableSelectProject = true;
        $scope.disabledAddressInput  = true;
        $scope.disableAutoFillAddressInput = true;
        $scope.is_office_check = false;
        $scope.is_client_check = false;
        $scope.is_other_check = false;

        $http.get(baseUrl+'apis/get_employee_disposition_group_status.php?employee_id='+employee_id).then(function (response){
            $scope.disposition_status  = response.data;
        });

        /* Model for holding updated status information */
        $scope.updatedStatus = {};

        // Fetch sub status on select on disposition status 

        $scope.getSubDispositionStatus = function (status_id){

            
            $scope.isActivityRemarkMandate = 0;
            $scope.disposition_error_class = '';

            // filter out sub status from primary status id
            // getting activity status flag of parent disposition status
            var $parent_disposition = $filter('filter')($scope.disposition_status.parent_status,{id : status_id},true); 
            
            if($parent_disposition[0]){
            
                // Check if actvity status is bind or not
                if($parent_disposition[0].is_activity_status){
                    $scope.showActivityStatusButtons = true;
                }else{
                    $scope.showActivityStatusButtons = false;
                    $scope.updatedStatus.activity_status = null;
                }
                
                // Lowercasing title
                var s_title = $filter('lowercase')($parent_disposition[0].title);
                var underscored_s_title = $filter('trimSpace')(s_title,'_');
                
                if(underscored_s_title == 'send_mail'){
                    // Open send mail popup
                    $('#sendMailModal').modal('show');
                }
                
                // Check remarks is mandatory or not
                mandatoryRemarksActivity.findIndex(function(element){
                    
                    if(element == status_id){
                        $scope.isActivityRemarkMandate = 1;
                    }
                });
            }
            
            // Filter primary status from list
            var sub_status = $filter('filter')($scope.disposition_status.sub_status,{group_id : status_id},true);
            
            if(sub_status[0]){
                $scope.isSubStatus = true;
                $scope.disposition_sub_status = sub_status[0].childs;
            }else{
                $scope.isSubStatus = false;
                $scope.disposition_sub_status = [];
            }
            
            $scope.enableDisableDateTimeInput(status_id);
            $scope.enableDisableProjectDropdown(status_id);
            $scope.enableDisableAddressField(status_id);
        };  

        //  On select handler sub disposition list 
        
        $scope.onSelectSubDisposition = function (sub_status_id){    
            
            // Filter sub status by id 
            var $sub_status = $filter('filter')($scope.disposition_sub_status,{id: sub_status_id},true);
            
            if($sub_status[0]){
               
                if($sub_status[0].is_activity_status){
                   
                    $scope.showActivityStatusButtons = true;
                }else{
                    $scope.showActivityStatusButtons = false;
                    $scope.updatedStatus.activity_status = null;
                }
                
                 // Check remarks is mandatory or not
                mandatoryRemarksActivity.findIndex(function(element){
                    
                    if(element == sub_status_id){
                        $scope.isActivityRemarkMandate = 1;
                    }
                });
            }
        };
        
        /**
         * Set Address 
         */
        $scope.setAddress = function (type){
            switch(type){

                case 'office':
                    $scope.updatedStatus.address = officeAddress;
                    break;
                case 'client':
                $scope.updatedStatus.address =  $scope.lead.customerAddress;
                    break;
                case 'other':
                $scope.updatedStatus.address = '';
                    break;
            }
        };

        /**
         * Function to enable/disable date time input for callback date and time
         */
        $scope.enableDisableDateTimeInput = function (status_id){

            var dateTimeRequiredStatus = [3,4,6,7,47];

            var is_enable = dateTimeRequiredStatus.findIndex(function (element){
                    if(element == Number(status_id)){
                        return true;
                    }
            });

            if(is_enable < 0){
                $scope.is_select_date = false;
                $scope.disableDateInput = true;
                $scope.disableTimeInput = true;
            }else{
                $scope.is_select_date = true;
                $scope.disableDateInput = false;
                $scope.disableTimeInput = false;        
            }
        };

        /**
         * Function to change disbale attr of project dropdown
         */
        $scope.enableDisableProjectDropdown = function (status_id){
            
            var projectRequiredStatus = [3,6];

            var is_enable = projectRequiredStatus.findIndex(function (element){
                    if(element == Number(status_id)){
                        return true;
                    }
            });

            if(is_enable < 0){
                $scope.isEnquiryProject = false;
                $scope.disableSelectProject = true;
                $scope.updatedStatus.projects = null;
            }else{
                $scope.isEnquiryProject = true;
                $scope.disableSelectProject = false;     
            }  
        };

        /**
         * Function to toggle address field input 
         */
        $scope.enableDisableAddressField = function (status_id){

            var addressRequiredStatus = [3,6];

            var is_enable = addressRequiredStatus.findIndex(function (element){
                
                if(element == Number(status_id)){
                    
                        $scope.is_other_check = true;
                        return true;
                }
            });

            if(is_enable < 0){
                $scope.isAddress = false;
                $scope.disableAutoFillAddressInput  = true;
                $scope.disabledAddressInput         = true;
                $scope.updatedStatus.address        = null;
                $scope.is_office_check = false;
                $scope.is_client_check = false;
                $scope.is_other_check  = false;
            }else{
                $scope.isAddress = true;
                $scope.disableAutoFillAddressInput = false;
                $scope.disabledAddressInput = false; 
            }  
        };

        $scope.$watch('updatedStatus.date', function (val){
            if(val){
                $scope.date_error = '';
            }
        });

        var _24hr_to_12hr = [
            {
                _24_hour_value : 13,
                _12_hour_value : 01
            },
            {
                _24_hour_value : 14,
                _12_hour_value : 02
            },
            {
                _24_hour_value : 15,
                _12_hour_value : 03
            },
            {
                _24_hour_value : 16,
                _12_hour_value : 04
            },
            {
                _24_hour_value : 17,
                _12_hour_value : 05
            },
            {
                _24_hour_value : 18,
                _12_hour_value : 06
            },
            {
                _24_hour_value : 19,
                _12_hour_value : 07
            },
            {
                _24_hour_value : 20,
                _12_hour_value : 08
            },
            {
                _24_hour_value : 21,
                _12_hour_value : 09
            },
            {
                _24_hour_value : 22,
                _12_hour_value : 10
            },
            {
                _24_hour_value : 23,
                _12_hour_value : 11
            }
        ];

        /**
         * Function to convert 1 24 hour time to 12 hour time
         */
        function convert_24_hr_to_12_hr (hour){
            
            var $12_hr_time = $filter('filter')(_24hr_to_12hr,{_24_hour_value : Number(hour)},true);

            if(typeof $12_hr_time == 'object' && $12_hr_time.length > 0){
                return $12_hr_time[0]._12_hour_value;
            }else{
                return hour;
            }
        }

        $scope.time_in_12_format = '';
        $scope.$watch('updatedStatus.time', function (val){
            if(val){
                $scope.time_error = '';

                // Split hour part form time string 
                var time_string = val.split(':');
                
                time_string[0] = convert_24_hr_to_12_hr(time_string[0]);

                $scope.time_in_12_format = time_string.join(':');
            }
        });

        /**
         * Convert time format to 12 hour format
         */
        $scope.timeTo12HrFormat = function (time)
        {   // Take a time in 24 hour format and format it in 12 hour format
            var time_part_array = time.split(":");
            var ampm = 'AM';

            if (time_part_array[0] >= 12) {
                ampm = 'PM';
            }

            if (time_part_array[0] > 12) {
                time_part_array[0] = time_part_array[0] - 12;
            }

            formatted_time = time_part_array[0] + ':' + time_part_array[1] + ' ' + ampm;

            return formatted_time;
        }

        $scope.updateLeadStatus = function (status){
            
            // Validation on required fields
            if(!status.disposition_status_id){
                alert('Please select disposition status');
                $scope.disposition_error_class = 'danger';
                return false;
            }

            var has_sub_status = $filter('filter')($scope.disposition_status.sub_status,{group_id : status.disposition_status_id},true);
            
            if(has_sub_status.length){
                if(!status.disposition_sub_status_id){
                    alert('Please select sub disposition status');
                    $scope.sub_disposition_error_class = 'danger';
                    return false;
                }
            }

            var is_datetime_required = [3,4,6,7,47].findIndex(function (element){
                if(element === Number(status.disposition_status_id)){
                    return true;
                }
            });

            if(is_datetime_required > -1){
                if(!status.date){
                    alert('Please select date');
                    $scope.date_error = 'danger';
                    return false;
                }
                if(!status.time){
                    alert('Please select time');
                    $scope.time_error = 'danger';
                    return false;
                }
            }

            // Project input validation

            var is_project_required = [3,6].findIndex(function (element){
                if(element === Number(status.disposition_status_id)){
                    return true;
                }
            });

            if(is_project_required > -1){

                if(!status.projects || !status.projects){
                    alert('Please select project');
                    $scope.project_error = 'danger';
                    return false;
                }
            }

            // Address input validation
            var is_address_required = [3,6].findIndex(function (element){
                if(element === Number(status.disposition_status_id)){
                    return true;
                }
            });

            if(is_address_required > -1){
                if(!status.address){
                    alert('Please enter address');
                    $scope.address_error = 'danger';
                    return false;
                }
            }

            if(!status.remark && $scope.isActivityRemarkMandate){
                alert('Please enter remarks');
                $scope.remark_error = 'danger';
                return false;
            }

            // Pass login user id
            status.user_id = employee_id;

            // pass enquiry id with status
            status.enquiry_id = $scope.enquiry_id;

            status.remarks_mandatory = $scope.isActivityRemarkMandate;
            
            $scope.disable_status_update_button = true;
            
            // form is clear 
            $http({
                url : baseUrl + 'apis/updateLeadStatus.php',
                method : 'POST',
                data : $.param(status),
                headers : {
                    'Content-Type' : 'application/x-www-form-urlencoded'
                }
            }).then(function (response){

                if(response.data.success === 1){
                    showToast(response.data.message,response.data.title,'success');
                    $scope.getLead();
                }
                else if (response.data.success === 0) 
                {
                    showToast(response.data.message,response.data.title,'error');
                }
                else if(response.data.success === -1)
                {   
                    
                }
                
                $scope.disable_status_update_button = false;
            });

        }; // function end here

        // Set Activity Status model 
        $scope.setActivityStatus = function (act_status){
            $scope.updatedStatus.activity_status = act_status;
        };
        
    });

}(app, jQuery)); 
