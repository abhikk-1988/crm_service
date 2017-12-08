/**
 * Add Lead Controller
 */

var app = app || {};

(function (app, $) {

    app.controller('addLeadCtrl', function ($scope, $rootScope, $http, $location, Session, utilityService, baseUrl, httpService, $log, projectFilters, $filter, user_session, validationService, $route, $compile, tlcrm_config, officeAddress, hide_cal_for_sub_status, mandatoryRemarksActivity) {

        $scope.user = user_session;

        $scope.disable_add_lead_button = false;

        $scope.showActivityStatusButtons = false;

        // change activity Status
        $scope.setActivityStatus = function (act_status) {
            $scope.lead_enquiry.activity_status = act_status;
        };

        // Flag to determine activity remark mandatory or not
        $scope.isActivityRemarkMandate = 0;

        $scope.project_mode = 1;

        $scope.builder_id = 40; // Default Builder ID of Raheja Developers

        $scope.profession_list = app_constant.profession_list;

        $scope.hide_cities = true;

        $scope.property_types = projectFilters.property_types;

        $scope.budget_range = projectFilters.budget_range;

        // added on 04/04/2017
        $scope.property_status = projectFilters.property_status;

        // added on 04/04/2017
        $scope.bhk_range = [
            {label: '1 BHK', value: 1},
            {label: '2 BHK', value: 2},
            {label: '3 BHK', value: 3},
            {label: '4 BHK', value: 4},
            {label: '5 BHK', value: 5},
        ];

        $scope.max_mobile_num_length = 10;

        $scope.showActionIcons = false; // A flag to show or hide action icons based on enquiry status selected 

        $scope.cold_call = 0;

        $scope.customer_number_exists = false;

        $scope.existing_lead_action_url = 'edit-lead'; // default route to edit lead

        $scope.project_loading = false;

        // Calender 
        $scope.showCalender = false;

        // Show Address area for meeting or site visit
        $scope.showAddressArea = false;

        /* User gender values */
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
         * Model to store lead status properties 
         */

        $scope.lead_enquiry = {
            id: null,
            group_title: '',
            sub_status_id: null,
            sub_status_title: '',
            callback_date: '',
            callback_time: '',
            status_remark: '',
            address: '',
            meridian: '',
            activity_status: null
        };

        // Toggle Change event 
        $scope.changeProjectState = function (state) {

            // change project display according to mode selected

            // If state is true get only raheja builder project else get all projects

            if (state) {
                $scope.builder_id = 40;
            } else {
                $scope.builder_id = null;
            }

            $scope.applyFilter();

        };


        /**
         * Convert the value of callback date to YYYY-MM-DD format
         */

        $scope.$watch('lead_enquiry.callback_date', function (val) {

            if (val != '') {
                $scope.lead_enquiry.callback_date = $filter('date')(val, 'yyyy-MM-dd');
            }
        });

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
            _pref_bhk: [],
            _pref_property_status: [],
            _pref_property_types: [],
            resetBudget: function () {
                this.budget.min = '';
                this.budget.max = '';
                this.budget.min_label = '';
                this.budget.max_label = '';
                angular.element('.filter-budget-container .min-list div').removeClass('active');
                angular.element('.filter-budget-container .max-list div').removeClass('active');
            },
            resetPropertyFilter: function (event) {

                $('#filter_property_type').find('input[type="checkbox"]').prop('checked', false);
                this._pref_property_types = [];
            }
        };

        /**
         * To reset project filters
         * @returns {undefined}
         */
        $scope.resetFilters = function () {
            $scope.filters._pref_bhk = [];
            $scope.filters._pref_property_status = [];
            $scope.filters.resetPropertyFilter();
            $scope.filters.resetBudget();
            $scope.applyFilter();
        };

        // clear all of the multiselect forms
        $scope.resetPropertyStatusFilter = function () {
            $scope.filters._pref_property_status = [];
            $scope.applyFilter();
        };

        $scope.resetPropertyTypeFilter = function () {
            $scope.filters._pref_property_types = [];
            $scope.applyFilter();
        };

        $scope.resetBHKTypeFilter = function () {
            $scope.filters._pref_bhk = [];
            $scope.applyFilter();
        };

        /**
         * Client Basic Detail object 
         */
        $scope.client = {
            gender: null,
            fullname: '',
            email: '',
            mobile_number: null,
            alternate_mobile_number: '',
            landline_number: {
                std_code: '',
                number: '',
                ext: ''
            },
            dob: '',
            profession: '',
            country_id: 91,
            country: 'INDIA',
            city_id: '',
            state_id: '',
            state_name: '',
            city_name: '',
            address: '',
            remark: ''
        };

        /**
         * Watch on mobile number input to limit input number length
         */
        $scope.$watch('client.mobile_number', function (val) {
            $scope.client.mobile_number = $filter('limitTo')(val, 10);
        });

        $scope.$watch('client.alternate_mobile_number', function (val) {
            $scope.client.alternate_mobile_number = $filter('limitTo')(val, 10);
        });

        /*End */

        /**
         * To check if client number already exists in CRM 
         */

        $scope.loadingData = {
            number: $scope.client.mobile_number,
            stop: 1,
            start: 0
        };

        /**
         * Function to check if mobile number exists or not 
         * @param {type} number
         * @param {type} element
         * @returns {undefined}
         */
        $scope.checkNumberExists = function (number, element) {

            var offset_parent = element.currentTarget.offsetParent;
            var number_length = number.length;
            $scope.loadingData.stop = 0;
            $scope.loadingData.start = 1;

            // Fetch some detail about this number is system if already exists
            if (number_length === 10) {

                $scope.loadingData.start = 1;
                $scope.loadingData.stop = 0;

                var response = utilityService.isMobileNumberExists(number);

                response.then(function (success) {

                    $scope.loadingData.stop = 1;
                    $scope.loadingData.start = 0;

                    if (parseInt(success.data.success) === 1) {

                        $scope.customer_number_exists = true; // set flag variable to true

                        $scope.message_for_existing_number = 'This number is already exists in our system';

                        $scope.enquiry_id = success.data.data.enquiry_id;
                        $scope.lead_id = success.data.data.lead_id;
                        $scope.lead_added_by = success.data.data.lead_added_by_user;


                        // Check if number is re-assigned

                        if (success.data.data.lead_reassinged) {

                            if (success.data.data.lead_reassined_to_user != $scope.user.id) {

                                // Not authorize for this number 
                                $scope.show_lead_action = false;
                                $scope.message_for_existing_number = 'You are not authorized person for this Enquiry';
                            } else {
                                // Allow for edit

                                $scope.show_lead_action = true;
                                $scope.existing_lead_action_url = 'edit-lead';
                                $scope.action_button_text = 'Auto Fill';
                                $scope.existing_lead_action_url = $scope.existing_lead_action_url + '/' + $scope.enquiry_id + '/' + $scope.lead_id;
                            }
                        } else {

                            $scope.show_lead_action = true;

                            if (success.data.data.lead_edit_access_to_users.indexOf($scope.user.id) <= -1) {
                                // Allow only view

                                $scope.action_button_text = 'View';
                                $scope.existing_lead_action_url = 'lead_detail/' + $scope.enquiry_id;
                            } else {
                                $scope.existing_lead_action_url = 'edit-lead';
                                $scope.action_button_text = 'Auto Fill';
                                $scope.existing_lead_action_url = $scope.existing_lead_action_url + '/' + $scope.enquiry_id + '/' + $scope.lead_id;
                            }
                        }
                    } else {

                        // Get details from LMS data
                        var lms_detail_response = utilityService.get_user_detail_from_lms(number);

                        lms_detail_response.then(function (response) {

                            if (parseInt(response.data.success) === 1) {

                                $scope.client.email = response.data.data.email;
                                $scope.client.mobile_number = response.data.data.phone;
                                $scope.client.fullname = response.data.data.name;
                            } else {
                                // Do additional stuff here
                                // Clear email and phone number textbox input
                                
                                $scope.client.email = null;
                                $scope.client.fullname = null;
                                
                            }
                        });
                    }
                });
            } else {
                $scope.customer_number_exists = false;
                $scope.loadingData.stop = 1;
                $scope.loadingData.start = 0;
            }

        };

        /**
         * Form valiation errors object 
         */
        $scope.validation_error = {};

        /**
         *  Validation function on client email address input  
         * @param {string} email
         * @param {object} event
         * @returns {undefined}
         */

        $scope.clientEmailValidation = function (email, event) {

            if (!validationService.email(email)) {
                $scope.validation_error.email = 'parsley_error';
                $scope.email_error = 'Invaid email address';
                return false;
            } else {
                $scope.validation_error.email = '';
                $scope.email_error = '';
                return true;
            }
        };

        /**
         * Validation on client name input 
         */

        $scope.clientNameValidation = function (name) {
            if (name === '' || name === null) {
                $scope.name_error = 'Client name is required';
                $scope.validation_error.fullname = 'parsley_error';
                return false;
            } else {
                $scope.name_error = '';
                $scope.validation_error.fullname = '';
                return true;
            }
        };

        /**
         * Validation on client mobile nunber 
         * @returns {undefined}
         */

        $scope.clientMobileNumberValidation = function (number) {

            if (number === null || number.toString() === '') {
                $scope.validation_error.mobile_number_error = 'parsley_error';
                $scope.mobile_number_error = 'Please enter mobile number';
                return false;
            } else {

                // Validation of alphabetical characters 
                if (!validationService.isStringContainAlphaChar(number)) {
                    $scope.mobile_number_error = 'Please enter only number\'s in mobile number ';
                    $scope.validation_error.mobile_number_error = 'parsley_error';
                    return false;
                }

                $scope.validation_error.mobile_number_error = '';
                $scope.mobile_number_error = '';
            }
            return true;
        };

        //------------Code Block ----------------------------------------------------------------------------------------------------------------------------

        /**
         * 
         * @returns {undefined}
         */
        $scope.leadSourceValidation = function (type, value) {

            if (value === '' || value === null) {
                if (type === 'primary') {
                    $scope.validation_error.primary_lead_source_error = 'parsley_error';
                    $scope.primary_lead_source_error = 'Please select primary lead source';
                    return false;
                } else {
                    $scope.validation_error.secondary_lead_source_error = 'parsley_error';
                    $scope.secondary_lead_source_error = 'Please select secondary lead source';
                    return false;
                }
            } else {
                $scope.validation_error.primary_lead_source_error = '';
                $scope.primary_lead_source_error = '';
                $scope.validation_error.secondary_lead_source_error = '';
                $scope.secondary_lead_source_error = '';
                return true;
            }
        };
        //----------End Code Block -------------------------------------------------------------------------------------------------------------------------

        /**
         * Function to validate lead enquiry sub status 
         * @param {type} value
         * @returns {Boolean}
         */

        $scope.subStatusValidation = function (primary_status_id) {

            console.log($scope.sub_status);

            // If no sub status is seleced
            if ($scope.sub_status.length === 0) {

                if (!$scope.lead_enquiry.status_remark) {
                    alert('Please fill status remark');
                    return false;
                } else {
                    return true;
                }
            }

            // When sub status is updated 
            if ($scope.sub_status.length > 0) {
                // if no sub status is selected 
                if ($scope.lead_enquiry.sub_status_id === null) {
                    return false;
                } else {

                    var primary_status = $filter('filter')($scope.enquiry_status.disposition_group, {id: primary_status_id}, true);
                    var primary_status_title = $filter('trimSpace')($filter('lowercase')(primary_status[0].title), '_');

                    if (primary_status_title) {

                        switch (primary_status_title) {

                            case 'meeting':

                                if (!$scope.lead_enquiry.callback_date || !$scope.lead_enquiry.callback_time || !$scope.lead_enquiry.status_remark || !$scope.lead_enquiry.address) {
                                    return false;
                                } else {
                                    // meridian validation
                                    if (!$scope.lead_enquiry.meridian) {
                                        alert('Please select time meridian');
                                        return false;
                                    }
                                    return true;
                                }
                                break;

                            case 'site_visit':
                                if (!$scope.lead_enquiry.callback_date || !$scope.lead_enquiry.callback_time || !$scope.lead_enquiry.status_remark || !$scope.lead_enquiry.address) {
                                    return false;
                                } else {
                                    // meridian validation
                                    if (!$scope.lead_enquiry.meridian) {
                                        alert('Please select time meridian');
                                        return false;
                                    }
                                    return true;
                                }
                                break;

                            case 'technical_issue':

                                if (!$scope.lead_enquiry.status_remark) {
                                    alert('Plese enter remark');
                                    return false;
                                } else {
                                    return true;
                                }
                                break;

                            case 'future_references':
                                alert('hello');
                                var sub_status_title = $filter('trimSpace')($filter('lowercase')($scope.lead_enquiry.sub_status_title), '_');

                                // for call_back sub status date time and status remark is required
                                if (sub_status_title === 'call_back' || sub_status_title === 'follow_up') {

                                    if (!$scope.lead_enquiry.callback_date || !$scope.lead_enquiry.callback_time || !$scope.lead_enquiry.status_remark) {
                                        alert('Either Date or Time or Remark is not filled ');
                                        return false;
                                    } else {
                                        if (!$scope.lead_enquiry.meridian) {
                                            alert('Please select time meridian');
                                            return false;
                                        }
                                        return true;
                                    }
                                }
                                // for cold call sub status only status remark is required
                                else if ('cold_call') {
                                    if (!$scope.lead_enquiry.status_remark) {
                                        alert('Please fill status remark ');
                                        return false;
                                    } else {
                                        return true;
                                    }
                                }

                                break;
                        }

                    }
                }

            }

        };

        $scope.enquiryStatusValidation = function (value) {

            if (!value && typeof valaue == 'object') {
                $scope.validation_error.enquiry_status_error = 'parsley_error';
                $scope.enquiry_status_error = 'Please select enquiry status';
                return false;
            } else {

                // Check for sub status data for validation 
                if (!$scope.subStatusValidation(value)) {
                    return false;
                }
                $scope.validation_error.enquiry_status_error = '';
                $scope.enquiry_status_error = '';
                return true;
            }
        };


        /**
         * Enquiry Source Model
         */
        $scope.leadsource = {
            primary: {
                source_id: null,
                source_name: ''
            },
            secondary: {
                source_id: null,
                source_name: ''
            }
        };

        /**
         * To set Lead source name
         * @returns {undefined}
         */
        $scope.setLeadSourceName = function (mode) {

            if (!$scope.leadsource.primary.source_id && typeof $scope.leadsource.primary.source_id == 'object') {
                $scope.leadsource.primary.source_name = '';
                $scope.leadsource.secondary.source_name = '';
                return;
            }

            var _primary_source_object = $filter('filter')($scope.primary_lead_source, {id: $scope.leadsource.primary.source_id}, true);
            if (_primary_source_object) {
                $scope.leadsource.primary.source_name = _primary_source_object[0].title;
            }

            $scope.getSecondaryLeadSource($scope.leadsource.primary.source_id);
        };



        /**
         * Function to get Primary Sources
         */
        $scope.getPrimaryLeadSource = function () {

            var http_config = {
                url: baseUrl + 'apis/helper.php?method=getCampaigns&params=campaign_type:primary',
                method: 'GET'
            };

            var primary_lead_source = httpService.makeRequest(http_config);

            primary_lead_source.then(function (response) {

                if (response.data.success == 1) {
                    $scope.primary_lead_source = response.data.data;
                }
            });
        };


        // Call to server for list or primary lead sources 
        $scope.getPrimaryLeadSource();



        /**
         * Fetch child list of primary lead source
         */
        $scope.getSecondaryLeadSource = function (parent_source_id) {

            var secondary_source_object = $filter('filter')($scope.primary_lead_source, {id: parent_source_id}, true);
            if (angular.isDefined(secondary_source_object) && secondary_source_object.length > 0) {
                $scope.secondary_lead_source = secondary_source_object[0];
            }
        };

        /*
         * State List 
         */
        $scope.states = [];
        $scope.cities = [];

        $scope.fetchStatesList = function () {

            var state = utilityService.getStateList();

            state.then(function (response) {

                $scope.states = response.data;

            });
        };

        $scope.fetchStatesList();


        /**
         * Event handler for fetching cities from state_id
         */
        $scope.getStateCities = function () {

            if (!$scope.client.state_id && typeof $scope.client.state_id == 'object') {
                $scope.client.state_name = '';
                $scope.client.city_id = null;
                $scope.client.city_name = '';
                return false;
            }

            // Getting selected state object from list of states
            var state_obj = $filter('filter')($scope.states, {
                state_id: $scope.client.state_id
            }, true);

            $scope.client.state_name = state_obj[0].state_name;
            var city = utilityService.getCityList($scope.client.state_id);
            city.then(function (response) {
                $scope.cities = response.data;
            });

        };

        // Set City name on selection of city from list 
        $scope.setCityName = function () {

            if (!$scope.client.city_id && typeof $scope.client.city_id == 'object') {
                $scope.clientCityValidation(null);
                return false;
            }

            var city_obj = $filter('filter')($scope.cities, {city_id: $scope.client.city_id}, true);
            $scope.client.city_name = city_obj[0].city_name;
        };

        /*
         * Default Page record limit 
         */

        $scope.page_record_limit = 10;


        $scope.showCitiesList = function () {
            $scope.hide_cities = !$scope.hide_cities;
        };

        /*
         * Fetching list of cities
         */
        $scope.project_cities = [];

        $scope.getProjectCities = function () {

            var config = {
                url: baseUrl + 'apis/getProjectCities.php',
                method: 'GET'
            };

            var response = httpService.makeRequest(config);

            response.then(function (response) {

                if (response.data.success) {
                    $scope.project_cities = response.data.city_list;
                }
            });

        };

        $scope.getProjectCities();

        /**
         * Project model
         */
        $scope.project = {
            city_id: null,
            city_name: ''
        };

        $scope.addHoverClass = function (element) {
            var target = element.target;
            angular.element(target).addClass('active').css({cursor: 'pointer'});
        };

        $scope.RemoveHoverClass = function (element) {
            var target = element.target;
            angular.element(target).removeClass('active');
        };

        $scope.setCityVal = function (city) {

            $scope.project.city_id = city.city_id;
            $scope.project.city_name = city.city_name;
            $scope.clearCityQuery();
            $scope.showCitiesList();
        };

        $scope.searchProject = function (city_name) {
            $scope.fetchCRMProjects(city_name);
        };

        $scope.clearCityQuery = function () {
            $scope.city_query = '';
        };

        /**
         * CRM Projects 
         */
        $scope.crm_projects = [];
        $scope.crm_projects_cloned = [];
        $scope.featured_projects = [];
        $scope.selected_featured_projects = [];
        $scope.current_page_number = 1;

        /**
         * Getting projects from bookmyhouse with filters applied 
         * @param {string} city_name
         * @returns {object}
         */
        $scope.fetchCRMProjects = function (city_name) {
            $scope.applyFilter(); // apply filters 
        };


        /**
         * Setting minimum budget filter 
         * @param {type} budget
         * @param {type} event
         * @returns {undefined}
         */
        $scope.setMinBudget = function (budget, event) {

            // If min value is greater than max budget value then alert user and unselect min value
            // Apply a filter to check that min value should not greater then max value 

            if (angular.isDefined($scope.filters.budget.max)) {

                if (parseInt(budget.value) > parseInt($scope.filters.budget.max)) {
                    alert('Min budget value should not greater than max budget value');
                    $scope.filters.budget.min = null;
                    $scope.filters.budget.min_label = null;
                    angular.element('.filter-budget-container .min-list div').removeClass('active');
                    return false;
                }
            }

            $scope.filters.budget.min = budget.value;
            $scope.filters.budget.min_label = budget.label + ' ' + budget.currency_suffix;
            angular.element('.filter-budget-container .min-list div').removeClass('active');
            angular.element(event.currentTarget).addClass('active');
        };

        /**
         * Setting maximum budget filter
         * @param {type} budget
         * @param {type} event
         * @returns {undefined}
         */
        $scope.setMaxBudget = function (budget, event) {

            // If max value is less then minimum then alert user and unselect max value 
            if (angular.isDefined($scope.filters.budget.min)) {

                if (parseInt(budget.value) < parseInt($scope.filters.budget.min)) {
                    alert('Max budget value should not less than min budget value');
                    $scope.filters.budget.max = null;
                    $scope.filters.budget.max_label = null;
                    angular.element('.filter-budget-container .max-list div').removeClass('active');
                    return false;
                }
            }

            $scope.filters.budget.max = budget.value;
            $scope.filters.budget.max_label = budget.label + ' ' + budget.currency_suffix;
            angular.element('.filter-budget-container .max-list div').removeClass('active');
            angular.element(event.currentTarget).addClass('active');
        };


        /**
         * To apply filters on projects
         * @returns {undefined}
         */
        $scope.applyFilter = function () {

            // start loading animation 
            $scope.project_loading = true;

            var property_status_filter_array = [];
            var bhk_filter = [];
            var property_types = [];

            if ($scope.filters._pref_property_status.length > 0) {

                angular.forEach($scope.filters._pref_property_status, function (value) {
                    property_status_filter_array.push(value.value)
                });
            }

            if ($scope.filters._pref_bhk.length > 0) {

                angular.forEach($scope.filters._pref_bhk, function (value) {
                    bhk_filter.push(value.value)
                });
            }

            if ($scope.filters._pref_property_types.length > 0) {
                angular.forEach($scope.filters._pref_property_types, function (value) {
                    property_types.push(value.value)
                });
            }


            var config = {
                url: baseUrl + 'apis/fetchCRMProjects.php',
                method: 'POST',
                data: $.param({
                    city: $scope.project.city_name,
                    ptype: property_types,
                    status_data: property_status_filter_array,
                    bhk1: bhk_filter,
                    min_price: $scope.filters.budget.min,
                    max_price: $scope.filters.budget.max,
                    builder_id: $scope.builder_id
                }),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
                }
            };

            var filtered_projects = $http(config);

            filtered_projects.then(function (success) {

                if (success.data.success) {
                    $scope.project_loading = false;
                    $scope.crm_projects = success.data.data;
                    $scope.crm_projects_cloned = success.data.data;
                    $scope.extractFeaturedProjects(success.data.featuredprojects);
                    $scope.hotProjects = success.data.crmhotprojects; // hot projects
                }
            });
        };

        // Create page offset on change of page change 
        $scope.pageChange = function (page) {
            $scope.offset = $scope.page_record_limit * (parseInt(page) - 1);
        };

        /* Selected Enquiry Projects */
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
        $scope.selectProject = function (selected_project, event) {

            var is_checked = $(event.currentTarget).prop('checked');

            if (is_checked) {
                $scope.selectedProjects.ids.push(selected_project.project_id);
                $scope.selectedProjects.projects.push({project_name: selected_project.project_name, project_url: selected_project.project_url, id: selected_project.project_id, element: event});

            } else {

                var project_id_index = $scope.selectedProjects.ids.indexOf(selected_project.project_id);
                $scope.selectedProjects.ids.splice(project_id_index, 1);
                $scope.selectedProjects.projects.splice(project_id_index, 1);
            }
        };

        /**
         * To remove or unselect selected projects 
         * @param {type} removed_project
         * @returns {undefined}
         */

        $scope.removeSelected = function (removed_project) {

            $(removed_project.element.currentTarget).prop('checked', false);
            var project_id_index = $scope.selectedProjects.ids.indexOf(removed_project.id);
            $scope.selectedProjects.ids.splice(project_id_index, 1);
            $scope.selectedProjects.projects.splice(project_id_index, 1);
        };


        $scope.searchFromSelectedProjects = function (value) {

            if ($.inArray(value, $scope.selectedProjects.ids) > -1) {
                return 1;
            } else {
                return 0;
            }
        };

        /**
         * Enquiry Status Model
         */
        $scope.enquiry_status = {
            disposition_group: [],
            group_sub_status: []
        };

        /**
         * Function to get disposition group list
         */
        $scope.getDispositionGroupList = function () {

            httpService.makeRequest({
                url: baseUrl + 'apis/get_employee_disposition_group_status.php?employee_id=' + $scope.user.id
            }).then(function (response) {

                if (response.data.success) {

                    if (response.data.parent_status) {
                        $scope.enquiry_status.disposition_group = response.data.parent_status;
                    }

                    if (response.data.sub_status) {
                        $scope.enquiry_status.group_sub_status = response.data.sub_status;
                    }
                }
            });
        };

        $scope.getDispositionGroupList();


        /**
         * 
         * @returns {Boolean}
         */
        $scope.populate_sub_status = function (group_id) {

            $scope.sub_status = [];
            $scope.isActivityRemarkMandate = 0;

            // Get group title from enquiry_status.disposition group 
            var parent_group = $filter('filter')($scope.enquiry_status.disposition_group, {id: group_id}, true);

            // Assigning enquiry status title 
            if (parent_group.length > 0) {

                // Fetching group title
                $scope.lead_enquiry.group_title = parent_group[0].title;

                if (parent_group[0].is_activity_status) {

                    if (parseInt(parent_group[0].is_activity_status)) {
                        $scope.showActivityStatusButtons = true;
                    } else {
                        $scope.showActivityStatusButtons = false;
                    }
                } else {
                    $scope.showActivityStatusButtons = false;
                    $scope.lead_enquiry.activity_status = null;
                }

                // Lowercasing group title  
                var lowercase_title = $filter('lowercase')($scope.lead_enquiry.group_title);

                // Appending underscore with group title name
                var title_with_underscore = $filter('trimSpace')(lowercase_title, '_');

                $scope.displayCalender(title_with_underscore);

                // Open send mail popup
                if (title_with_underscore == 'send_mail') {
                    $scope.openSendMailPopup();
                }

                mandatoryRemarksActivity.findIndex(function (element) {

                    if (group_id == element) {
                        $scope.isActivityRemarkMandate = 1;
                    }
                });
            } else {
                $scope.lead_enquiry.group_title = '';
                $scope.resetFollowupData();
            }

            if (angular.isUndefined(group_id) || group_id === null) {
                $scope.sub_status = [];
            }

            $scope.sub_status = $filter('filter')($scope.enquiry_status.group_sub_status, {group_id: group_id});

            // If sub status list items are there then only we show the list dropdown
            $scope.enquiry_sub_status_list_item = Object.keys($scope.sub_status).length;

        };


        /**
         * Function to handle change in enquiry sub status 
         * @returns {Boolean}
         */

        $scope.setValueEnquiryForSubStatus = function (sub_status_item) {

            var sub_status_selected_object = $filter('filter')($scope.sub_status[0].childs, {id: sub_status_item});

            if (sub_status_selected_object.length > 0) {

                $scope.isActivityRemarkMandate = 0;// Reset remark flag

                $scope.lead_enquiry.sub_status_id = sub_status_item;

                $scope.lead_enquiry.sub_status_title = sub_status_selected_object[0].status;

                if (sub_status_selected_object[0].is_activity_status) {
                    $scope.showActivityStatusButtons = true;
                } else {
                    $scope.lead_enquiry.activity_status = null;
                    $scope.showActivityStatusButtons = false;
                }


                var status_title_lowercase = $filter('lowercase')($filter('trimSpace')($scope.lead_enquiry.sub_status_title, '_'));

                if (status_title_lowercase === 'cold_call' || status_title_lowercase === 'location_issue' || status_title_lowercase === 'low_budget' || status_title_lowercase === 'other') {
                    $scope.cold_call = 1; // This is a cold call
                    $scope.showCalender = false;
                } else {
                    $scope.cold_call = 0;
                    $scope.showCalender = true;
                }

                // Hide calender for some sub status 
                if (hide_cal_for_sub_status.indexOf(Number($scope.lead_enquiry.sub_status_id)) > -1) {
                    $scope.showCalender = false;
                }


                mandatoryRemarksActivity.findIndex(function (element) {
                    if (sub_status_item == element) {
                        $scope.isActivityRemarkMandate = 1;
                    }
                });

            } else {
                $scope.lead_enquiry.sub_status_id = null;
                $scope.lead_enquiry.sub_status_title = '';
            }
        };


        /**
         * Client End Validation before submitting data to server
         */
        $scope.clientEndValidation = function () {

            var validation_errors = []; // Error container

            // Validation check on mobile number 
            if (!$scope.client.mobile_number) {
                validation_errors.push('Please enter client mobile number');
            } else if (Number.isNaN(Number($scope.client.mobile_number))) {
                validation_errors.push('Please enter a valid mobile number');
            } else if ($scope.client.mobile_number.length < 10) {
                validation_errors.push('Please enter 10 digit client mobile number');
            } else {

                var space_regex = /\s/;

                if (space_regex.test($scope.client.mobile_number)) {
                    validation_errors.push('Space is not allowed in mobile number');
                }
            }

            // Client name validation
            if (!$scope.client.fullname) {
                validation_errors.push('Please enter client name if provided otherwise use default name');
            }

            // Enquiry Status Validation 
            if (!$scope.lead_enquiry.id) {
                validation_errors.push('Please select enquiry status');
            }

            // Enquiry Remarks validation
            if (!$scope.lead_enquiry.status_remark && $scope.isActivityRemarkMandate)
            {
                validation_errors.push('Please enter enquiry remark');
            }

            // Sub Status Vaidation
            var is_sub_status_required = [3, 4, 6, 7, 8, 9, 38].findIndex(function (element) {
                if (element === Number($scope.lead_enquiry.id)) {
                    return true;
                }
            });

            if (is_sub_status_required > -1) {
                if (!$scope.lead_enquiry.sub_status_id) {
                    validation_errors.push('Please select sub enquiry status');
                }
            }

            // Address validation for meeting or site visit
            var is_meeting_or_site_visit = [3, 6].findIndex(function (element) {

                if (element === Number($scope.lead_enquiry.id)) {
                    return true;
                }
            });

            if (is_meeting_or_site_visit > -1) {

                var enquiry_status_title = (!is_meeting_or_site_visit ? 'meeting' : 'site visit');
                if (!$scope.lead_enquiry.address) {
                    validation_errors.push('Please enter ' + enquiry_status_title + ' address');
                }
            }

            // DateTime validation
            var is_date_time_required = [3, 6, 4, 47].findIndex(function (element) {

                if (element == Number($scope.lead_enquiry.id)) {
                    return true;
                }
            });

            if (is_date_time_required > -1) {

                if (!$scope.lead_enquiry.callback_date) {
                    validation_errors.push('Please select Date');
                } else if (!$scope.lead_enquiry.callback_time) {
                    validation_errors.push('Please select Time');
                } else if (!$scope.lead_enquiry.meridian) {
                    validation_errors.push('Please select time meridian');
                }

            }

            // Project validation if meeting or site visit
            var is_meeting_or_sitevisit_status = [3, 6].findIndex(function (element) {
                // if selected enquiry status is from meeting or site visit
                if (Number($scope.lead_enquiry.id) == element) {
                    return true;
                }
            });

            if (is_meeting_or_sitevisit_status > -1) {
                if ($scope.selectedProjects.ids.length == 0) {
                    var enquiry_status_title = (!is_meeting_or_sitevisit_status ? 'meeting' : 'site visit');
                    validation_errors.push('Please select ' + enquiry_status_title + ' project');
                }
            }

            return validation_errors;
        };


        /**
         * Function to add new lead
         */
        $scope.addLead = function () {

            var _isErrors = [];
            _isErrors = $scope.clientEndValidation();

            // Display Errors to user if any
            if (_isErrors.length > 0) {
                window.showToast(_isErrors.join('<br/>'), 'Errors', 'error');
                return false;
            }

            // Logic to remove hot projects from list which were seleted with enquiry
            angular.forEach($scope.selectedProjects.ids, function (pId, key) {

                $scope.hotProjects.findIndex(function (element, index) {

                    if (parseInt(pId) === parseInt(element.project_id)) {
                        $scope.hotProjects.splice(index, 1);
                        return true;
                    }
                });
            });
            // Code ends here

            $scope.disable_add_lead_button = true;

            var lead_data = {
                client_info: $scope.client,
                lead_source: $scope.leadsource,
                projects: $scope.removeEventFromObject($scope.selectedProjects),
                project_city: $scope.project.city_name,
                filters: {
                    budget: $scope.filters.budget,
                    bhk: $scope.filters._pref_bhk,
                    property_status: $scope.filters._pref_property_status,
                    property_types: $scope.filters._pref_property_types
                },
                enquiry: $scope.lead_enquiry,
                cold_call: $scope.cold_call,
                hot_projects: $scope.hotProjects,
                remarks_mandatory: $scope.isActivityRemarkMandate
            };


            var http_config = {
                url: baseUrl + 'apis/add_lead.php',
                method: 'POST',
                data: $.param(lead_data),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
                }
            };

            // Post data to server
            var lead_request = $http(http_config);
            lead_request.then(function (response) {

                if (parseInt(response.data.success) === -1) {

                    // Server sends back some form errors
                    // show them to user to correct
                    window.showToast(response.data.message, response.data.message_title, 'error');
                    $scope.disable_add_lead_button = false;
                    return false;
                }

                if (parseInt(response.data.success) === 0) {

                    var errors = [];

                    if (Object.keys(response.data.errors).length > 0) {
                        angular.forEach(response.data.errors, function (val, key) {
                            errors.push(val);
                        });
                    }

                    // Some warning messages to user
                    window.showToast(errors.join('<br/>'), response.data.message_title, 'error');
                    $scope.disable_add_lead_button = false;
                    return false;
                }

                if (parseInt(response.data.success) === 1) {
                    window.showToast(response.data.message, response.data.message_title, 'success');
                    $location.path('/my-leads');
                }
            });
        };

        /**
         * Remove element object from selected projects model
         */
        $scope.removeEventFromObject = function (obj) {
            for (var i = 0; i <= obj.projects.length - 1; i++) {
                delete  obj.projects[i].element;
            }
            return $scope.selectedProjects;
        };

        /**
         * Route Reload 
         */
        $scope.reloadRoute = function () {
            $route.reload('/add-lead');
        };

        var date = new Date();
        $scope.meridians = ['AM', 'PM'];
        $scope.minDate = new Date();
        dateOptions: {
            minDate: $scope.minDate;
        }
        ;


        /**
         * Function to show or hide followup actions icons 
         * @argument {string} title 
         * description title : title of the enquiry status
         * @returns {boolean}
         */
        $scope.displayCalender = function (title) {

            switch (title) {

                case 'meeting':
                    $scope.address_event = 'Meeting';
                    $scope.showCalender = true;
                    $scope.showAddressArea = true;
                    break;

                case 'site_visit':
                    $scope.address_event = 'Site Visit';
                    $scope.showCalender = true;
                    $scope.showAddressArea = true;
                    break;

                case 'future_references':
                    $scope.showCalender = true;
                    $scope.showAddressArea = false;
                    break;

                case 'callback':
                    $scope.showCalender = true;
                    break;

                default :
                    $scope.resetFollowupData();
                    $scope.showCalender = false; // Always hide this as this is no more a required
                    $scope.showAddressArea = false;
            }
            ;
        };

        /**
         * Function to reset all followup information with lead enquiry callback date and time
         * @returns {undefined}
         */

        $scope.resetFollowupData = function () {
            $scope.lead_enquiry.callback_date = '';
            $scope.lead_enquiry.callback_time = '';
            $scope.lead_enquiry.status_remark = '';
            $scope.lead_enquiry.sub_status_id = null;
            $scope.lead_enquiry.sub_status_title = '';
        };


        /**
         * Function to refresh list of project cities
         * @returns {undefined}
         */
        $scope.refreshProjectCities = function () {
            $scope.getProjectCities();
        };


        /**
         * Datepicker Model 
         */
        $scope.datepicker = {

            dt: new Date(),
            options: {
                //customClass: getDayClass,
                minDate: new Date(),
                showWeeks: false,
                datepickerMode: 'day',
                initDate: new Date()
            }
        };


        // Time format 12 hours
        function format12() {
            // var meridian = 'AM';
            var temp = [];

            for (var i = 1; i <= 12; i++) {
                temp.push(i + ':00 ');
                temp.push(i + ':15 ');
                temp.push(i + ':30 ');
                temp.push(i + ':45 ');
            }
            return temp;
        }

        // Time format 24 hours
        function format24() {

            var temp = new Array;
            var meridian = 'AM';

            for (var i = 0; i <= 23; i++) {

                if (i < 12) {
                    meridian = 'AM';
                } else {
                    meridian = 'PM';
                }

                temp.push(i + ':00 ' + meridian);
                temp.push(i + ':15 ' + meridian);
                temp.push(i + ':30 ' + meridian);
                temp.push(i + ':45 ' + meridian);
            }
            return temp;
        }

        // Time picker object 
        $scope.timepicker = {
            meridian: ['AM', 'PM'],
            time: format12()
        };


        /**
         * function to set address type
         * @param {type} string
         * @returns {undefined}
         */

        $scope.setAddressType = function (type) {

            if (type === 'client') {
                $scope.lead_enquiry.address = $scope.client.address;
            }

            if (type === 'office') {

                $scope.lead_enquiry.address = officeAddress;
            }

            if (type === 'misc') {
                $scope.lead_enquiry.address = '';
            }
        };

        // Fetch Featured projects from CRM projects list
        $scope.extractFeaturedProjects = function (list) {

            $scope.featured_projects = [];
            $scope.featured_projects = list;

//            angular.forEach($scope.crm_projects, function (value){
//                if(value.is_featured == true){
//                   $scope.featured_projects.push(value);
//                }
//            });
//            
        };

        // Function to send feature projects links to client in mail
        $scope.copyFeaturedProjects = function () {

            // copy all the selected featured project links in hidden text area
            var links = '<ul style="list-style:none; display:inline;">';
            angular.forEach($scope.selected_featured_projects, function (value) {
                links += '<li style="padding: 5px;"><a href="' + value.project_url + '">' + value.project_name + '</a></li>';
            });
            links += '</ul>';

            // check if user has entered or not client email (fullname is optional)

            if ($scope.client.email != '') {

                var featured_project_mail_data = {
                    to_email: $scope.client.email,
                    to_name: $scope.client.fullname,
                    cc_email: '',
                    bcc_email: '',
                    featured_projects: links
                };

                // Call to server
                $http({
                    url: baseUrl + 'apis/send_featured_projects_mail.php',
                    method: 'POST',
                    data: $.param(featured_project_mail_data),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
                    }
                }).then(function (response) {
                    if (Number(response.data.success) === 1) {
                        alert('Featured Projects Links sent successfully');
                    }
                });
            } else {
                alert('Enter client email address or name');
            }
        };

        /**
         * To set default name for client in case of name not avaibale from client
         */
        $scope.setDefaultName = function (name) {
            $scope.client.fullname = name;
        };

        $scope.isFeaturedProject = function (x) {

            var project_index = $scope.featured_projects.find(function (item) {

                if (parseInt(item.project_id) == parseInt(x)) {
                    return true;
                }
            });

            if (project_index) {
                return '<i style="color:green;" class="fa fa-check t-green"></i>';
            } else {
                return '<i style="color:red;" class="fa fa-times t-red"></i>';
            }
        };

        // To filter out hot projects
        $scope.isHotProject = function (x) {

            var project_index = $scope.hotProjects.find(function (item) {

                if (parseInt(item.project_id) == parseInt(x)) {
                    return true;
                }
            });

            if (project_index) {
                return '<i style="color:green;" class="fa fa-check t-green"></i>';
            } else {
                return '<i style="color:red;" class="fa fa-times t-red"></i>';
            }
        };

        // Function to open send mail popup
        $scope.openSendMailPopup = function () {
            $('#sendMailModal').modal('show');
        };

    }); // End controller 

})(app, jQuery);
