/**
 * Custom application service
 * 
 * Service name: chequeCollectionService
 */


var app = app || {};

(function (app, $){
	
	
	app.service('chequeCollectionService', function ($http, baseUrl, $filter){
		
		this.saveChequeDetail = function (data){
		  
			var fd = new FormData();

			// convet date to mysql format 
			data.date = $filter('date')(data.date , 'shortDate', '+0530');
			
			for(var key in data){
				fd.append(key, data[key]);
			}
			
			var post_url = baseUrl + 'apis/save_cheque_detail.php';
			
			return $http.post(post_url , fd , {
				transformRequest : angular.identity,
				headers : {
					'Content-Type' : undefined
				}
			});
		};
		
		this.saveTransactionDetail = function (data){
			
			var fd = new FormData();
			
			// convet date to mysql format 
			data.date = $filter('date')(data.date , 'shortDate', '+0530');
			
			for(var key in data){
				fd.append(key, data[key]);
			}
			
			var post_url = baseUrl + 'apis/save_transaction_detail.php';
			
			return $http.post(post_url , fd , {
				transformRequest : angular.identity,
				headers : {
					'Content-Type' : undefined
				}
			});
		};
		
		this.closeLead = function (data){
			
			// convet date to mysql format 
			data.date = $filter('date')(data.date , 'shortDate', '+0530');
			var url		= baseUrl + 'apis/close_lead.php';
			var config = {
                headers : {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
                }
            };
			
			return $http.post(url, $.param(data) , config);
		};
	});
	
	
} (app, jQuery));