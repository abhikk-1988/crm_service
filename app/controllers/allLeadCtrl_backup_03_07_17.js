/**
 * 
 */

var app = app || {};

(function (app, $) {

    app.controller('allLeadCtrl', function ($scope, user_auth, httpService, $location,baseUrl) {

        $scope.leadsData = [];
        
        var leads_config = {
            url: baseUrl + 'apis/fetchLeads.php',
            method: 'GET'
        };

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

        var lead_response = httpService.makeRequest(leads_config);

        lead_response.then(function (response) {

            if (response.data.success == 1 && response.data.http_status_code == 200) {
                $scope.leadsData = response.data.data;
				
				console.log($scope.leadsData);
				
            } else if (response.data.http_status_codes == 401) {

                // User is not authorized
                // Redirect to login page 
                $location.path('/');
            }
        });

        $scope.modal = {size: 'sm', title: 'Projects'};
        $scope.view_projects = function (data) {
            $scope.client_enquiry_projects = data;
            $('.bd-example-modal-sm').modal('show'); // Opening modal
        };
    });

})(app, jQuery);