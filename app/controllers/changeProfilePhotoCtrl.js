/**
 * Change profile photo controller
 */

var app = app || {};

(function (app){
	
	app.controller('changeProfilePhotoCtrl', ['$scope','userProfileService','$state','baseUrl','$route', function ($scope, userProfileService, $state, baseUrl, $route){
			
			var user_id = $scope.user.id; // login user id 
			
			$scope.profile = {};
			
			$scope.default_profile_pic = baseUrl + 'stuffs/images/default.png';
			
			$scope.getProfilePhoto = function (){
			
				var photo = userProfileService.getProfilePhoto(user_id);
				// append loading spinner 
				
				angular.element('.photo_box').
				append('<i class="fa fa-spinner faa-spin animated loading_spinner"></i>').
				addClass('fade_photo_bg');
				
				photo.then(function (res){
					
					if(res.data !== ''){
						
						angular.element('.photo_box').find('i').remove().removeClass('fade_photo_bg');
						
						$scope.default_profile_pic = res.data;
						
					}
					
				});
			};
			
			$scope.getProfilePhoto();
			
			$scope.uploadProfilePic = function (form_data){
				
				form_data.user_id = user_id;
				
				if( angular.isUndefined (form_data.photo) ){
					alert('Please upload profile photo'); return false;
				}
				
				var file_upload = userProfileService.uploadProfilePhoto(form_data);
				
				file_upload.then(function (res){
					
					if( parseInt(res.data.success) === 1){
						$scope.getProfilePhoto();
						$scope.profile.photo = '';
					}else{
						$scope.message = 'File not uploaded. Please try again';
					}
				});
			};
			
	}]);
	
}(app));