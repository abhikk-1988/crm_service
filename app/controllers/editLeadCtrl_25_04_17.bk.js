    /**
     * Edit Lead Controller 
     */

    var app = app || {};

    (function (app,$){

        app.controller('editLeadCtrl' , function ($scope, $routeParams, utilityService, httpService, baseUrl, $filter, $log, primaryLeadSource, $http, $compile, projectFilters, Session, validationService, $route,officeAddress){

            $scope.user = Session.getUser (); // Current user 

            $scope.profession_list = app_constant.profession_list;
            
            $scope.enquiry_id = $routeParams.enquiry_id;

            $scope.lead_id = null;

            $scope.hide_cities = true;

            $scope.page_record_limit = 10; // page record limit 

            $scope.budget_range = projectFilters.budget_range;

			$scope.property_types = projectFilters.property_types;

			// added on 04/04/2017
			$scope.property_status = projectFilters.property_status;
		
			// added on 04/04/2017
			$scope.bhk_range = [
				{label : '1 BHK', value : 1},
				{label : '2 BHK', value : 2},
				{label : '3 BHK', value : 3},
				{label : '4 BHK', value : 4},
				{label : '5 BHK', value : 5}
			];

            $scope.project_loading = false; // project loading icon flag 

            // CAPTURE UPDATE FEILDS 
            $scope.capture_update_feilds = {
                name        : '', // current name
                mobile      : '', // current mobile number 
                email       : '', // current email address
                projects    : [], // count of total projects
                status      : '', // current status 
                alternate_mobile :  '', // current alternate number 
            };
            
            $scope.gender = [
                {
                  value: null,
                  label: 'Select Gender'
                },
                {
                  value: 'M',
                  label: 'Male'
                },
                {
                  value: 'F',
                  label: 'Female'
                }
            ];

            /**
             * Floor data 
             */

            $scope.floors = new Array;
            for (var i = 0; i <= 25; i++) {
                $scope.floors.push(i);
            }

            /**
             * Projects Filters 
             */

            $scope.filters = {
                budget: {
                    min: '',
                    min_label: '',
                    max: '',
                    max_label: ''
                },
                _pref_bhk : [],
				_pref_property_status: [],
				_pref_property_types: [],
                resetBudget: function () {
                    this.budget.min = '';
                    this.budget.max = '';
                    this.budget.min_label = '';
                    this.budget.max_label = '';
                    angular.element ( '.filter-budget-container .min-list div' ).removeClass ( 'active' );
                    angular.element ( '.filter-budget-container .max-list div' ).removeClass ( 'active' );
                },
                resetPropertyFilter: function ( event ) {

                    $ ( '#filter_property_type' ).find ( 'input[type="checkbox"]' ).prop ( 'checked', false );
                    this._pref_property_status = [];
                }
            };

            /**
             * Model property to store client information 
             */
            $scope.client_basic = [];

            /**
             * Form valiation errors object 
             */
            $scope.validation_error = {};

            // State list 
            $scope.stateList = new Array;

            // City List
            $scope.cityList = new Array;

            // Landline number
            $scope.landline  = {
                std : '',
                number : '',
                ext : ''
            };

            /**
             * Watch on client state and city modal properties 
             * @type type
             */

            $scope.$watch('client_basic.state_id', function (val){
                $scope.client_basic.state_id = val;
            });

            $scope.$watch('client_basic.city_id', function (val){
                $scope.client_basic.city_id = val;
            });

            // Watch function on customer landline number 

            $scope.$watch('client_basic.customerLandline', function(val){

                if(!angular.isUndefined (val)){

                    var landline_number_arr = val.split('-');

                    if(angular.isArray(landline_number_arr)){

                        $scope.landline.std		= landline_number_arr[0];
                        $scope.landline.number	= landline_number_arr[1];
                        $scope.landline.ext		= landline_number_arr[2];
                    }
                }			
            });

            /**
            * Site Visit 
            */
            $scope.site_visits = ['<24 hrs', '<48 hrs', '<1 Wk', '15 Days', '1 Month', '45 Days'];

            // usage 
            $scope.usage = ['Investment', 'Self Usage'];

            // Call to get state list from server 
            var state_list_req = utilityService.getStateList();

            state_list_req.then(function(successCallback){

                $scope.stateList = successCallback.data;
            });


            /**
             * Event handler for fetching cities from state_id
             */

            $scope.getStateCities = function (state_id) {


                if(typeof state_id === 'object' && state_id === ''){
                    return true;
                }

                var state_obj = $filter ( 'filter' ) ( $scope.stateList, {state_id: state_id}, true );

                $scope.client.state_name = state_obj[0].state_name;

                var city = utilityService.getCityList ( state_id );

                city.then ( function ( response ) {
                    $scope.cityList = response.data;
                });
            };

            // Set City name on select
            $scope.setCityName = function (city_id) {
                
                alert(city_id);
                if ( typeof city_id === 'object' && city_id === '' ) {

                    alert('here');
                    $scope.clientCityValidation ( null );
                    return false;
                }

                var city_obj = $filter ( 'filter' ) ( $scope.cityList, {city_id: city_id}, true );
                $scope.client_basic.city_name = city_obj[0].city_name;
            };


            if(angular.isDefined ($routeParams.lead_id)){
                $scope.lead_id = $routeParams.lead_id;
            }

            $scope.fetchLeadData = function (){

                var config = {
                   method : 'GET',
                   url : baseUrl + 'apis/getLeadData.php?enquiry_id='+ $scope.enquiry_id+'&lead_id='+$scope.lead_id
                };

                var lead_data = httpService.makeRequest(config);

                lead_data.then(function (promise){

                    if( parseInt(promise.data.success) === 1){

                        angular.forEach (promise.data.data, function (val , key){

                            switch(key){

                               case 0:
                                $scope.client_basic = val.client_basic;   
                                    
                                // save some client information in capture update detail object
                                console.log($scope.client_basic);
                                    
                                $scope.capture_update_feilds.email = $scope.client_basic.customerEmail;
                                $scope.capture_update_feilds.mobile = $scope.client_basic.customerMobile;
                                $scope.capture_update_feilds.alternate_mobile = $scope.client_basic.customer_alternatee_mobile;
                                $scope.capture_update_feilds.name = $scope.client_basic.customerName;
                                    
                                $scope.getSecondaryLeadSourceList ($scope.client_basic.leadPrimarySource);
                                $scope.getClientStateAndCity($scope.client_basic.customerState, $scope.client_basic.customerCity);
                                break;
                               case 1:
                                break;
                               case 2 :
								   $scope.agent_info = val.agent_info;
                                break;
                               case 3:
                                break;
                               case 4:
                                break;

                            }
                        });

                    }

                });
            };

            $scope.fetchLeadData ();

            $scope.client = [];

            /**
             * Function to get user state and city 
             */

            $scope.getClientStateAndCity = function (state_name, city_name){

                var filtered_state = $filter('filter')($scope.stateList,{'state_name' : state_name});

                if(angular.isArray (filtered_state) ){


                    if(angular.isDefined(filtered_state[0])){
                        var filter_state_id = filtered_state[0].state_id;
                        $scope.client_basic.state_id = filter_state_id;


                        // Call service to get city List 
                        var city_list = utilityService.getCityList(filter_state_id);

                        city_list.then(function (successCallback){

                           $scope.cityList = successCallback.data;

                           var filtered_city = $filter('filter')($scope.cityList,{'city_name' : city_name});

                           $scope.client_basic.city_id = filtered_city[0].city_id;

                        });
                    }
                }
            };


            /**
             * Lead Source List (Primary)
             */
            $scope.primary_lead_source = primaryLeadSource.data.data;

            /**
             * Function to get secondary lead source list by passing primary source id 
             * @returns {undefined}
             */
            $scope.getSecondaryLeadSourceList = function (primary_source_id){

                var secondary_lead_source_object = $filter('filter')($scope.primary_lead_source, {id: primary_source_id});
                if (angular.isDefined(secondary_lead_source_object) && secondary_lead_source_object.length > 0) {
                    $scope.secondary_lead_source = secondary_lead_source_object[0];
                }
            };


            /**
             * Function to get project cities list 
             */
            $scope.project_cities = [];

            $scope.getProjectCities = function () {

                var config = {
                    url: baseUrl + 'apis/getProjectCities.php',
                    method: 'GET'
                };

                var response = httpService.makeRequest ( config );

                response.then ( function ( response ) {

                    if ( response.data.success ) {
                        $scope.project_cities = response.data.city_list;
                    }
                } );
            };

            $scope.getProjectCities (); // Call to function 

            /**
             * Function to refresh list of project cities
             * @returns {undefined}
             */
            $scope.refreshProjectCities = function () {
                $scope.getProjectCities ();
            };

            /**
             * function to toggle project city list 
             * @returns {undefined}
             */
            $scope.showCitiesList = function () {
                $scope.hide_cities = ! $scope.hide_cities;
            };


            $scope.project = {
                city_id: null,
                city_name: ''
            };

            /**
             * Function to set the project city name 
             * @param {type} city
             * @returns {undefined}
             */
            $scope.setCityVal = function ( city ) {

                $scope.project.city_id = city.city_id;
                $scope.project.city_name = city.city_name;
                $scope.clearCityQuery ();
                $scope.showCitiesList ();
            };

            $scope.clearCityQuery = function () {
                $scope.city_query = '';
            };

            /**
             * CRM Projects 
             */
            $scope.crm_projects = [];
			$scope.featured_projects    = [];
        	$scope.selected_featured_projects = [];
            $scope.current_page_number = 1;

            /**
             * Getting projects from bookmyhouse with filters applied 
             * @param {string} city_name
             * @returns {object}
             */
            $scope.fetchCRMProjects = function ( city_name ) {
                $scope.applyFilter ();
            };

            /**
             * Function to trigger on click of search project button while searching projects for a specific city 
             * @param {type} city_name
             * @returns {undefined}
             */
            $scope.searchProject = function ( city_name ) {
                $scope.fetchCRMProjects ( city_name );
            };

            /**
             * To reset project filters
             * @returns {undefined}
             */
            $scope.resetFilters = function () {
				$scope.filters._pref_bhk = [];
				$scope.filters._pref_property_status = [];
				$scope.filters.resetPropertyFilter ();
				$scope.filters.resetBudget ();
				$scope.applyFilter ();
			};
			
			// clear all of the multiselect forms
			$scope.resetPropertyStatusFilter = function (){
				$scope.filters._pref_property_status = [];
				$scope.applyFilter();
			};

			$scope.resetPropertyTypeFilter = function (){
				$scope.filters._pref_property_types= [];
				$scope.applyFilter();
			};

			$scope.resetBHKTypeFilter = function (){
				$scope.filters._pref_bhk = [];
				$scope.applyFilter();
			};

            // Create page offset on change of page change 
            $scope.pageChange = function ( page ) {
                $scope.offset = $scope.page_record_limit * ( parseInt ( page ) - 1 );
            };
            $scope.addHoverClass = function ( element ) {
                var target = element.target;
                angular.element ( target ).addClass ( 'active' ).css ( {cursor: 'pointer'} );
            };
            $scope.RemoveHoverClass = function ( element ) {
                var target = element.target;
                angular.element ( target ).removeClass ( 'active' );
            };
            $scope.showFilters = function () {
                $ ( '#filter-modal' ).modal ( 'show' );
            };

            /**
             * Setting minimum budget filter 
             * @param {type} budget
             * @param {type} event
             * @returns {undefined}
             */
            $scope.setMinBudget = function ( budget, event ) {

                // If min value is greater than max budget value then alert user and unselect min value
                if( angular.isDefined($scope.filters.budget.max) ){
                
                    if(parseInt(budget.value) > parseInt($scope.filters.budget.max)){
                        alert('Min budget value should not greater than max budget value');
                        $scope.filters.budget.min       = null;
                        $scope.filters.budget.min_label = null;
                        angular.element ( '.filter-budget-container .min-list div' ).removeClass ( 'active' );
                        return false;
                    }
                }
                
                $scope.filters.budget.min = budget.value;
                $scope.filters.budget.min_label = budget.label + ' ' + budget.currency_suffix;
                angular.element ( '.filter-budget-container .min-list div' ).removeClass ( 'active' );
                angular.element ( event.currentTarget ).addClass ( 'active' );
            };

            /**
             * Setting maximum budget filter
             * @param {type} budget
             * @param {type} event
             * @returns {undefined}
             */
            $scope.setMaxBudget = function ( budget, event ) {

                // If max value is less then minimum then alert user and unselect max value 
                // If max value is less then minimum then alert user and unselect max value 
                if( angular.isDefined($scope.filters.budget.min) ){
                
                    if(parseInt(budget.value) < parseInt($scope.filters.budget.min)){
                        alert('Max budget value should not less than min budget value');
                        $scope.filters.budget.max       = null;
                        $scope.filters.budget.max_label = null;
                        angular.element ( '.filter-budget-container .max-list div' ).removeClass ( 'active' );
                        return false;
                    }
                }
    
                $scope.filters.budget.max = budget.value;
                $scope.filters.budget.max_label = budget.label + ' ' + budget.currency_suffix;
                angular.element ( '.filter-budget-container .max-list div' ).removeClass ( 'active' );
                angular.element ( event.currentTarget ).addClass ( 'active' );
            };

         
            $scope.selectedProjects = {
                ids: [],
                projects: []
            };

            /**
             *  To select project and put it in selcted projects list
             * @param {type} selected_project
             * @param {type} event
             * @returns {undefined}
             */
            $scope.selectProject = function ( selected_project, event ) {

                var is_checked = $ ( event.currentTarget ).prop ( 'checked' );

                if ( is_checked ) {
                    $scope.selectedProjects.ids.push ( selected_project.project_id );
                    $scope.selectedProjects.projects.push ( {project_name: selected_project.project_name, project_url: selected_project.project_url, id: selected_project.project_id, element: event} );

                }
                else {

                    var project_id_index = $scope.selectedProjects.ids.indexOf ( selected_project.project_id );
                    $scope.selectedProjects.ids.splice ( project_id_index, 1 );
                    $scope.selectedProjects.projects.splice ( project_id_index, 1 );
                }
            };

            /**
             * To remove or unselect selected projects 
             * @param {type} removed_project
             * @returns {undefined}
             */

            $scope.removeSelected = function ( removed_project ) {

                $ ( removed_project.element.currentTarget ).prop ( 'checked', false );
                var project_id_index = $scope.selectedProjects.ids.indexOf ( removed_project.id );
                $scope.selectedProjects.ids.splice ( project_id_index, 1 );
                $scope.selectedProjects.projects.splice ( project_id_index, 1 );
            };

            // Function to remove element key from selectedProject scope array objects
            $scope.removeEventFromObject = function ( obj ) {

                for ( var i = 0; i <= obj.projects.length - 1; i ++ ) {
                    delete  obj.projects[i].element;
                }

                return $scope.selectedProjects;
            };

            $scope.searchFromSelectedProjects = function ( value ) {

                if ( $.inArray ( value, $scope.selectedProjects.ids ) > - 1 ) {
                    return 1;
                }
                else {
                    return 0;
                }
            };

            /*
             * Function to check if any project filter selected or not 
             * @returns {boolean}
             */
            $scope.isFilterSelected = function () {

                var is_select = false;

                if ( $scope.filters.budget.min != '' ) {
                    is_select = true;
                }

                if ( $scope.filters.budget.max != '' ) {
                    is_select = true;
                }

                if ( $scope.filters.property_status != '' ) {
                    is_select = true;
                }

                if ( $scope.filters.bhk != '' && $scope.filters.bhk !== null ) {
                    is_select = true;
                }

                if ( $scope.filters.property_types.length > 0 ) {
                    is_select = true;
                }

                return is_select;
            };

            /**
             * Function to show or hide followup actions icons 
             * @argument {string} title 
             * description title : title of the enquiry status
             * @returns {boolean}
             */
            $scope.showHideActionIcons = function ( title ) {

                switch ( title ) {

                    case 'not_interested':
                        $scope.showActionIcons = false;
                        $scope.resetFollowupData ();
                        break;

                    case 'technical_issue':
                        $scope.showActionIcons = false;
                        $scope.resetFollowupData ();
                        break;

                    default :
                        $scope.showActionIcons = true;
                }
                ;
            };

            /**
             * Model property to store callback date & time 
             */
            $scope.followup = {};
            $scope.followup.callback_time = '';
            $scope.followup.callback_date = '';
            $scope.followup.status_remark = '';

            $scope.resetFollowupData = function () {

                $scope.followup.callback_date		= '';
                $scope.followup.callback_time		= '';
                $scope.followup.status_remark		= '';
                $scope.lead_enquiry.callback_date		= '';
                $scope.lead_enquiry.callback_time		= '';
                $scope.lead_enquiry.status_remark		= '';
            };


            /**
             * To apply filters on projects
             * @returns {undefined}
             */
            $scope.applyFilter = function () {

				// Blank featured projects array 
            	$scope.featured_projects          = [];
            	$scope.selected_featured_projects = [];

                // start loading animation 
                $scope.project_loading = true;

				var property_status_filter_array = [];
				var bhk_filter 					 = [];
				var property_types 			     = [];
			
				if($scope.filters._pref_property_status.length > 0){

					angular.forEach($scope.filters._pref_property_status, function(value){
						property_status_filter_array.push(value.value)
					});
				}
			
				if($scope.filters._pref_bhk.length > 0){

					angular.forEach($scope.filters._pref_bhk, function(value){
						bhk_filter.push(value.value)
					});
				}
			
				if($scope.filters._pref_property_types.length > 0){
				   angular.forEach($scope.filters._pref_property_types, function(value){
						property_types.push(value.value)
					});
				}
			   
                var config = {
                    url: baseUrl + 'apis/fetchCRMProjects.php',
                    method: 'POST',
                    data: $.param ( {
                        city: $scope.project.city_name,
                        ptype: property_types,
                        status_data: property_status_filter_array,
                        bhk1: bhk_filter,
                        min_price: $scope.filters.budget.min,
                        max_price: $scope.filters.budget.max
                    } ),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
                    }
                };

                var filtered_projects = $http ( config );

                filtered_projects.then ( function ( success ) {

                    if ( success.data.success ) {
                        $scope.project_loading = false;
                        $scope.crm_projects = success.data.data;
						$scope.extractFeaturedProjects();
                    }
                } );

            };

            /* End of Project filters */

            /**
             * HTTP call to get enquiry status list 
             */
            $scope.enquiry_status = {
                disposition_group: [],
                group_sub_status: []
            };

            /**
             * HTTP call to server to get disposition status list 
             */
            httpService.makeRequest ( {
                url: baseUrl + 'apis/get_employee_disposition_group_status.php?employee_id=' + $scope.user.id
            } ).then ( function ( response ) {

                if ( response.data.success ) {

                    if(response.data.parent_status){
                        $scope.enquiry_status.disposition_group = response.data.parent_status;
                    }

                    if(response.data.sub_status){
                        $scope.enquiry_status.group_sub_status = response.data.sub_status;
                    }
                }			
            } );

            $scope.showCalender = false;  // Flag to show calender for callback date and time

            // Show Address area for meeting or site visit
		    $scope.showAddressArea	= false;
            
            $scope.lead_enquiry = {}; // Holding disposition status data 

            /**
             * 
             * @returns {Boolean}
             */
            $scope.populate_sub_status = function ( group_id ) {

                $scope.sub_status				        = [];
                $scope.lead_enquiry.group_title		    = '';
                $scope.lead_enquiry.remark			    = '';
                $scope.lead_enquiry.sub_status_id		= '';
                $scope.lead_enquiry.sub_status_title	= '';

                $scope.resetFollowupData ();

                // Get selected group status title 
                var parent_group = $filter ( 'filter' ) ( $scope.enquiry_status.disposition_group, {id: group_id}, true );

                $scope.lead_enquiry.group_title = parent_group[0].title;

                var lowercase_title			= $filter ( 'lowercase' ) ( $scope.lead_enquiry.group_title ); // lowercasing title of the selected enquiry status
                
                var title_with_underscore	= $filter ( 'trimSpace' ) ( lowercase_title, '_' );
                $scope.sub_status			= $filter ( 'filter' ) ( $scope.enquiry_status.group_sub_status, {group_id: group_id} );
                $scope.showHideActionIcons ( title_with_underscore );

                // If sub status list items are there then only we show the sub status list dropdown
                $scope.enquiry_sub_status_list_item = Object.keys($scope.sub_status).length;
                
                if(title_with_underscore == 'meeting'){
                     $scope.showCalender		= true;
                     $scope.showAddressArea     = true;
                }
                else if(title_with_underscore == 'site_visit'){
                     $scope.showCalender		= true;
                     $scope.showAddressArea     = true;
                }
                else if(title_with_underscore == 'future_references'){
                     $scope.showCalender		= true;
                     $scope.showAddressArea     = false;
                }else{
                     $scope.showCalender		= false;
                     $scope.showAddressArea     = false;
                }
               
				// Apply site visit time constraint
				if(parseInt(group_id) === 6){
					$scope.applySiteVisitTimeFrame();
				}else{
					$scope.timepicker.default.list = format24();
				}
			   
            };
            // ------Code Block End-----------------------------------------------------------------------------------------------------------------------------

            /**
             * Function to handle change in enquiry sub status 
             * @returns {Boolean}
             */

            $scope.setValueEnquiryForSubStatus = function ( sub_status_item ) {

                var sub_status_selected_object = $filter ( 'filter' ) ( $scope.sub_status[0].childs, {id: sub_status_item} );

                if ( sub_status_selected_object.length > 0 ) {
                    $scope.lead_enquiry.sub_status_id = sub_status_item;
                    $scope.lead_enquiry.sub_status_title = sub_status_selected_object[0].status;

                    if ( $filter ( 'lowercase' ) ( $filter ( 'trimSpace' ) ( $scope.lead_enquiry.sub_status_title, '_' ) ) === 'cold_call' ) {
                        $scope.cold_call = 1; // This is a cold call
                        
                        // Hide calender from view if open 
                        $scope.showCalender = false;
                    }
                    else {
                        $scope.cold_call = 0;
                        $scope.showCalender= true;
                    }
                    
                     // For technical issue sub status
                    if (parseInt($scope.lead_enquiry.sub_status_id) === 19){
                        $scope.showCalender = false;
                    }else if(parseInt($scope.lead_enquiry.sub_status_id) === 20){
                         $scope.showCalender = false;
                    }else if(parseInt($scope.lead_enquiry.sub_status_id) === 21){
                         $scope.showCalender = false;
                    }
                    
                }
                else {
                    $scope.lead_enquiry.sub_status_id = null;
                    $scope.lead_enquiry.sub_status_title = '';
                }
            };
            /*End: Function to handle change in enquiry sub status */


            /**
             * Angular Date picker directive configuration 
             */

            function getDayClass(data) {

                var date	= data.date,
                mode		= data.mode;

                if (mode === 'day') {
                    var dayToCheck = new Date(date).setHours(0,0,0,0);

                    for (var i = 0; i < $scope.datepicker.events.length; i++) {
                        var currentDay = new Date($scope.datepicker.events[i].date).setHours(0,0,0,0);

                        if (dayToCheck === currentDay) {
                          return $scope.datepicker.events[i].status;
                        }
                    }
                }

                return '';
            }

            var tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);		
            var afterTomorrow = new Date(tomorrow);
            afterTomorrow.setDate(tomorrow.getDate() + 1);

            $scope.setDate = function(year, month, day) {
                $scope.dt = new Date(year, month, day);
            };

            $scope.datepicker = {

                dt : new Date() ,
                options : {
                    customClass: getDayClass,
                    minDate: new Date(),
                    showWeeks: true
                },
                events : [
                    {
                        date:	tomorrow,
                        status: 'full'
                    },
                    {
                        date:	afterTomorrow,
                        status: 'partially'
                    }
                ]
            };

            // watch on datepicker date 

            $scope.$watch('datepicker.dt', function (val){
                if(val){
                    $scope.followup.callback_date = $filter('date')(val , 'd-MM-yyyy' , '+0530');
					
					if(parseInt($scope.lead_enquiry.id) === 6){
						$scope.setCallbackTime($scope.timepicker.time); // Check now for time selected is allowed or not
					}else{
						$scope.timepicker.time = null;
						$scope.followup.callback_time = '';
					}
					
                }
            });

            function format12(){
                var temp = new Array;
                for(var i = 1; i<=12;i++){
                    temp.push (i);
                }
                return temp;
            }

            function format24(){
                var temp = new Array;
				var meridian = 'AM';
				
                for(var i = 0; i<=23;i++){
					
					if(i < 12){
						meridian = 'AM';
					}else{
						meridian = 'PM';
					}
					
                    temp.push (i + ':00 ' + meridian);
					temp.push (i + ':15 ' + meridian);
					temp.push (i + ':30 ' + meridian);
					temp.push (i + ':45 ' + meridian);
					
                }
                return temp;
            }

			function siteVisitTimeFormat(){
				var temp = new Array;
				var meridian = 'AM';
			
				for(var i = 7; i<=17;i++){

					if(i < 12){
						meridian = 'AM';
					}else{
						meridian = 'PM';
					}

					temp.push (i+':00 ' + meridian);
					temp.push (i+':15 ' + meridian);
					temp.push (i+':30 ' + meridian);
					if(i<=16){
						temp.push (i+':45 ' + meridian);	
					}
				}
				return temp;
        }

            $scope.timepicker = {

                meridian		: ['AM','PM'],
                time_format		: [12,24],
                format12 : function (){
                    return format12 ();
                },
                format24 : function (){
                    return format24 ();
                },
                default : {
                    meridian : (new Date().getHours () > 12 && 'PM' || 'AM'),
                    list : format24()
                },
                time : ''
            };


//            $scope.setCallbackTime_old = function (time){
//
//                $scope.timepicker.time = time;
//                var meridian = 'AM';
//                if(time > 11){
//                    meridian = 'PM';
//                }
//
//                $scope.followup.callback_time = time + ': 00' +' '+ meridian;
//            };

			/**
			 * Function to set date and time for meeting, site visit or callback events
			 * @param {type} time
			 * @returns {Boolean}
			 */
			$scope.setCallbackTime = function (time){
            
            var selected_date	= new Date($scope.datepicker.dt).getDate();
			var selected_year   = new Date($scope.datepicker.dt).getFullYear();
			var selected_month  = new Date($scope.datepicker.dt).getMonth () + 1;
			
			var current_date		= new Date().getDate();
			var current_year		= new Date().getFullYear ();
			var current_month		= new Date().getMonth () + 1;
			var current_meridian	= (new Date().getHours () < 12 ? 'AM': 'PM'); // get current time meridian

			

			// selected hour/time extraction from string 
			if(time !== null){
				var time_array = time.split(':');
				var hour = time_array[0];
				var meridian = time_array[1].slice(3);
				var minute   = time_array[1].slice(0,2);
			}
			
            // For site visit enquiry status book site visit after 2 hours of the current time
            if( parseInt($scope.lead_enquiry.id) === 6){
				var current_hour = new Date().getHours();
	
					if(selected_year > current_year){
						$scope.timepicker.time = time;
                        //$scope.followup.callback_time = time + ': 00' +' '+ meridian;
						$scope.followup.callback_time = time;
                        return true;
					}
					else if(selected_year == current_year) {
						
						if(selected_month > current_month){
							// allow the selected time as it is
							$scope.timepicker.time = time;
							//$scope.followup.callback_time = time + ': 00' +' '+ meridian;
							$scope.followup.callback_time = time;
							return true;
						}
						else if(selected_month == current_month){
							
							   // selected date is current date
							if(selected_date > current_date){
								// allow the selected time as it is 
								$scope.timepicker.time = time;
								//$scope.followup.callback_time = time + ': 00' +' '+ meridian;
								$scope.followup.callback_time = time;
								return true;
							}   
							else if(selected_date === current_date){
                     
								if(new Date().getHours () > hour){
								   alert('Unable to book site visit at this time. Choose another time that is 90 minutes later from now or choose next day between 7:00 AM - 5:30 PM');
								   $scope.timepicker.time = null;
								   $scope.followup.callback_time = null;
								   return false;
								}
								else{

									var selected_timestamp = new Date(selected_year+'-'+selected_month+'-'+selected_date + ' ' + hour+':'+ minute).getTime ()					
									var time_diff = Math.floor( (selected_timestamp - new Date().getTime())/60000);

									if(time_diff >= 90){

										$scope.timepicker.time = time;
										//$scope.followup.callback_time = time + ': 00' +' '+ meridian;
										$scope.followup.callback_time = time;
										return true;
									}else{
										alert('Unable to book site visit at this time. Choose another time that is 90 minutes later from now or choose next day between 7:00 AM - 5:30 PM');
										$scope.timepicker.time = null;
										$scope.followup.callback_time = null;
										return false;
									}
								}
							}
							else {
								// case for back date
							}
						}else{
							// do stuff when applicable 
						}
					}else{
						
					}
                 
                
            }
			
			$scope.timepicker.time = time;
            
//			$scope.followup.callback_time = time + ': 00' +' '+ meridian;
			$scope.followup.callback_time = time;
		};
			

            /**
             * Function to change time format
             */

            $scope.changeTimeFormat = function (format){

                if(parseInt(format) === 12){

                    $scope.timepicker.default.list = format12 ();
                }else{
                    $scope.timepicker.default.list = format24 ();
                }
            };

            /************************************************************************************/

			// Function to apply site visit time frame
			$scope.applySiteVisitTimeFrame = function (){
				$scope.timepicker.default.list = siteVisitTimeFormat ();
			};

            /**
             * Edit lead form validation function 
             * @returns {Boolean}
             */
            $scope.validation_test = function () {

                var validation_fails = false;
                ( $scope.clientNameValidation ( $scope.client_basic.customerName ) === true ? validation_fails = true : validation_fails = false );
                ( $scope.clientEmailValidation ( $scope.client_basic.customerEmail ) === true ? validation_fails = true : validation_fails = false );
                ( $scope.clientMobileNumberValidation ( $scope.client_basic.customerMobile ) === true ? validation_fails = true : validation_fails = false );
                return validation_fails;
            };

            /**************************************************************************************************/

            //		FORM INPUT VALIDATIONS 

            // CLIENT NAME VALIDATION FUNCTION 
            $scope.clientNameValidation = function ( name ) {

                $scope.name_error = '';

                if ( name === '' ) {
                    $scope.name_error = 'Client name is required';
                    return false;
                }
                return true; 
            };


            // CLIENT EMAIL VALIDATION 
            $scope.clientEmailValidation = function (email, event ) {

                $scope.email_error = '';
                if ( ! validationService.email ( email ) ) {
                    $scope.email_error = 'Invaid email address';
                    return false;
                }
                return true;
            };

            // CLIENT MOBILE NUMBER VALIDATION 
            $scope.clientMobileNumberValidation = function ( number ) {

                $scope.mobile_number_error = '';

                if ( number === '' ) {
                    $scope.mobile_number_error = 'Please enter mobile number';
                    return false;
                }

                // Validation of alphabetical characters 
                if ( ! validationService.isStringContainAlphaChar ( number ) ) {
                    $scope.mobile_number_error = 'Please enter only number\'s in mobile number ';
                    return false;
                }

                return true; 
            };

            // End validation 
            /**************************************************************************************************/

            /************************************************************************************************** 
                Edit lead function 
            /**************************************************************************************************/

            /**
             * Function to edit lead 
             * @returns {Boolean}
             */

            $scope.editLead = function (){

                if ( ! $scope.validation_test () ) {
                    return false;
                }
                
                // Run value comparator
                var changed_form_feilds = $scope.valueComparator($scope.client_basic,$scope.selectedProjects, $scope.lead_enquiry);
                
                var lead_data = {
                    client_basic_info   : $scope.client_basic,
                    projects            : $scope.removeEventFromObject ( $scope.selectedProjects ),
                    project_city        : $scope.project.city_name,
                    filters: {
                        budget  : $scope.filters.budget,
                        bhk     : $scope.filters.bhk,
                        property_status : $scope.filters.property_status,
                        property_types  : $scope.filters.property_types
                    },
                    landline : $scope.landline,
                    followup : $scope.followup,
                    user     : {
                        id	    : $scope.user.id,
                        email	: $scope.user.email
                    },
                    lead_enquiry : $scope.lead_enquiry,
                    updated_fields : changed_form_feilds
                };

                
                var http_config = {
                    url: baseUrl + 'apis/edit_lead.php',
                    method: 'POST',
                    data: $.param ( lead_data ),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
                    }
                };

                var edit_lead_response = $http(http_config);

                edit_lead_response.then(function (success){

                    if(parseInt(success.data.success) === 1){

                        $scope.notify({message: success.data.message, class: ['alert','alert-success','center-aligned']});

                    }else{
                        $scope.notify({message: success.data.message, class: ['alert','alert-warning','center-aligned']});

                    }
                }, function (error){

                });

            };

            /*End: edit lead function */
            
            
            // Value comparator function 
            $scope.valueComparator = function (client,project,enquiry){
              
                var fields = [];
                
                // comparison of client basic details
                angular.forEach(client, function (value , key){
                    
                    if(key === 'customerName'){
                        if(value !== $scope.capture_update_feilds.name){
                            fields.push('Name');            
                        }
                    }
                    else if(key === 'customerEmail'){
                        if(value !== $scope.capture_update_feilds.email){
                            fields.push('Email');    
                        }
                    }else if(key === 'customerMobile'){
                        if(value !== $scope.capture_update_feilds.mobile){
                            fields.push('Mobile');        
                        }
                    }else if(key === 'customer_alternate_mobile'){
                        
                        if(value != '' && angular.isDefined(value)){
                            
                            if(value !== $scope.capture_update_feilds.alternate_mobile ){
                                fields.push('Alteranate Number');    
                            }
                        }
                    }
                });
                
                // comparison of projects                
                if( angular.isDefined(project.ids)){
                    
                   if(project.ids.length > 0){
                       
                       var is_project_changed = false;
                       angular.forEach(project.ids, function (id){
                           
                           if($scope.capture_update_feilds.projects.indexOf(id) < 0){
                                
                               if(!is_project_changed){
                                    fields.push('Project');
                                    is_project_changed = true;   
                               }
                           }
                       });
                   }
                }
                
                // comparison of enquiry status 
                if(Object.keys(enquiry).length > 0){
                    
                    if($scope.capture_update_feilds.status != enquiry.group_title){
                        fields.push('Enquiry Status');
                    }
                }
                
                return fields; // return the array of fields which have changed while editing lead
            };
            
            
            /**
		      * function to set address type
		      * @param {type} string
		      * @returns {undefined}
		     */
		
                $scope.setAddressType = function (type){

                    if(type === 'client'){
                        $scope.lead_enquiry.address = $scope.client_basic.customerAddress;
                    }

                    if(type === 'office'){

                        $scope.lead_enquiry.address = officeAddress;
                    }

                    if(type === 'misc'){
                        $scope.lead_enquiry.address = '';
                    }
                };
				
						   
        // Fetch Featured projects from CRM projects list
        $scope.extractFeaturedProjects = function (){
            angular.forEach($scope.crm_projects, function (value){
                if(value.is_featured == true){
                   $scope.featured_projects.push(value);
                }
            });
        };
	
        // Function to send feature projects links to client in mail
        $scope.copyFeaturedProjects = function (){  
            
            // copy all the selected featured project links in hidden text area
            var links = '<ul style="list-style:none; display:inline;">';
            angular.forEach($scope.selected_featured_projects, function (value){
                links += '<li style="padding:5px;"><a href="'+value.project_url+'">'+value.project_name+'</a></li>';
            });     
            links += '</ul>';    
         
            // add new value to the custom data attribute on copy button    
            //$('#copy_featured_project_links_btn').attr({'data-clipboard-text' : links})                 
            //var clipboard = new Clipboard('#copy_featured_project_links_btn');
            // trigger copy button to copy links    
            //$('#copy_featured_project_links_btn').trigger('click', function (){});     

            // check if user has entered or not client email (fullname is optional)
            
            if($scope.client_basic.customerEmail != ''){
                
                var featured_project_mail_data = {
                    to_email            : $scope.client_basic.customerEmail,
                    to_name             : $scope.client_basic.customerName,
                    cc_email            : '',
                    bcc_email           : '',
                    featured_projects   : links
                };
                
                // Call to server
                $http({
                    url     : baseUrl +'apis/send_featured_projects_mail.php',
                    method  : 'POST',
                    data    : $.param(featured_project_mail_data),
                    headers : {
                        'Content-Type' : 'application/x-www-form-urlencoded;charset=utf-8;'
                    } 
                }).then(function (response){
                    if(Number(response.data.success) === 1){
                        alert('Featured Projects Links sent successfully');
                    }
                });
            }else{
                alert('Enter client email address or name');
            }
        };	
            
            
        });


        /**
         * Controller for lead enquiry projects 
         */
        app.controller('enquiryProjects', function ($scope, httpService, $log, baseUrl, $routeParams){

            $scope.enquiry_projects = new Array;

            $scope.getLeadEnquiryProjects = function (){

                var config = {};

                config.url  = baseUrl + 'apis/get_lead_enquiry_projects.php?enquiry_number='+$scope.enquiry_id+'&lead_number='+$scope.lead_id;

                config.method   = 'GET';

                config.data     = {};

                var projects    = httpService.makeRequest(config);

                projects.then(function(successCallback){
                    
                    $scope.enquiry_projects                 = successCallback.data.data;
                    angular.forEach($scope.enquiry_projects, function (value, key){
                        $scope.capture_update_feilds.projects.push(value.project_id);    
                    });                    
                    
                });
            };

            $scope.getLeadEnquiryProjects();

            /**
             * Function to remove project
             * @returns {undefined}
             */
            $scope.remove_project = function (enq_id, p_id){

                var config = {};

                config.url  = baseUrl + 'apis/remove_lead_enquiry_project.php';
                config.method = 'POST';
                config.data = {
                    enquiry_number : enq_id,
                    project_id : p_id
                };

                var remove_project = httpService.makeRequest (config);

                remove_project.then(function (successCallback){

                    if( parseInt(successCallback.data.success) === 1){
                        $scope.getLeadEnquiryProjects ();
                    }else{
                        alert('Project could not be removed at this time. Please try again later.');
                        return false;
                    }
                });
            };


        });

        /**
         * 
         */

        app.controller('leadHistory', function ($scope, httpService, $log, baseUrl, $routeParams,$filter){
			
			$scope.lead_status_detail = {
                future_ref : false,
                meeting : false,
                site_visit : false,
                technical_issue : false,
				no_response : false,
				just_enquiry : false
            };

            function leadStatus(){

                var enquiry_id          = $scope.enquiry_id;
                var lead_id             = $scope.lead_id;
                $scope.lead_status_data = {};
                $scope.show_lead_status_details = false;

                $scope.status_loader = {
                   start : 1, stop : 0
                };

                /**
                 * 
                 * @type String
                 */
                var url = baseUrl + 'apis/helper.php?method=getLeadStatus&params=enquiry_id:'+enquiry_id+'/lead_id:'+lead_id;

                httpService.makeRequest({url : url , method:'GET'}).
                      then(function (response){
                        $scope.status_loader.start	= 0;
                        $scope.status_loader.stop	= 1;
                        $scope.lead_status_data		= response.data.status_data;
                        $scope.capture_update_feilds.status = $scope.lead_status_data.type;
                        $scope.displayLeadDetail($scope.lead_status_data.type);
                      });
            }

            $scope.getLeadStatus = leadStatus;
            $scope.getLeadStatus ();

            $scope.getStatusData = function (status){
                $scope.show_lead_status_details = !$scope.show_lead_status_details;
            };

            /**
             * Function to showing lead status detail according to status type 
             * @returns {undefined}
             */
            $scope.displayLeadDetail  = function (status_type){

                switch($filter('lowercase')(status_type)){

                    case 'future references':
                        $scope.lead_status_detail.meeting			= false;
                        $scope.lead_status_detail.site_visit		= false;
                        $scope.lead_status_detail.technical_issue	= false;
						$scope.lead_status_detail.just_enquiry      = false;
                        $scope.lead_status_detail.no_response       = false;
                        $scope.lead_status_detail.future_ref		= true;
                    break;

                    case 'meeting':
                        $scope.lead_status_detail.site_visit		= false;
                        $scope.lead_status_detail.technical_issue	= false;
                        $scope.lead_status_detail.future_ref		= false;
						$scope.lead_status_detail.just_enquiry      = false;
                        $scope.lead_status_detail.no_response       = false;
                        $scope.lead_status_detail.meeting			= true;
                    break;

                    case 'site visit':
                        $scope.lead_status_detail.technical_issue	= false;
                        $scope.lead_status_detail.future_ref		= false;
                        $scope.lead_status_detail.meeting			= false;
						$scope.lead_status_detail.just_enquiry      = false;
                        $scope.lead_status_detail.no_response       = false;
                        $scope.lead_status_detail.site_visit		= true;
                    break;

                    case 'technical issue':
                        $scope.lead_status_detail.future_ref		= false;
                        $scope.lead_status_detail.meeting			= false;
                        $scope.lead_status_detail.site_visit		= false;
						$scope.lead_status_detail.just_enquiry      = false;
                        $scope.lead_status_detail.no_response       = false;
                        $scope.lead_status_detail.technical_issue	= true;
                    break;

					case 'no response':
						$scope.lead_status_detail.future_ref		= false;
                        $scope.lead_status_detail.meeting			= false;
                        $scope.lead_status_detail.site_visit		= false;
                        $scope.lead_status_detail.technical_issue	= false;
                        $scope.lead_status_detail.just_enquiry      = false;
                        $scope.lead_status_detail.no_response       = true;
						break;
						
					case 'just enquiry':
						$scope.lead_status_detail.future_ref		= false;
                        $scope.lead_status_detail.meeting			= false;
                        $scope.lead_status_detail.site_visit		= false;
                        $scope.lead_status_detail.technical_issue	= false;
                        $scope.lead_status_detail.no_response       = false;
                        $scope.lead_status_detail.just_enquiry      = true;
						break;
                };
            };
        });


        



    } (app,jQuery));