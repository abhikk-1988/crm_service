// controller file 
// ProjectMailerCtrl

var app = app || {};

(
    function (app, $){


        app.controller('projectMailerCtrl',['$scope','$location','projectMailerService', function ($scope, $location,projectMailerService){

            $scope.addNewMailer =  function (){
                $location.path('addNewMailer');
            };

            // Editor options.
            $scope.options = {
                language: 'en',
                allowedContent: true,
                entities: false,
                uiColor: '#9AB8F3'
            };

            $scope.projectMailers = [];

            $scope.getMailers= function (){
                var mailers = projectMailerService.getAllProjectMailers();
                mailers.then(
                        function (resp){
                            $scope.projectMailers = resp.data;
                        }
                );    
            }

            $scope.getMailers();

            // Rremove mailer function 
            $scope.removeMailer = function (mailer_id){
                
                var confirm_remove = confirm('Are you sure to remove this mailer?');
                if(confirm_remove){
                    var remove = projectMailerService.removeMailer(mailer_id);
                    remove.then(function (resp){
                        if(parseInt(resp.data) === 1){
                            alert('Mailer removed successfully');
                            $scope.getMailers();
                        }
                        else{
                            alert('Error in removing mailer');
                            return false;
                        }
                    });
                }
            };

           
            // To hold attachment file
            $scope.attachment = {};

            $scope.uploadAttachment = function (x){
              
                if(!x.file){
                    alert('Please choose a file to upload');
                    return false;
                }

                projectMailerService.uploadAttachent(x).then(
                    function (resp){
                        if(parseInt(resp.data) === 1){
                            alert('Attachment uploaded successfully');
                            $scope.getMailers();
                            // Close modal
                            angular.element('#upload_attachment').modal('toggle')
                        }
                    }
                );

            }

            $scope.prepareUpload = function (m_id){
                $scope.attachment.mailer_id = m_id;
            }

            // Edit mailer content
            $scope.editMailerContent = function (o){

                projectMailerService.editMailer(o).then(
                    function (resp){
                        if(parseInt(resp.data) === 1){
                            alert('Mailer content updated successfully');
                            $scope.getMailers();
                        }else{
                            alert('Could not updated mailer content');
                        }
                    }
                );
            }

            $scope.prepareAttachmentToDisplay = function (x){
                $scope.showMyAttachments.attachments = angular.copy(x.attachments);
                $scope.showMyAttachments.mailer_id = x.id;
            }

            // To loop over attachments to display in modal
            $scope.showMyAttachments = [];

            // Remove attachment function 
            $scope.removeAttachment = function (x,mailer_id,e){

                e.target.parentNode.remove();

                x.mailer_id = mailer_id;

                projectMailerService.removeAttachment(x).then(
                    function(resp){
                        if(parseInt(resp.data) === 1){
                            $scope.getMailers();
                            alert('Attachment Removed successfully');
                        }else{
                            alert('Attachment could not be removed');
                        }
                    }
                );
            };

        }]);

    }
)(app,jQuery);