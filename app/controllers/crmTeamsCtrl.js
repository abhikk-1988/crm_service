//
// ─── @FILE OVERVIEW : CRM TEAMS CONTROLLER ─────────────────────────────────────────
//

// ──────Getting module instance ──────────────────────────────────────────────────────────────────────────

var app = app || {};

(function (app,$){

    app.controller('crmTeamsCtrl', function ($scope, crmTeamService, $filter){

        $scope.teams = [];

        crmTeamService.getAllTeams().then(function (response){
            $scope.teams = response.data;
        });

        $scope.new_team = {};

        $scope.asm_options = [];

        // A watch on team name to count number of characters 
        $scope.$watch('new_team.team_name', function (val){

            if(val){
                $scope.new_team.team_name = $filter('limitTo')(val,30);
            }else{
                $scope.team_name_char = 0;
            }

        }, true);

        // Get list of asm from service
        crmTeamService.getAsmList().then(function (resp){
            
            console.log('ASM list');
            console.log(resp.data);
            
            $scope.asm_options = resp.data;
        });

        // CRM List
        $scope.members = [];


        $scope.getCrmList = function (asm_id){
            
            if(asm_id){
                crmTeamService.getCrmList(asm_id).then(function (response){
                    $scope.members = response.data;
                });
            }
        }

        // Save Team Button Click Handler  
        $scope.saveCrmTeam = function (data){

            if(!data.asm){
                alert('Please select ASM');
                return false;
            }          

            if(!data.team_name){
                alert('Please enter team name');
                return false;
            }
            
            if(data.team_members.length < 1){
                alert('Please select team members');
                return false;
            }

            // Form has been filled with all information
            // submit the form to server to save team  

            data.asm_id     = data.asm.asm_id;
            data.asm_name   = data.asm.asm_name;
            
            crmTeamService.createTeam(data).then(function (response){
            
                if(response.data.success == 1){
                    
                    $scope.is_error = false;
                    angular.element('#error_container').html('');

                    alert(response.data.message);
                    crmTeamService.getAllTeams().then(function (response){
                        $scope.teams = response.data;
                    });

                    crmTeamService.getAsmList().then(function (resp){
                        $scope.asm_options = resp.data;
                        $scope.getCrmList(data.asm_id);
                    });

                    $scope.new_team = {};
                }
                else{
                    if(response.data.is_error){

                        // display errors to user
                        if(response.data.errors){

                            $scope.is_error = true;
                            var error_html = '<ul>';
                            angular.forEach(response.data.errors, function (val, index){
                                error_html += '<li class="list-group-item">'+val+'</li>';
                            });
                            error_html += '</ul>';
                            angular.element('#error_container').append(error_html);
                        }
                    }else{
                        alert(response.data.message);
                    }
                }

            });
        }

        // Delete button click handler 
        $scope.deleteTeam = function (team_id, team_owner){
           
            var confirm_to_delete = confirm('Are you sure you want to delete this team? Deleting team will remove reporting of all crm agents from '+ team_owner);
            if(confirm_to_delete){
                crmTeamService.deleteTeam(team_id).then(function (response){
                    alert(response.data);
                    crmTeamService.getAllTeams().then(function (response){
                        $scope.teams = response.data;
                        crmTeamService.getAsmList().then(function (resp){
                            $scope.asm_options = resp.data;
                            $scope.getCrmList($scope.new_team.asm.asm_id);
                        });
                    });
                });
            }
        }
        
        // Click event handler to remove single team member 
        $scope.removeSingleMember = function (member_id, member_name,team_id, asm_id){
            
            var confirm_member_delete = confirm('Are you sure you wish to delete '+member_name+' from team?');
            
            if(confirm_member_delete){
               crmTeamService.removeTeamMember(member_id).then(function(resp){
                   
                   if(resp.data.success == 1){
                       
                        alert(resp.data.message);
                       
                        crmTeamService.getAllTeams().then(function (response){
                            $scope.teams = response.data;
                            crmTeamService.getAsmList().then(function (resp){
                                $scope.asm_options = resp.data;
                                $scope.getCrmList(asm_id);
                            });
                        });
                   }else{
                       
                       if(resp.data.is_error == 1){
                         
                           // render errors    
                       }
                       else{
                            alert(resp.data.messge);      
                       }
                   }
                   
                   
                   
               });
            }else{
                return false;
            }
        }


    });

})(app, jQuery);

    