/**
 * Add Lead Controller
*/

var app = app || {};




(function (app){


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

 app.controller('leadEnquiryCtrl', function ($scope,$location,$http,Session,utilityService,baseUrl,$interval,$filter)
 {
	
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
					
					$scope.csvData 		=	data.csvData;
					
					
					}else{
				
				}
			
			})
			.error(function () {
		});
	}

	
		$scope.leadCapture				=	[];
		$scope.enquiryData				=	[];

		$scope.read_captured_leads		=    function()
		{
			$http.post(baseUrl+"/apis/read_enquiry.php")
			.then(function(response) {
				
				$scope.enquiryData     		=			response.data;
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
	
	$interval(helloCallMe, 30000);
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
		//$(window).trigger('click');
		$http.post(baseUrl+"/apis/manual_push_ivr.php", {mydata:$scope.prepare_ivr})
			.then(function(response) {
		
				if(response.data.action=='success'){
					$scope.read_captured_leads();
					var cList 			= 	[];
					$scope.queryCount	=	0;
					//$('#selected_queries').hide();
				}
		 });
	}
	//End of push to IVR... 

	
	
	
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

	//Uploading the document.
	$scope.import_csv		=	function(){
		
		alert("Coming Soon CSV Import");
	}

	

});
	
})(app);

