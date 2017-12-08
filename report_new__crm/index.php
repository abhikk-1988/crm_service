<?php 
	session_start();
	require_once '../apis/function.php';
	require_once '../apis/user_authentication.php';

	if(!$is_authenticate){
		echo unauthorizedResponse();
		exit;
	}
//	$loginId = $_SESSION['currentUser']['id'];
//	if($loginId != 7){
//		echo "Unauthorized Access";
//		exit;
//	}
	
	$sqlASM = mysql_query("SELECT id, firstname, lastname FROM employees WHERE designation = 28 AND activeStatus=1 AND isDelete=0 ORDER BY firstname ASC");
	
	
	$sqlAgent = mysql_query("SELECT id, firstname, lastname FROM employees WHERE designation = 9 AND activeStatus=1 AND isDelete=0 ORDER BY firstname ASC");
	
	$sqlNewAgent = mysql_query("SELECT id, firstname, lastname FROM employees WHERE designation = 9 AND activeStatus=1 AND isDelete=0 ORDER BY firstname ASC");
	
	$sqlProject = mysql_query("SELECT project_name FROM lead_enquiry_projects GROUP BY project_name ORDER BY project_name ASC");
	
	
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		<title>BMH CRM</title>
		<link href="https://netdna.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css" rel="stylesheet">

		<script type="text/javascript" src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
		<script type="text/javascript" src="https://netdna.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker.min.css" />
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker3.min.css" />

		<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.min.js"></script>

		<link href="css/daterangepicker.css" rel="stylesheet" media="screen">
	  
		<link href="css/sticky-footer.css" rel="stylesheet">
		<link href="css/custom.css" rel="stylesheet">
		<style type="text/css">
			.demo {  position: relative;}
			.demo i { position: absolute;  bottom: 10px;  right: 24px;  top: auto;  cursor: pointer; }
			
			.demo1 {  position: relative;}
			.demo1 i { position: absolute;  bottom: 10px;  right: 24px;  top: auto;  cursor: pointer; }
			
			.demo1_crm {  position: relative;}
			.demo1_crm i { position: absolute;  bottom: 10px;  right: 24px;  top: auto;  cursor: pointer; }
			
			
			.demo1_sales {  position: relative;}
			.demo1_sales i { position: absolute;  bottom: 10px;  right: 24px;  top: auto;  cursor: pointer; }
      		
      		.demo1_project {  position: relative;}
			.demo1_project i { position: absolute;  bottom: 10px;  right: 24px;  top: auto;  cursor: pointer; }
			
			.demo1_project_sales {  position: relative;}
			.demo1_project_sales i { position: absolute;  bottom: 10px;  right: 24px;  top: auto;  cursor: pointer; }
			
			.demo1_project_crm {  position: relative;}
			.demo1_project_crm i { position: absolute;  bottom: 10px;  right: 24px;  top: auto;  cursor: pointer; }
			
			.demo1_meeting_sales {  position: relative;}
			.demo1_meeting_sales i { position: absolute;  bottom: 10px;  right: 24px;  top: auto;  cursor: pointer; }
			
			.demo1_quality_report {  position: relative;}
			.demo1_quality_report i { position: absolute;  bottom: 10px;  right: 24px;  top: auto;  cursor: pointer; }
		</style>
	</head>

	<body>

		<!-- Wrap all page content here -->
		<div id="wrap">
			<div class="container" >
				<div class="row">
					<h3 class="text-center">CRM - Report</h3>
					<h3 class="text-center" style="float: right;"><button class="btn btn-warning reset">Reset</button></h3>
				</div>
				<hr />
				
				<div class="row">
					<div class="col-md-4 demo">
						<h4>Meeting/Site Visit leads details</h4>
						<input type="text" id="config-demo" class="form-control" placeholder="Select Date Range">
						<i class="glyphicon glyphicon-calendar fa fa-calendar" id="agent"></i>
					</div>
					
					<div class="col-md-4 demo1">
						<h4>All Leads</h4>
						<input type="text" id="config-demo1" name="date" class="form-control" placeholder="Select Date">
						<i class="glyphicon glyphicon-calendar fa fa-calendar" id="asm"></i>
					</div>
					
					<div class="col-md-4">
						<h4 style="text-align: center;">&nbsp;&nbsp;&nbsp;&nbsp;</h4>
						<div id="output" class="col-md-6"></div>
						
					</div>
				</div>	
				
				<hr />
				
				<!--CRM wise report-->
				<div class="row">
					<div class="col-md-4 demo_crm">
						<h4>CRM wise report </h4>
						<select name="agent_name" id="agent_name" class="form-control">
							<option value="">Select Agent</option>
							<option value="all">All</option>
							<?php  while($rowAgent = mysql_fetch_assoc($sqlAgent)){ ?>
							<option value="<?php echo $rowAgent['id']; ?>"><?php echo ucwords(strtolower($rowAgent['firstname']." ".$rowAgent['lastname'])); ?></option>
							<?php }?>
						</select>
					</div>
						
					<div class="col-md-4 demo1_crm">
						<h4>Date</h4>
						<input type="text" id="date_crm" name="date_crm" class="form-control" placeholder="Select Date">
						<i class="glyphicon glyphicon-calendar fa fa-calendar" id="asm"></i>
					</div>
						
					<div class="col-md-4">
						<h4 style="text-align: center;">&nbsp;&nbsp;&nbsp;&nbsp;</h4>
						<div id="output_crm" class="col-md-6"></div>
					</div>
				</div>
				
				<hr />
				<!--Sales Manager wise report-->
				<div class="row">
					<div class="col-md-4 demo_sales">
						<h4>Area Sales Manager wise report </h4>
						<select name="sales_person_name" id="sales_person_name" class="form-control">
							<option value="">Select ASM </option>
							<!--<option value="all">All</option>-->
							<?php  while($rowSales = mysql_fetch_assoc($sqlASM)){ ?>
							<option value="<?php echo $rowSales['id']; ?>"><?php echo ucwords(strtolower($rowSales['firstname']." ".$rowSales['lastname'])); ?></option>
							<?php }?>
						</select>
					</div>
					
					<div class="col-md-4 demo1_sales">
						<h4>Date</h4>
						<input type="text" id="date_sales" name="date_sales" class="form-control" placeholder="Select Date">
						<i class="glyphicon glyphicon-calendar fa fa-calendar" id="asm"></i>
					</div>
					
					<div class="col-md-4">
						<h4 style="text-align: center;">&nbsp;&nbsp;&nbsp;&nbsp;</h4>
						<div id="output_sales" class="col-md-6"></div>
					</div>
				</div>
				
				<hr />
				<!--Project wise report-->
				<div class="row">
					<div class="col-md-4 demo_project">
						<h4>Project wise report </h4>
						<select name="project_name" id="project_name" class="form-control">
							<option value="">Select Project</option>
							<option value="all">All</option>
							<?php  while($rowProject = mysql_fetch_assoc($sqlProject)){ ?>
							<option value="<?php echo trim($rowProject['project_name']); ?>"><?php echo ucwords(strtolower(trim($rowProject['project_name']))); ?></option>
							<?php }?>
						</select>
					</div>
					
					<div class="col-md-4 demo1_project">
						<h4>Date</h4>
						<input type="text" id="date_project" name="date_project" class="form-control" placeholder="Select Date">
						<i class="glyphicon glyphicon-calendar fa fa-calendar" id="asm"></i>
					</div>
					
					<div class="col-md-4">
						<h4 style="text-align: center;">&nbsp;&nbsp;&nbsp;&nbsp;</h4>
						<div id="output_project" class="col-md-6"></div>
					</div>
				</div>
				<hr />
				<!--Project sales wise report-->
				<div class="row">
					<div class="col-md-4 demo1_project_sales">
						<h4>Project wise sales report </h4>
						<input type="text" id="date_project_sales" name="date_project_sales" class="form-control" placeholder="Select Date">
						<i class="glyphicon glyphicon-calendar fa fa-calendar" id="sales"></i>
					</div>
					
					<div class="col-md-4 demo1_project_crm">
						<h4>Project wise CRM report </h4>
						<input type="text" id="date_project_crm" name="date_project_crm" class="form-control" placeholder="Select Date">
						<i class="glyphicon glyphicon-calendar fa fa-calendar" id="crm"></i>
					</div>
					
					<div class="col-md-2">
						<h4 style="text-align: center;">&nbsp;&nbsp;&nbsp;&nbsp;</h4>
						<div id="output_project_sales" class="col-md-6"></div>
					</div>
				</div>	 
				
				<hr/>
				<!--sales wise meeting status report-->
				<div class="row">
					<div class="col-md-4 demo1_meeting_sales">
						<h4>Sales Wise Meeting Done / Not Done</h4>
						<input type="text" id="date_sales_meeting_status" name="date_sales_meeting_status" class="form-control" placeholder="Select Date">
						<i class="glyphicon glyphicon-calendar fa fa-calendar" id="sales"></i>
					</div>
					
					<!--<div class="col-md-4 demo1_project_crm">
						<h4>Project wise CRM report </h4>
						<input type="text" id="date_project_crm" name="date_project_crm" class="form-control" placeholder="Select Date">
						<i class="glyphicon glyphicon-calendar fa fa-calendar" id="crm"></i>
					</div>--> 
					
					<div class="col-md-2">
						<h4 style="text-align: center;">&nbsp;&nbsp;&nbsp;&nbsp;</h4>
						<div id="output_sales_meeting" class="col-md-6"></div>
					</div>
				</div>	
				
				<hr/>
				<!--Quality Report-->
				<div class="row">
					<div class="col-md-4 demo_crm_quality">
						<h4>Agents</h4>
						<select name="agent_name_quality" id="agent_name_quality" class="form-control">
							<option value="">Select Agent</option>
							<option value="all">All</option>
							<?php  while($rowNewAgent = mysql_fetch_assoc($sqlNewAgent)){ ?>
							<option value="<?php echo $rowNewAgent['id']; ?>"><?php echo ucwords(strtolower($rowNewAgent['firstname']." ".$rowNewAgent['lastname'])); ?></option>
							<?php }?>
						</select>
					</div>
					
					<div class="col-md-4 demo1_quality_report">
						<h4>Quality Report</h4>
						<input type="text" id="date_quality_report" name="date_quality_report" class="form-control" placeholder="Select Date">
						<i class="glyphicon glyphicon-calendar fa fa-calendar" id="quality"></i>
					</div> 
					
					<div class="col-md-2">
						<h4 style="text-align: center;">&nbsp;&nbsp;&nbsp;&nbsp;</h4>
						<div id="output_quality" class="col-md-6"></div>
					</div>
				</div>		
				
				<div class="col-lg-1 col-md-1 col-sm-1"></div>
			</div>
			<hr>
		
			<div class="col-lg-1 col-md-1 col-sm-1"></div>
			<div class="col-lg-10 col-md-10 col-sm-10">
				<div class="loader text-center" style="display:none"><img src="img/loading.gif"></div>
				<div class="response"></div>
			</div>

			<div class="col-lg-1 col-md-1 col-sm-1"></div>
		</div>

		<script type="text/javascript" src="js/moment.min.js"></script>
		<script type="text/javascript" src="js/daterangepicker.js"></script>
		<script type="text/javascript">
			$(document).ready(function() {
				// $('.demo i').trigger('click');
				// passDate('2017-06-07','2017-06-07');
				
				$("button").click(function(){
					location.reload(); 
				});
				
				$("#agent_name").change(function(){
					$("#date_crm").val('');
					$("#output_crm").html('');
				});
				
				$("#agent_name_quality").change(function(){
					$("#date_quality_report").val('');
					$("#output_quality").html('');
				});
				
				$("#sales_person_name").change(function(){
					$("#date_sales").val('');
					$("#output_sales").html('');
				});
				
				$("#project_name").change(function(){
					$("#date_project").val('');
					$("#output_project").html('');
				});
				
				
				// Meeting/site visit & all leads
				$('.demo i').click(function() {
					updateConfig();
					$(this).parent().find('input').click();
					$("#config-demo1").attr("disabled","disabled");
					$(".demo1 i").css("display","none");
					if($("#config-demo").val()){
						var date = $("#config-demo").val().split(' - ');
						passDate(date[0],date[1]);
					}
				});
				
				$('.demo1 i').click(function() {
					var options = {};
					options.opens = "right";
					options.ranges = {
						'Today': [moment(), moment()],
						'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
						'Last 7 Days': [moment().subtract(6, 'days'), moment()],
						'Last 30 Days': [moment().subtract(29, 'days'), moment()],
						'This Month': [moment().startOf('month'), moment().endOf('month')],
						'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
					};
					$('#config-demo1').daterangepicker(options, function(start, end, label) { 
						var startDate = start.format('YYYY-MM-DD'); 
						var endDate = end.format('YYYY-MM-DD');
						passupdatedDate(startDate,endDate);
					});
					$(this).parent().find('input').click();
					$("#config-demo").attr("disabled","disabled");
					$(".demo i").css("display","none");
					if($("#config-demo1").val()){
						var date = $("#config-demo1").val().split(' - ');
						passupdatedDate(date[0],date[1]);
					}
				});
				
				$('.demo1_crm i').click(function() {
					$("#agent_name").css('border-color','#ccc');
					if($("#agent_name").val()!=''){
						var agent = $("#agent_name").val();
						var options = {};
						options.opens = "right";
						options.ranges = {
							'Today': [moment(), moment()],
							'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
							'This Month': [moment().startOf('month'), moment().endOf('month')],
							'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
						};
						$('#date_crm').daterangepicker(options, function(start, end, label) { 
							var startDate = start.format('YYYY-MM-DD'); 
							var endDate = end.format('YYYY-MM-DD');
							passupdatedDateCrm(agent,startDate,endDate);
						});
						$(this).parent().find('input').click();
						
						if($("#date_crm").val()){
							var date = $("#date_crm").val().split(' - ');
							passupdatedDateCrm(agent, date[0],date[1]);
						}
					}else{
						alert("Please select agent!");
						$("#agent_name").css('border-color','red');
						$("#agent_name").focus();
					}
				});
				
				$('.demo1_sales i').click(function() {
					$("#sales_person_name").css('border-color','#ccc');
					if($("#sales_person_name").val()!=''){
						var asm = $("#sales_person_name").val();
						var options = {};
						options.opens = "right";
						options.ranges = {
							'Today': [moment(), moment()],
							'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
	//						'Last 7 Days': [moment().subtract(6, 'days'), moment()],
	//						'Last 30 Days': [moment().subtract(29, 'days'), moment()],
							'This Month': [moment().startOf('month'), moment().endOf('month')],
							'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
						};
						$('#date_sales').daterangepicker(options, function(start, end, label) { 
							var startDate = start.format('YYYY-MM-DD'); 
							var endDate = end.format('YYYY-MM-DD');
							passupdatedDateSales(asm, startDate, endDate);
						});
						$(this).parent().find('input').click();
						
						if($("#date_sales").val()){
							var date = $("#date_sales").val().split(' - ');
							passupdatedDateSales(asm, date[0], date[1]);
						}
					}else{
						alert("Please select area sales manager!");
						$("#sales_person_name").css('border-color','red');
						$("#sales_person_name").focus();
					}
				});
				 
				 
				$('.demo1_project i').click(function() {
					$("#project_name").css('border-color','#ccc');
					if($("#project_name").val()!=''){
						var project = $("#project_name").val();
						var options = {};
						options.opens = "right";
						options.ranges = {
							'Today': [moment(), moment()],
							'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
	//						'Last 7 Days': [moment().subtract(6, 'days'), moment()],
	//						'Last 30 Days': [moment().subtract(29, 'days'), moment()],
							'This Month': [moment().startOf('month'), moment().endOf('month')],
							'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
						};
						$('#date_project').daterangepicker(options, function(start, end, label) { 
							var startDate = start.format('YYYY-MM-DD'); 
							var endDate = end.format('YYYY-MM-DD');
							passupdatedDateProject(project, startDate, endDate);
						});
						$(this).parent().find('input').click();
						
						if($("#date_project").val()){
							var date = $("#date_project").val().split(' - ');
							passupdatedDateProject(project, date[0], date[1]);
						}
					}else{
						alert("Please select project!");
						$("#project_name").css('border-color','red');
						$("#project_name").focus();
					}
				});
				
				
				$('.demo1_project_sales i').click(function() {
					var options = {};
					options.opens = "right";
					options.ranges = {
						'Today': [moment(), moment()],
						'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
						'This Month': [moment().startOf('month'), moment().endOf('month')],
						'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
					};
					$('#date_project_sales').daterangepicker(options, function(start, end, label) { 
						
						var startDate = start.format('YYYY-MM-DD'); 
						var endDate = end.format('YYYY-MM-DD');
						passupdatedDateProjectSales(startDate, endDate);
					});
					$(this).parent().find('input').click();
					$("#date_project_crm").attr("disabled","disabled");
					$(".demo1_project_crm i").css("display","none");
					if($("#date_project_sales").val()){
						var date = $("#date_project_sales").val().split(' - ');
						passupdatedDateProjectSales(date[0], date[1]);
					}
				});
				
				$('.demo1_project_crm i').click(function() {
					
					var options = {};
					options.opens = "right";
					options.ranges = {
						'Today': [moment(), moment()],
						'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
						'This Month': [moment().startOf('month'), moment().endOf('month')],
						'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
					};
					$('#date_project_crm').daterangepicker(options, function(start, end, label) { 
						$("#date_project_sales").attr("disabled","disabled");
						var startDate = start.format('YYYY-MM-DD'); 
						var endDate = end.format('YYYY-MM-DD');
						passupdatedDateProjectCRM(startDate, endDate);
					});
					$(this).parent().find('input').click();
					$("#date_project_sales").attr("disabled","disabled");
					$(".demo1_project_sales i").css("display","none");
					if($("#date_project_crm").val()){
						var date = $("#date_project_crm").val().split(' - ');
						passupdatedDateProjectCRM(date[0], date[1]);
					}
				});
						
				$('.demo1_meeting_sales i').click(function() {
					
					var options = {};
					options.opens = "right";
					options.ranges = {
						'Today': [moment(), moment()],
						'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
						'This Month': [moment().startOf('month'), moment().endOf('month')],
						'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
					};
					$('#date_sales_meeting_status').daterangepicker(options, function(start, end, label) { 
						$("#date_sales_meeting_status").attr("disabled","disabled");
						var startDate = start.format('YYYY-MM-DD'); 
						var endDate = end.format('YYYY-MM-DD');
						salesWiseMeetingStatus(startDate, endDate);
					});
					$(this).parent().find('input').click();
					if($("#date_sales_meeting_status").val()){
						var date = $("#date_sales_meeting_status").val().split(' - ');
						salesWiseMeetingStatus(date[0], date[1]);
					}
				});
				
				$('.demo1_quality_report i').click(function() {
					$("#agent_name_quality").css('border-color','#ccc');
					if($("#agent_name_quality").val()!=''){
						var agent = $("#agent_name_quality").val();
						var options = {};
						options.opens = "right";
						options.ranges = {
							'Today': [moment(), moment()],
							'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
							'This Month': [moment().startOf('month'), moment().endOf('month')],
							'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
						};
						$('#date_quality_report').daterangepicker(options, function(start, end, label) { 
							$("#date_quality_report").attr("disabled","disabled");
							var startDate = start.format('YYYY-MM-DD'); 
							var endDate = end.format('YYYY-MM-DD');
							qualityReport(agent, startDate, endDate);
						});
						$(this).parent().find('input').click();
						if($("#date_quality_report").val()){
							var date = $("#date_quality_report").val().split(' - ');
							qualityReport(agent, date[0], date[1]);
						}
					}else{
						alert("Please select agent/crm Name!");
						$("#agent_name_quality").css('border-color','red');
						$("#agent_name_quality").focus();
					}
				});
				
				function updateConfig() {
					var options = {};
					options.opens = "right";
					options.ranges = {
						'Today': [moment(), moment()],
						'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
						'Last 7 Days': [moment().subtract(6, 'days'), moment()],
						'Last 30 Days': [moment().subtract(29, 'days'), moment()],
						'This Month': [moment().startOf('month'), moment().endOf('month')],
						'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
					};
    
					$('#config-demo').daterangepicker(options, function(start, end, label) { 
						var startDate = start.format('YYYY-MM-DD'); 
						var endDate = end.format('YYYY-MM-DD');
						passDate(startDate,endDate);
					});
          		}
			});

			function passDate(startDate,endDate) {
    
				$('#output').html('<a href="meeting_sitevisit.php?'+'startDate='+startDate+'&endDate='+endDate+'" class="btn btn-primary" target="_blank">Download </a>');
				
				return;
			}

			function passupdatedDate(startDate,endDate) {
    
				$('#output').html('<a href="all_leads.php?'+'startDate='+startDate+'&endDate='+endDate+'" class="btn btn-primary" target="_blank">Download</a>');
				
				return;
			}
			
			function passupdatedDateCrm(agent, startDate,endDate) {
    
				$('#output_crm').html('<a href="crm_wise.php?agent_id='+agent+'&startDate='+startDate+'&endDate='+endDate+'" class="btn btn-primary" target="_blank">Download</a>');
				
				return;
			}
			
			function passupdatedDateSales(asm,startDate,endDate) {
    
				$('#output_sales').html('<a href="sales_person_wise.php?asm_id='+asm+'&startDate='+startDate+'&endDate='+endDate+'" class="btn btn-primary" target="_blank">Download</a>');
				
				return;
			}
			
			function passupdatedDateProject(project_name, startDate, endDate) {
    
				$('#output_project').html('<a href="project_wise.php?project='+project_name+'&startDate='+startDate+'&endDate='+endDate+'" class="btn btn-primary" target="_blank">Download</a>');
				
				return;
			}
			
			function passupdatedDateProjectSales(startDate, endDate) {
    
				$('#output_project_sales').html('<a href="project_wise_sales.php?startDate='+startDate+'&endDate='+endDate+'" class="btn btn-primary" target="_blank">Download</a>');
				
				return;
			}
			
			function passupdatedDateProjectCRM(startDate, endDate) {
    
				$('#output_project_sales').html('<a href="project_wise_agents.php?startDate='+startDate+'&endDate='+endDate+'" class="btn btn-primary" target="_blank">Download</a>');
				
				return;
			}
			
			function salesWiseMeetingStatus(startDate, endDate) {
    
				$('#output_sales_meeting').html('<a href="sales_wise_meeting_status.php?startDate='+startDate+'&endDate='+endDate+'" class="btn btn-primary" target="_blank">Download</a>');
				
				return;
			}
			
			function qualityReport(agent, startDate, endDate) {
    
				$('#output_quality').html('<a href="crm_wise_quality_reprot.php?agent_id='+agent+'&startDate='+startDate+'&endDate='+endDate+'" class="btn btn-primary" target="_blank">Download</a>');
				
				return;
			}
		</script>
	</body>
</html>
