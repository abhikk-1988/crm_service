
    /*App module script to configure modules*/

    var app = app || {};
    var Pace = Pace || {};

    ( function ( Pace, $ ) {

        // module dependency of ngRoute module 
        app = angular.module ( 'app', ['ngRoute', 'ngSanitize', 'ngAnimate', 'ui.bootstrap','ui.router','uiSwitch','isteven-multi-select','ui.timepicker'] );

        app.value ( 'application_blocks', {
            left_sidebar_path: "html/sidebar/left_side_nav.html",
            right_sidebar_path: "html/sidebar/right_side_nav.html",
            header_path: "html/header/index.html"
        } );

        app.value ( 'appUrls', {
            baseUrl: 'http://52.77.73.171/staging_crm/v1.1/',
            stuffUrl: 'http://52.77.73.171/staging_crm/v1.1/stuff/',
            appUrl: 'http://52.77.73.171/staging_crm/v1.1/app/',
            apiUrl: 'http://52.77.73.171/staging_crm/v1.1/apis/'
        });

        // App layout
        app.value ( 'appLayout', {
            width: '1119px',
            minHeight: '1164px',
            height: '',
            backgroundColor: '#F7F7F7'
        } );

        // users to which print access is allowed
        app.value('print_access_users', [2,4,28,35]);

		// lead assign for status id
		app.value('lead_assign_for_status',[3,6]);
		
        // hide calender for sub status 
        app.value('hide_cal_for_sub_status', [19,20,21,39,40,41,42,43]);
        
        app.constant ('baseUrl', 'http://52.77.73.171/staging_crm/v1.1/');

        app.value ('officeAddress', 'Raisina Farms Pvt. Ltd.Minus One, Raheja Mall,Sec-47, Gurgaon - 122007');

        var email_template_events = [
            {title : 'Not Interested'	, value :'not_interested'},
            {title : 'Just Enquiry'	, value :'just_enquiry'},
            {title : 'Meeting Done'	, value :'meeting_done'},
            {title : 'Meeting Schedule'	, value :'meeting_schedule'},
            {title : 'Meeting Reschedule'	, value :'meeting_reschedule'},
            {title : 'Meeting Reject'	, value :'meeting_reject'},
            {title : 'Site Visit Done'	, value :'site_visit_done'},
            {title : 'Site Visit Schedule'	, value :'site_visit_schedule'},
            {title : 'Site Visit Reject', value : 'site_visit_reject'},
            {title : 'Site Visit Reschedule'	, value :'site_visit_reschedule'},
            {title : 'Call Back', value :'call_back'},
            {title : 'Escalation', value: 'esclation'},
            {title : 'Agent to CRM TL', value : 'lead_assignment_level_1'},
            {title : 'CRM TL to ASM', value : 'lead_assignment_level_2'},
            {title : 'ASM to SP', value : 'lead_assignment_level_3'},
            {title : 'Lead Closure', value :'lead_closure'},
            {title: 'Follow Up', value : 'follow_up'},
            {title: 'Re-Assign To Agent', value : 're_assign_agent'},
            {title: 'Re-Assign To ASM', value : 're_assign_asm'}
        ];

        var message_template_events = [
            {title : 'Not Interested'	, value :'not_interested'},
            {title : 'Just Enquiry'	, value :'just_enquiry'},
            {title : 'Meeting Done'	, value :'meeting_done'},
            {title : 'Meeting Schedule'	, value :'meeting_schedule'},
            {title : 'Meeting Reschedule'	, value :'meeting_reschedule'},
            {title : 'Meeting Reject'	, value :'meeting_reject'},
            {title : 'Site Visit Done'	, value :'site_visit_done'},
            {title : 'Site Visit Schedule'	, value :'site_visit_schedule'},
            {title : 'Site Visit Reject', value : 'site_visit_reject'},
            {title : 'Site Visit Reschedule'	, value :'site_visit_reschedule'},
            {title : 'Call Back', value :'call_back'},
            {title : 'Escalation', value: 'esclation'},
            {title : 'Lead Closure', value :'lead_closure'},
            {title : 'Lead Assign To TL CRM', 'value' : 'lead_assign_to_tl_crm'},
            {title : 'Lead Assign to ASM' , value : 'lead_assign_to_asm'},
            {title : 'Lead Assign To SP', value : 'lead_assign_to_sp'},
            {title : 'Follow Up', value :'follow_up'},
            {title : 'Lead Assign TL to ASM', value : 'lead_assignment_level_2'},
            {title : 'Lead Assign ASM to SP', value : 'lead_assignment_level_3'},
            {title: 'Other', value: 'local_sms_testing'},
            {title: 'Re-Assign To ASM', value : 're_assign_asm'}
        ]; 

        app.constant ('email_template_events',email_template_events);
        app.constant ('message_template_events', message_template_events);

        // configuration of TLCRM 
        app.constant('tlcrm_config', {

            disposition_status : {
                meeting : ['schedule','reschedule'],
                site_visit : ['schedule','reschedule']
            }
        });

        app.constant ( 'notify', {
            template: '<div><notification-message></notification-message/></div>'
        } );

        // Application configuration block
        app.config ( function ( $routeProvider, baseUrl, $locationProvider, $stateProvider, $urlRouterProvider) {

            $routeProvider.caseInsensitiveMatch = true; // Match URL with case insensitive match
            $routeProvider.
                    when ( '/', {
                        templateUrl: baseUrl + 'html/login/index.html',
                        resolve: {
                            user_auth: function ( $http, $location ) {
                                return $http ( {
                                    url: baseUrl + 'apis/getCurrentUser.php',
                                    method: 'GET'
                                } ).then ( function ( promise ) {

                                    if ( promise.data ) {

                                        // Authenticate user     
                                        if ( angular.isDefined ( promise.data.id ) ) {
                                            $location.path ( '/home' );
                                        }
                                    }
                                } );
                            }
                        },
                        controller: 'loginCtrl'
                    } ).
                    when ( '/home', {
                        templateUrl: baseUrl + 'home.php',
                        controller: 'homeCtrl'
                    } ).
                    when ( '/designation', {
                        templateUrl: baseUrl + 'templates/designation.html',
                        controller: 'designationCtrl',
                        resolve: {
                            user_auth: function ( $http, $location ) {
                                return $http ( {
                                    url: baseUrl + 'apis/getCurrentUser.php',
                                    method: 'GET'
                                } ).then ( function ( promise ) {

                                    if ( promise.data ) {

                                        // Authenticate user     
                                        if ( ! angular.isDefined ( promise.data.id ) ) {
                                            $location.path ( '/' );
                                        }
                                    }
                                    else {
                                        $location.path ( '/' );
                                    }
                                } );
                            }}
                    } ).
                    when ( '/employee', {
                        templateUrl: baseUrl + 'templates/addEmployee.html',
                        controller: 'addEmployeeCtrl',
                        resolve: {
                            user_auth: function ( $http, $location ) {

                                return $http ( {
                                    url: baseUrl + 'apis/getCurrentUser.php',
                                    method: 'GET'
                                } ).then ( function ( promise ) {

                                    if ( promise.data ) {

                                        // Authenticate user     
                                        if ( ! angular.isDefined ( promise.data.id ) ) {
                                            $location.path ( '/' );
                                        }
                                    }
                                    else {
                                        $location.path ( '/' );
                                    }
                                } );
                            },

                            assign_to_employees_list : function (httpService){

                                return httpService.makeRequest({
                                    url : baseUrl + 'apis/employees.php',
                                    method : 'GET'
                                }).then(function (success){
                                    return success.data; // Returning list of employees
                                });
                            }
                        }
                    } ).
                    when ( '/search-employee/:employee_id?', {
                        templateUrl: baseUrl + 'templates/employee_search.html',
                        controller: 'employeeSearchCtrl',
                        resolve: {
                            user_auth: function ( $http, $location ) {
                                return $http ( {
                                    url: baseUrl + 'apis/getCurrentUser.php',
                                    method: 'GET'
                                } ).then ( function ( promise ) {

                                    if ( promise.data ) {

                                        // Authenticate user     
                                        if ( ! angular.isDefined ( promise.data.id ) ) {
                                            $location.path ( '/' );
                                        }
                                    }
                                    else {
                                        $location.path ( '/' );
                                    }
                                } );
                            },
                            user_designation : function (designationService){

                                return designationService.fetchAllDesignations().
                                then(function (res){
                                    return res.data;
                                });
                            }
                        }
                    } ).
                    when ( '/update_employee/:employee_id', {
                        templateUrl: baseUrl + 'templates/update_employee.html',
                        controller: 'editEmployeeCtrl',
                        resolve: {
                            user_auth: function ( $http, $location ) {
                                return $http ( {
                                    url: baseUrl + 'apis/getCurrentUser.php',
                                    method: 'GET'
                                } ).then ( function ( promise ) {

                                    if ( promise.data ) {

                                        // Authenticate user     
                                        if ( ! angular.isDefined ( promise.data.id ) ) {
                                            $location.path ( '/' );
                                        }
                                    }
                                    else {
                                        $location.path ( '/' );
                                    }
                                } );
                            }}
                    } ).
                    when ( '/managePriviledge/:designation_id?', {
                        templateUrl: baseUrl + 'templates/priviledges.html',
                        controller: 'managePriviledgeCtrl',
                        resolve: {
                            user_auth: function ( $http, $location ) {
                                return $http ( {
                                    url: baseUrl + 'apis/getCurrentUser.php',
                                    method: 'GET'
                                }).then ( function ( promise ) {
                                        if ( promise.data ) {

                                            // Authenticate user     
                                            if ( ! angular.isDefined ( promise.data.id ) ) {
                                                $location.path ( '/' );
                                            }
                                        }
                                        else {
                                            $location.path ( '/' );
                                        }
                                    });
                                }
                            }
                    }).
                    when ( '/moduleManager', {
                        templateUrl: baseUrl + 'templates/module_manager.html',
                        controller: 'moduleManagerCtrl',
                        resolve: {
                            user_auth: function ( $http, $location ) {
                                return $http ( {
                                    url: baseUrl + 'apis/getCurrentUser.php',
                                    method: 'GET'
                                } ).then ( function ( promise ) {

                                    if ( promise.data ) {

                                        // Authenticate user     
                                        if ( ! angular.isDefined ( promise.data.id ) ) {
                                            $location.path ( '/' );
                                        }
                                    }
                                    else {
                                        $location.path ( '/' );
                                    }
                                } );
                            }}
                    } ).
                    when ( '/primary-campaign', {
                        templateUrl: baseUrl + 'templates/addPrimaryCampaign.html',
                        controller: 'primaryCampaignCtrl',
                        resolve: {
                            user_auth: function ( $http, $location ) {
                                return $http ( {
                                    url: baseUrl + 'apis/getCurrentUser.php',
                                    method: 'GET'
                                } ).then ( function ( promise ) {

                                    if ( promise.data ) {

                                        // Authenticate user     
                                        if ( ! angular.isDefined ( promise.data.id ) ) {
                                            $location.path ( '/' );
                                        }
                                    }
                                    else {
                                        $location.path ( '/' );
                                    }
                                } );
                            }}
                    } ).
                    when ( '/secondary-campaign', {
                        templateUrl: baseUrl + 'templates/addSecondaryCampaign.html',
                        controller: 'secondaryCampaignCtrl',
                        resolve: {
                            user_auth: function ( $http, $location ) {
                                return $http ( {
                                    url: baseUrl + 'apis/getCurrentUser.php',
                                    method: 'GET'
                                } ).then ( function ( promise ) {

                                    if ( promise.data ) {

                                        // Authenticate user     
                                        if ( ! angular.isDefined ( promise.data.id ) ) {
                                            $location.path ( '/' );
                                        }
                                    }
                                    else {
                                        $location.path ( '/' );
                                    }
                                } );
                            }
                        }
                    } ).
                    when ( '/disposition-group-master', {
                        templateUrl: baseUrl + 'templates/disposition_group.html',
                        controller: 'dispositionGroupCtrl',
                        resolve: {
                            user_auth: function ( $http, $location ) {
                                return $http ( {
                                    url: baseUrl + 'apis/getCurrentUser.php',
                                    method: 'GET'
                                } ).then ( function ( promise ) {

                                    if ( promise.data ) {

                                        // Authenticate user     
                                        if ( ! angular.isDefined ( promise.data.id ) ) {
                                            $location.path ( '/' );
                                        }
                                    }
                                    else {
                                        $location.path ( '/' );
                                    }
                                } );
                            }}
                    } ).
                    when ( '/disposition-status-master', {
                        templateUrl: baseUrl + 'templates/disposition_group_status.html',
                        controller: 'dispositionGroupStatusCtrl',
                        resolve: {
                            user_auth: function ( $http, $location ) {
                                return $http ( {
                                    url: baseUrl + 'apis/getCurrentUser.php',
                                    method: 'GET'
                                } ).then ( function ( promise ) {

                                    if ( promise.data ) {

                                        // Authenticate user     
                                        if ( ! angular.isDefined ( promise.data.id ) ) {
                                            $location.path ( '/' );
                                        }
                                    }
                                    else {
                                        $location.path ( '/' );
                                    }
                                } );
                            }}
                    } ).
                    when ( '/manage-disposition-group-mapping/:group_id/:group_name', {
                        templateUrl: baseUrl + 'templates/mapping.html',
                        resolve: {
                            assigned_status: function ( utilityService, $routeParams ) {
                                return {
                                    status_ids: function () {
                                        var group_id = $routeParams.group_id;
                                        var promise = utilityService.get_disposition_group_status ( group_id );
                                        return promise;
                                    }
                                };
                            },
                            user_auth: function ( $http, $location ) {
                                return $http ( {
                                    url: baseUrl + 'apis/getCurrentUser.php',
                                    method: 'GET'
                                } ).then ( function ( promise ) {

                                    if ( promise.data ) {

                                        // Authenticate user     
                                        if ( ! angular.isDefined ( promise.data.id ) ) {
                                            $location.path ( '/' );
                                        }
                                    }
                                    else {
                                        $location.path ( '/' );
                                    }
                                } );
                            }
                        },
                        controller: 'mapping'
                    } ).
                    when ( '/add-lead', {
                        templateUrl: baseUrl + 'templates/add_lead_form.html',
                        controller: 'addLeadCtrl',
                        resolve: {
                            user_session: function ( $http, $location ) {
                                return $http ( {
                                    url: baseUrl + 'apis/getCurrentUser.php',
                                    method: 'GET'
                                } ).then ( function ( promise ) {

                                    if ( promise.data ) {

                                        // Authenticate user     
                                        if ( ! angular.isDefined ( promise.data.id ) ) {
                                            $location.path ( '/' );
                                        }else{
                                            return promise.data;
                                        }
                                    }
                                    else {
                                        $location.path ( '/' );
                                    }
                                } );
                            }
                        }
                    } ).
                    when ( '/edit-lead/:enquiry_id/:lead_id?', {
                        templateUrl: baseUrl + 'templates/edit_lead_form.html',
                        controller: 'editLeadCtrl',
                        resolve: {
                            client_data: function ( $http, $routeParams ) {
                                var enquiry_id = $routeParams.enquiry_id;
                            },
                            primaryLeadSource: function ( $http, httpService ) {
                                var data = new Array;
                                var http_config = {
                                    url: baseUrl + 'apis/helper.php?method=getCampaigns&params=campaign_type:primary',
                                    method: 'GET'
                                };

                                var primary_lead_source = httpService.makeRequest ( http_config );
                                return primary_lead_source;
                            },
                            user: function ( $http, $location ) {
                                return $http ( {
                                    url: baseUrl + 'apis/getCurrentUser.php',
                                    method: 'GET'
                                }).then ( function ( promise ) {
                                        if ( promise.data ) {

                                            // Authenticate user     
                                            if ( ! angular.isDefined ( promise.data.id ) ) {
                                                $location.path ( '/' );
                                            }

                                            return promise.data;
                                        }
                                        else {
                                            $location.path ( '/' );
                                        }
                                    });
                                },
                            is_editable : function ($http,$route,$location){

                                var is_editable = $http({
                                    url : baseUrl + 'apis/getLeadEditPermission.php',
                                    data : {
                                        enquiry_id : $route.current.params.enquiry_id
                                    },
                                    method: 'POST'
                                });

                                is_editable.then(function (response){

                                    if(response.data.http_status_code === 200){
                                       
                                        if(!response.data.is_editable){
                                            $location.path('/');
                                        }

                                    }else{
                                        if(response.data.http_status_code === 401){
                                            $location.path('/');
                                        }
                                    }
                                });
                            }
                        
                        } // End of resolve 
                    } ).
                    when ( '/lead-enqueries', {
                        templateUrl: baseUrl + 'templates/lead_enquiry.html',
                        controller: 'leadEnquiryCtrl'
                    } ).
                    when ( '/all-lead', {
                        templateUrl: baseUrl + 'templates/all-leads.html',
                        controller: 'allLeadCtrl',
                        resolve: {
                            user_auth: function ( $http, $location ) {
                                return $http ( {
                                    url: baseUrl + 'apis/getCurrentUser.php',
                                    method: 'GET'
                                } ).then ( function ( promise ) {

                                    if ( promise.data ) {

                                        // Authenticate user     
                                        if ( ! angular.isDefined ( promise.data.id ) ) {
                                            $location.path ( '/' );
                                        }
                                    }
                                    else {
                                        $location.path ( '/' );
                                    }
                                } );
                            }
                        }
                    } ).
                    when ( '/re-assign-lead', { // Umesh Chandra Katiyar
                        templateUrl: baseUrl + 'templates/reAssignLeads.html',
                        controller: 'reAssignLeadCtrl',
                        resolve: {
                            user_auth: function ( $http, $location ) {
                                return $http ( {
                                    url: baseUrl + 'apis/getCurrentUser.php',
                                    method: 'GET'
                                } ).then ( function ( promise ) {

                                    if ( promise.data ) {

                                        // Authenticate user     
                                        if ( ! angular.isDefined ( promise.data.id ) ) {
                                            $location.path ( '/' );
                                        }
                                    }
                                    else {
                                        $location.path ( '/' );
                                    }
                                } );
                            }
                        }
                    } ).
                    when ( '/my-re-assigned-leads', { // Umesh Chandra Katiyar
                        templateUrl: baseUrl + 'templates/getAgentReAssignedLeads.html',
                        controller: 'getAgentReAssignedLeadCtrl',
                        resolve: {
                            user_auth: function ( $http, $location ) {
                                return $http ( {
                                    url: baseUrl + 'apis/getCurrentUser.php',
                                    method: 'GET'
                                } ).then ( function ( promise ) {

                                    if ( promise.data ) {

                                        // Authenticate user     
                                        if ( ! angular.isDefined ( promise.data.id ) ) {
                                            $location.path ( '/' );
                                        }
                                    }
                                    else {
                                        $location.path ( '/' );
                                    }
                                } );
                            }
                        }
                    }).	//End Umesh Chandra Katiyar
                    when ( '/my-leads', {
                        templateUrl: baseUrl + 'templates/my-leads.html',
                        controller: 'myLeadsCtrl',
                        resolve: {

                            user_session : function (httpService){

                                return httpService.makeRequest({
                                    url : baseUrl + 'apis/getCurrentUser.php',
                                    method : 'GET'
                                }).then(function (success){
                                    return success.data;
                                });
                            },

                            disposition_status_list: function ( httpService ) {

                                return httpService.makeRequest ( {url: baseUrl + 'apis/get_disposition_status_list.php', method: 'GET'} ).then ( function ( success ) {
                                    return success.data;
                                } );
                            },

                            asm_users : function (httpService){

                                return httpService.makeRequest({
                                    url		: baseUrl + 'apis/get_asm_capacities.php',
                                    method	: 'GET',
                                    data : {}
                                }).then(function (successCallback){
                                    return successCallback.data;
                                });
                            }
                        }
                    } ).
                    when ( '/assigned-leads', {
                        templateUrl: baseUrl + 'templates/assigned_leads.html',
                        controller: 'assignedLeadsCtrl',
                        resolve: {
                            disposition_status_list: function ( httpService ) {

                                return httpService.makeRequest ( {url: baseUrl + 'apis/get_disposition_status_list.php', method: 'GET'} ).then ( function ( success ) {
                                    return success.data;
                                } );
                            }
                        }
                    } ).
                    when ( '/assign-disposition-group/:role/:id', {
                        templateUrl: baseUrl + 'templates/assign_disposition_group.htm',
                        controller: 'dispositionGroupAssignmentCtrl',
                        resolve: {
                            user_auth: function ( $http, $location ) {
                                return $http ( {
                                    url: baseUrl + 'apis/getCurrentUser.php',
                                    method: 'GET'
                                } ).then ( function ( promise ) {

                                    if ( promise.data ) {

                                        // Authenticate user     
                                        if ( ! angular.isDefined ( promise.data.id ) ) {
                                            $location.path ( '/' );
                                        }
                                    }
                                    else {
                                        $location.path ( '/' );
                                    }
                                } );
                            }
                        }
                    } ).
                    when ( '/add-disposition-group', {
                        templateUrl: baseUrl + 'templates/admin/add_disposition_group.htm',
                        controller: function ( $scope, utilityService, Session, user, $log, httpService ) {

                            $log.info ( user );

                            $scope.module = 'Admin disposition group';
                            $scope.user = user;

                            // Getting admin group name 
                            $scope.disposition_group = {
                            };

                            $scope.getAdminGroup = function () {

                                var promise = utilityService.getAdminDispositionGroup ( 'admin', $scope.user.id );

                                promise.then ( function ( response ) {

                                    if ( angular.isDefined ( response.data ) ) {
                                        $scope.disposition_group = response.data;
                                        if ( $scope.disposition_group.assign !== null ) {
                                            $scope.assign = 1;
                                        }
                                        else {
                                            $scope.assign = 0;
                                        }
                                    }
                                } );
                            };

                            $scope.getAdminGroup ();

                            $scope.setdisposition_group = function ( event ) {
                                var response = '';

                                if ( $ ( event.currentTarget ).prop ( 'checked' ) ) {
                                    // Save disposition group 
                                    response = utilityService.saveAdminDispositionGroup ( $scope.disposition_group.id, $scope.user.id, 1 );
                                }
                                else {
                                    // remove disposition group 
                                    response = utilityService.saveAdminDispositionGroup ( $scope.disposition_group.id, $scope.user.id, 0 );
                                }

                                response.then ( function ( promise ) {
                                    if ( promise.data.success == 1 ) {
                                        $scope.notify ( {message: promise.data.message, 'class': ['alert', 'alert-success', 'bottom-right']} );
                                    }
                                    else {
                                        $scope.notify ( {message: promise.data.message, 'class': ['alert', 'alert-warning', 'bottom-right']} );
                                    }
                                } );
                            };

                        },
                        resolve: {
                            user: function ( Session, httpService ) {

                                if ( angular.isUndefined ( Session.getUser () ) ) {

                                    // Call to server to authenticate user 
                                    var user_authenticate = httpService.makeRequest ( {url: baseUrl + 'apis/getCurrentUser.php', method: 'GET'} );

                                    user_authenticate.then ( function ( success ) {

                                        if ( success.data ) {

                                            if ( success.data.id ) {

                                                Session.createUser = success.data;
                                                return success.data;
                                            }
                                            else {
                                                $location.path ( '/' ); // Redirect user to login page 
                                            }
                                        }
                                        else {
                                            $location.path ( '/' ); // Redirect user to login page 
                                        }
                                    }, function ( error ) {

                                    } );
                                    return user_authenticate;
                                }
                                else {
                                    return Session.getUser ();
                                }

                            }
                        }

                    } ).
                   when ( '/lead-management', {
                             templateUrl: baseUrl + 'templates/lead_enquiry.html',
                             controller: 'leadEnquiryCtrl',
                             resolve: {
                                    user_session : function (httpService){

                                        var user_session = [];
                                        return httpService.makeRequest({
                                            url : baseUrl + 'apis/getCurrentUser.php',
                                            method : 'GET'
                                        }).then(function (success){
                                            return success.data;
                                        });							

                                }
                             }
                        }).
                    when('/manage_disposition_group_status/:group/:group_id/:employee_id', {
                        templateUrl : baseUrl + 'templates/manage_employee_disposition_status.html',
                        resolve : {

                            disposition_group : function ($route, $http){
                                var params = $route.current.params;
                                var group_id = params.group_id;
                                var employee_id  = params.employee_id;

                                return $http({
                                    url : baseUrl + 'apis/helper.php?method=getDispositionGroupData&params=group_id:'+group_id+'/employee_id:'+employee_id
                                });
                            },

                            employee_name : function ($route, httpService){

                                var employee_id = $route.current.params.employee_id;

                                return httpService.makeRequest({url : baseUrl + 'apis/helper.php?method=getEmployeeNameById&params=employee_id:'+employee_id , method : 'GET'}).then(function (success){

                                    return success.data;
                                });
                            }

                        },
                        controller : 'manageEmpDispositionGroupCtrl' 
                    }).
                    when('/lead_detail/:enquiry_id/:lead_id?/:sub_page?', {
                        templateUrl : baseUrl + 'templates/view_lead.html',
                        controller : 'viewLeadCtrl',
                        resolve : {
                            user_session : function (httpService, $location){

                                    var user_session = [];

                                    return httpService.makeRequest({
                                        url : baseUrl + 'apis/getCurrentUser.php',
                                        method : 'GET'

                                    }).then(function (success){

                                        if(typeof success.data === 'object'){

                                            if(Object.keys(success.data).length > 0){
                                                return success.data;
                                            }else{
                                                $location.path('/');    
                                            }
                                        }else{
                                            $location.path('/');
                                        }

                                    });							
                            },

                            enquiry_status : function ($route, httpService){

                                var params = $route.current.params;
                                var enquiry_id = params.enquiry_id;
                                var lead_id ='';

                                if(params.lead_id){
                                    lead_id = params.lead_id;
                                }

                                return httpService.makeRequest({
                                    url : baseUrl + 'apis/helper.php?method=getEnquiryActionStatus&params=enquiry_id:'+enquiry_id+'/lead_id:'+lead_id,
                                    method : 'GET'
                                }).then(function (success){

                                    if(parseInt(success.data.success) === 1){
                                        return success.data.data;
                                    }else{
                                        return [];
                                    }

                                }, function (error){

                                });

                            },

                            sales_persons : function ($http){

                                return $http (
                                        {
                                            url: baseUrl + 'apis/getEmployeeByDesignation.php',
                                            method: 'POST',
                                            data: $.param ( {slug: 'sales_person'} ),
                                            headers: {
                                                'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
                                            }
                                        }).then ( function ( success ) {

                                                return ( success.data.data );
                                        });
                            },

                            is_lead_accepted : function ($route, httpService){

                                var params		= $route.current.params;
                                var enquiry_id	= params.enquiry_id;
                                var lead_id		= 'NULL';

                                if(params.lead_id){
                                    lead_id = params.lead_id;
                                }

                                return httpService.makeRequest({
                                    url : baseUrl + 'apis/helper.php?method=getLeadAcceptStatus&params=enquiry_id:'+enquiry_id+'/lead_id:'+lead_id,
                                    method : 'POST',
                                    data : {
                                        enquiry_id : enquiry_id,
                                        lead_id : lead_id
                                    }
                                }).then(function (success){

                                    return success.data.accept_status;
                                });
                            },

                            is_lead_closed : function (httpService, $route){
                                var params = $route.current.params;
                                var enquiry_id = params.enquiry_id;

                                return httpService.makeRequest({
                                    url : baseUrl + 'apis/helper.php?method=isLeadClosed&params=enquiry_id:'+enquiry_id,
                                    method : 'GET'
                                }).then(function (success){
                                    return success.data;
                                }, function (error){
                                    return false;
                                });
                            },

                            is_editable : function ($http,$route,$location){

                                var is_editable = $http({
                                    url : baseUrl + 'apis/getLeadEditPermission.php',
                                    data : {
                                        enquiry_id : $route.current.params.enquiry_id
                                    },
                                    method: 'POST'
                                });

                                return is_editable.then(function (response){

                                    if(response.data.http_status_code === 200){
                                        return response.data.is_editable;
                                    }else{
                                        if(response.data.http_status_code === 401){
                                            $location.path('/');
                                        }
                                    }
                                });
                            }
                        }

                    }).
                    when('/email-template-system', {
                        templateUrl : baseUrl + 'templates/email_templates.html',
                        controller : 'emailTemplateCtrl',
                        resolve : {
                            user_session : function (httpService){

                                    var user_session = [];

                                    return httpService.makeRequest({
                                        url : baseUrl + 'apis/getCurrentUser.php',
                                        method : 'GET'
                                    }).then(function (success){

                                        return success.data;
                                    });							
                            },

                            templates : function (httpService){

                                return httpService.makeRequest({
                                    url : baseUrl + 'apis/get_email_templates.php',
                                    method : 'GET'
                                }).then(function (successCallback){
                                    return successCallback.data;
                                });
                            }
                        }
                    }).
                    when('/add_email_template', {
                        templateUrl : baseUrl + 'templates/add_email_template.html',
                        controller : 'addEmailTemplateCtrl',
                        resolve : {
                            user_session : function (httpService){
                                var user_session = [];
                                return httpService.makeRequest({
                                    url : baseUrl + 'apis/getCurrentUser.php',
                                    method : 'GET'
                                }).then(function (success){
                                    return success.data;
                                });	
                            },

                            email_users : function (httpService){

                                return httpService.makeRequest({
                                    url : baseUrl + 'apis/get_email_users.php',
                                    method : 'GET'
                                }).then(function (success){
                                    return success.data;
                                });
                            }
                        }
                    }).
                    when('/edit_email_template/:template_id', {
                        templateUrl : baseUrl + 'templates/add_email_template.html',
                        controller : function ($scope, email_template_events, email_template, email_users, $filter, httpService, $window, $route, baseUrl, user_session,$routeParams,$location){

                            $scope.user						= user_session; // Logged in user session

                            var template_id					=  $routeParams.template_id; // template id 

                            $scope.page_title				= 'Edit Email Template';
                            $scope.email_template_events	= email_template_events;
                            $scope.email_users				= email_users;
                            $scope.submit_btn_label			= 'Update';

                            $scope.email_template = {};

                            var template						= email_template[0]; // data of email template to edit
                            $scope.email_template.subject		= template.subject;
                            $scope.email_template.event			= template.event; 
                            $scope.email_template.category		= template.email_category;
                            $scope.email_template.template_id	= template_id;

                            var email_message					= template.message_body;
                            $scope.email_template.message		= email_message;

                            $('#summernote').summernote('code', email_message);

                            $scope.to	= [];
                            $scope.cc	= [];
                            $scope.bcc	= [];

                            /**
                             * Pushing email users to respective group 
                             */
                            angular.forEach (template.to_users, function(val, key){
                                $scope.to.push(val.id);
                            });
                            angular.forEach (template.cc_users, function(val, key){
                                $scope.cc.push(val.id);
                            });
                            angular.forEach (template.bcc_users, function(val, key){
                                $scope.bcc.push(val.id);
                            });

                            // auto select drop down values of to, cc and bcc email users list  
                            $('#to_user').val($scope.to).prop('selected', true);
                            $('#cc_user').val($scope.cc).prop('selected', true);
                            $('#bcc_user').val($scope.bcc).prop('selected', true);

                            $scope.clearError = function (element_id){
                                var dom_element = '#' + element_id;
                                angular.element(dom_element).removeClass ('parsley_error').next().html('');
                            };

                            $scope.resetFormErrors = function (){
                                var element_classes = ['email_category','email_event','email_subject'];			
                                angular.forEach (element_classes, function(val){
                                    var element_id = '#' + val;
                                    angular.element(element_id + '_help_block').html('');
                                    angular.element(element_id).removeClass('parsley_error');
                                });
                            };

                            $scope.saveTemplate = function (){

                                $scope.resetFormErrors();
                                var error_flag	= false;
                                var markupStr	= $('#summernote').summernote('code');
                                $scope.email_template.message = markupStr; // Get message body data again if any change has been made to message 

                                // category validation
                                if(!$scope.email_template.category){
                                    angular.element('#email_category').addClass('parsley_error');
                                    angular.element('#email_category_help_block').html('Email category is required');
                                    error_flag = true;
                                }

                                // event validation
                                if(!$scope.email_template.event){
                                    angular.element('#email_event').addClass('parsley_error');
                                    angular.element('#email_event_help_block').html('Email event is required');
                                    error_flag = true;
                                }

                                // subject validation
                                if(!$scope.email_template.subject){
                                    angular.element('#email_subject').addClass('parsley_error');
                                    angular.element('#email_subject_help_block').html('Email subject is required');
                                    error_flag = true;
                                }

                                if(error_flag){
                                    return false;
                                }

                                if($scope.to.length){
                                    $scope.email_template.toUsers = [];

                                    angular.forEach($scope.to, function (val){
                                        var temp_to_user = $filter('filter')($scope.email_users, {id : val}, true);
                                        $scope.email_template.toUsers.push( temp_to_user[0] );
                                    });
                                }else{
                                    $scope.email_template.toUsers = [];
                                }

                                if($scope.cc.length){	

                                    $scope.email_template.ccUsers = [];

                                    angular.forEach($scope.cc, function (val){
                                        var temp_cc_user = $filter('filter')($scope.email_users, {id : val}, true);
                                        $scope.email_template.ccUsers.push( temp_cc_user[0] );
                                    });
                                }else{
                                    $scope.email_template.ccUsers = [];
                                }

                                if($scope.bcc.length){					

                                    $scope.email_template.bccUsers = [];

                                    angular.forEach($scope.bcc, function (val){
                                        var temp_bcc_user = $filter('filter')($scope.email_users, {id : val}, true);
                                        $scope.email_template.bccUsers.push( temp_bcc_user[0] );
                                    });
                                }else{
                                    $scope.email_template.bccUsers = [];
                                }

                                // making http request to server to update the template 

                                var save_email_template = httpService.makeRequest({

                                    url : baseUrl + 'apis/save_email_template.php',
                                    method : 'POST',
                                    data : {
                                        template_data	: $scope.email_template,
                                        user_id			: $scope.user.id,
                                        mode			: 'edit'
                                    }
                                });

                                save_email_template.then(function (successCallback){

                                    if(parseInt(successCallback.data.success === 1)){

                                        $scope.notify({
                                            message : successCallback.data.message,
                                            class : ['alert alert-success center-aligned']
                                        });

                                        $window.location.reload();		
                                    }else{
                                        $scope.notify({
                                            message : successCallback.data.message,
                                            class : ['alert alert-warning center-aligned']
                                        });
                                    }
                                }, function (errorCallback){
                                    alert('Some error has been occurred.');
                                });

                            }; // End save template function 

                            $scope.back_to_list = function (){
                                    $location.path('/email-template-system');
                            };

                        },
                        resolve : {
                            email_template : function (httpService, $route){

                                var template_id = $route.current.params.template_id;
                                return httpService.makeRequest({
                                    url : baseUrl + 'apis/get_email_templates.php?template_id='+template_id,
                                    method : 'GET'
                                }).then(function (successCallback){
                                    return successCallback.data;
                                });
                            },
                            email_users : function (httpService){

                                return httpService.makeRequest({
                                    url : baseUrl + 'apis/get_email_users.php',
                                    method : 'GET'
                                }).then(function (success){
                                    return success.data;
                                });
                            },
                            user_session : function (httpService){
                                var user_session = [];
                                return httpService.makeRequest({
                                    url : baseUrl + 'apis/getCurrentUser.php',
                                    method : 'GET'
                                }).then(function (success){
                                    return success.data;
                                });
                            }
                        }
                    }).
                    when('/message-communication-templates', {
                                templateUrl : baseUrl + 'templates/message_templates.html',
                                controller : 'messageTemplatesCtrl',
                                resolve : {
                                    user_session : function (httpService){
                                        return httpService.makeRequest({
                                            url : baseUrl + 'apis/getCurrentUser.php',
                                            method : 'GET'
                                        }).then(function (success){
                                            return success.data;
                                        });
                                    },
                                    message_templates : function ($route, httpService){

                                        return httpService.makeRequest({
                                            url : baseUrl + 'apis/get_message_templates.php',
                                            method : 'GET'
                                        }).then(function (successCallback){
                                            return successCallback.data;
                                        });

                                    }
                                }
                            }).
                    when('/add_message_template', {
                        templateUrl : baseUrl + '/templates/add_message_template.html',
                        controller : 'addMessageTemplateCtrl',
                        resolve : {
                            user_session : function (httpService){

                                return httpService.makeRequest({
                                    url : baseUrl + 'apis/getCurrentUser.php',
                                    method : 'GET'
                                }).then(function (success){
                                    return success.data;
                                });
                            }
                        }
                    }).
                    when('/edit_message_template/:template_id',{
                                templateUrl : baseUrl + 'templates/add_message_template.html',
                                controller : function ($scope,message_template, user_session, message_template_events, httpService, $route){
                                    $scope.user = user_session;
                                    var $template = message_template;


                                    $scope.page_title = 'Edit Template';
                                    $scope.submit_btn_label = 'Edit Template';
                                    $scope.message_event_templates = message_template_events;

                                    $scope.message_template = {};
                                    $scope.message_template.template_id		= $template[0].template_id;
                                    $scope.message_template.category		= $template[0].message_category;
                                    $scope.message_template.event			= $template[0].event;
                                    $scope.message_template.message_text	= $template[0].message;

                                    if($template[0].default_numbers.length > 0){
                                        $scope.message_template.numbers = $template[0].default_numbers;
                                        $scope.number = $template[0].default_numbers.join(',');
                                    }

                                    $scope.addNumber = function (number){

                                        if(number){
                                            // split string from comma
                                            $scope.message_template.numbers = number.split(',');
                                        }else{
                                            $scope.message_template.numbers = [];			
                                        }			
                                    };

                                    $scope.clearError = function (element_id){
                                        var dom_element = '#' + element_id;
                                        var help_block = '#' + element_id + '_help_block';

                                        angular.element(dom_element).removeClass ('parsley_error');
                                        angular.element(help_block).html('');
                                    };


                                    $scope.saveTemplate = function (){

                                        // Validations 

                                        var error_flag = false;

                                        if($scope.message_template.category === undefined){
                                            angular.element('#message_category').addClass('parsley_error');
                                            angular.element('#message_category_help_block').html('Please select message category');
                                            error_flag = true;
                                        }

                                        if($scope.message_template.event === undefined){
                                            angular.element('#message_event').addClass('parsley_error');
                                            angular.element('#message_event_help_block').html('Please select message event');
                                            error_flag = true;
                                        }

                                        if($scope.message_template.message_text === undefined){
                                            angular.element('#message_text').addClass('parsley_error');
                                            angular.element('#message_text_help_block').html('Message text is required');
                                            error_flag = true;
                                        }

                                        if(error_flag){
                                            return false;
                                        }else{

                                            var save_template = httpService.makeRequest({

                                                url : baseUrl + 'apis/add_message_template.php',
                                                method : 'POST',
                                                data : {
                                                    message_data	: $scope.message_template,
                                                    user_id			: $scope.user.id,
                                                    mode : 'edit'
                                                }
                                            });

                                            save_template.then(function (succcessCallback){

                                                if(parseInt(succcessCallback.data.success === 1)){

                                                    $scope.notify({
                                                        class:['alert','alert-success','center-aligned'],
                                                        message : succcessCallback.data.message
                                                    });

                                                    $route.reload();

                                                }else{
                                                    $scope.notify({
                                                        class:['alert','alert-warning','center-aligned'],
                                                        message : succcessCallback.data.message
                                                    });
                                                }
                                            });
                                        }
                                    };
                                },
                                resolve : {
                                    user_session : function (httpService){
                                        return httpService.makeRequest({
                                            url : baseUrl + 'apis/getCurrentUser.php',
                                            method : 'GET'
                                        }).then(function (success){
                                            return success.data;
                                        });
                                    },

                                    message_template : function (httpService, $route){
                                        var template_id = $route.current.params.template_id;
                                        return httpService.makeRequest({
                                            url : baseUrl + 'apis/get_message_templates.php?template_id='+template_id,
                                            method : 'GET'
                                        }).then(function (successCallback){
                                            return successCallback.data;
                                        });
                                    }
                                }
                    }).
                    when('/capacity-area-sales-manager', {
                        templateUrl : baseUrl + '/templates/capacity_asm.html',
                        controller : 'capacityManagerAsmCtrl',
                        resolve : {
                            user_session : function (httpService){
                                return httpService.makeRequest({
                                    url : baseUrl + 'apis/getCurrentUser.php',
                                    method : 'GET'
                                }).then(function (success){
                                    return success.data;
                                });
                            },
                            asm : function (httpService){
                                return httpService.makeRequest({
                                    url : baseUrl + 'apis/helper.php?method=getAsmEmployees',
                                    method : 'GET',
                                    data : {}
                                }).then(function (success){
                                    return success.data;
                                });
                            },
                            asm_projects : function ($route, httpService){

                                var asm_id = $route.current.params.asm_id;

                                var projects = httpService.makeRequest({
                                    url : baseUrl + 'apis/helper.php?method=get_area_sales_manager_projects&params=asm_user:'+asm_id,
                                    method : 'GET'
                                });

                                return projects.then(function (successCallback){
                                    return successCallback.data;
                                });
                            }
                        }
                    }).
                    when('/capacity-sales-person', {
                        templateUrl : baseUrl + 'templates/sales_person_capacity.html',
                        controller : 'salesPersonCapacityCtrl',
                        resolve : {
                            user_session : function (httpService){
                                return httpService.makeRequest({
                                    url : baseUrl + 'apis/getCurrentUser.php',
                                    method : 'GET'
                                }).then(function (success){
                                    return success.data;
                                });
                            },
                            sales_person_capacities : function (httpService){

                                return httpService.makeRequest({
                                    url : baseUrl + 'apis/get_sales_person_capacity.php',
                                    method : 'GET'
                                }).then(function (response){

                                    if(parseInt(response.data.success) === 1 && parseInt(response.data.http_status_code) === 200){

                                        return response.data.data;
                                    }
                                    else{
                                        alert(response.data.error);
                                        return [];
                                    }
                                });

                            }
                        }
                    }).
                    when('/add_sales_person_capacity/:sales_person_id?/:manager_id?/:manager_capacity?/:mode?',{

                        templateUrl : baseUrl + 'templates/add_sales_person_capacity.html',

                        controller	: 'addSalesPersonCapacityCtrl',

                        resolve : {
                            user_session : function (httpService){
                                return httpService.makeRequest({
                                    url : baseUrl + 'apis/getCurrentUser.php',
                                    method : 'GET'
                                }).then(function (success){
                                    return success.data;
                                });
                            },

                            sales_person_list : function ($route, httpService){

                                return (httpService.makeRequest({
                                    url : baseUrl + 'apis/helper.php?method=getCRMUsersByDesignation&params=designation_slug:sales_person',
                                    method : 'GET'
                                })).then(function (response){

                                        return response.data;
                                });
                            },

                            already_assigned_capacity_users : function ($http){
                                return $http.get(baseUrl + 'apis/helper.php?method=is_all_sp_user_has_capacity_assigned').
                                then(function (response){

                                    return response.data.data;

                                });
                            }
                        }
                    }).	
                    when('/capacity/:designation_slug?', {
                        templateUrl : baseUrl + 'templates/asm_capacity_listing.html',
                        resolve : {
                            user_session : function (httpService){
                                return httpService.makeRequest({
                                    url : baseUrl + 'apis/getCurrentUser.php',
                                    method : 'GET'
                                }).then(function (success){
                                    return success.data;
                                });
                            },
                            capacities : function (httpService){

                                var capacities = httpService.makeRequest({
                                    url : baseUrl + 'apis/get_asm_capacities.php',
                                    method : 'GET'
                                });

                                return capacities.then(function(successCallback){
                                    return successCallback.data;
                                });
                            }
                        },
                        controller : 'capacities_asm'
                    }).		
                    when('/edit_capacity_asm/:asm_id', {
                        templateUrl : baseUrl + 'templates/capacity_asm.html',
                        controller : 'capacityManagerAsmCtrl',
                        resolve : {
                            user_session : function (httpService){
                                return httpService.makeRequest({
                                    url : baseUrl + 'apis/getCurrentUser.php',
                                    method : 'GET'
                                }).then(function (success){
                                    return success.data;
                                });
                            },
                            asm : function (httpService){
                                return httpService.makeRequest({
                                    url : baseUrl + 'apis/helper.php?method=getAsmEmployees',
                                    method : 'GET',
                                    data : {}
                                }).then(function (success){
                                    return success.data;
                                });
                            },
                            asm_projects: function ($route, httpService){

                                var asm_id	= $route.current.params.asm_id;
                                var month	= new Date().getMonth ();
                                var year	= new Date().getFullYear ().toString ();

                                var projects = httpService.makeRequest({
                                    url : baseUrl + 'apis/helper.php?method=get_area_sales_manager_projects&params=asm_user:'+asm_id+'/month:'+month+'/year:'+year,
                                    method : 'GET'
                                });

                                return projects.then(function (successCallback){
                                    return successCallback.data;
                                });
                            }
                        }
                    }).		
                    when('/previous_capacities/:user_id/:designation_slug?', {
                        templateUrl : baseUrl + 'templates/previousCapacitiesListing.html',
                        resolve		: {
                            user_session : function (httpService){
                                return httpService.makeRequest({
                                    url : baseUrl + 'apis/getCurrentUser.php',
                                    method : 'GET'
                                }).then(function (success){
                                    return success.data;
                                });
                            },
                            user_name : function (httpService, $route){
                                var user_id = $route.current.params.user_id;

                                return httpService.makeRequest ({
                                    url : baseUrl + 'apis/helper.php?method=getEmployeeNameById&params=user_id:'+ user_id, method : 'GET'
                                }).then(function (successResponse){
                                    return( successResponse.data );
                                });
                            }
                        },
                        controller	: 'previousCapacitiesCtrl'
                    }).
                    when('/my-settings', {
                        templateUrl : baseUrl + 'templates/settings.html',
                        controller : function ($scope){
                        }
                    }).
                    when('/user_settings', {
                        templateUrl : baseUrl + 'templates/user_settings.html',
                        controller : function ($scope, user_session){

                            $scope.user = user_session;

                        },
                        resolve : {
                            user_session : function (httpService){
                                return	httpService.makeRequest({
                                            url : baseUrl + 'apis/getCurrentUser.php',
                                            method : 'GET'
                                        }).then(function (success){
                                            return success.data;
                                });
                            }
                        }

                    }).	
                    when('/testing_page', {
                        templateUrl : baseUrl + 'templates/testing_page.html',
                        controller : function ($scope, dateUtility){

                            $scope.names = [
                                {id: 1, name: 'Abhishek'},
                                {id: 2, name: 'Jeff'},
                                {id: 3, name: 'Hitesh'},
                                {id: 4, name: 'Abhay'},
                            ];	

                            $scope.getPerson = function (person_name){

                                alert('name in scope - ' + $scope.name);
                                alert('name in parameter - ' + person_name);
                            };

                        }
                    }).
                    when('/reset-password-request',{
                        templateUrl : baseUrl + 'templates/reset_employee_passwords.html',
                        controller : function ($scope, requests, httpService, baseUrl, $route){

                            $scope.requests			= requests;

                            $scope.resetPassword	= function (data){

                                var reset_password =  httpService.makeRequest({
                                    url: baseUrl + 'apis/reset_employee_password.php',
                                    method : 'POST',
                                    data : data
                                });

                                reset_password.then(function (response){

                                    if( parseInt(response.data.success) === 1 ){
                                        $scope.notify({class : ['alert','alert-success','bottom-right'], message: response.data.message});
                                        $route.reload();
                                    }else{
                                        $scope.notify({class : ['alert','alert-warning','center-aligned'], message: response.data.message});
                                    }		
                                });
                            };
                        },
                        resolve : {
                            requests : function (httpService){

                                return httpService.makeRequest({
                                    url : baseUrl + 'apis/get_reset_password_requests.php',
                                    method : 'GET'
                                }).then(function (response){

                                    return response.data;
                                });
                            }
                        }
                    }).	
                    when('/lead-closure/:enquiry_number/:status_id/:sub_status_id', {
                        templateUrl : baseUrl + 'templates/lead_closure.html',
                        controller : 'leadClosureCtrl',
                        resolve : {
                            closure_mode : function ($route, httpService){

                                var status_id		= $route.current.params.status_id;
                                var sub_status_id	= $route.current.params.sub_status_id;

                                if( parseInt(sub_status_id) === 16){
                                    return 'cheque';
                                }
                                else if ( parseInt(sub_status_id) === 17) {
                                    return 'online_transaction';
                                }
                                else if ( parseInt(sub_status_id) === 33){
                                    return 'lead_close';
                                }
                            },
                            user_session : function (httpService){
                                return	httpService.makeRequest({
                                            url : baseUrl + 'apis/getCurrentUser.php',
                                            method : 'GET'
                                        }).then(function (success){
                                            return success.data;
                                });
                            }
                        }
                    }).
                    when('/closed-leads', {
                        templateUrl : baseUrl +'templates/closed_leads.html',
                        controller : function ($scope, user_session, leadsService, $filter, $location){

                            $scope.user = user_session;

                            $scope.closed_leads = [];

                            $scope.get_closed_leads = function (){

                                var lead_promise = leadsService.getClosedLeads($scope.user.id);

                                lead_promise.then(function (res){

                                    if(parseInt(res.data.success) === 1){
                                        $scope.closed_leads = res.data.data;
                                    }
                                });
                            };

                            $scope.get_closed_leads ();

                            $scope.getPaymentCollectionDetail = function (enquiry_id,lead_id){

                                // redirect to closed lead detail page

                                if(lead_id.toLowerCase() == 'null' || lead_id == ''){
                                    lead_id = '';
                                }

                                $location.path('/closed_lead_payment_detail/'+enquiry_id+'/'+lead_id);
                            };

                        },
                        resolve : {
                            user_session : function (httpService){
                                return	httpService.makeRequest({
                                            url : baseUrl + 'apis/getCurrentUser.php',
                                            method : 'GET'
                                        }).then(function (success){
                                            return success.data;
                                });
                            }
                        }
                    }).
                    when('/edit_sales_person_capacity/:sp_id/:manager_id/:manager_capacity', {
                        templateUrl : baseUrl + 'templates/edit_sp_capacity.html',
                        controller : function ($scope,sp, dateUtility,max_capacity,httpService){

                            $scope.sales_person = sp;

                            $scope.current_month  = dateUtility.current_month_in_textual_representation('full');

                            $scope.current_year   = dateUtility.current_year;

                            $scope.sales_person.capacity_month  = new Date().getMonth();

                            $scope.sales_person.capacity_year   = new Date().getFullYear();

                            $scope.sales_person_max_capacity =  max_capacity;

                            // validation on assignment of max capacity
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

                            // Function to save capacity of sales person
                            $scope.saveCapacity = function (data){

                                if(!data.sales_person_capacity){
                                    alert('Please enter sales person capacity');
                                    return false;
                                }

                                var save_capacity = httpService.makeRequest({
                                    url     : baseUrl + 'apis/edit_sales_person_capacity.php',
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

                        },
                        resolve : {
                            sp : function ($route, $http){

                                var params = $route.current.params;

                                var url  = baseUrl + 'apis/helper.php?method=get_sales_person_details&params=user_id:'+params.sp_id;
                                return $http.get(url).then(function (response){

                                    return response.data;
                                });
                            },

                            max_capacity : function ($route, $http){

                                var params = $route.current.params;
                                var url = baseUrl + 'apis/helper.php?method=getSalesPersonMaxCapacity&params=asm_id:'+params.manager_id+'/sales_person_id:'+params.sp_id+'/asm_capacity:'+params.manager_capacity;

                                return $http.get(url).then(function (response){
                                    return response.data;
                                });
                            }
                        }
                    }).
                    when('/closed_lead_payment_detail/:enquiry_id/:lead_number?/', {

                        templateUrl : baseUrl + 'templates/closed_lead_payment_detail.html',
                        controller  : 'closedLeadPaymentDetailCtrl',
                        resolve     : {
                            user_session : function (httpService){
                                return	httpService.makeRequest({
                                            url : baseUrl + 'apis/getCurrentUser.php',
                                            method : 'GET'
                                        }).then(function (success){
                                            return success.data;
                                });
                            },
                            payment_details : function ($http, $route){

                                var enquiry_id  = $route.current.params.enquiry_id;
                                var lead_id     = (angular.isDefined($route.current.params.lead_id) ? $route.current.params.lead_id : '');

                                var url = baseUrl + 'apis/getPaymentCollectionDetails.php'
                                var lead_data = $http.post(url,$.param({enquiry_id : enquiry_id, lead_number : lead_id}),{
                                    headers : {'Content-Type' : 'application/x-www-form-urlencoded; charset=UTF-8'}
                                });

                                return lead_data.then(function (response){

                                    if(response.data.success == 1){
                                       return response.data.data;
                                    }else{
                                        return [];
                                    }
                                });
                            }
                        }

                    }).
                    when('/enquiry_remarks/:enquiry_id', {
                        templateUrl : baseUrl + 'templates/enquiry_remarks.html',
                        controller: function ($scope,$routeParams, $location, $filter, remarks){
                            $scope.remarks = remarks; // Enquiry remarks
                            $scope.enquiry_id = $routeParams.enquiry_id;
                        },
                        resolve: {
                            remarks : function ($route,httpService){
                                var enquiry_id = $route.current.params.enquiry_id;
                                return httpService.makeRequest({
                                    url : baseUrl + 'apis/helper.php?method=getAllEnquiryRemarks&params=enquiry_id:'+enquiry_id,
                                    method: 'GET'
                                }).then(function(response){
                                    return response.data;
                                });
                            }
                        }
                    }).
                    otherwise ( {
                        // Redirect to home
                        redirectTo: '/home'
                    } );

            /**
             * UI-Router Configuration 
             */

            $stateProvider

                    .state('view_lead_customer_info', {
                        templateUrl : baseUrl + 'partials/view_lead/customer_info.html',
                        controller : function ($scope, $routeParams, customer_details, $location, $state){

                            $scope.customer = customer_details;

                            // Listening event of sending client address
                            $scope.$on('sendClientAddress', function (event, args){
                               $scope.getClientAddress($scope.customer.customerAddress);
                            });

                        },
                        resolve : {
                            customer_details : function ($route, httpService){

                                var route_params			= $route.current.params;
                                var employee_details		= [];
                                var enquiry_id				= route_params.enquiry_id;
                                var lead_id					= '';

                                if(typeof route_params.lead_id !== 'undefined'){
                                    lead_id = route_params.lead_id;
                                }else{
                                    lead_id = '';
                                }

                                var employee = httpService.makeRequest({
                                    url : baseUrl + 'apis/helper.php?method=getLeadCustomerInfo&params=enquiry_id:'+enquiry_id+'/lead_id:'+lead_id,
                                    method : 'GET'
                                });

                                return employee.then(function (success){

                                    if(success.data){
                                        return employee_details = success.data;
                                    }
                                });

                            }
                        }
                    })
 
                    .state('view_lead_info', {
                        templateUrl : baseUrl + 'partials/view_lead/lead_info.html',
                        controller : function ($scope, $routeParams, lead_info, $filter,more_remarks){

                            $scope.more_remarks = more_remarks;
                            
                            $scope.lead_detail = lead_info;

                            $scope.client_pref = [];
                            // client prefrences
                            if($scope.lead_detail.client_property_preferences){

                                var client_pref_object = JSON.parse($scope.lead_detail.client_property_preferences);

                                angular.forEach(client_pref_object, function (val){
                                    angular.forEach(val, function (pref){
                                        $scope.client_pref.push(pref);
                                    });
                                });    
                            }

                            $scope.is_event_detail = false;

                            $scope.event_detail = {

                                datetime: null,
                                address : null,
                                event_id: null,
                                project : null,
                            };

                            if( parseInt($scope.lead_detail.disposition_status_id) === 3){

                                $scope.is_event_detail = true;
                                // Meeting status
                                $scope.event_detail.datetime = $filter('date')($scope.lead_detail.meeting.meeting_timestamp,'dd MMM, yyyy H:mm a','+0530');
                                $scope.event_detail.address = $scope.lead_detail.meeting.meeting_address;
                                $scope.event_detail.event_id = $scope.meeting_id;
                                $scope.event_detail.project = JSON.parse($scope.lead_detail.meeting.project);
								
                            }

                            if( parseInt($scope.lead_detail.disposition_status_id) === 6){

                                $scope.is_event_detail = true;
                                // Meeting status
                                $scope.event_detail.datetime = $filter('date')($scope.lead_detail.site_visit.site_visit_timestamp,'dd MMM, yyyy H:mm a','+0530');
                                $scope.event_detail.address = $scope.lead_detail.site_visit.site_location;
                                $scope.event_detail.event_id = $scope.site_visit_id;
                                $scope.event_detail.project = JSON.parse($scope.lead_detail.site_visit.project);
                            }



                        },
                        resolve : {

                            lead_info : function (httpService, $route){

                                var route_params		= $route.current.params;
                                var lead_details		= [];
                                var enquiry_id			= route_params.enquiry_id;
                                var lead_id			    = '';

                                if(route_params.lead_id){
                                    lead_id = route_params.lead_id;
                                }

                                var lead_response = httpService.makeRequest({
                                    url : baseUrl + 'apis/get_single_lead.php',
                                    method : 'POST',
                                    data : {
                                        enquiry_id : enquiry_id
                                    }
                                });

                                return lead_response.then(function (success){

                                    if(success.data){
                                        return lead_details = success.data;
                                    }
                                });
                            },
                            
                            more_remarks : function (httpService, $route){
                                var enquiry_id		= $route.current.params.enquiry_id;
                                return httpService.makeRequest({
                                     url : baseUrl + 'apis/helper.php?method=getAllEnquiryRemarks&params=enquiry_id:'+enquiry_id,
                                     method: 'GET'
                                }).then(function (response){
                                    if(response.data.length > 1){
                                       return true;
                                    }else{
                                       return false;
                                    }
                                });
                            }
                        }

                    })

                    .state('view_lead_project_info', {
                        templateUrl : baseUrl + 'partials/view_lead/lead_project_info.html',
                        resolve : {
                            params : function ($route){
                                var params = $route.current.params;
                                return params;
                            }
                        },
                        controller : function ($scope, httpService, params, $filter, $route, $state, $http){

                            $scope.enquiry_id = params.enquiry_id;

                            if(angular.isDefined (params.lead_id)){
                                $scope.lead_number = params.lead_id;
                            }

                            $scope.project_loading_text = 'Loading ...';
                            
                            $scope.projects = [];

                            $scope.getLeadProjects = function (){

                                $scope.project_loading_text = 'Loading ...';
                                
                                var status_id = $scope.current_enquiry_status.status_id;

                                var project_response = $http.get(baseUrl + 'apis/get_lead_enquiry_projects.php?enquiry_number='+$scope.enquiry_id+'&lead_number='+$scope.lead_id+'&status='+status_id);

                                return project_response.then(function (success){

                                    if( parseInt(status_id) === 3 || parseInt(status_id) === 6 ){
                                        $scope.projects = success.data.data;
                                    }
                                    else{
                                        if( success.data.data.length > 0){

                                            angular.forEach(success.data.data, function (project_obj, key){

                                               var temp = {
                                                   project_id : project_obj.project_id,
                                                   project_name : project_obj.project_name,
                                                   project_city : project_obj.project_city,
                                                   project_url : project_obj.project_url
                                               };

                                               $scope.projects.push(temp);
                                            });
                                            return $scope.projects;    
                                        }else{
                                            $scope.project_loading_text = 'No Project Found';
                                            return [];
                                        }      
                                    }
                                });
                            };

                            $scope.getLeadProjects();

                            $scope.reloadRoute = function (state){
                                $route.reload();
                            };

                            /**
                                * Function to remove project
                                * @returns {undefined}
                             */
                                
                                // Remove enquiry project
                                $scope.removeProject = function (p){

                                    // Confirm from user to delete project 

                                    var confirm_delete = confirm('Do you really want to delete this project?');

                                    if(confirm_delete){
                                        $http.post(baseUrl+'apis/remove_enquiry_project.php', {enquiry_id: $scope.enquiry_id, project : p}).then(function (response){
                                        
                                        if(response.data.http_status_code === 200){

                                            if(response.data.success === 1){
                                                showToast(response.data.message, 'Edit Lead','success');
                                                $route.reload();
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
                        }
                    })

                    .state('view_lead_history', {
                        templateUrl : baseUrl + 'partials/view_lead/lead_history.html',
                        resolve : {

                            history : function ($route, httpService){

                                var route_params		= $route.current.params;
                                var enquiry_id			= route_params.enquiry_id;

                                if(angular.isDefined (route_params.lead_id)){
                                    var lead_number = route_params.lead_id;
                                }

                                // calling API to get history of enquiry 
                                var history = httpService.makeRequest({
                                    url : baseUrl + 'apis/getEnquiryHistory.php',
                                    method : 'POST',
                                    data : {
                                        enquiry_id : enquiry_id,
                                        lead_number : lead_number
                                    }
                                });

                                return history.then(function (success){

                                        return success.data; // returning response from server
                                });

                            }

                        },
                        controller : function ($scope, $route, history, $state, print_access_users){

                            $scope.history_data   = history;
                            $scope.print_acccess  = false;


                            /* Open print option only for admin access */
                            if(print_access_users.indexOf(parseInt($scope.currentUser.designation)) > -1 ){
                                $scope.print_acccess = true;
                            }

                            $scope.setPopupContent = function (content_data){
                                $scope.popup_content = content_data.data;

                                if(angular.isDefined(content_data.added_by)){
                                    $scope.note_added_by = content_data.added_by;
                                }

                            };

                            // On hide event of popup
                            $('#popup').on('hidden.bs.modal', function (e) {
                                $scope.popup_content = '';
                            });

                            /**
                             * Function to print history of enquiry 
                             * @returns {undefined}
                             */
                            $scope.print_history = function (el){

                                var id_selector = '#'+el;
                                $.print( id_selector );
                            };
                            /* function:end */

                        }
                    })

                    .state('view_lead_notes', {
                        templateUrl : baseUrl +  'partials/view_lead/lead_notes.html',
                        controller : 'notesCtrl',
                        resolve : {
                            enquiry_id : function ($route){
                                var route_params		= $route.current.params;
                                var enquiry_id			= route_params.enquiry_id;
                                return enquiry_id;
                            },

                            notesCount : function ($route, httpService){
                                var route_params		= $route.current.params;
                                var enquiry_id			= route_params.enquiry_id;

                                return httpService.makeRequest({
                                    url : baseUrl + 'apis/helper.php?method=getEnquiryNotesCount&params=enquiry_id:'+enquiry_id,
                                    method: 'GET'
                                }).then(function (success){
                                    return success.data;
                                });
                            }
                        }
                    })

                    .state('change-password',{
                        url : '/user_settings',
                        templateUrl : baseUrl +'partials/user_settings/change_password_screen.html',
                        controller : 'changePasswordCtrl',
                        resolve : {

                        }
                    })

                    .state('change-profile-photo', {
                        url : '/user_settings',
                        templateUrl : baseUrl + 'partials/user_settings/change_profile_photo.html',
                        controller : 'changeProfilePhotoCtrl',
                        resolve: {

                        }
                    })

                    .state('my_pref', {
                        template : '<p>Web notifications template</p>',
                        controller : function ($scope){alert($scope.$id);}
                    });

            // use the HTML5 History API
            $locationProvider.html5Mode ( true );

        } );

        // Application bootstrapping (Run) block
        app.run ( function ( $rootScope, $location, $http, baseUrl, Session ) {

            $rootScope.$on ( '$routeChangeStart', function ( event, next, current ) {

                // Authenticate user 
                var authPromise = $http.get(baseUrl + 'apis/getCurrentUser.php');
                // attach a loader to body 
                authPromise.then ( function ( success ) {

                    if ( parseInt ( success.data ) === 0 ) {
                        window.bmh_pusher = new pusherClient();
                        window.bmh_pusher.disconnectPusher();
                        Session.destroyUser (); // Remove user session data from session service 
                        $location.path ( '/' );
                    }
                    else {
                        // Stroing user session in client service 
                        Session.createUser ( success.data );

                        // remove loader 

                        // Broadcast event to spread out user session in application by Session Service 
                        $rootScope.$broadcast ( 'userSession', {user_session: success.data} );
                        
                         // create new pusher connection if not available
                        
                        if(!window.bmh_pusher){
                           window.bmh_pusher = new pusherClient();
                           window.bmh_pusher.getConnection();
                           // channel subscription
                           var user_id = success.data.id;    
                           window.bmh_pusher.subscribeToChannel('user-'+user_id);
                
                           // bind to event
                           window.bmh_pusher.bindToEvents();
                           window.bmh_pusher.trackConnectionState();
                        }
                        else{
                            
                        }
                        
                    }

                }, function ( error ) {
                    // if server is unavailable to respond or any server side issue is there then we have to prevent user to login in system 
                    window.bmh_pusher = new pusherClient();
                    window.bmh_pusher.disconnectPusher();
                    Session.destroyUser (); // Removing user session client side
                    $location.path ( '/' );
                } );
            } );

        } );
        /**
         * 
         * @class mainAppCtrl
         * @fileOverview Main application controller 
         */
        app.controller ('mainAppCtrl', function ( $scope, application_blocks, appUrls, appLayout, $location, AuthService, baseUrl, Session, $http, notify, $compile, $window, $interval, $rootScope ) {

            $scope.load_left_nav = true; // Default value 
            $scope.left_nav_template_url = application_blocks.left_sidebar_path;

            $scope.loadHeader = true; // Default value
            $scope.header_template_url = application_blocks.header_path;

            $scope.currentUser = {};

            $rootScope.$on ( 'userSession', function ( event, data ) {
                $scope.currentUser = data.user_session;
            });

            // user assigned modules 
            $scope.modules = null;

            //$scope.auth = AuthService.currentUser;

            // Make left/ right sidebar show or hide page wise 
            $scope.changeSidebarAppearence = function ( sidebar, value ) {

                switch ( sidebar ) {

                    case 'right':
                        $scope.load_right_nav = value;
                        break;

                    case'left':

                        $scope.load_left_nav = value;

                        if ( ! value ) { // For full width layout

                            $scope.changeAppLayout ( '100%', '0px', false );
                        }
                        else {
                            $scope.changeAppLayout ( '1119px', '230px', true );
                        }

                        break;
                }
            };

            // Change the appearance of application header 
            $scope.toggleApplicationHeader = function ( value ) {
                $scope.loadHeader = value;
            };

            // To change main content layout and top header layout
            $scope.changeAppLayout = function ( width, ml, nav_toggle_btn ) {

                angular.element ( '#content-layout' ).css ( {width: width, marginLeft: ml} );
                angular.element ( '#menu-header' ).css ( {width: width, marginLeft: ml} );

                if ( ! nav_toggle_btn ) {
                    angular.element ( '.left-nav-expander' ).hide ();
                }
                else {
                    angular.element ( '.left-nav-expander' ).show ();
                }
            };

            /**
             * Logout function 
             * @returns {undefined}
             */
            $scope.logout = function (user_id) {

                var logout = $http ( {
                    url: baseUrl + 'apis/logoutUser.php',
                    method: 'GET'
                } );

                logout.then ( function ( success ) {

                    if ( parseInt(success.data) === 1 ) {
                        $scope.stopPusher(user_id);
                        Session.destroyUser ();
                        $location.path ( '/' );
                    }
                }, function ( error ) {

                } );
            };

            // End: logout function


            /**
             * Function to open user setting 
             * @param {type} notification
             * @returns {undefined}
             */

            $scope.user_setting = function (){
                $location.path('user_settings');
            };

            /**
             * Notification Message function 
             */
            $scope.notify = function ( notification ) {

                $scope.notificationConfig = notification;
                $ ( 'body .notification-block' ).prepend ( $compile ( notify.template ) ( $scope ) );
            };

            /**End of function **/

            // Set Interval to check user session in 1 minute time interval
            $scope.user_session = $interval(function (){

                var user_session = Session.checkUserSession();

                user_session.then(function (success){

                    if(typeof success.data === 'object' && !Object.keys(success.data).length){
                        Session.destroyUser ();
                        $location.path('/');
                    }
                });

            }, 60000*3); // 1 minute interval of checking user session 


            /**
            Function to redirect to home page
            */
            $scope.goToHome = function (){
                $location.path('/');  
            };
        
            // get pusher current state
            $scope.getPusherState = function (){
                alert(bmh_pusher.getConnectionCurrentState());
            };
            
            // Function to start a new conenction to puhser client
            $scope.startPusher = function (user_id){
              
                // create connection 
                bmh_pusher = new pusherClient();
                bmh_pusher.getConnection();
                
				// We use Channel Prefix as domain name 
                var channel_prefix = window.location.host;
				
                // channel subscription
                bmh_pusher.subscribeToChannel(channel_prefix+'@'+user_id);
                
                // bind to event
                bmh_pusher.bindToEvents();
                
                bmh_pusher.trackConnectionState();
            };
            
            // Function to disconnect from puser client
            $scope.stopPusher = function (user_id){
                // If pusher connection already there then disconnect from pusher
                if(window.bmh_pusher){
                   window.bmh_pusher.disconnectPusher();
                }else{
                    // create connection and then disconnect
                    window.bmh_pusher = new pusherClient();
                    window.bmh_pusher.getConnection();
                    
					// We use Channel Prefix as domain name 
					var channel_prefix = window.location.host;
					
                    // disconnect from pusher
                    window.bmh_pusher.unbindEvents();
                    window.bmh_pusher.unsubscribeFromChannels(channel_prefix+'@'+user_id);
                    window.bmh_pusher.disconnectPusher();
                }
            }

            /**
             * Function to perform deep copy of an object (Object Cloning)
             */

            $scope.deepObjectCopy = function( original )  
            {
                // First create an empty object with
                // same prototype of our original source
                var clone = Object.create( Object.getPrototypeOf( original ) ) ;

                var i , descriptor , keys = Object.getOwnPropertyNames( original ) ;

                for ( i = 0 ; i < keys.length ; i ++ )
                {
                    // Save the source's descriptor
                    descriptor = Object.getOwnPropertyDescriptor( original , keys[ i ] ) ;

                    if ( descriptor.value && typeof descriptor.value === 'object' )        
                    {
                        // If the value is an object, recursively deepCopy() it
                        descriptor.value = naiveDeepCopy( descriptor.value ) ;
                    }

                    Object.defineProperty( clone , keys[ i ] , descriptor ) ;
                }

                return clone ;
            }

            /**
             * Function to find object's differences
             */

            $scope.findDifferences = function (objectA, objectB) 
            {
                var propertyChanges = [];
                var objectGraphPath = ["this"];
                (function(a, b) {

                    if(a.constructor == Array) {
                        // BIG assumptions here: That both arrays are same length, that
                        // the members of those arrays are _essentially_ the same, and 
                        // that those array members are in the same order...
                        for(var i = 0; i < a.length; i++) {
                            objectGraphPath.push("[" + i.toString() + "]");
                            arguments.callee(a[i], b[i]);
                            objectGraphPath.pop();
                        }
                    } else if(a.constructor == Object || (a.constructor != Number && 
                        a.constructor != String && a.constructor != Date && 
                        a.constructor != RegExp && a.constructor != Function &&
                        a.constructor != Boolean)) {
                                // we can safely assume that the objects have the 
                                // same property lists, else why compare them?
                                for(var property in a) {
                                    objectGraphPath.push(("." + property));
                                    if(a[property]){
                                        if(a[property].constructor != Function) {
                                            arguments.callee(a[property], b[property]);
                                        }
                                    }
                                    objectGraphPath.pop();
                                }
                        } else if(a.constructor != Function) { // filter out functions
                            if(a != b) {
                                propertyChanges.push({ "Property": objectGraphPath.join(""), "ObjectA": a, "ObjectB": b });
                            }
                        }
                        })(objectA, objectB);
                        return propertyChanges;
                }

        }); // End of controller function

        app.directive ( 'accountInfo', function ( $http, appUrls ) {
            var info = {};

            info.restrict = 'A';
            info.templateUrl = appUrls.appUrl + 'directives/template/settings_popup.html';
            info.scope = false;
            return info;
        } );

        // Auth service 
        app.factory ( 'AuthService', function ( $http, Session, baseUrl ) {

            var authentication = {};

            authentication.login = function ( credentials ) {
                return  $http ( {
                    url: baseUrl + 'apis/login.php',
                    method: 'POST',
                    data: credentials
                } );
            };

            authentication.isAuthenticated = function () {

                var user_session_data = Session.getUser ();

                if(user_session_data){
                    return true;
                }
                else{
                    return false;
                }
            };

            authentication.isAuthorized = function ( authorizedRoles ) {
                return ( this.isAuthenticated () &&
                        authorizedRoles.indexOf ( Session.user.role ) !== - 1 );
            };

            return authentication;

        } );

        // Session Service
        app.service ( 'Session', function ($http, $location, baseUrl) {

            var _user = {}; // Private variable 

            function isSessionExists(){

                return $http ( {
                    url: baseUrl + 'apis/getCurrentUser.php',
                    method: 'GET'
                } );
            };

            this.createUser = function ( user ) {
                _user = user;
                this.isSessionAvailable = true;
            };

            this.destroyUser = function () {
                _user = {};
                this.isSessionAvailable = false;
            };

            this.getUser = function () {
                return _user;
            };

            this.checkUserSession = function (){
                return isSessionExists();
            };

            this.isSessionAvailable = false;
        } );

        /**
         * Mapping Controller 
         */
        app.controller ( 'mapping', function ( $scope, utilityService, $routeParams, assigned_status, $location, $route, user_auth ) {

            $scope.groupName = $routeParams.group_name;
            $scope.groupId = $routeParams.group_id;

            $scope.assigned_status = [];

            assigned_status.status_ids ().then ( function ( response ) {
                $scope.assigned_status = response.data;
            } );

            $scope.statusItems = [];

            $scope.statusList = function () {

                var status_promise = utilityService.getDispositionStatusList ();

                status_promise.then ( function ( promise ) {
                    $scope.statusItems = promise.data;
                } );
            };

            $scope.statusList ();

            $scope.selectStatus = function ( event, status ) {

                var status_id = parseInt ( status.status_id );

                if ( angular.element ( event.currentTarget ).prop ( 'checked' ) === true ) {
                    $scope.assigned_status.push ( status_id );
                }
                else {
                    var index = $scope.assigned_status.indexOf ( status_id );
                    $scope.assigned_status.splice ( index, 1 );
                }
            };

            $scope.checkitem = function ( value ) {

                var checked = false;

                for ( var i = 0; i < $scope.assigned_status.length; i ++ ) {

                    if ( parseInt ( value ) === $scope.assigned_status[i] ) {
                        checked = true;
                    }
                }

                return checked;
            };

            /**
             * 
             * @returns {undefined}
             */
            $scope.save_checked_status = function ( checked_statuses ) {

                if ( checked_statuses.length <= 0 ) {
                    var ans = confirm ( 'You have not checked any of the status. Are you sure to continue with empty mapping?' );

                }

                var req_obj = {
                    group_id: $scope.groupId,
                    status_ids: checked_statuses
                };

                var promise = utilityService.mapDispositionGroupStatus ( req_obj );

                promise.then ( function ( response ) {

                    if ( response.data.success == 1 ) {
                        alert ( 'Status saved successfully' );
                    }
                    else {
                        alert ( 'Server error. Status could not be saved' );
                    }

                    $route.reload ();
                } );
            };
        } );


        /**
         * Custom directive of getting employee name
         */

        app.directive('employeeName', function (httpService){

                return  {
                    restrict : 'EA',
                    replace : false,
                    template : '{{lead_add_by_user}}',
                    scope : {
                        id :'@employeeId'
                    },
                    controller : function ($scope, httpService, baseUrl){
                        $scope.lead_add_by_user = '';
                        httpService.makeRequest({
                            url : baseUrl + 'apis/helper.php?method=getEmployeeNameById&params=employee_id:'+$scope.id,
                            method : 'GET'
                        }).then(function (data){
                            $scope.lead_add_by_user = data.data;
                        });
                    }
                };
            }); 


        /**
         * custom directive for geeting enquiry status text
         */

        app.directive('enquiryCurrentStatus', function (httpService){

                return  {
                    restrict : 'EA',
                    replace : false,
                    template : '{{enquiry_text}}',
                    scope : {
                        enquiry : '@enquiry'
                    },
                    controller : function ($scope, httpService, baseUrl){

                        $scope.enquiry_text = '';

                        httpService.makeRequest({
                            url : baseUrl + 'apis/helper.php?method=getEnquiryActionStatus&params=enquiry_id:'+$scope.enquiry,
                            method : 'GET'
                        }).then(function (data){

                            var sub_status_title = '';
                            if( data.data.data.sub_status_title){
                                sub_status_title = data.data.data.sub_status_title;
                            }

                            $scope.enquiry_text = data.data.data.status_title +' '+ sub_status_title;
                        });
                    }
                };
        });

        
        // Common directive to format date 
        app.directive('formatDt', function ($filter){
    
            return {
          
                restrict : 'A',
                scope : {
                    dt : '@',
                    format: '@'
                },
                transclude : true,
                
                link : function ($scope, tEle, tAttr){
                    
                    if($scope.format == ''){
                       $scope.format = 'mediumDate';
                    }
                    
					if($scope.dt.indexOf('PM') > -1 || $scope.dt.indexOf('AM') > -1){
						// Remove meridian(AM/PM) from string 
						$scope.dt = $scope.dt.slice(0, ($scope.dt.length - 2));						
					}
					
                    if($scope.dt == '0000-00-00' || !$scope.dt){
                        tEle.text('NA').css({color:'#F00'});
                    }else{
                        var formated_date = $filter('date')(new Date($scope.dt),$scope.format,'+0530');
                        tEle.text(formated_date);
                    }
                }
            };
        });

    } ) ( Pace, jQuery );