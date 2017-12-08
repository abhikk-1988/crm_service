/***************************************************************/
/* Controller File    : ClosedLeadDetailCtrl
/* Objective          : To show closed lead detail
/***************************************************************/
 
    var app = app || {};

    (function (app, $){
        
        app.controller('closedLeadDetailCtrl', function ($scope, user_session){
            
            $scope.user                 = user_session;
            
            $scope.transaction_details  = {};
            
            $scope.cheque_details       = {};
            
            // To download cheque receipt 
            $scope.downloadChequeReceipt = function (file_path){
                
            };
            
            // To download transaction receipt
            $scope.downloadTransactionReceipt = function (file_path){
                
            };
        });
        
    }(app,jQuery));
