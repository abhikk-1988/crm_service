/**
 * User profile service 
 */

var app = app || {};

(function (app) {
	
	app.service('userProfileService', function ($http, $filter, baseUrl){
		
		this.uploadProfilePhoto = function (data){
			
			var post_url = baseUrl + 'apis/change_user_profile_photo.php';
			var fd =  new FormData();
			for(var key in data){
				fd.append(key, data[key]);
			}
			
			return $http.post(post_url , fd , {
				transformRequest : angular.identity,
				headers : {
					'Content-Type' : undefined
				}
			});
			
		};
		
		
		this.getProfilePhoto = function (user_id){
			
			var url  = baseUrl + 'apis/helper.php?method=getProfilePicture&params=user_id:'+ user_id;
			return $http.get(url);
		};
	});
	
} (app));