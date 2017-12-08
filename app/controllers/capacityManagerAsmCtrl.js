/**
 * Package CRM
 * 
 * @fileOverview capacityManagerAsmCtrl
 * @author Abhishek Agrawal
 */

var app = app || {};

(function(app,$){
	
		/**
		 * This controller will be used for add/ edit capacities for area sales managers 
		 * so for edit mode we get an asm_id as route params to differentiate it from add mode 
		 * if no asm_id provided then this controller will work for add mode  
		 */
		app.controller('capacityManagerAsmCtrl', ['$scope','$routeParams','user_session','$log','httpService','asm','baseUrl','utilityService','$filter','$http','$route','asm_projects',function ($scope, $routeParams, user_session, $log, httpService,asm, baseUrl, utilityService, $filter, $http, $route,asm_projects){
            
		$scope.user						= user_session; // current logged in user session
		$scope.asm_users				= asm.users;
		$scope.asm_employees			= asm.employees; // List of all are sales manager's and asst. area sales manager's.
		$scope.cities					= [];
		$scope.bmh_projects				= [];
		$scope.selected_projects		= [];
		$scope.removed_projects 		= []; 	
		$scope.selected_projects_name	= [];
		$scope.capacity_values			= [];
		$scope.mode = 'add'; // default mode

		// Scope modal object to addon capacity by user input value
		$scope.is_open_addon_input = 0;

        $scope.total_capacity = 0; // initial capacity sum    
		
		// scope variable to contain capacity information data  
		$scope.capacity_data = {
			user : null,
			project_wise_capacity : []
		};
		
		/**
		 * Function to set total capacity count
		 * @returns {undefined}
		 */
		
		$scope.setTotalCapacityCount = function (){
            
			// Get the sum of all the individual project capacities of AREA SALES MANAGER		
			var capacity_inputs			=	 $('.capacity_input');
			var total_capacity_sum		=    0;
			
			angular.forEach (capacity_inputs, function(input, index){
				
				// We have to again populate the capacity value array with the correct capacity values 
				var capacity_index = index;
				
				$scope.capacity_values[capacity_index] = angular.element(input).val();
                
                if($scope.capacity_values[capacity_index]){
                    total_capacity_sum = total_capacity_sum + parseInt(angular.element(input).val());
                }else{
                    total_capacity_sum = 0;
                }
			});
    
			$scope.total_capacity = total_capacity_sum;		
		};
		
		
		
		/* Code block for edit mode if asm_id is provided in route params */
		
		if($routeParams.asm_id){
			
			$scope.asm_id	= $scope.capacity_data.user = $routeParams.asm_id;
			$scope.mode		= 'edit';
			
			// filter out asm employee list to just have only one entry of editable asm 
			
			$scope.asm_employees	= $filter('filter')($scope.asm_employees, {id: $scope.asm_id});
			$scope.selected_asm		= $scope.asm_id;
			$scope.asm_projects		= asm_projects;
			
			$scope.current_capacities = [];
			
			angular.forEach ($scope.asm_projects, function (val, index){
				
				$scope.selected_projects.push(val.pId);
				$scope.selected_projects_name.push({id : val.pId, name:val.pName,is_disabled : true});
				$scope.capacity_values.push(val.capacity);				
				$scope.current_capacities.push(val.capacity); 
			});
			
		}
			
			
		/*End: Code block for edit mode */
		
		// date scope variable
		var date = new Date();
		
		Date.prototype.monthNames = {
			fullname : [
				"January", "February", "March",
				"April", "May", "June",
				"July", "August", "September",
				"October", "November", "December"
			],
			shortname : [
				"Jan", "Feb", "Mar",
				"Apr", "May", "Jun",
				"Jul", "Aug", "Sep",
				"Oct", "Nov", "Dec"
			]
		};
		
		$scope.month = date.monthNames.shortname[date.getMonth()];
		
		$scope.year = date.getFullYear ();
		
		$scope.getProjects = function (){
		
			var project_list = utilityService.get_all_bmh_projects();
			
			project_list.then(function (success){
				$scope.bmh_projects = success.data.data;
			});

		};
		
		$scope.getProjects ();
		
		/**
		 * Function to handle change in ASM 
		 * @param {type} asm_id
		 * @returns {undefined}
		 */
		$scope.changeASM = function (asm_id){
			
			if($scope.capacity_data.user === null){
				$scope.capacity_data.user = parseInt(asm_id);
			}
			
			if(asm_id === null || (parseInt(asm_id) !== parseInt($scope.capacity_data.user)) ){
				// clear all data 
				$scope.clearData();
				return true;
			}			
		};

		$scope.tabs = {
			tab1 : {disable : false},
			tab2 : {disable : true},
			tab3 : {disable : true},
			tab4 : {disable : true}
		};
		
		$scope.activeTab = 0;
		
		/**
		 * Function to enable tabs 
		 * @returns {undefined}
		 */
		$scope.continue_to_tab = function (tab_number){
			
			switch(parseInt(tab_number)){
				
				case 2: 
					$scope.tabs.tab2.disable	= false;
					$scope.activeTab			= 1;
					
					// get all assigned projects of current month for rest of area sales managers 
					var assigned_projects = httpService.makeRequest({
						url		: baseUrl + 'apis/get_already_assigned_asm_projects.php',
						method  : 'POST',
						data : {asm_id : $scope.capacity_data.user}
					});
					
					assigned_projects.then(function (successCallback){	
						
						if(successCallback.data.length > 0){
							
							// Remove BHM projects from list that were already asssigned to rest of the area sales managers 
							
							var assigned_bmh_projects = successCallback.data;
							
							var indexes_to_remove = [];
							
							angular.forEach ($scope.bmh_projects, function (p, p_index){
								
								if(assigned_bmh_projects.indexOf(p.id) > -1 ){
									
									// get the index number of found project Ids
									indexes_to_remove.push(p_index);
								}
							});
							
							// Code to remove projects from bmh project list which is already assigned to area sales managers
							if(indexes_to_remove.length > 0){
								
								for (var i = $scope.bmh_projects.length - 1; i >= 0; i--) {
									
									/*	Code commected on 20th Feb
										Reason: Change in requirement to allow same project to be assigned to different asm's 
									*/
//									if(indexes_to_remove.indexOf (i) > -1 ){
//										$scope.bmh_projects.splice (i,1);
//									}

									/* Comment End */
								}	
							}							
						}
					});
					
					break;
					
				case 3:
					$scope.tabs.tab3.disable	= false;
					$scope.activeTab = 2;
					$scope.setTotalCapacity();
					break;
				
			};
			
		};
		
		/**
		 * Function to trigger when tab is selected 
		 */
		
		$scope.select = function (tab_index){
			
			if(tab_index === 0){
				$scope.tabs.tab2.disable = true;
				$scope.tabs.tab3.disable = true;
				$scope.tabs.tab4.disable = true;
				return true;
			}
			
			if(tab_index === 1){
				$scope.tabs.tab3.disable = true;
				$scope.tabs.tab4.disable = false;
				return true;
			}
		};
		
		/**
		 * Function to select unselect bmh_project
		 * @returns {undefined}
		 */
		$scope.selectProject = function (element, p_id){
			
			var project = $filter('filter')($scope.bmh_projects,{id:p_id});
			
			var p_index = $scope.selected_projects.indexOf(p_id);
			
			if(element.currentTarget.checked ){
				
				if(p_index <= -1){
					$scope.selected_projects.push(p_id);
					var temp = {id : p_id, name:project[0].name, is_disabled : false};   
					$scope.selected_projects_name.push(temp);
				}
			}else{
				
				if(p_index > -1){
					$scope.selected_projects.splice (p_index,1);
					$scope.selected_projects_name.splice (p_index,1);
				}
			}
		};
		
		/**
		 * Function to remove project from selected list 
		 * @returns {undefined}
		 */
		$scope.removeProject = function (p_id,capacity_value){
			
			if($scope.capacity_values[capacity_value] !== undefined){
				$scope.decreaseTotalCapacity($scope.capacity_values[capacity_value]);
			}
			
			// remove capacity value from array  
			$scope.capacity_values.splice(capacity_value,1);
			
			var p_index = $scope.selected_projects.indexOf(p_id);	
			$scope.selected_projects.splice (p_index,1);
			$scope.selected_projects_name.splice (p_index,1);
			$scope.updateBmhProjectList();
			
			// in edit mode we need to keep the ids of removed projects
			
			if($scope.mode === 'edit'){
			   $scope.removed_projects.push(p_id);
			}
		};
		
		
		/**
		 * Fucntion to set total capacity
		 * @param {object} element
		 * @returns {undefined}
		 */
		$scope.setTotalCapacity = function (element){
			// Restrict user to decrease in capacity
			var capacity_sum	= $scope.capacity_values.reduce(function (v1,v2){
				
				if(isNaN (v1)){
					v1 = 0;
				}
				
				if(isNaN (v2)){
					v2 = 0;
				}
				
				if(v1 === ''){
					v1 = 0;
				}
				
				if(v2 === ''){
					v2 = 0;
				}
				
				return (parseInt(v1)+ parseInt(v2));
			},0);

			$scope.total_capacity = capacity_sum;
		};
		
		/**
		 * 
		 * @param {type} value
		 * @returns {undefined}
		 */
		
		$scope.checkCapacity = function (element){
				
			if($scope.mode === 'edit'){
				
				var value = element.currentTarget.value;
				
				var current_assigned_capacity = $(element.currentTarget).data('current_capacity');

				if(parseInt(value) < parseInt(current_assigned_capacity) || value === ''){
					
					alert('You can not decrease from the capacity that was previously set');
					
					element.currentTarget.value = current_assigned_capacity;

					$scope.setTotalCapacityCount();

					return false;
				}
			}
		};
		
		/**
		 * Function to decrease value from total capacity
		 * @param {string} value
		 * @returns {undefined}
		 */
		$scope.decreaseTotalCapacity = function (value){
			
			$scope.total_capacity = parseInt($scope.total_capacity - parseInt(value));
			$scope.updateBmhProjectList();
		};
		
		/**
		 * 
		 * @returns {undefined}
		 */
		$scope.updateBmhProjectList = function (){
			
			$scope.bmh_projects = angular.copy ($scope.bmh_projects);
		};
		
		/**
		 * Function to clear all scope variables 
		 * @returns {undefined}
		 */
		$scope.clearData = function (){
			
			$scope.selected_projects		= [];
			$scope.selected_projects_name	= [];
			$scope.capacity_values			= [];
			$scope.total_capacity			= 0;
			$scope.updateBmhProjectList ();
		};

		
		/**
		 * Function to save capacity data 
		 * @returns {undefined}
		 */
		$scope.saveCapacity = function (){
			
			var $capaciy_form = angular.element('form[name="capacity_form"]');
			
			if(Object.keys ($scope.capacity_values).length > 0){
				
				angular.forEach ($scope.selected_projects_name, function (project, index){
					var project_wise_capacity = {project_id :project.id, project_name: project.name, capacity : $scope.capacity_values[index]};
					$scope.capacity_data.project_wise_capacity.push(project_wise_capacity);
				});
			}
			
			$scope.capacity_data.total_capacity = $scope.total_capacity;
			$scope.capacity_data.capacity_month = date.monthNames.shortname.indexOf($scope.month);
			$scope.capacity_data.capacity_year	= $scope.year;
			$scope.capacity_data.removed_projects = $scope.removed_projects;
			
			var api_url = baseUrl + 'apis/';
			if($scope.mode === 'add'){
				api_url = baseUrl + 'apis/save_user_capacity.php';
			}else{
				api_url = baseUrl + 'apis/edit_user_capacity.php';
			}
			
			$scope.capacity_data.login_user_id = $scope.user.id;
			
			$http({
				url : api_url,
				method : 'POST',
				data : $.param ($scope.capacity_data),
				headers :{
					'Content-Type':'application/x-www-form-urlencoded; charset=utf-8'
				}
			}).then(function (successCallback){
				
				if(parseInt(successCallback.data.success) === 1){
					
					$scope.notify({
						message : successCallback.data.message,
						class   : ['alert','alert-success','center-aligned']
					});
					
					$route.reload(); // reloading current route 
				}else{
					
					$scope.notify({
						message : successCallback.data.error,
						class   : ['alert','alert-danger','center-aligned']
					});
				}
			});
		};		
		
			
	 /*
	  *	Function to increase in capacity in edit mode
	  * @decription: added on 5/04/014 by Abhishek Agrawal 
	  *
	  */ 
		$scope.increaseCapacity = function (index, addOnValue, addOnInBaseValue){

			var input_element = '#capacity_input_'+index;
			// get related capacity input DOM Element
			var capacity_input = angular.element(input_element);

			// Base value of capacity
			var base_value = angular.element(capacity_input).data('base_value');

			var capacity_value = angular.element(capacity_input).val(); 
			// increase capacity by

			if(!addOnInBaseValue){
				var increment_capacity_value  = parseInt(capacity_value) + parseInt(addOnValue);
			}else{
				if(addOnValue){
					var increment_capacity_value  = parseInt(base_value) + parseInt(addOnValue);
				}else{
					var increment_capacity_value  = base_value;
				}
			}
			
			// update in capacity_values scope array variable
			$scope.capacity_values[index] = increment_capacity_value;
			angular.element(capacity_input).val(increment_capacity_value);
			// set total capacity count
			$scope.setTotalCapacity();
		};	

		$scope.toggleAddOnInput = function (){
			$scope.is_open_addon_input = !$scope.is_open_addon_input;
		}

	}]);

//	 End: controller 

	/*
	 * Controller : mplCapacityCtrl 
	 */
	
	app.controller ('mplCapacityCtrl', function ($scope, httpService, baseUrl, $routeParams){
	
		$scope.capacity = {};
		
		$scope.capacity.current_capacity		= '';
		$scope.capacity.capacity_addon_value	= '';
		$scope.asm_id					= null;
		$scope.capacity_editable		= true;
		$scope.is_edit_mode				= false;
		
		
		if($scope.mode === 'edit'){
			$scope.is_edit_mode = true;
			$scope.capacity_editable = false;
		}
		
		/**
		 * Function to get user current MPL capacity 
		 * @returns {undefined}
		 */
		$scope.getUserMPLCapacity = function (){
		
			var asm_id	= $routeParams.asm_id;
							
			var capacity = httpService.makeRequest({
				url : baseUrl + 'apis/helper.php?method=get_mpl_capacity&params=asm_id:'+ asm_id,
				method: 'GET'
			});

			capacity.then(function (responseData){				
				if($scope.is_edit_mode){
					$scope.capacity.current_capacity = responseData.data.capacity;
				}
			});
		};
		
		$scope.getUserMPLCapacity();
		
		/**
		 * Watch on parent scope variable 'selected_asm'
		 */
		$scope.$watch('selected_asm', function (val){
			$scope.asm_id = val;			
		});
		
		/**
		 * Function to save user MPL capacity
		 * @returns {Boolean}
		 */
		$scope.saveMplCapacity = function (){
				
			if(!$scope.asm_id){
				alert('Please select ASM');
			}
			
			if($scope.is_edit_mode && $scope.capacity.capacity_addon_value === ''){
				alert('Please enter addon value');
				return false;
			}
			
			// ajax call to server 
			httpService.makeRequest({
				url : baseUrl +'apis/save_mpl_capacity_of_asm.php',
				method : 'POST',
				data : {
					asm_id : $scope.asm_id,
					capacity : $scope.capacity.current_capacity,
					session_user : $scope.user.id,
					addon_value : $scope.capacity.capacity_addon_value
				}
			}).then(function (successCallback){
				
				if(parseInt(successCallback.data.success) === 1){
					$scope.notify({
						class : ['alert','alert-success','bottom-right'], message : successCallback.data.message
					});
					$scope.getUserMPLCapacity ();
					$scope.capacity.capacity_addon_value = '';
				}else{
					$scope.notify({
						class : ['alert','alert-warning','center-aligned'], message : successCallback.data.message
					});
				}
			});
		};
		
	});
	
	
		// Direcive to track check uncheck status of checkbox 
		app.directive('trackStatus', function (){
		
		var checkbox = {};
		
		checkbox.restrict = 'A';
		
		checkbox.link = function (scope,element,attr){
			
			if(scope.selected_projects.indexOf(attr.pid) > -1){
				element.prop('checked',true);
			}
		};
		
		return checkbox;
		
	});
	
	app.directive('isNewAdd', function (){
		
		return {
			restrict : 'A',
			scope : {
				mode : '@',
				input_value : '@'
			}, // isolated
			link : function (scope, element, attr){
				
				console.log('mode: '+ scope.mode);
				console.debug('input value: '+ scope.input_value);
				
				console.log('INPUT VALUE');
				console.log(angular.element(element).val());
				
				
			}
		};
		
	});
	
}(app,jQuery));

