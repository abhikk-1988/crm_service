/**
 * Add Lead Controller
 */
var app = app || {};

(function (app, $) {

    app.directive('ngFiles', ['$parse', function ($parse) {

            function fn_link(scope, element, attrs) {
                var onChange = $parse(attrs.ngFiles);
                element.on('change', function (event) {
                    onChange(scope, {$files: event.target.files});
                });
            }
            return {
                link: fn_link
            };
        }]);

    app.controller('leadEnquiryCtrl', function ($scope, $location, user_session, $http, Session, utilityService, baseUrl, $interval, $filter, $window, $compile)
    {

        $scope.page_record_limit = 10;
        $scope.current_page_number = 1;
        $scope.baseUrl = baseUrl;
        $scope.leadCapture = [];
        $scope.enquiryData = [];
        $scope.currentUser = user_session;
        $scope.agentShow1 = true;
        $scope.ptypes = 'agent';
        $scope.selectedAgent = 0;

        // Prevent SEO User to view information

        $scope.view_information = true;

        if (parseInt($scope.currentUser.designation) == 40) {
            $scope.view_information = false;
        }


        /*
         * author: @sudhanshu
         * added for filters
         */
        $scope.appliedFilters = {};
        $scope.selectedFilterType = 'today';
        $scope.filters = {};
        $scope.filters.source = [];
        $scope.filters.agents = [];
        $scope.filters.dispositions = [];

        $http.get(baseUrl + "apis/get_lead_enquiry_filters.php")
                .then(function (response) {
                    $scope.filters = response.data;
                });

        $scope.applyFilter = function () {

//            var date_range = $scope.dateRangeLead;

            $http.post(baseUrl + "/apis/read_enquiry.php", {
                date_range: $scope.dateRangeLead,
                filterType: $scope.selectedFilterType,
                filters: $scope.appliedFilters
            }).then(function (response) {
                $scope.enquiryData = response.data.enData;
                $scope.dateRangeTextShow = response.data.dateRange
                $scope.rcount = response.data.count;

            });
        }

        // @sudhanshu filter ended here


        /**
         * Pagination Model 
         */

        $scope.pagination = {
            current_page: 1,
            pagination_size: 4,
            page_size: 10,
            show_boundary_links: true,
            total_page: 0,
            changePage: function (page) {
                this.current_page = page;
            }
        };


        $scope.dynamicPopover = {
            content: 'Select Option',
            templateUrl: 'myPopoverTemplate.html',
            title: 'Filter By Date',
            placement: 'bottom'

        };


        // CSV Export button model
        $scope.csv_export = {};

        $scope.$watch('csv_export.start_date', function (val) {

            if (val != '') {
                $scope.csv_export.err = '';
            }
        });

        $scope.$watch('csv_export.end_date', function (val) {

            if (val != '') {
                $scope.csv_export.err = '';
            }
        });

        $scope.exportCsv = function () {

            if (typeof $scope.csv_export.start_date != 'undefined' && typeof $scope.csv_export.end_date != 'undefined') {

                // validate date input 

                var p1 = $http.get(baseUrl + 'apis/helper.php?method=validateDate&params=d1:' + $scope.csv_export.start_date + '/d2:' + $scope.csv_export.end_date);

                p1.then(function (s) {

                    if (s.data && parseInt(s.data.success) === 0) {
                        $scope.csv_export.err = s.data.error;
                    } else {

                        $scope.csv_export.err = '';
                        downloadCSV('range', s.data.start_date, s.data.end_date);
                    }

                });

            } else {
                $scope.csv_export.err = 'Please select start date and end date';
            }
        };

        // function to download CSV
        function downloadCSV(type, d1, d2) {

            if (type == 'all') {

                var anchor = angular.element('<a/>');
                anchor.attr({
                    href: baseUrl + 'apis/export_csv.php',
                    target: '_blank'
                })[0].click();

            } else {

                var anchor = angular.element('<a/>');
                anchor.attr({
                    href: baseUrl + 'apis/export_csv.php?start_date=' + d1 + '&end_date=' + d2,
                    target: '_blank'
                })[0].click();

                $scope.csv_export.start_date = null;
                $scope.csv_export.end_date = null;
                $scope.csv_export.option = null;
                angular.element('#download_csv_btn').click();
            }
        }

        // On change handler to download all csv data 
        $scope.downloadAllCsvRecord = function () {
            downloadCSV('all');
        };

        $http.get(baseUrl + "apis/helper.php?method=getCRMUsersByDesignation&params=designation_slug:agent").then(function (response) {
            $scope.agentData = response.data.data;
        });

        $scope.read_captured_leads = function ()
        {

            var date_range = $('#dateRangText').val();
            $scope.dateRangeTextShow = '';

            $http.post(baseUrl + "/apis/read_enquiry.php", {date_range: date_range})
                    .then(function (response) {
                        $scope.enquiryData = response.data.enData;
                        $scope.dateRangeTextShow = response.data.dateRange;
                        $scope.rcount = response.data.count;
                    });
        }

        $scope.capture_lead = function ()
        {
            $http.post(baseUrl + "/apis/syn_crm_enquiry.php")
                    .then(function (response) {

                    
                        /**
                         *  if any filter is applied then should not concat new sync data
                         */

                        if (typeof $scope.dateRangeLead === 'undefined' &&
                                $scope.selectedFilterType === 'today' &&
                                (Object.keys($scope.appliedFilters).length <= 0 || $scope.appliedFilters.source === 'All')) {

                                $scope.enquiryData = response.data.concat($scope.enquiryData);
                        } else {
                            
                        }

                    });
        }

        function helloCallMe() {
            $scope.capture_lead();
        }

        $interval(helloCallMe, 30000);

        $scope.read_captured_leads();

        //import Query CSV..
        $scope.import_csv = function () {}
        $scope.queryCount = 0;

        $scope.prepare_push_ivr = function () {

            var cList = [];
            angular.forEach($scope.enquiryData, function (value, key) {

                if (value.checked) {

                    cList.push({query_request_id: value.query_request_id, name: value.name, phone: value.phone, email: value.email, status_dis: value.status_dis});

                }

            });

            $scope.prepare_ivr = cList;
            $scope.queryCount = cList.length;

        };

        $scope.check_all_ivr = function () {

            var flag = false;
            if ($scope.check_all)
            {

                flag = true;
            }

            var cList = [];
            angular.forEach($scope.enquiryData, function (row, key) {

                cList.push({query_request_id: row.query_request_id, name: row.name, phone: row.phone, email: row.email, status_dis: row.status_dis});
                row.checked = flag;
            });




            if (flag) {

                $scope.prepare_ivr = cList;
                $scope.queryCount = cList.length;

            } else {

                $scope.prepare_ivr = [];
                $scope.queryCount = 0;

            }


        };


        $scope.remove_selected = function ($index, $va) {
            $scope.prepare_ivr.splice($index, $va);
            $scope.prepare_push_ivr();
        }



        //push  to IVR.... 
        $scope.push_to_ivr = function ()
        {
            if ($scope.ptypes == 'agent') {

                if ($scope.selectedAgent == 0) {

                    $scope.myMessage = "Please select agent.";

                    return false;

                } else {

                    $scope.myMessage = "";
                }

            } else {

                $scope.agentShow = false;
            }

            if ($scope.prepare_ivr.length > 0) {

                $http.post(baseUrl + "/apis/manual_push_ivr.php", 
                {
                    mydata: $scope.prepare_ivr, 
                    agent_id: $scope.selectedAgent, 
                    ptype: $scope.ptypes, 
                    assigned_by: $scope.currentUser.id
                }).then(function (response) {

                            if (response.data.action == 'success') {

                                $scope.read_captured_leads();
                                var cList = [];
                                $scope.queryCount = 0;
                                $('#popover_item').popover('hide');
                                $scope.myMessage = response.data.message;
                            }

                        });
            }

        }
        //End of push to IVR... 

        $scope.det_chooser = function ($type)
        {

            if ($type == 'agent') {

                $scope.agentShow1 = true;

            } else {

                $scope.agentShow1 = false;
            }
        }


        $scope.updateValue = function ($agent) {
            $scope.selectedAgent = $agent.id;
        }


        $scope.push_into_ivr = function () {
        }

        $scope.query_delete = function ($qid) {
            //alert("=="+$qid);
            //return false;
        }

        $scope.removefromchlist = function (item) {

            var index = $scope.prepare_ivr.indexOf(item);
            $scope.prepare_ivr = $scope.prepare_ivr.splice(index, 1);
            $scope.queryCount = $scope.prepare_ivr.length;
            //alert($scope.queryCount);
        }

        $scope.csvData = [];
        var formdata = new FormData();

        $scope.getTheFiles = function ($files) {

            angular.forEach($files, function (value, key) {
                formdata.append(key, value);
            });

            $scope.uploadFiles();
        };

        // NOW UPLOAD THE FILES.
        $scope.uploadFiles = function () {

            var request = {
                method: 'POST',
                url: baseUrl + "/apis/uploadFile_lms.php",
                data: formdata,
                headers: {
                    'Content-Type': undefined
                }
            };

            // SEND THE FILES.
            $http(request)
                    .success(function (data) {

                        if (data.action == 'success') {

                            $scope.totalCSVData = data;
                            $('#csvImportModal').modal('show');

                        } else {


                        }
                    })
                    .error(function () {

                    });

        }

        //////////// End  of  Import section /////////
        // Create page offset on change of page change 
        $scope.pageChange = function (page) {
            $scope.offset = $scope.page_record_limit * (parseInt(page) - 1);
        };


        $scope.ViewQuery = function ($data) {

            $scope.QueryDetailData = $data;
            $('#classModal').modal('show');
        }

        // Reload list on  close  of pop up.
        $scope.CloseModal = function ()
        {
            $('#csvImportModal').modal('hide');
            $scope.read_captured_leads();
        }

        $scope.export_csv = function () {

            $http.post(baseUrl + "/apis/export_csv.php")
                    .then(function (response) {
                    });
        }

        //import csv formate ...	
        $scope.import_format_csv = function () {

            var csv_formate = baseUrl + 'queryupload/query_report.csv';
            $window.location = csv_formate;

        }

        //Import csv formate.

        $scope.changeDD = function () {

            $http.post(baseUrl + "/apis/read_enquiry.php", {
                date_range: $scope.dateRangeLead,
                filters: $scope.appliedFilters
            }).then(function (response) {
                $scope.enquiryData = response.data.enData;
                $scope.dateRangeTextShow = response.data.dateRange;
                $scope.rcount = response.data.count;

            });
        }

        // custom filter.. 
        $scope.get_custom_search = function ($filterType) {


            /* Author: @sudhanshu code strated
             * to reset the applied fitler and keep on
             * track of the applied filterType
             */
//            $scope.appliedFilters = {};

            $scope.selectedFilterType = $filterType;
            // @sudhanshu code ended

            /**
             * Remove dateRangeLead filter
             * code added by Abhishek on 11th December 2017
             */

            $scope.dateRangeLead = '';

            /* code end */

//            var date_range = $('#dateRangText').val();

            $http.post(baseUrl + "/apis/read_enquiry.php", {
                date_range: $scope.dateRangeLead,
                'filterType': $filterType,
                'filters': $scope.appliedFilters
            }).then(function (response) {

                $scope.enquiryData = response.data.enData;
                $scope.dateRangeTextShow = response.data.dateRange
                $scope.rcount = response.data.count;

            });
        }

        //End of the custom filter..


        $scope.assign_agent = function ($query) {
            
            $scope.assignmentMessage    = '';
            $scope.assignmentAction     = ''
            $scope.QueryData            = $query;	//Query Data..
            $scope.agent_assign_status  = $query.agent_assign_status;

            $scope.selecteUserDetail = '';

            if ($query.agent_assign_status == '1') {

                $scope.selectedUser = {'id': $query.agent_id, 'firstname': $query.agent_firstname, 'lastname': $query.agent_lastname, 'email': $query.agent_email, 'contactNumber': $query.agent_contactNumber};

            } else {

                $scope.selectedUser = '';
            }

            $('#assignAgent').modal('show');

        }

        $scope.mark_assign = function ($user) {
            $scope.assignmentMessage = '';
            $scope.assignmentAction = ''
            $scope.selecteUserDetail = $user;
        }


        $scope.save_assignment = function ($userID, $enqueryID)
        {

            var login_user_id = $scope.currentUser.id;
            $http.post(baseUrl + "apis/assign_enquery_to_agent.php", {'enqueryID': $enqueryID, 'userID': $userID, 'assign_by': login_user_id})
                    .then(function (response) {

                        if (response.data.action != '') {

                            $scope.assignmentMessage = response.data.message;
                            $scope.assignmentAction = response.data.action;
                        }

                        //Reload the lead list.. 

                        if (response.data.action == 'success') {

                            $scope.read_captured_leads();
                            $scope.agent_assign_status = response.data.agent_assign_status;
                            $scope.selectedUser = $scope.selecteUserDetail;
                        }

                    });

        }


        $scope.remove_assignment = function ($userID, $enqueryID) {

            var login_user_id = $scope.currentUser.id;

            $http.post(baseUrl + "apis/assign_enquery_to_agent.php", {'enqueryID': $enqueryID, 'userID': $userID, 'assign_by': login_user_id})
                    .then(function (response) {

                        if (response.data.action != '') {

                            $scope.assignmentMessage = response.data.message;
                            $scope.assignmentAction = response.data.action;
                        }

                        if (response.data.action == 'success') {

                            $scope.read_captured_leads();
                            $scope.agent_assign_status = response.data.agent_assign_status;

                            if ($scope.agent_assign_status == '1') {

                                $scope.selectedUser = $scope.selecteUserDetail;

                            } else {

                                $scope.selectedUser = '';
                                $scope.selecteUserDetail = '';
                            }
                        }
                    });
        }

        $scope.get_duplicate_enquiry = function (ph) {

            var promise = $http.get(baseUrl + 'apis/get_duplicate_enquiry.php?phone=' + ph);

            $scope.duplicate_enquiries = [];

            promise.then(function (r) {

                if (r.data.length > 0) {
                    $scope.duplicate_enquiries = r.data;

                    var modal_markup = `<div class="modal fade bd-example-modal-lg" id="duplicate" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

        <div class="modal-header">
            <h5 class="modal-title">Duplicate enquiry of ${ph} </h5>
        </div>
        <div class="modal-body">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-striped">
                            <tr>
                                    <th>SIN</th>
                                    <th>Name</th><th>Phone</th><th>Email</th><th>Created On</th><th>Source</th>
                            </tr>
                            <tr ng-repeat="item in duplicate_enquiries">
                                <td>{{item.query_request_id}}</td>
                                <td>{{item.name}}</td>
                                <td>{{item.phone}}</td>
                                <td>{{item.email}}</td>
                                <td>{{item.created_on}}</td>
                                <td>{{item.enquiry_from}}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>        
    </div>
  </div>
</div>`;

                    var compiled_modal_markup = $compile(modal_markup)($scope);

                    angular.element('body').append(compiled_modal_markup);

                    angular.element('#duplicate').modal('show');


                } else {
                    alert('No Duplicate found');
                }

            }, function (e) {
                alert('No Duplicate found');
            });
        };

        // Removing appended bootstrap modal for showing duplicates enquiries on a number
        angular.element(document).on('hidden.bs.modal', '#duplicate', function () {
            $(this).remove();
        });
    });

})(app, jQuery);

