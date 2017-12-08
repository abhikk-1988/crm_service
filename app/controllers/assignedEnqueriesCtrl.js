/**
 * Add Lead Controller
 */
var app = app || {};

(function (app, $) {


    app.controller('assignedEnqueriesCtrl', function ($scope, $location, user_session, $http, Session, utilityService, baseUrl, $interval, $filter, $window)
    {


        $scope.page_record_limit = 10;
        $scope.current_page_number = 1;
        $scope.baseUrl = baseUrl;
        $scope.leadCapture = [];
        $scope.enquiryData = [];
        $scope.currentUser = user_session;


        $scope.read_captured_leads = function ()
        {

            var date_range = $('#dateRangText').val();
            $scope.dateRangeTextShow = '';


            $http.post(baseUrl + "/apis/read_assigned_enqueries.php", {date_range: date_range, userID: user_session.id, 'designation_slug': user_session.designation_slug})
                    .then(function (response) {
                        $scope.enquiryData = response.data.enData;
                        $scope.dateRangeTextShow = response.data.dateRange
                    });
        }

        $scope.read_captured_leads();

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

            var date_range = $('#dateRangText').val();

            $http.post(baseUrl + "/apis/read_assigned_enqueries.php", {date_range: date_range, userID: user_session.id, 'designation_slug': user_session.designation_slug})
                    .then(function (response) {

                        $scope.enquiryData = response.data.enData;
                        $scope.dateRangeTextShow = response.data.dateRange

                    });

        }




        $scope.assign_agent = function ($query) {

            $scope.assignmentMessage = '';
            $scope.assignmentAction = ''
            $scope.QueryData = $query;	//Query Data..
            $scope.agent_assign_status = $query.agent_assign_status;

            $scope.selecteUserDetail = '';

            if ($query.agent_assign_status == '1') {

                $scope.selectedUser = {'id': $query.agent_id, 'firstname': $query.agent_firstname, 'lastname': $query.agent_lastname, 'email': $query.agent_email, 'contactNumber': $query.agent_contactNumber};

            } else {

                $scope.selectedUser = '';
            }

            $('#assignAgent').modal('show');

        };


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
        };

        $scope.call = function (number) {
            location.href = "add-lead?event-data=|" + number;
        };
    });

})(app, jQuery);

