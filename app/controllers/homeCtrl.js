
/*
 * Home Controller
 * @scope: Dashbaord
 * @author : Abhishek Agrawal
 * version : 1.0
 */

var app     = app || '';
var Pace    = Pace || {};

( function (app,$) {
	
	app.controller ( 'homeCtrl', ['$scope', '$rootScope', 'application_blocks', 'appUrls', 'appLayout', '$http', 'paceLoading', 'Session', 'AuthService', 'modalService', '$location', '$compile','notify','dashboardService','login_user','$q','baseUrl','$filter', function ( $scope, $rootScope, application_blocks, appUrls, appLayout, $http, paceLoading, Session, AuthService, modalService, $location, $compile, notify,dashboardService, login_user, $q, baseUrl, $filter ) {
        
        $scope.currentUser  = {};
                
        $scope.color_codes = ['#EF7F3B','#33704D','#38398F'];
        
        $scope.piechart_colors = ['#8B0000','#FF4500','#FFA07A','#CD5C5C','#B22222','#FF6347'];
        
        $scope.user_role    = '';
        
        function PieOptions (text, fs, title_text){
            
            this.cutoutPercentage   = 35;
            this.rotation           = -0.5 * Math.PI;
            this.title = {
                display: true, 
                fontsize:14,
                text:title_text
            };
            
            this.responsive = true;
            this.legend = {
                display: true,
                position:'bottom',
                generateLabels : function (chart){
                    var data = chart.data;
                    if (data.labels.length && data.datasets.length) {
                        return data.labels.map(function(label, i) {
                            var meta = chart.getDatasetMeta(0);
                            var ds = data.datasets[0];
                            var arc = meta.data[i];
                            var custom = arc && arc.custom || {};
                            var getValueAtIndexOrDefault = Chart.helpers.getValueAtIndexOrDefault;
                            var arcOpts = chart.options.elements.arc;
                            var fill = custom.backgroundColor ? custom.backgroundColor : getValueAtIndexOrDefault(ds.backgroundColor, i, arcOpts.backgroundColor);
                            var stroke = custom.borderColor ? custom.borderColor : getValueAtIndexOrDefault(ds.borderColor, i, arcOpts.borderColor);
                            var bw = custom.borderWidth ? custom.borderWidth : getValueAtIndexOrDefault(ds.borderWidth, i, arcOpts.borderWidth);

							// We get the value of the current label
			    var value = chart.config.data.datasets[arc._datasetIndex].data[arc._index];
                            
                            return {
                                // Instead of `text: label,`
                                // We add the value to the string
                                text: label + " : " + value,
                                fillStyle: fill,
                                strokeStyle: stroke,
                                lineWidth: bw,
                                hidden: isNaN(ds.data[i]) || meta.data[i].hidden,
                                index: i
                            };
                        });
                    } else {
                        return [];
                    }
                }
            };
            this.centertext = text;
            this.fillstyle = fs;
            this.title_text = title_text;
//            this.options = {
//                showAllTooltips = true
//            };
        }
       
        $scope.fetchAgentDashboardData = function (agent_id,d1,d2){
          
            if(!d2){
                d2 = '';
            }
            
            var total_calls_promise = dashboardService.getAgentTotalCallsMade(agent_id,d1,d2);
            total_calls_promise.then(function (s){
                    $scope.calls_made = s.data;
                }, function (e){
                    $scope.calls_made = 0;
            });
            
            
            // Callbacks 
            var callbacks_promise = dashboardService.getAgentCallbacksCount($scope.currentUser.id,d1,d2);
            callbacks_promise.then(function (s){
                    
                $scope.cb_pie_bgcolors    = [];
                $scope.cb_labels          = [];
                $scope.cb_data            = [];
                
                if(s.data && (s.data.total > 0) ){
                    
                    $scope.cb_data              = s.data.data;  
                    $scope.cb_labels            = s.data.labels;
                    $scope.cb_pie_bgcolors      = s.data.bg_colors;
                    $scope.total_callbacks      = s.data.total;
                
                    $scope.callback_piechart_options = new PieOptions();
                    $scope.callback_piechart_options.title.text = 'Total Callback status wise';
                }                
                else{
                    $scope.total_callbacks = 0;
                }
            }, function (e){
                    $scope.callbacks_stats = [];
            });
            
            var others_promise = dashboardService.getAgentOtherStats(agent_id,d1,d2);
            others_promise.then(function (s){
                    
                    $scope.others_count = s.data.total_count;
                    
                    if(s.data.status){
                        
                        $scope.others_data      = [];
                        $scope.others_labels    = [];
                        
                        $scope.others_piechart_options = new PieOptions();
                        $scope.others_piechart_options.title.text = 'Others\'s statistics status wise ';
                        
                        var tooltip_inner_div = '';
                        for(i in s.data.status){
                            
                            $scope.others_labels.push(s.data.status[i].status);
                            $scope.others_data.push(s.data.status[i].count);
                            
                            tooltip_inner_div += `<div>`+s.data.status[i].status+` : `+s.data.status[i].count+`</div>`;
                        }
                        
                        
                        $scope.others_tooltip_html = `
                                        <div ng-repeat="item in s.data.status">
                                            `+tooltip_inner_div+`
                                        </div>`;    
                    }else{
                    
                        $scope.others_tooltip_html = '';
                    }
                    
                    
                }, function (e){
                    $scope.others_count = 0;
                });    
            
            
            var meeting_stats = dashboardService.getAgentMeetingCount(agent_id,d1,d2);
            meeting_stats.then(function (s){

                    $scope.meeting_labels           = [];
                    $scope.meeting_data             = [];
                    $scope.meeting_label_bgcolors   = [];    
            
                    $scope.meeting_count = s.data.total;
                
                    if(s.data.total > 0){
                        
                        for(var i in s.data.status){
                            $scope.meeting_labels.push(s.data.status[i].status);
                            $scope.meeting_data.push(s.data.status[i].count);
                            $scope.meeting_label_bgcolors.push(s.data.status[i].bg_color);
                        }
                        
                        $scope.meeting_piechart_options = new PieOptions();
                        $scope.meeting_piechart_options.title.text = 'Meeting Pie Chart';
                    }
                    
                }, function (e){
                    $scope.meeting_count = 0;
            });
    
            var sitevisit_stats = dashboardService.getAgentSiteVisitCount(agent_id,d1,d2);
            sitevisit_stats.then(function (s){
                    
                    $scope.sitevisit_count          = s.data.total;
                    $scope.sitevisit_data           = [];
                    $scope.sitevisit_labels         = [];
                    $scope.site_visit_label_bgcolors   = [];
                        
                    if(s.data.total > 0){
                        
                        for(var i in s.data.status){
                            $scope.sitevisit_labels.push(s.data.status[i].status);
                            $scope.sitevisit_data.push(s.data.status[i].count);
                            $scope.site_visit_label_bgcolors.push(s.data.status[i].bg_color);
                            
                        }
                        
                        $scope.sitevisit_piechart_options = new PieOptions();
                        $scope.sitevisit_piechart_options.title.text = 'Sitevisit status distribution';
                    }
                    
                }, function (e){
                $scope.sitevisit_count = 0;
            });
            
            var just_enquiry_stats = dashboardService.getAgentJustEnquiryCount(agent_id,d1,d2);
            just_enquiry_stats.then(function (s){
                
                $scope.just_enquiry_data    = [];
                $scope.just_enquiry_labels  = [];
                    
                if(s.data.total_count > 0){
                    
                    $scope.just_enquiry_count = s.data.total_count;
                    $scope.just_enquiry_data = s.data.data;
                    $scope.just_enquiry_labels = s.data.labels;
                    $scope.just_enquiry_piechart_options = new PieOptions();
                    $scope.just_enquiry_piechart_options.title.text = 'Just Enquiry status distribution';
                }else{
                    $scope.just_enquiry_count = 0;
                }
                
            }, function (e){
                
            });
        };
        
        function createPiechartForSalesPerson(data, labels, options, colors){
            
            $scope.sales_person_pie_chart.push(
                    {
                        labels: labels,
                        data: data,
                        options: new PieOptions(options.innerText, options.innerTextColor, options.title),
                        colors: colors
                    } 
            );
        }
        
        function createHotPieChart(m,s,c){
           
            var hot_meeting     = m.hot;
            var hot_sitevisit   = s.hot;
            var hot_callback    = 0;
            if(c.hot){
                var hot_callback = c.hot.count;
            }
            
            var total_count = parseInt(hot_meeting) + parseInt(hot_sitevisit) + parseInt(hot_callback);
            
            if(total_count){
                
                // Chart Options
                var options = {
                    innerText : 'Hot',
                    innerTextColor: $scope.color_codes[0],
                    title: 'Hot Leads('+total_count+')'
                };
                
                // Data Sets
                var data        = [hot_meeting,hot_sitevisit,hot_callback];
           
                // Label colors
                var colors  = [$scope.color_codes[0],$scope.color_codes[1],$scope.color_codes[2]];        
                        
                // Lables             
                var labels  = ['Meeting','Site Visit','Callback'];
            
                createPiechartForSalesPerson(data,labels,options,colors);
            }else{
                $scope.no_pie_chart_count++;
            }
        }
        
        function createWarmPieChart(m,s,c){
            var warm_meeting     = m.warm;
            var warm_sitevisit   = s.warm;
            var warm_callback    = 0;
            if(c.warm){
                var warm_callback = c.warm.count;
            }
            
            var total_count = parseInt(warm_meeting) + parseInt(warm_sitevisit) + parseInt(warm_callback);
            
            if(total_count){
            
                var options = {
                    innerText : 'Warm',
                    innerTextColor: $scope.color_codes[1],
                    title: 'Warm Leads('+total_count+')'
                };
            
                var data    = [warm_meeting,warm_sitevisit,warm_callback];
                var colors  = [$scope.color_codes[0],$scope.color_codes[1],$scope.color_codes[2]];
                var labels  = ['Meeting','Site Visit','Callback'];
                createPiechartForSalesPerson(data,labels,options,colors);
            }else{
                $scope.no_pie_chart_count++;
            }
        }
        
        function createColdPieChart(m,s,c){
            var cold_meeting     = m.cold;
            var cold_sitevisit   = s.cold;
            var cold_callback    = 0;
            if(c.cold){
                var cold_callback = c.cold.count;
            }
            
            var total_count = parseInt(cold_meeting) + parseInt(cold_sitevisit) + parseInt(cold_callback);
            
            if(total_count){
            
                var options = {
                    innerText : 'Cold',
                    innerTextColor: $scope.color_codes[2],
                    title: 'Cold Leads('+total_count+')'
                };
            
                var data    = [cold_meeting,cold_sitevisit,cold_callback];
                var colors  = [$scope.color_codes[0],$scope.color_codes[1],$scope.color_codes[2]];
                var labels  = ['Meeting','Site Visit','Callback'];
                createPiechartForSalesPerson(data,labels,options,colors);
            }else{
                $scope.no_pie_chart_count++;
            }
        }
        
        /**
         * For Sales Person Dashboard items
         * @param {type} agent_id
         * @param {type} d1
         * @param {type} d2
         * @returns {undefined}
         */
        
        $scope.fetchSalesPersonDashboardData = function (agent_id,d1,d2){
            
            $scope.sales_person_pie_chart = [];
            
            // To find how many pie chart not available
            $scope.no_pie_chart_count = 0;
            
            if(typeof d1 == 'undefined'){
                d1 = '';
            }
            
            if(typeof d2 == 'undefined'){
                d2 = '';
            }
                        
            var total_assigned = dashboardService.getTotalLeadAssignedToSalesPerson(agent_id,d1,d2);
            total_assigned.then(function (r){
                $scope.total_assigned_sales_person = r.data.total_assigned;
            }, function (e){
            });
            
            // Total accepted leads
            var total_accepted = dashboardService.getTotalLeadAcceptedBySalesPerson(agent_id,d1,d2);
            total_accepted.then(function (r){
                $scope.total_accepted_leads = r.data.total_accepted_count;
            }, function (e){
            });
            
            // Promises of meeting, site visit and callback data
            var total_meeting   = dashboardService.getTotalMeetingAssignedToSalesPerson(agent_id,d1,d2);
            var total_sitevisit = dashboardService.getTotalSitevisitAssignedToSalesPerson(agent_id,d1,d2);
            var total_callback  = dashboardService.getTotalCallback(agent_id,d1,d2);
            var closure_callback         = dashboardService.getClosureLeads(agent_id,d1,d2);
            var others_callback          = dashboardService.getOthers(agent_id,d1,d2);
            
            // Array of promises
            var promises = $q.all([total_meeting,total_sitevisit,total_callback,closure_callback,others_callback]);
            
            promises.then(function (r){
                    
                var meeting     = r[0];
                var sitevisit   = r[1];
                var callback    = r[2];
                var closure     = r[3];
                var others      = r[4];
                
                console.log('Callback', callback);
                
        
                $scope.total_meeting_assigned   = meeting.data.total_accepted_meeting;
                $scope.total_sitevisit_assigned = sitevisit.data.total_sitevisit_count;
                
                createHotPieChart(meeting.data.status_wise_count,sitevisit.data.status_wise_count,callback.data.status);
                createWarmPieChart(meeting.data.status_wise_count,sitevisit.data.status_wise_count,callback.data.status);                
                createColdPieChart(meeting.data.status_wise_count,sitevisit.data.status_wise_count,callback.data.status);
                
                if(closure.data.total_closure){
                    
                    var closure_data    = [];
                    var closure_labels  = [];
                    var closure_label_colors = [];
                    var closure_options = {};
                    
                    for(var i in closure.data.status){
                        closure_data.push(closure.data.status[i]);
                        closure_labels.push(i);
                    }
                          
                    closure_label_colors            = [$scope.color_codes[0],$scope.color_codes[1],$scope.color_codes[2]];
                    closure_options.innerText       = 'Closure';
                    closure_options.innerTextColor  = $scope.piechart_colors[Math.floor(Math.random()*5)];
                    closure_options.title           = 'Closure Leads ('+closure.data.total_closure+')';
                    createPiechartForSalesPerson(closure_data,closure_labels,closure_options,closure_label_colors);    
               
                }else{
                    $scope.no_pie_chart_count++;
                } // End if condition
                
                if(others.data.others_total){
            
                    var others_data         = [];
                    var others_labels       = [];
                    var others_label_colors = [];
                    var others_options      = {};
                 
                    // Iterating over status    
                    for(var i in others.data.status){
                        others_data.push(others.data.status[i]);
                        others_labels.push($filter('split_string_by_char')(i,'_'));
                    }
                
                
                    others_label_colors = [$scope.color_codes[0],$scope.color_codes[1],$scope.color_codes[2]];
                    
                    others_options.innerText       = 'Others';
                    others_options.innerTextColor  = $scope.piechart_colors[Math.floor(Math.random()*5)];
                    others_options.title           = 'Others Leads ('+others.data.others_total+')';
                    createPiechartForSalesPerson(others_data,others_labels,others_options,others_label_colors);
                   
                } else{ $scope.no_pie_chart_count++;}
            });  
        };
        
        // If user login available then call the dashboard services with respective to user
        
        if(login_user.data){
            
            $scope.currentUser  = login_user.data;
            $scope.user_role    = $scope.currentUser.designation_slug;
            
            var default_dt      = formatDate(getCurrentDate()); 
            
            $scope.user_dashboard_template = '';
            
            if($scope.user_role === 'agent'){                
                $scope.user_dashboard_template = baseUrl + 'partials/dashboard/agent_dashboard.html';
                $scope.fetchAgentDashboardData($scope.currentUser.id, default_dt);
            } 
            else if($scope.user_role === 'sales_person'){
                $scope.user_dashboard_template = baseUrl+'partials/dashboard/sales_person_dashboard.html';
                $scope.fetchSalesPersonDashboardData($scope.currentUser.id);
            }
            else if($scope.user_role === 'admin') {
                $scope.user_dashboard_template = baseUrl+'partials/dashboard/admin_dashboard.html';
            }
        }else{
            alert('No user login');
            $location.path('/');
        }
			
        
        // DatePicker config options
        $scope.filter_date_options = {
            maxDate: new Date(),
            startingDay : 0
        };
    
        // Filter date model
        
        $scope.date_filter_value = {
            to: '',
            from: ''
        };
        
        
        
        $scope.disable_filter_date_btn  = true;
        
        $scope.date_popup = {
            opened_to: false,
            opened_from : false
        };
        
        $scope.toggleFromDateFilter = function($event) {
            $event.preventDefault();
            $event.stopPropagation();
            $scope.date_popup.opened_from = true;
        };
        
        $scope.toggleToDateFilter = function($event) {
            $event.preventDefault();
            $event.stopPropagation();
            $scope.date_popup.opened_to = true;
        };
        
        // Format date in YYYY-MM-DD 
        function formatDate (d){
         
            var day     = ((new Date(d).getDate() >= 10 ? new Date(d).getDate() : '0'+new Date(d).getDate()));
            var mon     = ((new Date(d).getMonth()) > 10 ? new Date(d).getMonth() : (new Date(d).getMonth() + 1) );
            
            
            // appeding '0' before month less than 10
            if(mon < 10){
                mon = '0' + mon.toString();
            }
            
            var year    = new Date(d).getFullYear();
            return (year + '-'+ mon + '-'+ day);
        }
        
        function getCurrentDate(){
            var d = new Date();
            return formatDate(d);
        }
        
        $scope.filterDashbaordDataByDate = function (){
          
            var to_date     = $scope.date_filter_value.to;
            var from_date   = $scope.date_filter_value.from;
            
            if(!to_date && !from_date){
                alert('Please select a date');
                return false;
            }else if(!from_date && to_date != ''){
                alert("Please select 'From' date");
                return false;
            }
            
            
            // Check if from date is greater then to date 
            if(to_date){
                
                if( new Date(from_date).getTime() > new Date(to_date).getTime() ){
                    alert("'From' date should be less then 'To' date");
                    return false;
                }
                
                // Format To Date
                to_date     = formatDate(to_date);
            }
            
            // Format date in YYYY-MM-DD
            from_date   = formatDate(from_date);
            
            if($scope.user_role === 'agent'){
                $scope.fetchAgentDashboardData($scope.currentUser.id,from_date,to_date);
            }else if($scope.user_role === 'sales_person'){
                $scope.sales_person_pie_chart = [];
                $scope.fetchSalesPersonDashboardData($scope.currentUser.id,from_date,to_date);
            }
        };
        
        // To start loading bar on page load
        paceLoading.start ();

        // To toggle main application header    
        $scope.toggleApplicationHeader ( true );

        // To toggle left sidebar block
        $scope.changeSidebarAppearence ( 'left', true );        
        
    }] );
    
} ) (app, jQuery);


