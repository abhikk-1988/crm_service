/**
 * Custom service for leads module 
 */

var app = app || {};

(function (app){
	
	app.service('leadsService', ['$http','baseUrl', function ($http,baseUrl){
			
		this.getClosedLeads = function (employee_id){	
			var url = baseUrl + 'apis/get_closed_leads.php?user_id='+ employee_id;
			return $http.get(url);
		};
	
		this.getLeadStatus = function (enquiry_id){
			var post_url = baseUrl + 'apis/get_single_lead.php';
			return $http.post(post_url, {enquiry_id : enquiry_id});
		};	
		
		this.getLeadMeetingId = function (enquiry_id, type){
			
			if(typeof enquiry_id === 'undefined'){
				return '';
			}
			
			return $http.get(baseUrl+'apis/helper.php?method=getLeadMeetingID&params=enquiry_id:'+enquiry_id+'/id_type:'+type);
			
		};
        
        // To get enquiry status as Hot, Warm, Cold
        this.getEnquiryStatus = function (enquiry_number){
        
            if(!enquiry_number){
                return '';
            }
            
            return $http.get(baseUrl+'apis/helper.php?method=getEnquiryHWCStatus&params=enquiry_id:'+enquiry_number);
        }
        
        
	}]);
	
} (app));