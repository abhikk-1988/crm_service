/**
 * 
 */

var app = app || {};

(function (app, $) {

    app.controller('allLeadCtrl', function ($scope,$sce, user_auth, httpService, $location, baseUrl, asm_users,$http,disposition_status_list, $filter) {
        
        $scope.page_limit; // default page limit

        $scope.leadsData = [];

        // Ares Sales Managers 
        $scope.area_sales_managers = asm_users;
		$scope.recordings = null;
		$scope.trustSrc = function(src) {
        return $sce.trustAsResourceUrl(src);
        }
		$scope.stopAllAudio = function(src){
        var sounds = document.getElementsByTagName('audio');
        for(i=0; i<sounds.length; i++) sounds[i].pause();
        }
	  /*
     * getting recordings by sudhanshu
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
				status_title : 'FU',
				sub_status_title : ''
			});

			$scope.primary_enquiry_status_list.push({
				id: '10',
				parent_status: 4,
				parent_status_title : 'Future References',
				status_title : 'CB',
				sub_status_title : ''
			});
        }

        // pagination data 
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
        // End pagination

        var leads_config = {
            
            method: 'GET'
        };

        $scope.getAllLeads = function () {

            
            if(!angular.isDefined($scope.lead_creation_date_filter)){
                $scope.lead_creation_date_filter = '';
            }

            if(!angular.isDefined($scope.lead_updation_date_filter)){
                $scope.lead_updation_date_filter = '';
            }
            
            
            var lead_response = httpService.makeRequest({
                url: baseUrl + 'apis/fetchLeads.php?filter_lead_status='+$scope.enquiry_filter_status + '&create_date_filter='+$scope.lead_creation_date_filter+'&update_date_filter='+$scope.lead_updation_date_filter,
                method: 'GET'
            });
            lead_response.then(function (response) {
                if (response.data.success == 1 && response.data.http_status_code == 200) {
                    $scope.leadsData = response.data.data;
                } else if (response.data.http_status_codes == 401) {

                    // User is not authorized
                    // Redirect to login page 
                    $location.path('/');
                }
            });
        };

        $scope.getAllLeads();

        $scope.modal = { size: 'sm', title: 'Projects' };
        $scope.view_projects = function (data) {
            $scope.client_enquiry_projects = data;
            $('.bd-example-modal-sm').modal('show'); // Opening modal
        };

        /**
          * Function to popup asm list for lead assignment
         /**/

        $scope.popUpAsmList = function (enquiry_id, category) {

            $scope.enquiry_id_for_asm_assignment = enquiry_id;
            $scope.lead_category_for_asm_assignment = category;

            // open popup of asm users list 
            $('#asm_users_list_popup').modal('show');
        };

        /**
		 * Function to assign lead to area sales managers 
		 * @param <object> lead_data
		 * @param <object> dom element
		 * @returns <bool>
		 */

        $scope.manualLeadAssignToAsm = function (dom_element, enquiry_id, category, asm_id) {

            $scope.enquiry_id_for_asm_assignment = enquiry_id;
            $scope.lead_category_for_asm_assignment = '';

            // Start button animation 
            var button_innerHTML = dom_element.target.innerHTML; // existing button html
            dom_element.target.innerHTML = 'Assigning <i class="fa fa-spinner faa-spin animated"></i>';
            dom_element.target.disabled = true;

            var http_call_data = {
                enquiry_id: enquiry_id,
                asm_id: asm_id
            };

            var assign_lead_config = {
                url: baseUrl + 'apis/manual_lead_assign_to_asm.php',
                method: 'POST',
                data: $.param(http_call_data),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8' }
            };

            $http(assign_lead_config).then(function (success) {

                if (Number(success.data.success) === 1) {

                    showToast(success.data.message, success.data.title, 'success');
                    dom_element.target.disabled = false;
                    dom_element.target.innerHTML = button_innerHTML;
                    $('#asm_users_list_popup').modal('hide'); // hide asm popup modal
                    $scope.getAllLeads(); // get updated leads     

                } else {
                    // restore original button text 
                    dom_element.target.disabled = false;
                    dom_element.target.innerHTML = button_innerHTML;
                    showToast(success.data.message, success.data.title, 'error');
                }
            }, function (error) {
                dom_element.target.innerHTML = button_innerHTML;
                dom_element.target.disabled = false;
            });

        };

        /**
         * Function to check dispisition status
         */

         $scope.isMeeting = function (status_id){

            var is_meeting = [3,6].find(function (element){

                if(element == status_id){
                    return true;
                }
            });

            if(is_meeting >= 0){
                return true;
            }else{
                return false;
            }
         };

        /*
		 * @function: To Change Page size dynamically
		 * 
		 */
		$scope.changePageSize = function (page_size){

                if( 'all' === $filter('lowercase')(page_size)){
					$scope.pagination.page_size = $scope.leadsData.length;
				}
				else if (typeof page_size === 'object' && !page_size){
					$scope.pagination.page_size = 10; // default value of page size
				}else{
					$scope.pagination.page_size = page_size;
				}
		};


        $scope.resetDateFilters = function (){
            $scope.lead_creation_date_filter = '';

            $scope.lead_updation_date_filter = '';

            $scope.getAllLeads();
        }
    });

})(app, jQuery);
