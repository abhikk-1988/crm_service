// Service file 
// Project Mailer Service

var app = app || {};

(
    function (app,$){

        app.service('projectMailerService', function ($http, baseUrl){

            this.getAllProjectMailers = function (){
                return $http.get(baseUrl+'apis/getAllProjectMailers.php');
            };
            
            this.removeMailer = function (id){
                return $http.post(baseUrl+'apis/removeMailer.php',{id: id});
            }

            this.uploadAttachent = function (data){

                var fd = new FormData();

			    for(var key in data){
				    fd.append(key, data[key]);
			    }

			    var post_url = baseUrl + 'apis/upload_mailer_attachment.php';
                
                return $http.post(post_url , fd , {
                    transformRequest : angular.identity,
                    headers : {
                        'Content-Type' : undefined
                    }
                })

            }

            this.editMailer = function (data){
                return $http.post(baseUrl+'apis/editMailer.php',{data: data});
            }

            this.removeAttachment = function (data){                
                return $http.post(baseUrl+'apis/removeMailerAttachment.php',
                {
                    name: data.save_name, 
                    mailer_id: data.mailer_id
                });
            }

            this.getProjectCities = function (){
                return $http.get(baseUrl+'apis/getProjectCities.php');
            }

            this.getCityProjectList = function(city_name){

                return $http({
                    url : baseUrl+'apis/fetchCRMProjects.php',
                    method : 'POST',
                    data : $.param({city: city_name}),
                    headers : {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    }
                });
            }

        });
    }
)(app,jQuery);