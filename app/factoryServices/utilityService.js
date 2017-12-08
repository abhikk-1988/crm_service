
/**
 * Custom Factory service : Utility service
 * @author Abhishek Agrawal
 * @version 1.0 
 */

var app = app || {};

(function (app){
    
    app.factory('utilityService', function ($http,$location,$log,Session,AuthService,appUrls,httpService){
        
	var utility = {};
        
        utility.cityList    = [];
        
        utility.stateList   = [];
        
        utility.getCityList = function (state_id){
            
            return $http({
                url : appUrls.apiUrl + 'cities.php/?state_id='+state_id,
                method : 'GET'
            });
        };
        
        utility.getStateList = function (){
            
            return $http({
                url : appUrls.apiUrl + 'state_master.php',
                method : 'GET'
            });
        };
        
        utility.getDesignationList = function (){
            
            return $http({
                url : appUrls.apiUrl + 'getDesignation.php',
                method : 'GET'
            });
        };
        
        utility.checkAvailibility = function (username){
            
            return $http({
                url : appUrls.apiUrl + 'check_username_availibility.php',
                method : 'POST',
                data : {username : username}
            });
        };
        
        utility.getDesignationName = function (designation_id){
            return $http({
                url : appUrls.apiUrl + 'helper.php?method=getDesignationName&params=designation_id:'+designation_id,
                method : 'GET'
            });
        };
        
        utility.getDesignationModules = function (designation_id){
            return $http({
                url : appUrls.apiUrl + 'helper.php?method=getDesignationModules&params=designation_id:'+designation_id,
                method : 'GET'
            });
        };
        
        utility.getUnAssignedModules = function (designation_id){
            return $http({
                url : appUrls.apiUrl + 'helper.php?method=getUnassingedModules&params=designation_id:'+designation_id,
                method : 'GET'
            });
        };      
        
        utility.getAllModules = function (){
            return $http({
                url : appUrls.apiUrl + 'helper.php?method=getAllModules',
                method : 'GET'
            });
        };
        
        utility.enableDisableModule = function (module_id,action){
            return $http({
                url : appUrls.apiUrl + 'helper.php?method=enableDisableModule&params=module_id:'+module_id+'/action:'+action,
                method : 'GET'
            });
        };
        
        utility.deleteModule = function (module_id,action){
            return $http({
                url : appUrls.apiUrl + 'helper.php?method=deleteModule&params=module_id:'+module_id+'/action:'+action,
                method : 'GET'
            });
        };
        
        utility.getTreeView = function (module_id){
            return $http({
                url : appUrls.apiUrl + 'helper.php?method=getTreeView&params=module_id:'+module_id,
                method : 'GET'
            });
        };
        
        utility.getParentModules = function (){
            
            return $http({
                url : appUrls.apiUrl + 'helper.php?method=getParentModules',
                method : 'GET'
            });
        };
        
        utility.createModule = function (data){
          
            return $http({
                url : appUrls.apiUrl + 'createModule.php',
                method : 'POST',
                data : data
            });
            
        };
        
        utility.getModule = function (module_id){
            return $http({
                url : appUrls.apiUrl + 'helper.php?method=getModule&param=module_id:'+module_id,
                method : 'GET'
            });
        };
        
        utility.getParentTitle = function (module_id){
            return $http({
                url : appUrls.apiUrl + 'helper.php?method=getParentTitle&param=module_id:'+module_id+'/response_type:plain',
                method : 'GET'
            });
        };
        
        utility.addCampaign =  function (data){
            
            return $http({
                url : appUrls.apiUrl +'addCampaign.php',
                method : 'POST',
                data : data
            });
        };
        
        utility.getCampaigns = function (campaign_type){
            return $http({
                url : appUrls.apiUrl +'helper.php?method=getCampaigns&params=campaign_type:'+campaign_type,
                method : 'GET'
            });
        };
        
        utility.getSecondaryCampiagns = function (){
            return $http({
                url : appUrls.apiUrl +'helper.php?method=getSecondaryCampiagns',
                method : 'GET'
            });
        };
        
        utility.updateCampaign = function (data){
            return $http({
                url : appUrls.apiUrl +'addCampaign.php',
                method : 'POST',
                data : {
                    id      : data.edit_id,
                    title   : data.edit_title,
                    type    : data.type
                }
            });
        };
        
        utility.updateSecondaryCampaign = function (data){
            return $http({
                url : appUrls.apiUrl +'addCampaign.php',
                method : 'POST',
                data : {
                    primary_campaign_id : data.edit_parent_id,
                    id      : data.edit_campaign_id,
                    title   : data.edit_title,
                    type    : data.type
                }
            });
        };
        
        utility.changeCampaignStatus = function (id,status){
            
            return $http({
                url : appUrls.apiUrl + 'helper.php?method=changeCampaignStatus&params=campaign_id:'+id+'/status:'+status,
                method :'GET'
            });
        };
        
        utility.getDisposition_groups = function (){
            return $http({
                url : appUrls.apiUrl + 'get_disposition_group_list.php',
                method :'GET'
            });
        };
        
        utility.saveDispositionGroup = function (data){
          
            return $http({
                url : appUrls.apiUrl + 'manage_disposition_group.php',
                method :'POST',
                data : data
            });
        };
        
        utility.updateDispositionGroup = function (data){
            return $http({
                url : appUrls.apiUrl + 'manage_disposition_group.php',
                method :'POST',
                data : data
            });
        };
        
        utility.saveDispositionStatus = function (data){
            return $http({
                url : appUrls.apiUrl + 'manage_disposition_status.php',
                method :'POST',
                data : data
            });
        };
        
        utility.getParentDispositionStatus = function (){
            return $http({
                url : appUrls.apiUrl + 'helper.php?method=getParentDispositionStatus',
                method :'GET'
            });
        };
        
        utility.getDispositionStatusList = function (){
            return $http({
                url : appUrls.apiUrl + 'getDispositionStatusList.php',
                method :'GET'
            });
        };
        
        utility.getDispositionSubStatusList = function (){
            return $http({
                url : appUrls.apiUrl + 'getDispositionSubStatusList.php',
                method :'GET'
            });
        };
        
        utility.editDispositionStatus = function (data){
            
            return $http({
                url : appUrls.apiUrl + 'edit_disposition_status.php',
                method :'POST',
                data : data
            });
        };
        
        utility.get_disposition_group_status = function (group_id){
            return $http({
                url : appUrls.apiUrl + 'helper.php?method=get_disposition_group_status&params=group_id:'+group_id,
                method :'GET'
            });
        };
        
        utility.mapDispositionGroupStatus = function (data){
            
            console.log('Data');
            console.log(data);
            
            return $http({
                url : appUrls.apiUrl + 'disposition_group_status_mapping.php',
                method :'POST',
                data : data
            });
        };
        
        utility.saveEmployeeDispositionGroup = function (group_id,emp_id){
            
            return $http({
                url : appUrls.apiUrl + 'helper.php?method=saveEmployeeDispositionGroup&params=group_id:' +  group_id+'/emp_id:'+emp_id,
                method :'GET'
            });
            
        };
        
        utility.getEmployeeDispositionGroup = function (employee_id){
               return $http({
                url : appUrls.apiUrl + 'helper.php?method=getEmployeeDispositionGroup&params=emp_id:'+employee_id,
                method :'GET'
            });
        };
        
        utility.getAdminDispositionGroup = function (group_name){
            
             return $http({
                url : appUrls.apiUrl + 'helper.php?method=getAdminDispositionGroup&params=group_name:'+group_name,
                method :'GET'
            });
        };
        
        utility.saveAdminDispositionGroup = function (group_id,employee_id,status){
            
            // Status value is either 1 or 0
            return $http({
                url : appUrls.apiUrl + 'set_admin_disposition_group.php',
                method :'POST',
                data : {
                    group_id : group_id,
                    employee_id : employee_id,
                    status : status
                }
            });
            
        };
        
       utility.isMobileNumberExists = function (number){
	    
	    return $http({
		 url : appUrls.apiUrl + 'helper.php?method=isMobileNumberExists&params=number:'+number,
		 method : 'GET'
	    });
       };	  
	  
       utility.get_user_detail_from_lms = function (number){
	    return $http({
		 url : appUrls.apiUrl + 'get_query_detail.php?PhoneNumber='+number,
//		 url : 'http://52.77.73.171/CRM/apis/get_query_detail.php?PhoneNumber='+number,
		 method : 'GET'
	    });
       };
	  
		/**
		 * Function to get only disposition groups only without assigned disposition status and sub status 
		 * @returns {undefined}
		 */
		utility.get_disposition_group_only = function (){

			return $http({
				url : appUrls.apiUrl + 'get_disposition_groups.php',
				method : 'GET'
			});

		};

		utility.getBMHProjectCities = function (){
			return $http({
				url : appUrls.apiUrl + 'getProjectCities.php',
				method : 'GET'
			});
		};
	
		utility.get_all_bmh_projects = function (){
			
			return $http({
				url : appUrls.apiUrl + 'get_all_bmh_projects.php',
				mehod : 'GET'
			});
		};
	
		utility.getAllEmployees = function (){
		
			return $http({
				url : appUrls.apiUrl + 'employees.php',
				mehod : 'GET'
			});
		};
	
        /**
          *  Function to change the active state of disposition status
          * @date : 10 July, 2017 by Abhishek Agrawal
          */
        utility.changeDispositionState = function (disposition_id, state){
            
            var new_state;
            
            if(!state){
                new_state = 0;   
            }else{
                new_state = 1;
            }
            
            return $http({
                url : appUrls.apiUrl+'helper.php?method=changeDispositionState&params=disposition_id:'+disposition_id+'/state:'+new_state,
                method : 'GET'
            });
        };
        
        utility.changeDispositionActivityStatus = function (disposition_id, activity_status){
            var new_state;
            if(!activity_status){
                new_state = 0;   
            }else{
                new_state = 1;
            }
            
            return $http({
                url : appUrls.apiUrl+'helper.php?method=changeDispositionActivityStatus&params=disposition_id:'+disposition_id+'/state:'+new_state,
                method : 'GET'
            });
            
        }
        
		// Returning service object 
		return utility;

	});
}) (app);