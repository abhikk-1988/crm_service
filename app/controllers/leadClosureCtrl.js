/*
 * 
 */

var app = app || {};

(function (app, $){
	
	app.controller ('leadClosureCtrl', ['$scope','closure_mode','$routeParams','baseUrl','user_session', function ($scope,closure_mode,$routeParams, baseUrl, user_session){
			
		$scope.enquiry_number	= $routeParams.enquiry_number;
			
		$scope.closure_mode		= closure_mode;
		
		$scope.baseUrl			= baseUrl; 
		
		// logged in user session data
		$scope.login_user		= user_session;
	
		$scope.cheque = {
			number : '',
			bank_name : '',
			date : new Date(),
			amount : '',
			ac_number : '',
			ifsc_code : '',
			file : null,
			payment_type : 'cheque'
		};
		
		$scope.online_transaction = {
			amount : '',
			payment_mode : '',
			transaction_number : '',
			transaction_date : new Date(),
			file : null,
			payment_type : 'ot'
		};
			
		$scope.lead_close = {
			date : new Date(),
			remark : '',
			status_id : $routeParams.status_id,
			sub_status_id : $routeParams.sub_status_id 
		};	
		
		$scope.panel_heading = '';
		
		if($scope.closure_mode === 'cheque'){
			$scope.panel_heading = 'Cheque Detail';
		}
		
		if($scope.closure_mode === 'lead_close'){
			$scope.panel_heading = 'Closing Lead';
		}
		
		if($scope.closure_mode === 'online_transaction'){
			$scope.panel_heading = 'Online Transaction';
		}
		
		$scope.today  = function (){
			$scope.current_date = new Date(); 
		};
		
		$scope.open_datepicker = false;
		
		$scope.today();
		
		// Date options 
		$scope.dateOptions = {
			//dateDisabled: 'disabled',
			formatYear: 'yy',
			maxDate: new Date(2020, 5, 22),
			minDate: new Date(),
			startingDay: 1
		};
		
		$scope.formats = ['dd-MMMM-yyyy', 'yyyy/MM/dd', 'dd.MM.yyyy', 'shortDate','yyyy-MM-dd'];
		$scope.format = $scope.formats[4];
		
	}]);
	
	
	// Cheque Collection Controller 
	
	app.controller ('chequeCollectionCtrl', function ($scope, chequeCollectionService, $route, $compile, $filter,$routeParams){
		
		// Watch on amount 
		
		$scope.$watch('cheque.amount', function (val){
			
			if(	isNaN(val)){
				$scope.amount_error = 'Amount should be in numbers.';
				return false;
			}else{
				$scope.amount_error = '';
			}
		});
		
		$scope.$watch('cheque.bank_name', function (val){
			
			$scope.cheque.bank_name = $filter('uppercase')(val);
			
		});
	
		$('#cheque_datepicker').focusin (function (){
			$scope.open_datepicker = true;
			$scope.$apply();
		});
	
		$scope.saveChequeDetail = function (chequeData){
			
			chequeData.enquiry_number	= $scope.enquiry_number;
			chequeData.user_id              = $scope.login_user.id;
                        chequeData.status_id                          = $routeParams.status_id;
                        chequeData.sub_status_id                      = $routeParams.sub_status_id;
			var save_cheque                 = chequeCollectionService.saveChequeDetail(chequeData);
                        
			save_cheque.then(function (res){
				
				if( parseInt(res.data.success) === 1){
					$scope.notify({
						class : ['alert','alert-success','bottom-right'],
						message : res.data.message
					});
					
					$route.reload();
				}else{
					
					var error_list = '';
					error_list += '<ul>';
					for(var key in res.data.errors){	
						error_list += '<li>'+res.data.errors[key]+'</li>';
					}
					error_list += '</ul>';
					
					$compile(error_list)($scope);
					
					$scope.notify({
						class : ['alert','alert-warning','bottom-right'],
						message : error_list
					});
				}
			});
		};
	
	});
	
	
	// Online Transaction Controller 
	
	app.controller('onlineTransactionCtrl', function ($scope, chequeCollectionService, $route, $compile, $filter,$routeParams){
		
		$scope.$watch('online_transaction.amount', function (val){
			
			if(	isNaN(val)){
				$scope.ot_amount_error = 'Amount should be in numbers.';
				return false;
			}else{
				$scope.ot_amount_error = '';
			}
		});
		
		$('#transaction_datepicker').focusin (function (){
			$scope.open_datepicker = true;
			$scope.$apply();
		});
		
		$scope.saveTransactionDetail = function (data){
                
			data.enquiry_number			= $scope.enquiry_number;
			data.user_id				= $scope.login_user.id;
                        data.transaction_date                   = $filter('date')(data.transaction_date,'yyyy-MM-dd','+0530'); // modify date 
                        data.status_id                          = $routeParams.status_id;
                        data.sub_status_id                      = $routeParams.sub_status_id;
			var save_transaction                    = chequeCollectionService.saveTransactionDetail(data);
			
			save_transaction.then(function (res){
				
				if( parseInt(res.data.success) === 1){
					$scope.notify({
						class : ['alert','alert-success','bottom-right'],
						message : res.data.message
					});
					
					$route.reload();
				}else{
					
					var error_list = '';
					error_list += '<ul>';
					for(var key in res.data.errors){	
						error_list += '<li>'+res.data.errors[key]+'</li>';
					}
					error_list += '</ul>';
					
					$compile(error_list)($scope);
					
					$scope.notify({
						class : ['alert','alert-warning','bottom-right'],
						message : error_list
					});
				}
			});
		};
		
		
	});
	
	
	// Lead close controller 
	app.controller ('LeadCloseCtrl', function ($scope, chequeCollectionService, $route, $compile, $filter,$routeParams){
		
		$('#lead_close_datepicker').focusin (function (){
			$scope.open_datepicker = true;
			$scope.$apply();
		});
		
		$scope.closeLead =  function (data){
			
			data.user_id	= $scope.login_user.id;
			data.user_name	= $scope.login_user.firstname + ' ' + $scope.login_user.lastname;
			data.enquiry_id = $scope.enquiry_number;
			
                        data.status_id  = $routeParams.status_id;
                        data.sub_status_id  = $routeParams.sub_status_id;
                        
                        
			var lead_close =  chequeCollectionService.closeLead(data);
			
			lead_close.then(function (res){
				
                if( parseInt(res.data.success) === 1){
                    
                    $scope.notify({
                        class : ['alert','alert-danger','center-aligned'],
                        message: res.data.message
                    });
               
                    // redirect user to my leads page
                    $location.path('/my-leads');
                }
                else{
                    $scope.notify({
                        class : ['alert','alert-warning','bottom-right'],
                        message : res.data.error
                    });
                }
                
			});
		};

	});
}(app, jQuery));