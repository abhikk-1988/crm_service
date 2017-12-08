
/**
 * Controller file
 * addNewMailer
 */

var app = app || {};

(
    function (app, $){

        app.controller('addMailerCtrl', ['$scope','baseUrl','$http','projectMailerService','alreadyExistsMailerProjects','$filter', function ($scope,baseUrl,$http,projectMailerService,alreadyExistsMailerProjects,$filter){

            
            // project cities
            $scope.projectCities = [];

            $scope.mailerProjects = [];

            projectMailerService.getProjectCities().then(
                function (resp){
                    $scope.projectCities = resp.data;
                }
            )

            $scope.getProject = function (c){
                
                projectMailerService.getCityProjectList(c.city_name).then(
                    
                    function (resp){

                        var mailerProjects = resp.data.data;

                        // console.log('length Before - ' + Object.keys(mailerProjects).length);
                        var keys_to_remove = [];
                        angular.forEach(mailerProjects, function (val,key){
                            
                            var is_exists = $filter('filter')(alreadyExistsMailerProjects,{project_id:val.project_id},true);
                            
                            if(Object.keys(is_exists).length > 0){
                                keys_to_remove.push(key);
                            }
                        });

                        // Removing projects from collection
                        angular.forEach(keys_to_remove, function (key){
                            mailerProjects[key] =  null;
                        });

                        $scope.mailerProjects =  mailerProjects;
                    }
                );
            };

            $scope.mailer = {
                content : null,
                project : null,
                attachments : null
            };

            $scope.attachment = {
                file : null
            };

            // Uploade mailer attachments
            $scope.mailer_attachments = [];

            // Upload attachments 
            $scope.uploadAttachment = function (file){

                if(!file.file){
                    alert('Please choose an attachment');
                    return false;
                }

                var fd = new FormData();

			    for(var key in file){
				    fd.append(key, file[key]);
			    }

			    var post_url = baseUrl + 'apis/upload_mailer_attachment.php';
			
                return $http.post(post_url , fd , {
                    transformRequest : angular.identity,
                    headers : {
                        'Content-Type' : undefined
                    }
                }).then(function (resp){
                    
                    if(parseInt(resp.data.is_uploaded) === 1){

                        $scope.attachment.file = null;

                        $scope.mailer_attachments.push({original_name : resp.data.original_name, save_name: resp.data.save_name});
                    }else{
                        alert('File could not be uploaded. Please upload again');
                    }

                });
            }

            $scope.removeMailerAttachment = function (attachment_name, index_number){

                $http.post(baseUrl+'apis/removeMailerAttachment.php',{name: attachment_name}).then(
                    function (resp){

                        if(parseInt(resp.data) === 1){

                            // remove from attachments array 
                            $scope.mailer_attachments.splice(index_number,1);
                        }else{
                            alert('Failed to remove attachment. Remove again');
                            return false;
                        }
                    }
                );
            }

            // Save mailer function 
            $scope.saveMailer = function (mailer_data){
                
                if(!mailer_data.project){
                    alert('Select Project');
                    return false;
                }

                if(!mailer_data.content){
                    alert('Enter mailer content');
                    return false;
                }

                if(Object.keys($scope.mailer_attachments).length < 1){
                    var confirm_no_attachment = confirm('Do you wish to continue without attachments? ');
                    if(confirm_no_attachment){
                        $scope.mailer.attachments = null;
                    }   
                }else{
                    $scope.mailer.attachments = angular.copy($scope.mailer_attachments);
                }

                $http.post(baseUrl + 'apis/saveMailer.php',{mailer_data: mailer_data}).then(function (resp){

                    if(parseInt(resp.data.success) === 1){
                        alert(resp.data.message);
                        $scope.mailer = {};
                        $scope.mailer_attachments = {};
                    }
                    else{
                        alert(resp.data.message);
                    }
                });    
            }    
        }
        ]);

    }
)(app, jQuery);