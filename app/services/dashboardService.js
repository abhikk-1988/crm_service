/*
 Dashboard Service 
 */

var dashboardService = app.service('dashboardService', function ($http, $q, $filter, baseUrl) {

    this.getAgentTotalCallsMade = function (agent_id, filter_date1, filter_date2) {

        if (typeof agent_id == 'undefined') {
            return 0;
        }

        var d1 = '';
        var d2 = '';

        if (filter_date1) {
            d1 = filter_date1;
        }

        if (filter_date2) {
            d2 = filter_date2;
        }

        var defer = $q.defer();

        var promise = $http({
            url: baseUrl + 'apis/dashboard/agent/get_agent_total_call_made_count.php?agent_id=' + agent_id + '&filter_date_from=' + d1 + '&filter_date_to=' + d2,
            method: 'GET'
        });

        promise.then(function (r) {
            defer.resolve(r);
        }, function (err) {
            defer.reject(err);
        });

        return defer.promise;
    }

    this.getAgentCallbacksCount = function (agent_id, filter_date1, filter_date2) {

        if (typeof agent_id == 'undefined') {
            return 0;
        }

        var d1, d2 = '';

        if (filter_date1) {
            d1 = filter_date1;
        }

        if (filter_date2) {
            d2 = filter_date2;
        }

        var defer = $q.defer();

        var promise = $http({
            url: baseUrl + 'apis/dashboard/agent/get_agent_callback_counts.php?agent_id=' + agent_id + '&filter_date_from=' + d1 + '&filter_date_to=' + d2,
            method: 'GET'
        });

        promise.then(function (r) {
            defer.resolve(r);
        }, function (err) {

            defer.reject(err);
        });

        return defer.promise;

    }

    this.getAgentOtherStats = function (agent_id, filter_date1, filter_date2) {

        if (typeof agent_id == 'undefined') {
            return 0;
        }

        var d1 = '',d2 = '';

        if (filter_date1) {
            d1 = filter_date1;
        }

        if (filter_date2) {
            d2 = filter_date2;
        }

        var defer = $q.defer();

        var promise = $http({
            url: baseUrl + 'apis/dashboard/agent/get_agent_other_disposition_stats.php?agent_id=' + agent_id + '&filter_date_from=' + d1 + '&filter_date_to=' + d2,
            method: 'GET'
        });

        promise.then(function (r) {
            defer.resolve(r);
        }, function (err) {

            defer.reject(err);
        });

        return defer.promise;
    };

    this.getAgentMeetingCount = function (agent_id, filter_date1, filter_date2) {

        if (typeof agent_id == 'undefined') {
            return 0;
        }

        var d1 = '', d2 = '';

        if (filter_date1) {
            d1 = filter_date1;
        }

        if (filter_date2) {
            d2 = filter_date2;
        }

        var defer = $q.defer();

        var promise = $http({
            url: baseUrl + 'apis/dashboard/agent/get_agent_meeting_schedule_stats.php?agent_id=' + agent_id + '&filter_date_from=' + d1 + '&filter_date_to=' + d2,
            method: 'GET'
        });

        promise.then(function (r) {
            defer.resolve(r);
        }, function (err) {

            defer.reject(err);
        });

        return defer.promise;
    };

    this.getAgentSiteVisitCount = function (agent_id, filter_date1, filter_date2) {


        if (typeof agent_id == 'undefined') {
            return 0;
        }

        var d1 = '', d2 = '';

        if (filter_date1) {
            d1 = filter_date1;
        }

        if (filter_date2) {
            d2 = filter_date2;
        }

        var defer = $q.defer();

        var promise = $http({
            url: baseUrl + 'apis/dashboard/agent/get_agent_sitevisit_schedule_stats.php?agent_id=' + agent_id + '&filter_date_from=' + d1 + '&filter_date_to=' + d2,
            method: 'GET'
        });

        promise.then(function (r) {
            defer.resolve(r);
        }, function (err) {

            defer.reject(err);
        });

        return defer.promise;
    };

    this.getAgentJustEnquiryCount = function (agent_id, filter_date1, filter_date2) {

        if (typeof agent_id == 'undefined') {
            return 0;
        }

        var d1 = '', d2 = '';

        if (filter_date1) {
            d1 = filter_date1;
        }

        if (filter_date2) {
            d2 = filter_date2;
        }

        var defer = $q.defer();

        var promise = $http({
            url: baseUrl + 'apis/dashboard/agent/get_just_enquiry_stats.php?agent_id=' + agent_id + '&filter_date_from=' + d1 + '&filter_date_to=' + d2,
            method: 'GET'
        });

        promise.then(function (r) {
            defer.resolve(r);
        }, function (err) {

            defer.reject(err);
        });

        return defer.promise;
    };

    this.getTotalLeadAssignedToSalesPerson = function (user_id, filter_date1, filter_date2){
        
        if (typeof user_id == 'undefined') {
            return 0;
        }

        var d1 = '', d2 = '';

        if (filter_date1) {
            d1 = filter_date1;
        }

        if (filter_date2) {
            d2 = filter_date2;
        }
        
        var defer = $q.defer();

        var promise = $http({
            url: baseUrl + 'apis/dashboard/sales_person/get_total_assigned.php?user_id=' + user_id + '&filter_date_from=' + d1 + '&filter_date_to=' + d2,
            method: 'GET'
        });

        promise.then(function (r) {
            defer.resolve(r);
        }, function (err) {

            defer.reject(err);
        });

        return defer.promise;
        
    };
    
    this.getTotalLeadAcceptedBySalesPerson = function (user_id, filter_date1, filter_date2){
        
        if (typeof user_id == 'undefined') {
            return 0;
        }
        
        var d1 = '', d2 = '';

        if (filter_date1) {
            d1 = filter_date1;
        }

        if (filter_date2) {
            d2 = filter_date2;
        }
        
        var defer = $q.defer();

        var promise = $http({
            url: baseUrl + 'apis/dashboard/sales_person/get_total_accepted.php?user_id=' + user_id + '&filter_date_from=' + d1 + '&filter_date_to=' + d2,
            method: 'GET'
        });

        promise.then(function (r) {
            defer.resolve(r);
        }, function (err) {

            defer.reject(err);
        });

        return defer.promise;
        
    };
    
    this.getTotalMeetingAssignedToSalesPerson = function (user_id, filter_date1, filter_date2){
        
        if (typeof user_id == 'undefined') {
            return 0;
        }

        var d1 = '', d2 = '';

        if (filter_date1) {
            d1 = filter_date1;
        }

        if (filter_date2) {
            d2 = filter_date2;
        }
        
        var defer = $q.defer();

        var promise = $http({
            url: baseUrl + 'apis/dashboard/sales_person/get_assigned_meeting.php?user_id=' + user_id + '&filter_date_from=' + d1 + '&filter_date_to=' + d2,
            method: 'GET'
        });

        promise.then(function (r) {
            defer.resolve(r);
        }, function (err) {

            defer.reject(err);
        });

        return defer.promise;
        
    };
    
    this.getTotalSitevisitAssignedToSalesPerson = function (user_id, filter_date1, filter_date2){
        
        if (typeof user_id == 'undefined') {
            return 0;
        }

        var d1 = '', d2 = '';

        if (filter_date1) {
            d1 = filter_date1;
        }

        if (filter_date2) {
            d2 = filter_date2;
        }
        
        var defer = $q.defer();

        var promise = $http({
            url: baseUrl + 'apis/dashboard/sales_person/get_assigned_sitevisit.php?user_id=' + user_id + '&filter_date_from=' + d1 + '&filter_date_to=' + d2,
            method: 'GET'
        });

        promise.then(function (r) {
            defer.resolve(r);
        }, function (err) {

            defer.reject(err);
        });

        return defer.promise;
        
    };
    
    
    this.getTotalCallback = function (user_id,filter_date1, filter_date2){
      
        if (typeof user_id == 'undefined') {
            return 0;
        }

        var d1 ='', d2 = '';

        if (filter_date1) {
            d1 = filter_date1;
        }

        if (filter_date2) {
            d2 = filter_date2;
        }
        
        var defer = $q.defer();

        var promise = $http({
            url: baseUrl + 'apis/dashboard/sales_person/get_total_callback.php?user_id=' + user_id + '&filter_date_from=' + d1 + '&filter_date_to=' + d2,
            method: 'GET'
        });

        promise.then(function (r) {
            defer.resolve(r);
        }, function (err) {

            defer.reject(err);
        });

        return defer.promise;
    };
    
    this.getClosureLeads = function (user_id,filter_date1,filter_date2){
        
        if (typeof user_id == 'undefined') {
            return 0;
        }
        
        var d1 = ''; var d2 = '';

        if (filter_date1) {
            d1 = filter_date1;
        }

        if (filter_date2) {
            d2 = filter_date2;
        }
        
        var defer = $q.defer();

        var promise = $http({
            url: baseUrl + 'apis/dashboard/sales_person/get_closure_leads.php?user_id=' + user_id+'&filter_date_from='+filter_date1+'&filter_date_to='+filter_date2 ,
            method: 'GET'
        });

        promise.then(function (r) {
            defer.resolve(r);
        }, function (err) {

            defer.reject(err);
        });

        return defer.promise;
        
    };
    
    this.getOthers = function (user_id,filter_date1,filter_date2){
        
        if (typeof user_id == 'undefined') {
            return 0;
        }
        
        var d1 = '', d2 = '';

        if (filter_date1) {
            d1 = filter_date1;
        }

        if (filter_date2) {
            d2 = filter_date2;
        }
        
        var defer = $q.defer();

        var promise = $http({
            url: baseUrl + 'apis/dashboard/sales_person/get_other.php?user_id=' + user_id + '&filter_date_from='+filter_date1+'&filter_date_to='+filter_date2,
            method: 'GET'
        });

        promise.then(function (r) {
            defer.resolve(r);
        }, function (err) {

            defer.reject(err);
        });

        return defer.promise;
        
    };
});
