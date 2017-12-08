<?php
session_start();
require_once 'function.php';
$data			= filter_input_array(INPUT_POST);
$enquiry_id		= '';

if(isset($data['enquiry_id'])){
	$enquiry_id = $data['enquiry_id'];
}

// Get category of $enquiry_id 

$current_month	= (int) date('m') - 1;
$current_year	= date('Y');
$current_date	= date('Y-m-d H:i:s');

// get lead category type 
$get_lead_category	= 'SELECT lead_category FROM lead WHERE enquiry_id = '.$enquiry_id.' LIMIT 1';

$category_result	= mysql_query($get_lead_category);

if($category_result && mysql_num_rows($category_result) > 0){
	
	$lead_category = mysql_fetch_object($category_result) -> lead_category;
	
	/**
	 * if category is SPL then we auto allocate this enquiry to area sales manager
	 */

	// Get the auto assign spl lead switch state from settings
	
	$get_switch_setting			= 'SELECT `auto_assign_spl_lead` FROM crm_settings';
	
	$switch_setting_result		= mysql_query($get_switch_setting);
	
	if($switch_setting_result && mysql_num_rows($switch_setting_result) > 0){
		
		$switch = mysql_fetch_object($switch_setting_result);
					
			if($switch -> auto_assign_spl_lead == 1){
				
				// We check for SPL category leads only 
								
				if($lead_category === 'SPL'){
					
					// get project enquired on this enquiry id 
		
					$get_project_enquired = 'SELECT `project_id`, `project_name`, `project_url` '
							. ' FROM lead_enquiry_projects'
							. ' WHERE enquiry_id = '.$enquiry_id.' LIMIT 1';
		
					
					$enquiry_project_result = mysql_query($get_project_enquired);
					$project_id				= '';
					$project_name			= '';
					$project_url			= '';
		
					if($enquiry_project_result && mysql_num_rows($enquiry_project_result) > 0){
			
						// get area sales manager with project capacity of current month 
						// if capacity is not defined for current month 
						// then lead should not be assigned to that area sales manager.
						// Also get the remaining space of lead capacities of area sales manager for that particular project
			
						$project_detail = mysql_fetch_object($enquiry_project_result);
						$project_id		=  $project_detail -> project_id;
						$project_name	=  $project_detail -> project_name;
						$project_url	=  $project_detail -> project_url;
			
						$select_area_sales_manager_sql = 'SELECT userId, capacity, remaining_capacity '
								. ' FROM capacity_master '
								. ' WHERE pId = '.$project_id.' AND capacity_month = '.$current_month.' AND capacity_year = "'.$current_year.'" and remaining_capacity > 0';

			
						$area_sales_manager_result = mysql_query($select_area_sales_manager_sql);
			
						if($area_sales_manager_result && mysql_num_rows($area_sales_manager_result) > 0){
				
							// Here we assign lead to area sales manager
							// update in lead tabel against enquiry id on column <lead_assigned_to_asm>
				
							$asm_user = mysql_fetch_object($area_sales_manager_result);

							$assign_lead_to_asm = 'UPDATE `lead` '
									. ' SET lead_assigned_to_asm = '.$asm_user -> userId.','
									. ' lead_assigned_to_asm_on = "'.$current_date.'"'
									. ' WHERE enquiry_id = '.$enquiry_id.' LIMIT 1';
				
							$asm_name = getEmployeeName($asm_user -> userId);
				
							if(mysql_query($assign_lead_to_asm)){
					
								// Update remaining capacity of area sales manager in current month capacity
					
								$update_remaining_capacity = 'UPDATE capacity_master SET'
										. ' remaining_capacity = remaining_capacity - 1 '
										. ' WHERE userId = '.$asm_user -> userId.' AND pId= '.$project_id.' AND capacity_month = '.$current_month.' AND capacity_year = "'.$current_year.'" LIMIT 1';

								mysql_query($update_remaining_capacity);
					
								// on successfull update of asm id create history 

								// Lead has been assigned to area sales manager <ASM_NAME> on <CURRENT_DATE>
								// Lead Details - 
								// Enquiry ID
								// Project ID
					
								$asm_name = getEmployeeName($asm_user -> userId);
								$history_text = 'Lead has been assigned to area sales '.$asm_name.' manager on '. date('Y-m-d H:i:s');
								$details = '<p>Lead Details - <br/>Enquiry ID - '.$enquiry_id.' <br/>Project - '.$project_name.' </p>';
								$history_text .= $details;	

								$insert_history = 'INSERT lead_history (enquiry_id, details, type) VALUES ('.$enquiry_id.', "'.$history_text.'","new")';

								mysql_query($insert_history);
					
					
								// Apply Round Robin lead Assignment Process
					
								$request_url	= BASE_URL .'apis/round_robin_assignment.php';
								$ch				= curl_init();
								curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
								curl_setopt ($ch, CURLOPT_URL,$request_url);
								curl_setopt ($ch, CURLOPT_POST, 1);
								curl_setopt ($ch, CURLOPT_POSTFIELDS,array('enquiry_ids' => serialize(array($enquiry_id)), 'asm_id' => $asm_user -> userId ));
								$response		= curl_exec ( $ch );
								curl_close($ch);
							}
						}
					}
				}
			}
		}
	}




