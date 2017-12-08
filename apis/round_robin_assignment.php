<?php
session_start();
require 'function.php';

$data = filter_input_array(INPUT_POST);

// input params 
// area_sales_manager_id 
// enquiry_ids <array> (with one or multiple values)

$iterations = array();

function getAssignmentIterationOfRoundRobin($a, $b) {

	global $iterations;

	$c = $a - $b;

	if ($c > $b) {

		array_push($iterations, $b);

		getAssignmentIterationOfRoundRobin($c, $b);
		
	} else {

		array_push($iterations, $c);
	}

	return $iterations;
}


if(isset($data) && !empty($data['asm_id'])){

	$asm_id = $data['asm_id'];
	
	// check for enquiry ids. if no enquiry ids passed then we will not go further 
	
	if(!empty($data['enquiry_ids'])){
		
		if(gettype($data['enquiry_ids']) == 'string'){
			$enquiry_ids = unserialize($data['enquiry_ids']);
		}else{
			$enquiry_ids	= $data['enquiry_ids'];
		}
	
		$enquiry_count	= count($enquiry_ids);
		
		// get the status of round robin scheduler flag 
		
		$get_round_robin_status = 'SELECT `is_round_robin_enable` FROM `crm_settings` LIMIT 1';
		
		$round_robin_status_query_result = mysql_query($get_round_robin_status);
		
		$round_robin_status = mysql_fetch_object($round_robin_status_query_result);
		
		// If round robin is enable then only we assign leads to sales persons otherwise not 
		if($round_robin_status -> is_round_robin_enable == 1){
			
			// get all sales persons of area sales managers with their total capacities and remaining capacities of current month  
			
			$current_month	= (int) date('m') - 1;
			$current_year	= date('Y'); 
			
			$select_all_sales_persons = 'SELECT emp.id, capacity.capacity as total_capacity, capacity.remaining_capacity '
					. ' FROM employees as emp'
					. ' LEFT JOIN sales_person_capacities as capacity ON (emp.id = capacity.sales_person_id AND capacity.month = '.$current_month.' AND year = "'.$current_year.'")'
					. ' WHERE emp.reportingTo = '.$asm_id.' AND capacity.remaining_capacity > 0 ORDER BY RAND()';
			
			
			$sales_person_query_result = mysql_query($select_all_sales_persons);
			
			if($sales_person_query_result && mysql_num_rows($sales_person_query_result) > 0){
				
				$sales_persons = array();
				
				while($row = mysql_fetch_assoc($sales_person_query_result)){
					array_push($sales_persons, $row); // Pushing sales persons in array 
				}
				
				// Sales person count 
				$sales_person_count = count($sales_persons);
				
				// There are two cases to be handle 
				// Case 1:  If total leads is less then the no.of.sales persons then straight forward assign leads to sales persons upto to the leads count
				// Case 2 : If total leads were more than no.of.sales persons then we calculate no. of iterations to assign leads to sales person in a uniform manner 
				$iterations_result = array();
				
				// Check if sales persons were there to assign
				if(count($sales_persons) > 0){
			
					if($enquiry_count > $sales_person_count){
						
						$iterations_result = getAssignmentIterationOfRoundRobin($enquiry_count, $sales_person_count);
				
						array_unshift($iterations_result, $sales_person_count);
					
						$iteration_length	= count($iterations_result);
						$start_index		= 0;
						$current_date		= date('Y-m-d H:i:s');
					
						if($iteration_length > 0){
						
							$counter = 0;
						
							foreach($iterations_result as $iteration){

								$counter++;

								for($i = 0; $i<$iteration; $i++){

									$enquiry_id = $enquiry_ids[$start_index + $i];

									if($sales_persons[$i]['remaining_capacity'] > 0){
									
										// query to assign lead to sales person and update sales person capacity 
									
										$sp_id			= $sales_persons[$i]['id'];
										$assign_lead	= 'UPDATE `lead` '
											. ' SET lead_assigned_to_sp = '.$sp_id.', lead_assigned_to_sp_on = "'.$current_date.'"'
											. ' WHERE enquiry_id = '.$enquiry_id.' LIMIT 1';
									
										if(mysql_query($assign_lead)){
										
											// update remaining capacity of sales person 
										
											$update_capacity = 'UPDATE `sales_person_capacities` '
												. ' SET remaining_capacity = remaining_capacity - 1'
												. ' WHERE sales_person_id = '.$sp_id.' AND month = '.$current_month.' AND year = "'.$current_year.'" LIMIT 1';
										
											mysql_query($update_capacity);
										
											// Log history 
											$log			= NEW_LEAD_ASSIGNMENT_TO_SP; 
											$lead_number	= getLeadNumber($enquiry_id);
											$insert_log		= 'INSERT INTO `lead_history` SET'
												. ' lead_number = "'.$lead_number.'" ,'
												. ' enquiry_id = '.$enquiry_id.','
												. ' details = "'.$log.'",'
												. ' type = "new"';
										
											mysql_query($insert_log);
										}
									
										$sales_persons[$i]['remaining_capacity'] = $sales_persons[$i]['remaining_capacity'] - 1; // update remaining capacity in sales_persons array to check for 0 capacity 
									}			
								}	

								// Change start index 	
								if($counter < $iteration_length){
									$start_index = $start_index + $iteration;
								}
							}
						}
					}
					else{
					
						$iteration_result = array($sales_person_count);
					
						for($i=0; $i<$sales_person_count;$i++){
							
							$enquiry_id		= $enquiry_ids[$i];
							$sp_id			= $sales_persons[$i]['id'];
							$current_date	= date('Y-m-d H:i:s');
							
							$assign_lead	= 'UPDATE `lead` '
											. ' SET lead_assigned_to_sp = '.$sp_id.', lead_assigned_to_sp_on = "'.$current_date.'"'
											. ' WHERE enquiry_id = '.$enquiry_id.' LIMIT 1';
							
							if(mysql_query($assign_lead)){
								
								$update_capacity = 'UPDATE `sales_person_capacities` '
												. ' SET remaining_capacity = remaining_capacity - 1'
												. ' WHERE sales_person_id = '.$sp_id.' AND month = '.$current_month.' AND year = "'.$current_year.'" LIMIT 1';
										
								mysql_query($update_capacity);
								
								// Log history 
								$log			= NEW_LEAD_ASSIGNMENT_TO_SP; 
								$lead_number	= getLeadNumber($enquiry_id);
								$insert_log		= 'INSERT INTO `lead_history` SET'
									. ' lead_number = "'.$lead_number.'" ,'
									. ' enquiry_id = '.$enquiry_id.','
									. ' details = "'.$log.'",'
									. ' type = "new"';
										
								mysql_query($insert_log);
								
							}
						}
					}
				}
			}
		}
	}
}