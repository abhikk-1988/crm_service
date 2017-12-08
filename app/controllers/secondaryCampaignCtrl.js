/**
 * @fileOverview Secondary campaign module controller 
 * @author: Abhishek Agrawal
 * @version 1.0
 */

var app = app || {};

(function (app, $) {

    app.controller('secondaryCampaignCtrl', function ($scope, utilityService, Session, baseUrl, modalService,user_auth) {

        $scope.title = 'Secondary Campaign';

        $scope.primary_campaigns = [];

        $scope.secondary_campaigns = [];
        
        $scope.showAddBlock = true;
        
        $scope.showEditBlock = false;

        $scope.toggleBlock = function (){
            $scope.showEditBlock    = false;
            $scope.showAddBlock     = true;
        };
        
        $scope.secondary_campaign = {
            title: '',
            error: '',
            errorClass: '',
            primary_campaign_id: null,
            type: 'secondary',
            primary_campaign_id_error: ''
        };

        $scope.$watch('secondary_campaign.title', function () {
            $scope.secondary_campaign.error = '';
            $scope.secondary_campaign.errorClass = '';
        });

        $scope.$watch('secondary_campaign.primary_campaign_id', function () {
            $scope.secondary_campaign.primary_campaign_id_error = '';
        });

        $scope.fetchPrimaryCampaigns = function () {
            var primary_campaigns = utilityService.getCampaigns('primary');

            primary_campaigns.then(function (success) {

                if (success.data.success == 1) {
                    $scope.primary_campaigns = success.data.data;

                    var default_option = {
                        id: null,
                        title: 'Select Primary Campaign'
                    };

                    $scope.primary_campaigns.unshift(default_option);
                }
            });
        };
        $scope.fetchPrimaryCampaigns();

        $scope.fetchSecondaryCampaigns = function () {

            var secondary_campaigns = utilityService.getSecondaryCampiagns();
            secondary_campaigns.then(function (response) {

                $scope.secondary_campaigns = response.data;
            }, function (error) {

            });

        };
        $scope.fetchSecondaryCampaigns();

        $scope.saveCampaign = function (secondary_campaign) {

            if (secondary_campaign.title === '' || secondary_campaign === undefined) {
                $scope.secondary_campaign.error = 'Campaign title is required';
                $scope.secondary_campaign.errorClass = 'parsley_error';
                return false;
            }

            if (secondary_campaign.primary_campaign_id === null) {
                $scope.secondary_campaign.primary_campaign_id_error = 'Please select primary campaign from list';
                return false;
            }

            // Now save the secondary campaign
            var campaign_response = utilityService.addCampaign(secondary_campaign);

            campaign_response.then(function (success) {

                switch (parseInt(success.data.success)) {

                    case 1:
                        $scope.fetchSecondaryCampaigns();
                        $scope.secondary_campaign.title = '';
                        $scope.secondary_campaign.primary_campaign_id = null;
                        break;

                    case -1:
                        angular.forEach(success.data.error, function (value, key) {

                            if (key === 'title') {
                                $scope.campaign.error = value;
                                $scope.campaign.errorClass = 'parsley_error';
                            }

                            if (key === 'primary_campaign_id') {
                                $scope.secondary_campaign.primary_campaign_id_error = value;
                            }
                        });
                        break;

                    case 0:
                        alert(success.data.message);
                        break;

                }

            }, function (error) {

            });

        };

        $scope.setCampaignStatus = function (status, event) {

            if (status.active_status == 1) {

                // Make it disable 
                var disable = utilityService.changeCampaignStatus(status.id, 0);

                disable.then(function (response) {

                    if (response.data.success == 1) {
                        angular.element(event.currentTarget).removeClass('checkbox_success').addClass('checkbox_danger');
                    }
                    $scope.fetchSecondaryCampaigns();
                });

            } else {

                // Make it enable 
                var enable = utilityService.changeCampaignStatus(status.id, 1);

                enable.then(function (response) {

                    if (response.data.success == 1) {
                        angular.element(event.currentTarget).removeClass('checkbox_danger').addClass('checkbox_success');
                    }
                    $scope.fetchSecondaryCampaigns();
                });
            }

        };

        $scope.campaign_to_edit = {};
        
        $scope.$watch('campaign_to_edit.edit_title', function (){
            $scope.campaign_to_edit.error = '';
            $scope.campaign_to_edit.errorClass  = '';
        });
        
        $scope.editCampaign = function (data){
          
            $scope.showAddBlock = false;
            $scope.showEditBlock = true;
            
            $scope.campaign_to_edit.edit_campaign_id = data.id;
            $scope.campaign_to_edit.edit_title = data.title;
            $scope.campaign_to_edit.edit_parent_id = data.parent;
            $scope.campaign_to_edit.type = 'secondary';
            $scope.campaign_to_edit.edit_parent_title = data.parent_title;
        };
        
        $scope.updateCampaign = function (data){
            
            if(data.edit_title === ''){
                $scope.campaign_to_edit.error = 'Campaign title is required';
                $scope.campaign_to_edit.errorClass = 'parsley_error';
                return false;
            }
            sss
            var edit_secondary_campaign = utilityService.updateSecondaryCampaign(data);
            
            edit_secondary_campaign.then(function (response){
                
                if(response.data.success == 1){
                    $scope.fetchSecondaryCampaigns();
                    $scope.campaign_to_edit = {};
                    $scope.toggleBlock();
                }else{
                    alert(response.data.message);
                }
            });
            
        };
        
    });

})(app, jQuery);