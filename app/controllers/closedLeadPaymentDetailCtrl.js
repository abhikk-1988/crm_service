/***************************************************************/
/* Controller File    : closedLeadPaymentDetailCtrl
/* Objective          : To show closed lead detail
/***************************************************************/
 
    var app = app || {};

    (function (app, $){
        
        app.controller('closedLeadPaymentDetailCtrl', function ($scope, user_session, $http, $routeParams, baseUrl, payment_details,$compile){
            
            $scope.user                 = user_session;
            
            $scope.payment_details      = payment_details;
            
            $scope.enquiry_id   = $routeParams.enquiry_id;
            
            $scope.lead_number  = '';
            
            if(angular.isDefined($routeParams.lead_number)){
                $scope.lead_number      = $routeParams.lead_number;    
            }
            
            $scope.downloadReceipt = function (file_url){
                
                var url = baseUrl + 'apis/download_file.php?file='+file_url;
                console.log(url);
                window.location = url;
            };
            
           
        });
        
        
        app.directive('formatDate', function ($filter){
            return {
            
                restrict : 'A',
                scope : {
                    date: '@'
                },
                transclude : true,
                
                link: function (scope, tElement, tAttr){
                      
                    if(scope.date){
                        
                        var timestamp = new Date(scope.date).getTime();
                        
                        var date = $filter('date')(timestamp,'dd-MMM-yyyy','+0530');
                        
                        tElement.html(date).css({textAlign: 'center',fontSize: '12px'});
                        
                    }else{
                        tElement.html('-').css({textAlign: 'center',color: 'red'});
                    }
                }
            };
            
        });
        
        app.directive('isCellValue', function ($filter){
            return {
            
                restrict : 'A',
                scope : {
                    cellValue: '@'
                },
                transclude : true,
                
                link: function (scope, tElement, tAttr){
                      
                    if(scope.cellValue){
                        
                        tElement.html(scope.cellValue).css({textAlign: 'center',fontSize: '12px'});
                        
                    }else{
                        tElement.html('-').css({textAlign: 'center',color: 'red'});
                    }
                }
            };
            
        });
    }(app,jQuery));
