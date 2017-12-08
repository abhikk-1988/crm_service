// Angular Service: crmTeamService

var app = app || {};

(
    function (app, $)
    {
        app.service('crmTeamService', function ($http, baseUrl){

            this.getAllTeams = function (){
                return $http.get(baseUrl+'apis/crm_teams/getAllTeams.php');
            };

            this.getSingleTeam = function (team_id){
                return $http.get(baseUrl+'apis/crm_teams/getTeam.php?team_id='+team_id);
            };

            // create new team
            this.createTeam = function (team){
                return $http.post(baseUrl+'apis/crm_teams/createTeam.php',team);
            };

            // To delete complete team
            this.deleteTeam = function (team_id){
                return $http.post(baseUrl+'apis/crm_teams/deleteTeam.php',{team_id : team_id});
            }

            // To remove a team member 
            this.removeTeamMember = function (emp_id){
                return $http.post(baseUrl+'apis/crm_teams/removeTeamMember.php',{emp_id: emp_id});
            }

            // to add new team member 
            this.addTeamMember = function (emp_id, team_id){
                return $http.post(baseUrl+'apis/crm_teams/addTeamMember.php',{team_id : team_id, emp_id: emp_id});
            }

            this.changeTeamASM = function (team_id, asm_id){
                return $http.post(baseUrl+'apis/crm_teams/updateTeamASM.php',{team_id : team_id, asm_id: asm_id});
            }

            this.getTeamASM = function (team_id){
                return $http.get(baseUrl+'apis/crm_teams/getTeamASM.php?team_id='+team_id);
            }

            this.totalTeamMember = function (team_id){
                return this.getSingleTeam(team_id);
            }

            // To get List of asm having no team 
            this.getAsmList  = function (){
                return $http.get(baseUrl+'apis/crm_teams/getAsmList.php');
            }

            // To get List of CRM agents
            this.getCrmList  = function (asm_id){                
                return $http.get(baseUrl+'apis/crm_teams/getCrmList.php?asm_id='+asm_id);
            }

        });
    }
)(app,jQuery);