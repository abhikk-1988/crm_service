<?php 
/*
 * USER DASHBOARD
 */
?>

<style type="text/css">
    
    #dashboard_container{
        padding: 10px;
        border: 1px solid #EEE;
        margin-left: -15px;
        margin-right: -15px;
        background: url('stuffs/images/content_bg.png') repeat;
    }
    
    .stats_box {text-align: center;}
    .figures_container {
        border: 1px solid #CCC;
        padding: 0px;
/*        background-color: #FAFAFA;*/
        background: url('stuffs/images/cream_bg.png') repeat;
    }
    
    .figures_container .stat_count{
        padding: 4px;
        text-align: center;
        background-color: darkslategray;
        color: #FFF;
        font-weight: bold;
    }
    
    .figures_container .stat_text{
        padding: 4px;
        text-align: center;
        border-bottom: 1px solid #ccc;
        background-color: beige;
        font-size: 15px;
    }
    
    .dashboard_filter_plate {
        border: 1px solid #CCC;
        margin-left: -25px;
        margin-right: -25px;
        margin-top: -11px;
        padding: 15px;
        background-color: ghostwhite;
    }
</style>


<div id="dashboard_container">
    
    <div class="container-fluid" ng-if="user_role" >
        
        <div class="row dashboard_filter_plate">
            
            <div class="col-md-2">
                <label class="label" style="color:#000;">From Date:</label>
                <p class="input-group">
                    <input type="text" class="form-control" uib-datepicker-popup="yyyy/MM/dd" ng-model="date_filter_value.from" is-open="date_popup.opened_from" datepicker-options="filter_date_options" close-text="Close" on-open-focus="true" placeholder="Select Date" />
                     <span class="input-group-btn">
                        <button type="button" class="btn btn-default" ng-click="toggleFromDateFilter($event)"><i class="glyphicon glyphicon-calendar"></i></button>
                     </span>
                </p>
                
            </div>
            <div class="col-md-2">
                <label class="label" style="color:#000;">To Date:</label>
                <p class="input-group">
                    <input type="text" class="form-control" uib-datepicker-popup="yyyy/MM/dd" ng-model="date_filter_value.to" is-open="date_popup.opened_to" datepicker-options="filter_date_options" close-text="Close" on-open-focus="true" placeholder="Select Date" />
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-default" ng-click="toggleToDateFilter($event)"><i class="glyphicon glyphicon-calendar"></i></button>
                    </span>
                </p>
            </div>
            <div class="col-md-1">
                <p class="input-group">
                    <button 
                            class="btn btn-primary btn-xs" 
                            style="margin-top:25px;"
                            ng-click="filterDashbaordDataByDate()"
                            >Filter
                    </button>
                </p>
            </div>
        </div> 
        
        <!--User Wise Dashboard Template-->
        <div ng-include="user_dashboard_template"></div>
        
    </div>
    <div class="jumbotron" ng-if="!user_role">
      <h1>Hello, {{currentUser.firstname}} {{user_role}}</h1>
      <p>We're working on your dashboard</p>
    </div>
    
</div>

<script type="text/javascript">

    
//var ctx1 = document.getElementById("myChart1").getContext('2d');
//var ctx2 = document.getElementById("myChart2").getContext('2d');
//var ctx3 = document.getElementById("myChart3").getContext('2d');
    
    
//    generatechart(ctx1);
//    generatechart(ctx2);
//    generatechart(ctx3);
//    pieAndDoughnutchart(ctx3,config);
    

    
function pieAndDoughnutchart(chartObj,conf){
    
//    if(!options){options = undefined;}
//    
    
    var myPieChart = new Chart(chartObj,conf);    
    return myPieChart;
}

//
//var pie_config = {
//    type: 'pie',
//    data: 
//    {
//        datasets: [
//            
//            {
//                data: [
//                    Math.round(Math.random() * 100),
//                    Math.round(Math.random() * 100),
//                    Math.round(Math.random() * 100)
//                ],
//                backgroundColor: [
//                    '#00ff00', // hot
//                    '#e5ff00', // warm
//                    '#ff0000' // cold
//                ],
//                label : 'Callaback Stats'
//            }],
//        
//        labels: ['Hot','Warm','cold']
//    },
//    options:{
//        responsive: true,
////        animation.animateScale : true
//    }
//};   
//    
    
// Pie and doughnut chart conf    
</script>