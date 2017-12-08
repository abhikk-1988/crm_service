/**
 * Notes Controller 
 */

var app = app || {};

(function (app){
	
	app.controller('notesCtrl', function ($scope, httpService, baseUrl, enquiry_id, notesCount){
		
		$scope.current_employee_id		= $scope.currentUser.id;
		$scope.notes				= [];
		$scope.current_page_number	= 1;
		$scope.page_record_limit		= 5;
		$scope.total_pages			= '';
		
		if(notesCount > 0){
			
			$scope.total_pages = Math.ceil(notesCount / $scope.page_record_limit);
		}
	
		$scope.nextLink		= false;
		$scope.previousLink		= false;
		
		if(notesCount > $scope.page_record_limit){
			$scope.nextLink = true;
		}
		
		$scope.$watch('current_page_number', function (val){
			
			// Disable next nav link when reached to maximum pages 
			if(val >= $scope.total_pages){
				$scope.nextLink = false;
			}
		});
		
		// Create page offset on change of page change 
		$scope.pageChange = function ( page , navigation) {
			
			if(navigation === 'next'){
				$scope.current_page_number = page + 1;
			}
			else{
				$scope.current_page_number = page - 1;
			}
			
			// Enable previous page nav link when navigate to second page 
			if($scope.current_page_number > 1){
				$scope.previousLink		= true;
			}else{
				
				$scope.previousLink		= false;
				if($scope.current_page_number < $scope.total_pages){
					$scope.nextLink = true;
				}
			}
			
			$scope.getNotes();
		};
		
		$scope.note_text = '';
		
		/**
		 * Function to add new note 
		 * @returns {undefined}
		 */
		$scope.addNote = function (note_text){
		
			var note_response = httpService.makeRequest({
				url : baseUrl + 'apis/add_note.php',
				method : 'POST',
				data : {
					enquiry_id		: enquiry_id,
					note_text		: note_text,
					note_owner		: $scope.current_employee_id
				}
			});
			
			note_response.then(function (success){
				
				if(parseInt(success.data.success) === 1){
					$scope.note_text = '';
					$scope.getNotes (); // get new added notes 
				}else{
					
				}
				
				alert(success.data.message);
			});
		};
		
		/**
		 * Fetch notes 
		 */
		
		$scope.getNotes = function (){
			
			httpService.makeRequest ({
				url : baseUrl + 'apis/get_notes.php',
				method : 'POST',
				data : {
					enquiry_id	: enquiry_id,
					page		: $scope.current_page_number 
				}
			}).then(function (success){
				
				if(success.data){
					$scope.notes = success.data;
				}
			});
		};
		
		$scope.getNotes ();
	});
	
} (app));