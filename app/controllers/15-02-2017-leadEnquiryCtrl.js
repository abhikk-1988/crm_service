/**
 * Add Lead Controller
*/
var app = app || {};

(function (app,$){

	app.directive('ngFiles', ['$parse', function ($parse) {

            function fn_link(scope, element, attrs) {
                var onChange = $parse(attrs.ngFiles);
                element.on('change', function (event) {
                    onChange(scope, { $files: event.target.files });
                });
            };
			return {
                link: fn_link
            }
        } ])

	 app.controller('leadEnquiryCtrl', function ($scope,$location,$http,Session,utilityService,baseUrl,$interval,$filter,$window)
	 {
		$scope.page_record_limit 		=   10;
		$scope.current_page_number 		= 	1;
		$scope.baseUrl					=	baseUrl;
		$scope.leadCapture				=	[];
		$scope.enquiryData				=	[];

		$scope.read_captured_leads		=    function()
		{
		
		var  date_range =  $('#dateRangText').val();
		$scope.dateRangeTextShow		=	'';
		
		$http.post(baseUrl+"/apis/read_enquiry.php", {date_range:date_range})
			//$http.post(baseUrl+"/apis/read_enquiry.php")
			.then(function(response) {
				
				$scope.enquiryData     		=			response.data.enData;
				$scope.dateRangeTextShow	=	response.data.dateRange
			});
		}
		
		$scope.capture_lead   =  function()
		{
			$http.post(baseUrl+"/apis/syn_crm_enquiry.php")
			.then(function(response) {
			$scope.enquiryData = response.data.concat($scope.enquiryData);
		});
	
	}

	
	function helloCallMe(){
		
		$scope.capture_lead();
	}
	
	
	//$interval(helloCallMe, 30000);
	
	
	$scope.read_captured_leads();
	//import Query CSV..
	$scope.import_csv					=	function(){}
    $scope.queryCount					=	0;
	
	$scope.prepare_push_ivr = function(){
		
		var cList = [];
		angular.forEach($scope.enquiryData,function(value,key){
		
			if(value.checked){
				
				cList.push({query_request_id:value.query_request_id,name:value.name,phone:value.phone,email:value.email});
			
			}
		
		});
		
		$scope.prepare_ivr		=   cList;
		$scope.queryCount		=	cList.length;
	
	};
	
	$scope.check_all_ivr = function(){
		
		if($scope.check_all)
		{
			/*
			var cList = [];
			angular.forEach($scope.enquiryData,function(value,key){
				cList.push({query_request_id:value.query_request_id,name:value.name,phone:value.phone,email:value.email});
			});
			$scope.prepare_ivr		=   cList;
			$scope.queryCount		=	cList.length;
			*/
		
		}else{
			
			var cList 			= 	[];
			$scope.queryCount	=	0;
		}
		
	
 };
	
	//push  to IVR.... 
	$scope.push_to_ivr			=	function()
	{
		
		if($scope.prepare_ivr.length>0){
		
			//$(window).trigger('click');
			$http.post(baseUrl+"/apis/manual_push_ivr.php", {mydata:$scope.prepare_ivr})
				.then(function(response) {
					
					if(response.data.action=='success'){
						
						$scope.read_captured_leads();
						var cList 				= 	[];
						$scope.queryCount		=	0;
						//$scope.prepare_ivr		=	[];
						//$('#selected_queries').hide();
						$('#popover_item').popover('hide');
					}
				});
		
			}
	}
	//End of push to IVR... 
	
	$scope.push_into_ivr		=	function(){
	}

	$scope.query_delete			=	function($qid){
		//alert("=="+$qid);
		//return false;
	}

	 $scope.removefromchlist		=	function(item){
		
		var index 			=	 	$scope.prepare_ivr.indexOf(item);
		$scope.prepare_ivr	=		$scope.prepare_ivr.splice(index, 1);   
		$scope.queryCount		=	$scope.prepare_ivr.length;
		//alert($scope.queryCount);
	}

	
	$scope.csvData 			=	[];
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
			url: baseUrl+"/apis/uploadFile_lms.php",
			data: formdata,
			headers: {
				'Content-Type': undefined
			}
		};
		// SEND THE FILES.
		$http(request)
			.success(function (data) {
				
				if(data.action=='success'){
					
					$scope.totalCSVData		=	data;
					$('#csvImportModal').modal('show');
				
				}else{
				
				
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
	
	
	$scope.ViewQuery			=	function($data){
		
		$scope.QueryDetailData	=	$data;
		$('#classModal').modal('show');
	}

	// Reload list on  close  of pop up.
	$scope.CloseModal		=	function()
	{
		$('#csvImportModal').modal('hide');
		$scope.read_captured_leads();
	}
	
	$scope.export_csv		=	function(){
	
		$http.post(baseUrl+"/apis/export_csv.php")
			.then(function(response) {
		});
	}
	
	//import csv formate ...	
	 $scope.import_format_csv		=	function(){
		
		var csv_formate				=	baseUrl+'queryupload/query_report.csv';
		$window.location = csv_formate;
	
	}
	//Import csv formate.
	
	$scope.changeDD=function(){
		
		var  date_range =  $('#dateRangText').val();
		
		$http.post(baseUrl+"/apis/read_enquiry.php", {date_range:date_range})
			.then(function(response) {
				
				$scope.enquiryData     		=			response.data.enData;
				$scope.dateRangeTextShow	=			response.data.dateRange
				
		});
	
	}
	
	
	
	$scope.assign_agent						=	function($query){
		
		$scope.QueryData 					=	$query;	
		
		$('#assignAgent').modal('show');
	
	}
	

});
	
})(app,jQuery);

