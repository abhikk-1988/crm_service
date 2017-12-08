/**
 * @author Abhishek agrawal
 * @fileOverview Validation service using regex pattern
 */

var app  = app || {};

(function (app){
    
    app.service('validationService',function ($http, baseUrl){
        
        // Email pattern matching 
        this.email = function (val){
          
            var regex = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,24})+$/;
            if(regex.test(val)){
                return true;
            }else{
                return false;
            }
        };
        
        // Alphanumeric string matching
        this.isStringContainAlphaChar = function ( str ){
                
                if(str === null){
                        return false;
                }
                
                if (str.match(/[a-z]/i)) {
                    // alphabet letters found
                    return false;
                }else{
                    return true;
                }        
        };
        
        // Getting email availibility from server
        this.checkEmailAvailibility = function (email_id){
            
            if(!email_id || email_id === ''){
                return false;
            }
            
            var url = baseUrl + 'apis/helper.php?method=isEmailExists&params=email:'+email_id;
            
            return $http.get(url); // return promise object
        }
        
        
        // Getting mobile number availibility from server
        this.checkMobileNumberAvailibility = function (mobile_number){
            
            if(!mobile_number || mobile_number === ''){
                return false;
            }
            
            var url = baseUrl + 'apis/helper.php?method=isEmployeeMobileNumberExists&params=mobile_number:'+ mobile_number;
            
            return $http.get(url); // return promise object
        }
        
    });
    
})(app);