/**
 * Edit Lead Controller 
 */

var app = app || {};

(function (app,$){
   
	app.controller('editLeadCtrl' , function ($scope, $routeParams, utilityService,httpService, baseUrl, $filter, $log,primaryLeadSource,$http, $compile, projectFilters){
	
		$scope.enquiry_id = $routeParams.enquiry_id;
		
		$scope.lead_id = null;
		
		$scope.hide_cities = true;
		
		$scope.page_record_limit = 10; // page record limit 
		
		$scope.budget_range = projectFilters.budget_range;

		$scope.property_types = projectFilters.property_types;
		
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
			bhk: null,
			property_status: '',
			property_types: [],
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
				this.property_types = [];
			}
		};
		
		$scope.client_basic = [];
		
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

		$scope.$watch('floor_string', function (val){
			$scope.client_basic.floor_preference = val.join(',');
		});
	   
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
					$scope.landline.std = landline_number_arr[0];
					$scope.landline.number = landline_number_arr[1];
					$scope.landline.ext = landline_number_arr[2];
				}
				
			}
			
		});
		
		$scope.$watch('client_basic.landline.std', function (val){
		   
			console.log('landline std value - ' + val);
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
							$scope.getSecondaryLeadSourceList ($scope.client_basic.leadPrimarySource);
							$scope.getClientStateAndCity($scope.client_basic.customerState, $scope.client_basic.customerCity);
							break;
						   case 1:
							break;
						   case 2 :
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
			$log.info('state filter object');
			$log.info(filtered_state);
			if(angular.isArray (filtered_state)){
				
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
		$scope.current_page_number = 1;

		$scope.fetchCRMProjects = function ( city_name ) {

			// Reset All filters applied if any 
			$scope.resetFilters ();

			var config = {
				url: baseUrl + 'apis/fetchCRMProjects.php',
				method: 'POST',
				data: $.param ( {city: city_name} ),
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
				}
			};

			var project = $http ( config );

			project.then ( function ( response ) {

				if ( response.data.success ) {
					$scope.crm_projects = response.data.data;
				}
			} );
		};
		
		$scope.searchProject = function ( city_name ) {
			alert(city_name);
			$scope.fetchCRMProjects ( city_name );
		};
		
		/**
		 * To reset project filters
		 * @returns {undefined}
		 */
		$scope.resetFilters = function () {
			$scope.filters.bhk = null;
			$scope.filters.property_status = '';
			$scope.filters.resetPropertyFilter ();
			$scope.filters.resetBudget ();
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

		$scope.setMinBudget = function ( budget, event ) {

			// If min value is greater than max budget value then alert user and unselect min value

			$scope.filters.budget.min = budget.value;
			$scope.filters.budget.min_label = budget.label + ' ' + budget.currency_suffix;
			angular.element ( '.filter-budget-container .min-list div' ).removeClass ( 'active' );
			angular.element ( event.currentTarget ).addClass ( 'active' );
		};

		$scope.setMaxBudget = function ( budget, event ) {

			// If max value is less then minimum then alert user and unselect max value 


			$scope.filters.budget.max = budget.value;
			$scope.filters.budget.max_label = budget.label + ' ' + budget.currency_suffix;
			angular.element ( '.filter-budget-container .max-list div' ).removeClass ( 'active' );
			angular.element ( event.currentTarget ).addClass ( 'active' );
		};

		$scope.setBHK = function ( bhk_value ) {

			$scope.filters.bhk = bhk_value + ' BHK';
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

		$scope.resetFollowupData = function () {
			$scope.followup.callback_date = '';
			$scope.followup.callback_time = '';
			$scope.followup.status_remark = '';
			$scope.lead_enquiry.callback_date = '';
			$scope.lead_enquiry.callback_time = '';
			$scope.lead_enquiry.status_remark = '';
		};
		
		/**
		 * To set property status filter 
		 * @param {type} status
		 * @param {type} event
		 * @returns {undefined}
		 */
		$scope.setPropertyStatus = function ( status, event ) {

			$scope.filters.property_status = status;
		};
		
				/**
		 * To apply filters on projects
		 * @returns {undefined}
		 */
		$scope.applyFilter = function () {

			// First step to close the popup modal
			$ ( '#filter-modal' ).modal ( 'hide' );

			// Check if any of the filter selected
			if ( ! $scope.isFilterSelected () ) {
				return false;
			}

			var ptype = new Array;
			var bhk1 = new Array;



			if ( $scope.filters.property_types.length > 0 ) {

				console.log ( $scope.filters.property_types );
				angular.forEach ( $scope.filters.property_types, function ( val, key ) {

					var property_type_value = $filter ( 'lowercase' ) ( $filter ( 'removeForwardSlash' ) ( val, '-' ) );
					ptype.push ( property_type_value );
				} );

//			if (ptype.length > 0) {
//				ptype = angular.toJson(ptype, true);
//			}

			}

			if ( $scope.filters.bhk != '' && $scope.filters.bhk != null ) {

				bhk1.push ( $filter ( 'extractFirstLetter' ) ( $scope.filters.bhk ) );
				if ( bhk1.length > 0 ) {
					//bhk1 = angular.toJson(bhk1, true);
				}
			}

			var config = {
				url: baseUrl + 'apis/fetchCRMProjects.php',
				method: 'POST',
				data: $.param ( {
					city: $scope.project.city_name,
					ptype: ptype,
					status_data: $scope.filters.property_status,
					bhk1: bhk1,
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
					$scope.crm_projects = success.data.data;
				}
			} );

		};

		/* End of Project filters */

		
	});
	
	
	/**
	 * Controller for lead enquiry projects 
	 */
	app.controller('enquiryProjects', function ($scope, httpService, $log, baseUrl, $routeParams){
	   
		$scope.enquiry_projects = new Array;
		
		$scope.getLeadEnquiryProjects = function (){
		   
			var config = {};
			
			config.url  = baseUrl + 'apis/get_lead_enquiry_projects.php?enquiry_number='+$scope.enquiry_id+'&lead_number='+$scope.lead_id;
			
			config.method = 'GET';
			
			config.data = {};
			
			var projects = httpService.makeRequest(config);
			
			projects.then(function(successCallback){
				
				$scope.enquiry_projects = successCallback.data.data;
				
				$log.info($scope.enquiry_projects);
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
	
		function leadStatus(){
		   
			var enquiry_id = $scope.enquiry_id;
			var lead_id = $scope.lead_id;
			$scope.lead_status_data = {};
			
			$scope.show_lead_status_details = true;
			
			$scope.status_loader = {
			   start : 1, stop : 0
			};
			
			/**
			 * 
			 * @type String
			 */
			
			$scope.lead_status_detail = {
				future_ref : false,
				meeting : false,
				site_visit : false,
				technical_issue : false
			};
			
			var url = baseUrl + 'apis/helper.php?method=getLeadStatus&params=enquiry_id:'+enquiry_id+'/lead_id:'+lead_id;
			
			httpService.makeRequest({url : url , method:'GET'}).
				  then(function (response){
				     
					$scope.status_loader.start = 0;
					$scope.status_loader.stop = 1;
					$scope.lead_status_data = response.data.status_data;
					$log.info($scope.lead_status_data);
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
					$scope.lead_status_detail.meeting = false;
					$scope.lead_status_detail.site_visit = false;
					$scope.lead_status_detail.technical_issue = false;
					$scope.lead_status_detail.future_ref = true;
				break;
				
				case 'meeting':
					$scope.lead_status_detail.site_visit = false;
					$scope.lead_status_detail.technical_issue = false;
					$scope.lead_status_detail.future_ref = false;
					$scope.lead_status_detail.meeting = true;
				break;
				
				case 'site visit':
					$scope.lead_status_detail.technical_issue = false;
					$scope.lead_status_detail.future_ref = false;
					$scope.lead_status_detail.meeting = false;
					$scope.lead_status_detail.site_visit = true;
				break;
				
				case 'technical issue':
					$scope.lead_status_detail.future_ref = false;
					$scope.lead_status_detail.meeting = false;
					$scope.lead_status_detail.site_visit = false;
					$scope.lead_status_detail.technical_issue = true;
				break;
				
			};
		};
	});

} (app,jQuery));