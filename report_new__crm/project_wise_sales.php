<?php
	
session_start();
require_once '../apis/function.php';
require_once '../apis/user_authentication.php';

if(!$is_authenticate){
	echo unauthorizedResponse();
	exit;
}
$leads = array();
//$_GET = array('user_id'=>38,'startDate'=>'2017-06-13', 'endDate'=>'2017-06-13');
	
if((!empty($_GET))){	// Check whether the date is empty	
	if(isset($_GET['startDate'])){	
       
		$startDate = date('Y-m-d',strtotime($_GET['startDate']));
	}
	if(isset($_GET['endDate'])){ 
        	
		$endDate = date('Y-m-d',strtotime($_GET['endDate']));    
	}
       	
	$startDate = $startDate.' 00:00:00';
        	
	$endDate = $endDate.' 23:59:59';
       	
	$start = strtotime($startDate);
		
	$end = strtotime($endDate);
		
	$days_between = ceil(abs($end - $start) / 86400);
		
	$filename = 'sales_wise_meeting_generated_from_'.$startDate.'_to_'.$endDate;
		
	// Monthly/Yearly Report of Signle Project
	if(isset($startDate) && isset($endDate)){
    		
		$headers = array('TL','SP');
        	
		$sqlHeaders = mysql_query("SELECT project_name FROM `lead_enquiry_projects` group by project_name");
           	
		if(mysql_num_rows($sqlHeaders) > 0){
				
			$project = array();
				
			while($row = mysql_fetch_assoc($sqlHeaders)){
					
				$project[] = $row['project_name'];
					
				array_push($headers, $row['project_name']);
			
			}
			array_push($headers, "Total");
		}
			
		$sales_person = array();
			
		$sqlQuery = "SELECT A.id  FROM employees AS A, employees AS B WHERE A.reportingTo != 0 AND A.reportingTo = B.id AND A.isDelete=0 AND B.isDelete=0 AND A.designation=7 ORDER BY B.firstname";
			
           	
		$lead_resource = mysql_query($sqlQuery);
			
		if(mysql_num_rows($lead_resource) > 0){
			
			while($rowASM = mysql_fetch_assoc($lead_resource)){
			
				array_push($sales_person, $rowASM['id']);
			
			}
		}
		
		$output = [];
		
		$projectNames = [];	
		
		foreach($sales_person as $valSales){
			
			$spName = getEmployeeName($valSales);
						
			$emplyeeManager = getEmployeeManager($valSales);
					
			$ASM_name = $emplyeeManager['manager_name'];
		
			$out = [];
			
			$out[$ASM_name] = [];
			
			$out[$ASM_name][$spName] = [];
			
			foreach($project as $valProject){
		
				$project_count = getCount($valProject, $valSales, $startDate ,$endDate);
				
				if(!in_array($valProject,$projectNames)){
		
					$projectNames[] = $valProject;
				}
				
				if($project_count > 0){
						
					$out[$ASM_name][$spName][$valProject]  = $project_count;
					
				}else{
						
					$out[$ASM_name][$spName][$valProject] = 0;	
				}
			}
			$output[] = $out;
		}
		$output = getColumWiseTotal($output);
//		echo "<pre/>"; print_r($output); die;
		// print CSV
		
		if(count($output) > 0){
			$str = '<div class="media">';
	        header("Content-type: text/csv");
	        header("Content-Disposition: attachment; filename=$filename.csv");
	        header("Pragma: no-cache");
	        header("Expires: 0");
	        $data = array();
	        
	        foreach($headers as $h){
	            echo $h.',';
	        }
	        echo PHP_EOL;
	        $x = 0;
	        while($x <= count($output)) {
	        	foreach($output[$x] as $key => $value){
					$total = 0;
					$key = str_replace(',','-',$key);
					
        			echo $key.',';
        			
        			foreach($value as $key1 => $value1){
        				
        				$key1 = str_replace(',','-',$key1);
	        			
	        			echo $key1.',';

        				foreach($value1 as $key2 => $value2){
        					$total = $total + $value2;
							$value2 = str_replace(',','-',$value2);
		        			echo $value2.',';
		        			
				        }
			        }
			        echo $total;
		        }
		        
		        echo PHP_EOL;
		       
		        
	        	
	        	$x++;
			}
			
		}
		
//		printCSV($projectNames,$output);


	} else{
		echo "<p>You have not selected any date!</p>";
	}
}
	
function getCount($projectName, $spId, $startDate, $endDate){
		
	$SQL_Lead = "SELECT count(Q.enquiry_id) as total FROM lead AS Q
				
	JOIN lead_enquiry_projects AS P ON P.enquiry_id = Q.enquiry_id
				
	WHERE Q.lead_assigned_to_sp = '$spId' AND P.project_name = TRIM('$projectName') AND Q.disposition_status_id = 3 AND Q.lead_assigned_to_sp_on > '$startDate' AND Q.lead_assigned_to_sp_on < '$endDate'";
			
//	return $SQL_Lead;
		
	$query = mysql_query($SQL_Lead);
	if(mysql_num_rows($query) > 0){
		while($conter = mysql_fetch_array($query)){
			return $conter[0];
		}
	}
}

	function printCSV($projectHeader,$data){
?>
	<table border="1">
		<tr>
			<th>TL</th>
			<th>SP</th>
			<?php foreach($projectHeader as $projectName):?>
			<th><?php echo $projectName;?></th>	
			<?php endforeach;?>
		</tr>
		<?php foreach($data as $tls):?>
			<?php foreach($tls as $tlName => $sales):?>
				<tr>
					<td><?php echo $tlName?></td>
					<?php foreach($sales as $salesPersonName => $projects):?>
						<td><?php echo $salesPersonName?></td>
						<?php foreach($projects as $key =>$value):?>
							<td><?php echo $value;?></td>
						<?php endforeach;?>
					<?php endforeach;?>
				</tr>
			<?php endforeach;?>
		<?php endforeach;?>
	</table>
<?php }

function getColumWiseTotal($data){
	$temp = [];
	$tlName = "";
	$count = [];
	foreach($data as $index => $tls){
		
		
		foreach($tls as $name => $salesP){
			
			foreach($salesP as $sName => $projects){

				foreach($projects as $key => $total){

						$count[$name][$key] = $count[$name][$key] + $total;
				}
			}

			// checking if name for tl changed or not
			if(!empty($tlName) && ($tlName != $name)){

				$t = [];

				$t['Total'] = [];

				$t['Total']['-'] = [];

				foreach($count[$tlName] as $key1 => $total1){

					$t['Total']['-'][] = $total1;
				}
				
				$temp[] = $t;
			}
			
			$temp[] = $tls;
			
			$tlName = $name;
		}

		// adding total row blindaly for the last set of records or tl
		if(!empty($count) && ($index == (count($data)-1) )){
		
			$t = [];
			$t['Total'] = [];
			$t['Total']['-'] = [];
			
			foreach($count[$tlName] as $key2 => $total2){
				$t['Total']['-'][] = $total2;
			}
			$temp[] = $t;
		}
	}
	return $temp;	
}

?>

