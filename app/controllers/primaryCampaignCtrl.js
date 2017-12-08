/**
 * 
 * @type String
 */

var app = app || {};

(function (app, $) {

    app.controller('primaryCampaignCtrl', function ($scope, utilityService, Session, modalService, baseUrl,user_auth) {

        $scope.title = 'Primary Campaign';
        $scope.$watch('campaign.title', function () {
            $scope.campaign.error = '';
            $scope.campaign.errorClass = '';
        });

        // To switch add/edit screen block 
        $scope.showAddCampaignBlock = true;
        $scope.showEditCampaignBlock = false;


        $scope.campaign = {title: '', error: '', errorClass: '', type: 'primary', id: null};

        $scope.primaryCampaigns = [];
        $scope.secondary_campaigns_array = [];

        $scope.fetchPrimaryCampaigns = function () {
            var primary_campaigns = utilityService.getCampaigns('primary');

            primary_campaigns.then(function (success) {

                if (success.data.success == 1) {
                    $scope.primaryCampaigns = success.data.data;

                }
            });

        };

        $scope.fetchPrimaryCampaigns();

        $scope.saveCampaign = function (campaign) {

            if (campaign.title === '' || campaign.title === undefined) {
                $scope.campaign.error = 'Campaign title is required';
                $scope.campaign.errorClass = 'parsley_error';
                return false;
            }

            $scope.campaign.errorClass = '';
            $scope.campaign.error = '';

            var campaign_response = utilityService.addCampaign(campaign);

            campaign_response.then(function (success) {

                switch (parseInt(success.data.success)) {

                    case 1:
                        $scope.fetchPrimaryCampaigns();
                        $scope.campaign.title = '';
                        break;

                    case -1:
                        angular.forEach(success.data.error, function (value, key) {

                            if (key === 'title') {
                                $scope.campaign.error = value;
                                $scope.campaign.errorClass = 'parsley_error';
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

        $scope.showSecondaryCampaigns = function (secondary_campaigns) {

            if (secondary_campaigns === undefined) {
                return true;
            }

            modalService.title = 'Secondary Campaigns';
            modalService.size = 'modal-sm';
            modalService.showFooter = false;
            modalService.templateUrl = baseUrl + 'templates/secondary_campaign_list.html';
            modalService.data = secondary_campaigns;

            jQuery('#my-modal').modal('show');
        };

        $scope.campaign_to_edit = {
            edit_title: '',
            edit_id: null
        };

        $scope.$watch('campaign_to_edit.title', function () {
            $scope.campaign.error = '';
            $scope.campaign.errorClass = '';
        });

        $scope.editCampaign = function (campaign) {

            $scope.showAddCampaignBlock = false;
            $scope.showEditCampaignBlock = true;
            $scope.campaign_to_edit.edit_title = campaign.title;
            $scope.campaign_to_edit.edit_id = campaign.id;
        };

        $scope.$watch('campaign_to_edit.edit_title', function () {
            $scope.campaign_to_edit.error = '';
            $scope.campaign_to_edit.errorClass = '';
        });

        $scope.updateCampaign = function (data) {

            data.type = 'primary';
            if (data.edit_title === '') {
                data.error = 'Campaign title is required';
                data.errorClass = 'parsley_error';
                return false;
            }

            var update_campaign_res = utilityService.updateCampaign(data);

            update_campaign_res.then(function (response) {

                if (response.data.success == 1) {
                    $scope.server_update_response = response.data.message;
                    $scope.toggleBlcok();
                    $scope.server_update_response = '';
                    $scope.fetchPrimaryCampaigns();
                } else {
                    $scope.server_update_response = response.data.message;
                }

            }, function (error) {
                $scope.server_update_response = 'Server is enable to process this request at the moment.';
            });
        };


        $scope.toggleBlcok = function () {
            $scope.showEditCampaignBlock = false;
            $scope.showAddCampaignBlock = true;
        };

        $scope.setCampaignStatus = function (status, event) {

            if (status.active_status == 1) {

                // Make it disable 
                var disable = utilityService.changeCampaignStatus(status.id, 0);

                disable.then(function (response) {

                    if (response.data.success == 1) {
                        angular.element(event.currentTarget).removeClass('checkbox_success').addClass('checkbox_danger');
                    }
                    $scope.fetchPrimaryCampaigns();
                });

            } else {

                // Make it enable 
                var enable = utilityService.changeCampaignStatus(status.id, 1);

                enable.then(function (response) {

                    if (response.data.success == 1) {
                        angular.element(event.currentTarget).removeClass('checkbox_danger').addClass('checkbox_success');
                    }
                    $scope.fetchPrimaryCampaigns();
                });
            }

        };
    });
})(app, jQuery);