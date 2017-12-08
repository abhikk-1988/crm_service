/**
 * @fileOverview Service modal of designation module 
 */

var app = app || {};

(function (app){
    
    app.service('designationService', function ($http,$location,appUrls){
        
		/**
		 * 
		 * @param {type} modules
		 * @returns {unresolved}
		 */
        this.updateDesignationModules = function (modules){
            
            return $http({
                url : appUrls.apiUrl + 'updateDesignationModules.php',
                method : 'POST',
                data : modules
            });
        };
        
		/**
		 * 
		 * @param {type} module
		 * @returns {unresolved}
		 */
        this.addNewModule = function (module){
            return $http({
                url : appUrls.apiUrl + 'addDesignationModules.php',
                method : 'POST',
                data : module
            });
        };
		
		/**
		 * 
		 * @returns {unresolved}
		 */
		this.fetchAllDesignations = function (){
			
			var url = appUrls.apiUrl + 'get_all_designations.php';
			return $http.get(url);
			
		};
    });
    
})(app);