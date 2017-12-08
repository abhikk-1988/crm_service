<?php

// This is a helper file for common utility functions 

require_once 'function.php';

$method = '';
$params = '';
$args = array();

if (isset($_GET['method']) && !empty($_GET['method'])) {

	$method = $_GET['method'];

	// For every helper method requested it not mandate to have params
	// If method requires params then we check for it and make available to the method 
	if (isset($_GET['params']) && !empty($_GET['params'])) {

		// Params may be multiple and saperated by a (/) sign in request query string  

		$params = $_GET['params'];
		
		$params_array = explode('/', $params);

		foreach ($params_array as $val) {
			$args[] = explode(':', $val)[1];
		}
	}
}

// Define methods 

if (!function_exists('getDesignationName')) {

	function getDesignationName($designation_id = null) {

		if ($designation_id == null) {
			echo json_encode($response = array('success' => 0), true);
			exit;
		}

		$select_name_query = 'SELECT designation FROM ' . strtolower('designationMaster') . ' WHERE id = ' . $designation_id . ' LIMIT 1';

		$result = mysql_query($select_name_query);

		$rows = mysql_num_rows($result);

		if ($rows > 0) {
			$data = mysql_fetch_object($result);
			echo json_encode($response = array('success' => 1, 'data' => $data->designation), true);
		} else {
			echo json_encode($response = array('success' => 0), true);
			exit;
		}
	}

}

if (!function_exists('getParentTitle')) {

	function getParentTitle($id = null, $response_type = 'json') {

		if ($id == null) {

			if ($response_type == 'json') {
				echo json_encode($response = array('success' => 1, 'data' => ''), true);
				exit;
			} else {
				echo '';
				exit;
			}
		}

		$select_title_query = 'SELECT title FROM ' . strtolower('crmModules') . ' WHERE id = ' . $id . ' LIMIT 1';

		$result = mysql_query($select_title_query);

		$rows = mysql_num_rows($result);

		if ($rows > 0) {
			$data = mysql_fetch_object($result);

			if ($response_type == 'json') {
				echo json_encode($response = array('success' => 1, 'data' => $data->title), true);
			} else {
				echo $data->title;
			}
		} else {

			if ($response_type == 'json') {
				echo json_encode($response = array('success' => 0), true);
				exit;
			} else {
				echo '';
				exit;
			}
		}
	}

}

if (!function_exists('getDesignationModules')) {

	function getDesignationModules($designation_id = NULL, $response_type = 'json') {

		if ($designation_id == null) {
			echo json_encode($response = array('success' => 0), true);
			exit;
		}

		$select_modules = 'SELECT module.*, junction.permission '
				. ' FROM ' . strtolower('designationModuleMaster') . ' as junction '
				. ' LEFT JOIN ' . strtolower('crmModules') . ' as module ON(junction.moduleId = module.id) '
				. ' WHERE junction.designationId = ' . $designation_id . ' ';

		$result = mysql_query($select_modules);

		$rows = mysql_num_rows($result);

		$modules = array();

		if ($rows > 0) {

			while ($row = mysql_fetch_assoc($result)) {
				$row['assign'] = 1;
				$row['designation_id'] = $designation_id;
				array_push($modules, $row);
			}

			if ($response_type == 'json') {
				echo json_encode($response = array('success' => 1, 'data' => $modules), true);
			} else {
				echo $data->title;
			}
		} else {
			if ($response_type == 'json') {
				echo json_encode($response = array('success' => 0), true);
				exit;
			} else {
				echo '';
				exit;
			}
		}
	}

}

if (!function_exists('getUnassingedModules')) {

	function getUnassingedModules($designation_id = NULL) {

		if ($designation_id == NULL) {
			echo json_encode(array('data' => array()), true);
			exit;
		}

		$select_modules = 'SELECT * FROM `crmmodules` '
				. ' WHERE `id` NOT IN(SELECT `moduleId` FROM designationmodulemaster WHERE designationId = ' . $designation_id . ');';

		$result = mysql_query($select_modules);

		$rows = mysql_num_rows($result);

		$unassigned_modules = array();

		if ($rows > 0) {

			while ($row = mysql_fetch_assoc($result)) {
				$row['assign'] = 0;
				$row['designation_id'] = $designation_id;
				$row['permission'] = 4;
				array_push($unassigned_modules, $row);
			}

			echo json_encode(array('data' => $unassigned_modules), true);
			exit;
		} else {
			echo json_encode(array('data' => array()), true);
			exit;
		}
	}

}

if (!function_exists('getAllModules')) {

	function getAllModules($responseType = 'json') {

		$select = 'SELECT * FROM ' . strtolower('crmModules') . '';

		$result = mysql_query($select);

		$rows = mysql_num_rows($result);

		$modules = array();

		if ($rows > 0) {

			while ($row = mysql_fetch_assoc($result)) {

				array_push($modules, $row);
			}
		}

		echo json_encode(array('success' => 1, 'data' => $modules));
		exit;
	}

}

if (!function_exists('enableDisableModule')) {

	function enableDisableModule($module_id = null, $action = null) {

		if ($module_id == NULL || $action == NULL) {
			echo json_encode(array('success' => 0), true);
			exit;
		}

		$exeute_query = 'UPDATE ' . strtolower('crmModules') . ''
				. ' SET markAsDisable = ' . $action . ' '
				. ' WHERE id = ' . $module_id . ' LIMIT 1';

		if (mysql_query($exeute_query)) {
			echo json_encode(array('success' => 1), true);
			exit;
		} else {
			echo json_encode(array('success' => 0), true);
			exit;
		}
	}

}

/**--------------------------------------------------------------------------	
  |    Function to delete module from system                                |
  |--------------------------------------------------------------------------
 */

if(!function_exists('deleteModule')){
	
	function deleteModule($moduleId = ''){
		
		if($moduleId == ''){
			
			echo json_encode(array('success' => 0,'message' => 'Not able to find module'),true); exit;
		}
		
		$delete_sql = 'DELETE FROM crmmodules WHERE id = '.$moduleId.' LIMIT 1';
		
		if(mysql_query($delete_sql)){
			echo json_encode(array('success' => 1, 'message' => 'Module has been deleted successfully'),true); exit;
		}else{
			echo json_encode(array('success' => 0, 'message' => 'Enable to delete the module. Please try again later')); exit;
		}
		
	}
}


if (!function_exists('getTreeView')) {

	function getTreeView($module_id = NULL) {


		// If module id is null then generate full tree view 
		if ($module_id == null) {
			
		} else {

			$parent_module = 'SELECT * FROM ' . strtolower('crmModules') . ' WHERE id = ' . $module_id . ' LIMIT 1';

			$result = mysql_query($parent_module);
			$is_parent = mysql_num_rows($result);

			$parent_module_id = '';

			if ($is_parent > 0) {

				$parent_module = mysql_fetch_object($result);
			}

			// check if a parent module is called for tree view structure 
			if ($parent_module->parent == null) {
				$select_hierarchy = 'SELECT * FROM ' . strtolower('crmModules') . ' WHERE parent = ' . $parent_module->id . '';

				$parent_node_text = $parent_module->title;
			} else {
				$select_hierarchy = 'SELECT * FROM ' . strtolower('crmModules') . ' WHERE parent = ' . $parent_module->parent . '';

				$parent_node_text = file_get_contents(BASE_URL . 'apis/helper.php?method=getParentTitle&params=id:' . $parent_module->parent . '/response_type:plain');
			}

			$hierarchy_result = mysql_query($select_hierarchy);

			$is_hierarchy_view = mysql_num_rows($hierarchy_result);

			$hierarchy = array();

			$hierarchy['text'] = $parent_node_text;

			if ($parent_module->parent == null) {
				$hierarchy['selectable'] = false;
				$hierarchy['icon'] = 'glyphicon glyphicon-folder-open';
				$hierarchy['backColor'] = '#3BB4C8';
				$hierarchy['state'] = array('expanded' => TRUE, 'checked' => true);
			}

			if ($is_hierarchy_view > 0) {
				$hierarchy['nodes'] = array();
				while ($row = mysql_fetch_assoc($hierarchy_result)) {

					$temp = array();
					$temp['text'] = $row['title'];

					if ($row['id'] == $module_id) {
						$temp['selectable'] = FALSE;
						$temp['icon'] = 'glyphicon glyphicon-folder-open';
						$temp['backColor'] = '#3BB4C8';
						$temp['state'] = array('expanded' => TRUE, 'checked' => true);
					}

					array_push($hierarchy['nodes'], $temp);
				}
			}

			echo json_encode(array('data' => $hierarchy), true);
			exit;
		}
	}

}

if (!function_exists('getParentModules')) {

	function getParentModules() {

		$select_modules = 'SELECT * FROM ' . strtolower('crmModules') . ' WHERE parent IS NULL';

		$result = mysql_query($select_modules);

		$rows = mysql_num_rows($result);

		$modules = array();

		if ($rows > 0) {

			while ($row = mysql_fetch_assoc($result)) {

				array_push($modules, $row);
			}
		}

		echo json_encode(array('success' => 1, 'data' => $modules), true);
		exit;
	}

}

if (!function_exists('getModule')) {

	function getModule($module_id = null) {

		$select = 'SELECT * FROM crmmodules WHERE id = ' . $module_id . ' LIMIT 1';

		$result = mysql_query($select);

		$rows = mysql_num_rows($result);

		$module = array();

		if ($rows > 0) {

			$module = mysql_fetch_assoc($result);
		}

		echo json_encode(array('success' => 1, 'data' => $module), true);
		exit;
	}

}

if (!function_exists('isParentAssignedToDesignation')) {

	function isParentAssignedToDesignation($module_id = null, $designation_id = null) {

		if ($module_id == null) {
			return false;
		}

		$query = 'SELECT * FROM designationmodulemaster WHERE designationId = ' . $designation_id . ' AND moduleId = ' . $module_id . ' LIMIT 1';

		$result = mysql_query($query);

		if ($result) {
			echo 1;
			exit;
		} else {
			echo 0;
			exit;
		}
	}

}

if (!function_exists('getCampaigns')) {

	function getCampaigns($campaign_type = '') {

		if ($campaign_type == 'primary') {

			$query = 'SELECT tbl1.id,tbl1.title as primary_title,tbl1.active_status, GROUP_CONCAT(tbl2.id) as secondary_campaign_id, GROUP_CONCAT(tbl2.title ORDER BY tbl2.title ASC) as secondary_campaign_title
                        FROM `campaign_master` as tbl1 
                        LEFT JOIN `campaign_master` as tbl2 ON (tbl1.id = tbl2.primary_campaign_id AND tbl2.active_status = 1)
                        WHERE tbl1.primary_campaign_id IS NULL GROUP by tbl1.id ORDER BY tbl1.title ASC ';

            
			$result      = mysql_query($query);
			$campaigns   = array();

			if ($result) {
				if (mysql_num_rows($result) > 0) {

					while ($row = mysql_fetch_assoc($result)) {

						$temp = array();

						$temp['id']               = $row['id'];
						$temp['title']            = $row['primary_title'];
						$temp['active_status']    = $row['active_status'];

						if ($row['secondary_campaign_id'] != NULL) {

							$temp['child'] = explode(',', $row['secondary_campaign_title']);
						}

						array_push($campaigns, $temp);
					}
				}

				echo json_encode(array('success' => 1, 'data' => $campaigns), true);
				exit;
			}
		} else {
			echo json_encode(array('success' => 0, 'data' => $campaigns), true);
			exit;
		}
	}

}

if (!function_exists('getSecondaryCampiagns')) {

	function getSecondaryCampiagns() {

		$select = 'SELECT secondary.id, secondary.title,secondary.active_status, secondary.primary_campaign_id as parent, parent_campaign.title as parent_title
                    FROM `campaign_master` as secondary
                    INNER JOIN `campaign_master` as parent_campaign ON (secondary.primary_campaign_id = parent_campaign.id)
                    WHERE secondary.`primary_campaign_id` IS NOT NULL order by secondary.title ASC';

		$result = mysql_query($select);

		$secondary_campaigns = array();

		if ($result) {

			while ($row = mysql_fetch_assoc($result)) {
				array_push($secondary_campaigns, $row);
			}
		}

		echo json_encode($secondary_campaigns, TRUE);
		exit;
	}

}

if (!function_exists('changeCampaignStatus')) {

	function changeCampaignStatus($id = null, $status = null) {

		if ($id === null) {

			echo json_encode(array('success' => 0, 'message' => 'Status could not be set'), true);
			exit;
		}

		if ($status == 1) {

			// set status to active 
			$update_status = 'UPDATE `campaign_master` SET `active_status` = 1 WHERE id = ' . $id . ' LIMIT 1';
		} else {

			// set status to inactive
			$update_status = 'UPDATE `campaign_master` SET `active_status` = 0 WHERE id = ' . $id . ' LIMIT 1';
		}

		if (mysql_query($update_status)) {
			echo json_encode(array('success' => 1, 'message' => 'Status has been changed successfully'), true);
			exit;
		} else {
			echo json_encode(array('success' => 0, 'message' => 'Status could not be set'), true);
			exit;
		}
	}

}

if (!function_exists('getParentDispositionStatus')) {

	function getParentDispositionStatus() {

		$query = 'SELECT id , status_title FROM `disposition_status_substatus_master` '
				. ' WHERE `parent_status` IS NULL AND `active_state` = 1 AND delete_state = 0';

		$result = mysql_query($query);

		$top_level_status = array();

		if ($result) {

			while ($row = mysql_fetch_assoc($result)) {
				array_push($top_level_status, $row);
			}
		}

		echo json_encode(array('success' => 1, 'data' => $top_level_status), true);
		exit;
	}

}

if (!function_exists('get_disposition_group_status')) {

	function get_disposition_group_status($group_id = NULL) {

		if ($group_id == NULL) {
			echo 0;
			exit;
		}
//
//		$select = 'SELECT `disposition_status_id` FROM `disposition_group_status_master` WHERE disposition_group_id = ' . $group_id . '';
//        
        
        $select = 'SELECT tbl1.`disposition_status_id` , tbl2.status_title, tbl2.active_state
FROM `disposition_group_status_master` as tbl1
LEFT JOIN disposition_status_substatus_master as tbl2 ON (tbl1.`disposition_status_id` = tbl2.id)
WHERE disposition_group_id = '. $group_id;
    
        
		$result = mysql_query($select);

		$group_status = array();

		if ($result) {

			if (mysql_num_rows($result) > 0) {

				while ($row = mysql_fetch_assoc($result)) {

                    $group_data = array();
                    $group_data['status_id'] = $row['disposition_status_id'];
                    $group_data['status_title'] = $row['status_title'];
                    $group_data['status_active_state'] = $row['active_state'];
                 
                    array_push($group_status, $group_data);                    
				}
			}
		}

		echo json_encode($group_status, TRUE);
		exit;
	}

}

if (!function_exists('getDispositionStatusHeirarchy')) {

	function getDispositionStatusHeirarchy($ids = NULL) {

		if ($ids == '') {
			echo json_encode(array(), true);
			exit;
		}

		$select = 'SELECT master_tbl.id, master_tbl.status_title, GROUP_CONCAT(slave_tbl.id) as child_status_id, GROUP_CONCAT(slave_tbl.sub_status_title) as sub_status_title '
				. ' FROM `disposition_status_substatus_master` as master_tbl '
				. ' LEFT JOIN disposition_status_substatus_master as slave_tbl ON (master_tbl.id = slave_tbl.parent_status) '
				. ' WHERE master_tbl.id IN(' . $ids . ') '
				. ' GROUP BY master_tbl.status_title';

		$result = mysql_query($select);
		$child_status = array();
		$data = array();

		if ($result) {

			if (mysql_num_rows($result) > 0) {

				while ($row = mysql_fetch_assoc($result)) {

					$id_arr = array();

					if ($row['child_status_id'] != null) {
						$id_arr = explode(',', $row['child_status_id']);
					}

					if ($row['sub_status_title'] != null) {
						$sub_status_arr = explode(',', $row['sub_status_title']);
					}

					$row['childs'] = array();

					$row['assigned'] = 1; // Default parent status is assigned 
					
					foreach ($id_arr as $key => $val) {
						$temp = array();

						$temp['id'] = $val;
						$temp['assigned'] = 1; // default child paent is assigned
						$temp['status'] = $sub_status_arr[$key];
						array_push($row['childs'], $temp);
					}
					array_push($child_status, $row);
				}

				echo json_encode($child_status, TRUE);
				exit;
			}
		}
	}

}

if (!function_exists('getEnquiryStatus')) {

	function getEnquiryStatus() {

		$select_enquiry_status = 'SELECT id, status_title FROM enquiry_status WHERE disable = 0';

		$result = mysql_query($select_enquiry_status);

		$enquiry_status_list = array();

		if ($result) {

			if (mysql_num_rows($result) > 0) {

				while ($row = mysql_fetch_object($result)) {

					array_push($enquiry_status_list, $row);
				}
			}
		}

		echo json_encode($enquiry_status_list, true);
	}

}

if (!function_exists('getEnquiryProjects')) {

	function getEnquiryProjects($enquiry_id = null, $lead_id = null, $format = 'json') {

		$where_condition = '';
		$projects = array();

		// Enquiry ID should always come 
		if ($enquiry_id !== null) {
			$where_condition .= ' WHERE enquiry_id = ' . $enquiry_id . ' ';
		} else {
			return array();
		}

		if ($lead_id != null) {

			$where_condition .= ' AND lead_number = "' . $lead_id . '"';
		}

		$select_projects = 'SELECT * FROM `lead_enquiry_projects` ' . $where_condition . '';
		
		$projects_resource = mysql_query($select_projects);

		if ($projects_resource) {

			if (mysql_num_rows($projects_resource) > 0) {

				while ($row = mysql_fetch_assoc($projects_resource)) {

					array_push($projects, $row);
				}
			}

            if($format === 'json'){
                echo json_encode($projects, true);
            }
            else{
                print_r($projects[0]); exit;
            }
		} else {
			return array();
		}
	}

}

if (!function_exists('saveEmployeeDispositionGroup')) {

	function saveEmployeeDispositionGroup($group_id = null, $emp_id = null) {

		if ($group_id == NULL) {
			echo json_encode(array('success' => -1, 'http_status_code' => 200), true);
			exit;
		}

		if ($emp_id == NULL) {
			echo json_encode(array('success' => -1, 'http_status_code' => 200, ''), true);
			exit;
		}

		
		// Get all status and sub status assigned to this group 
		$status_sub_status_json_data = json_decode(file_get_contents(BASE_URL . 'apis/helper.php?method=getDispositionGroupStatusAndSubStatusIds&params=group_id:'.$group_id),true); 
		
		$employee_assigned_disposition_status = '';
		
		if(count($status_sub_status_json_data)){
			$employee_assigned_disposition_status = mysql_real_escape_string(json_encode($status_sub_status_json_data,true));
		}
	
		$save_group = 'UPDATE `employees` '
				. ' SET disposition_group = ' . $group_id . ' , '
				. ' assigned_disposition_status_json = "'.$employee_assigned_disposition_status.'" '
				. ' WHERE id = ' . $emp_id . ' LIMIT 1';

		if (mysql_query($save_group)) {
			echo json_encode(array('success' => 1, 'http_status_code' => 200), true);
			exit;
		} else {
			echo json_encode(array('success' => 0, 'http_status_code' => 200), true);
			exit;
		}
	}

}

if (!function_exists('getEmployeeDispositionGroup')) {

	function getEmployeeDispositionGroup($employee_id = null) {

		if ($employee_id === NULL) {
			echo json_encode(array('group_id' => null), true);
			exit;
		}

		$select_group = 'SELECT disposition_group FROM employees WHERE id = ' . $employee_id . ' LIMIT 1';
		$group_id = null;

		if ($result = mysql_query($select_group)) {

			$group_id = mysql_fetch_row($result)[0];

			echo json_encode(array('group_id' => $group_id), true);
			exit;
		}
	}

}

if (!function_exists('getAdminDispositionGroup')) {

	function getAdminDispositionGroup($group_name = '', $emp_id = null) {

		$select_disposition_group = 'SELECT id , group_title FROM disposition_group '
				. 'WHERE group_title = "' . $group_name . '" LIMIT 1';

		$result = mysql_query($select_disposition_group);

		if ($result) {

			if (mysql_num_rows($result) > 0) {
				$row = mysql_fetch_row($result);

				$is_admin_assigned_disposition_group = file_get_contents(BASE_URL . 'apis/helper.php?method=is_admin_assigned_disposition_group&params=emp_id:' . $emp_id);

				if ($is_admin_assigned_disposition_group != NULL) {

					$assign = 1;
				} else {
					$assign = 0;
				}

				echo json_encode(array('id' => $row[0], 'title' => $row[1], 'assign' => $assign), true);
				exit;
			} else {
				echo json_encode(array(), true);
				exit;
			}
		} else {
			echo json_encode(array(), true);
			exit;
		}
	}

}

if (!function_exists('is_admin_assigned_disposition_group')) {

	function is_admin_assigned_disposition_group($employee_id = null) {

		$select = 'SELECT disposition_group FROM employees WHERE id = ' . $employee_id . ' LIMIT 1';

		$result = mysql_query($select);

		$row = mysql_fetch_object($result);

		echo $row->disposition_group;
	}

}

/**
 * Function to check mobile number exists in database or not for any lead 
 */
//if (!function_exists('isMobileNumberExists')) {
//	
//	function isMobileNumberExists($number = NULL) {
//
//		if ($number === null) {
//			echo json_encode(array('success' => 0), true);
//			exit;
//		}
//
//		$number_exists = 'SELECT enquiry_id, lead_id, lead_added_by_user FROM `lead` WHERE customerMobile = "' . $number . '" LIMIT 1';
//
//		$result = mysql_query($number_exists);
//
//		if ($result) {
//
//			if (mysql_num_rows($result) > 0) {
//				$data = mysql_fetch_assoc($result);
//				echo json_encode(array('success' => 1, 'data' => $data), true);
//				exit;
//			} else {
//				echo json_encode(array('success' => 0), true);
//				exit;
//			}
//		}
//	}
//	
//}

if (!function_exists('isMobileNumberExists')) {
	
	function isMobileNumberExists($number = NULL) {

		if ($number === null) {
			echo json_encode(array('success' => 0), true);
			exit;
		}

		$number_exists = 'SELECT enquiry_id, lead_id, lead_added_by_user, reassign_user_id 
        FROM `lead` WHERE customerMobile = "' . $number . '" LIMIT 1';
		
		$lead_edit_access_to_users = [];
		
		$result = mysql_query($number_exists);

		// Sr Team leader
		$sr_team_leder = getEmployeeByDesignation('sr_team_leader');
	
		if(is_array($sr_team_leder) && isset($sr_team_leder[0])){
			// sr team leader
			array_push($lead_edit_access_to_users, $sr_team_leder[0]['id']);
		}
		
		if ($result) {

			if (mysql_num_rows($result) > 0) {

				$data = mysql_fetch_assoc($result);
				
				// agent itself
				if($data['reassign_user_id'] != ''){
                    
                    $data['lead_reassinged'] = 1;
                    $data['lead_reassined_to_user'] = $data['reassign_user_id'];
					array_push($lead_edit_access_to_users, $data['reassign_user_id']);
				}else{  
                    $data['lead_reassined'] = 0;
					array_push($lead_edit_access_to_users, $data['lead_added_by_user']);
				}
				
				
				// get sr. tl leader and agent reporting person
				// $reporting_manager = mysql_query('SELECT reportingTo FROM employees WHERE id = '.$data['lead_added_by_user'].' LIMIT 1');
				
				// if($reporting_manager && mysql_num_rows($reporting_manager) > 0){
				// 	$agent_reporting_manager 	= mysql_fetch_object($reporting_manager);
				// 	$reporting_manager_id 		= $agent_reporting_manager -> reportingTo;
				// }
				
				// agent reporting manager
				// array_push($lead_edit_access_to_users, $reporting_manager_id);
				
				$data['lead_edit_access_to_users'] = $lead_edit_access_to_users;
				
				echo json_encode(array('success' => 1, 'data' => $data), true);
				exit;
			} else {
				echo json_encode(array('success' => 0), true);
				exit;
			}
		}
	}

}

if (!function_exists('getModulePermission')) {

	function getModulePermission($module_id = '', $designation_id = '') {

		if ($module_id === '') {
			return 0;
		}

		if ($designation_id === '') {
			return 0;
		}

		$module_permission = 'SELECT permission from designationmodulemaster '
				. ' WHERE  designationId = ' . $designation_id . ' AND ModuleId = ' . $module_id . ' LIMIT 1';

		$module_permission_result = mysql_query($module_permission);

		if ($module_permission_result) {

			if (mysql_num_rows($module_permission_result) > 0) {

				$data = mysql_fetch_object($module_permission_result);
				echo $data->permission;
				exit;
			}
		}
	}

}

if (!function_exists('getLeadStatus')) {

	function getLeadStatus($enquiry_id = null, $lead_id = null) {

		if ($enquiry_id == null) {

			return array();
		}

		$condition = ' WHERE enquiry_id = ' . $enquiry_id . ' ';

		if ($lead_id != null && $lead_id != 'null') {

			$condition = $condition . ' AND lead_id = "' . $lead_id . '" ';
		}
		
		$select_lead_status = 'SELECT disposition_status_id , disposition_sub_status_id, future_followup_date, future_followup_time, enquiry_status_remark, site_visit_id, meeting_id '
				. ' FROM `lead` '
				. ' ' . $condition . ' LIMIT 1';
		
		$result = mysql_query($select_lead_status);
		
		if(!$result){
			echo json_encode(array('status' => 0,'message' => 'Internal Server Error'), true); exit;
		}
		
		if ($result) {

			if (mysql_num_rows($result) > 0) {

				$row_data = mysql_fetch_assoc($result);
                
				$status_label = getStatusLabel($row_data['disposition_status_id']);
				
				$sub_status_label = getStatusLabel($row_data['disposition_sub_status_id'], 'child');
   
				// Get further details of lead status 

				$data = array();
				switch (strtolower($status_label)) {
					case 'meeting':
                        
                        $meeting_data = getLeadMeetingData($enquiry_id, $row_data['meeting_id']);
						$data['status_data'] = getLeadMeetingData($enquiry_id);
						$data['status_data']['type'] = $status_label;
						$data['status_data']['sub_type'] = $sub_status_label;
						$data['status_data']['enquiry_remark'] = $row_data['enquiry_status_remark'];
                        $data['status_data']['event_time'] = date('d-M-Y H:i A', $meeting_data['meeting_timestamp']/1000);
                        $data['status_data']['event_location'] = $meeting_data['meeting_address'];
						$data['status_data']['disposition_status_id'] =  $row_data['disposition_status_id'];
						$data['status_data']['disposition_sub_status_id'] = $row_data['disposition_sub_status_id'];
						break;

					case 'site visit':
                        
                        $site_visit_data = getSiteVisitDataById($row_data['site_visit_id']);
                        
						$data['status_data']                   = getLeadSiteVisitData($enquiry_id);
						$data['status_data']['type']           = $status_label;
						$data['status_data']['sub_type']       = $sub_status_label;
						$data['status_data']['enquiry_remark'] = $row_data['enquiry_status_remark'];
                        $data['status_data']['event_time']     = date('d-M-Y H:i A', $site_visit_data['site_visit_timestamp']/1000);
                        $data['status_data']['event_location'] = $site_visit_data['site_location'];
						$data['status_data']['disposition_status_id'] =  $row_data['disposition_status_id'];
						$data['status_data']['disposition_sub_status_id'] = $row_data['disposition_sub_status_id'];
						break;

					case 'technical issue':
						$data['status_data'] = array(
							'type' => $status_label,
							'sub_type' => $sub_status_label,
							'remark' => $row_data['enquiry_status_remark'],
							'disposition_status_id' => $row_data['disposition_status_id'],
							'disposition_sub_status_id' => $row_data['disposition_sub_status_id']
						);
						break;

					case 'callback':
						$data['status_data'] = array(
							'type' => $status_label,
							'sub_type' => $sub_status_label,
							'callback_date' => $row_data['future_followup_date'],
							'callback_time' => $row_data['future_followup_time'],
							'remark' => $row_data['enquiry_status_remark'],
							'disposition_status_id' => $row_data['disposition_status_id'],
							'disposition_sub_status_id' => $row_data['disposition_sub_status_id']
						);
						break;

					case 'not interested':
						$data['status_data'] = array(
                            'type'=> $status_label,
                            'sub_type' => $sub_status_label,
							'disposition_status_id' => $row_data['disposition_status_id'],
							'disposition_sub_status_id' => $row_data['disposition_sub_status_id'],
							'remark' => $row_data['enquiry_status_remark'],
                        );
						break;
                        
                    case 'just enquiry':
                        $data['status_data'] = array(
                            'type'=> $status_label,
                            'sub_type' => $sub_status_label,
							'disposition_status_id' => $row_data['disposition_status_id'],
							'disposition_sub_status_id' => $row_data['disposition_sub_status_id'],
							'remark' => $row_data['enquiry_status_remark'],
                        );
                        break;
					
					case 'no response':
                        $data['status_data'] = array(
                        
                            'type' => $status_label,
                            'sub_type' => $sub_status_label,
                            'remark' => $row_data['enquiry_status_remark'],
							'disposition_status_id' => $row_data['disposition_status_id'],
							'disposition_sub_status_id' => $row_data['disposition_sub_status_id']
                        );
                        break;
                        
                    case 'send mail':
                        
                        $data['status_data'] = array(
                            'type' => $status_label,
                            'sub_type' => $sub_status_label,
                            'remark' => $row_data['enquiry_status_remark'],
							'disposition_status_id' => $row_data['disposition_status_id'],
							'disposition_sub_status_id' => $row_data['disposition_sub_status_id']
                        );
                        
                        break;
                  
                    case 'reject':
                        $data['status_data'] = array(
                            'type' => $status_label,
                            'sub_type' => $sub_status_label,
                            'remark' => $row_data['enquiry_status_remark'],
							'disposition_status_id' => $row_data['disposition_status_id'],
							'disposition_sub_status_id' => $row_data['disposition_sub_status_id']
                        );
                        break;    
				};

				echo json_encode($data, true);
				exit;
			}
		}
	}

}

if(!function_exists('getDispositionGroupData')){
	
	function getDispositionGroupData($group_id = '', $employee_id= ''){
		
		// employee id is optional 
		
		if($group_id == ''){
			return '';
		}
		
		$select_group = 'SELECT group_master.group_title, group_concat( status.disposition_status_id) as status_ids '
				. ' FROM disposition_group as group_master '
				. ' LEFT JOIN disposition_group_status_master as status ON (group_master.id = status.disposition_group_id) '
				. ' WHERE group_master.id = '.$group_id.' '
				. ' GROUP BY group_master.group_title';
		
		$result = mysql_query($select_group);
		
		$group_status_data = array();
		
		if($result && mysql_num_rows($result) > 0){
			
			$group_data = mysql_fetch_assoc($result);
			
			$group_status_data ['group_name'] = $group_data['group_title'];
			
			if($group_data['status_ids'] != ''){
				$group_status_data['group_status'] = json_decode(file_get_contents(BASE_URL .'apis/helper.php?method=getDispositionStatusHeirarchy&params=ids:'.$group_data['status_ids']),true);
			}
		}
		
		
		// get assigned status and sub status 
		if($employee_id){
			
			$employee_assigned_group_status_data_json = file_get_contents(BASE_URL . 'apis/helper.php?method=getEmployeeAssignedGroupStatus&params=employee_id:'.$employee_id);
			
			// json decode encoded string 
			$employee_assigned_group_status_data_arr = json_decode($employee_assigned_group_status_data_json,true);
			
			$assigned_parents_status_array	= array();
			$assigned_childs_status_array	= array();
			
			if($employee_assigned_group_status_data_arr){
				
				foreach($employee_assigned_group_status_data_arr as $key => $val){
				
					foreach($val as $parent_id => $childs_arr){

						array_push($assigned_parents_status_array, $parent_id);
						
						foreach($childs_arr as $v){
							array_push($assigned_childs_status_array,$v);
						}
					}
				}
			}
		}

		
		if(!empty($employee_assigned_group_status_data_arr)){
			
			foreach($group_status_data['group_status'] as $key => $val){
				
				if(in_array($val['id'], $assigned_parents_status_array)){

					$group_status_data['group_status'][$key]['assigned'] = true; // Manipulating assigned key value of status 
				}
				else{
					$group_status_data['group_status'][$key]['assigned'] = false; // Manipulating assigned key value of status 
				}
						
				foreach($val['childs'] as $child_key =>$childs){

					if(in_array($childs['id'], $assigned_childs_status_array)){
						$group_status_data['group_status'][$key]['childs'][$child_key]['assigned'] = true; // Manipulating assigned key value of status 
					}else{
						$group_status_data['group_status'][$key]['childs'][$child_key]['assigned'] = false; // Manipulating assigned key value of status 
					}
				}
			}
		}
		
		// return response in json
		echo json_encode($group_status_data,true); exit;
	}
}

/**
 *  Function to fetch only ids of status and sub status assigned to disposition group 
 * @param integer $group_id Id of the disposition group 	 
 */

if(!function_exists('getDispositionGroupStatusAndSubStatusIds')){
	
	function getDispositionGroupStatusAndSubStatusIds($group_id = ''){
		
		if($group_id == ''){
			return array();
		}
		
		// Query to get records 
		$sql = 'SELECT GROUP_CONCAT(disposition_status_id) as status_ids FROM disposition_group_status_master WHERE disposition_group_id = '.$group_id.' GROUP by disposition_group_id';
		
		$result  = mysql_query($sql);
		
		$parent_status = array();
		
		if($result && mysql_num_rows($result) > 0){
			$row			= mysql_fetch_object($result);
			$parent_status	= explode(',', $row->status_ids);
		}
		
		$json_data = array();
		
		if(!empty($parent_status)){
			
			foreach($parent_status as $key => $val){
			
				$temp = array();
				
				$sql1 = 'SELECT group_concat(id) as sub_status_id, parent_status
						FROM `disposition_status_substatus_master`	
							WHERE parent_status IN ('.$val.') group by parent_status';
				
				$result1 = mysql_query($sql1);
				if($result1){
					
					$sub_status_ids = mysql_fetch_object($result1);
										
					if(is_object($sub_status_ids)){	
						$temp[$val] = explode(',', $sub_status_ids -> sub_status_id);
						array_push($json_data, $temp);
					}else{
						$temp[$val] = array();
						array_push($json_data,$temp);
					}
				} // end inner if 
			} // end foerach
		} // end outer if
		
		echo  json_encode($json_data); // reponse in simple array format 
	}
}

/**
 * 
 */

if(!function_exists('getEmployeeAssignedGroupStatus')){
	
	function getEmployeeAssignedGroupStatus($employee_id = ''){
		
		if($employee_id == ''){
			return '';
		}
		
		$status = '';
		
		$sql = 'SELECT `assigned_disposition_status_json`'
				. '  FROM employees'
				. '  WHERE id = '.$employee_id.' LIMIT 1';
		
		$result = mysql_query($sql);
		
		if($result && mysql_num_rows($result) > 0){
			
			$data = mysql_fetch_object($result);
			$status = $data -> assigned_disposition_status_json; 
		}
		
		echo $status; exit; // JSON data 
	}
}

if(!function_exists('getEmployeeNameById')){

	function getEmployeeNameById($employee_id = ''){
	
		if($employee_id == ''){
			
			return '';
		}
		
		$employee_name = getEmployeeName($employee_id);
		
		echo $employee_name; exit; 
	}
}


/**
 * To get customer info by lead number or enquiry id 
 */

if(!function_exists('getLeadCustomerInfo')){
	
	function getLeadCustomerInfo($enquiry_id = '' , $lead_number = ''){
		
		if($enquiry_id == ''){	
			return array();
		}
		
		$customer_info = array();
		$customer_info = getCLientInfoByEnquiry($enquiry_id);	
		echo json_encode($customer_info, true); exit;
	}
}

/**
 * Function to get count of notes put on an enquiry 
 */
if(!function_exists('getEnquiryNotesCount')){
	
	function getEnquiryNotesCount($enquiry_id = ''){
		
		echo getNotesCount($enquiry_id); exit; 
	}
}


/**
 * function to get current status and sub status of enquiry with title 
 */
if(!function_exists('getEnquiryActionStatus')){
	
	function getEnquiryActionStatus($enquiry_id = ''){
		
		if($enquiry_id == ''){
			
			echo json_encode(array('success' => 0),true); exit;
		}
		
		$select_enquiry_status = 'SELECT disposition_status_id as status_id , disposition_sub_status_id as sub_status_id FROM `lead` WHERE enquiry_id = '.$enquiry_id.' LIMIT 1';
		$result = mysql_query($select_enquiry_status);
		
		if($result && mysql_num_rows($result) > 0){
			
			$enquiry_status = mysql_fetch_assoc($result);
			
			$enquiry_status['status_title']		= getStatusLabel($enquiry_status['status_id'],'parent');
			$enquiry_status['sub_status_title'] = getStatusLabel($enquiry_status['sub_status_id'],'child');
			
			echo json_encode(array('success' => 1, 'data' => $enquiry_status),true); exit;
		}else{
			echo json_encode(array('success' => 0),true); exit;
		}
	}
}

/**
 * Function to set lead status as cold lead 
 */
if(!function_exists('setAsColdLead')){
	
	function setAsColdLead($enquiry_id = '', $lead_id = '', $emp_id = ''){
	
        
        // get cold call is and parent id 
        
        $cold_call  = mysql_query('SELECT id, parent_status FROM disposition_status_substatus_master WHERE status_slug = "cold_call" LIMIT 1');
        
        if($cold_call && mysql_num_rows($cold_call) > 0){
         
            $cold_call_status = mysql_fetch_object($cold_call);
            $disposition_sub_status_id = $cold_call_status -> id;
            $disposition_status_id     = $cold_call_status -> parent_status;
            
            
            if(mysql_query('UPDATE `lead` SET is_cold_call = 1, disposition_sub_status_id = '.$disposition_sub_status_id.' , disposition_status_id = '.$disposition_status_id.' WHERE enquiry_id = '.$enquiry_id.'')){
			
			$history_detail = 'Status has been changed to cold call for enquiry number '.$enquiry_id.' on '.date('l dS M, Y').' at '.date('H:i A').'';
			
            $history_data = array(
				'enquiry_id' => $enquiry_id,
				'lead_number' => ($lead_id != 'NULL' ? $lead_id : 'NULL'),
				'details' => mysql_real_escape_string($history_detail),
				'employee_id' => $emp_id,
				'type' => 'edit'
            );
                createLog($history_data);
                echo json_encode(array('success' => 1), true); exit;
            }
            else{
					echo json_encode(array('success' => 0), true); exit;
		    }
        }
	}
}

if(!function_exists('getLeadAcceptStatus')){
	
	function getLeadAcceptStatus($enquiry_id = '', $lead_id='NULL'){
		
		$where_lead_id = '';
		
		if($lead_id == 'NULL' || $lead_id === 'null' || $lead_id === ''){
			$where_lead_id = ' AND lead_id IS NULL';
		}else{
			$where_lead_id = ' AND lead_id = "'.$lead_id.'"';
		}
		
		$select_lead_accept_status = 'SELECT is_lead_accepted FROM `lead` WHERE enquiry_id = '.$enquiry_id.' '.$where_lead_id.' LIMIT 1';
		
		$result = mysql_query($select_lead_accept_status);
		
		if($result && mysql_num_rows($result) > 0){
			
			$lead_accept_status = mysql_fetch_object($result);
			echo json_encode(array('success' => 1, 'accept_status' => $lead_accept_status -> is_lead_accepted), true); exit;	
		}else{
			echo json_encode(array('success' => 1, 'accept_status' => 0), true); exit;
		}
	}
}

/**
 * Function to change the delete status of email communication templates 
 */

if(!function_exists('changeDeleteStatusOfEmailTemplate')){
	
	function changeDeleteStatusOfEmailTemplate($template_id = '', $value = 0){
	
		if($template_id === ''){
			echo 0; exit;
		}
		
		$sql = 'UPDATE `email_templates` SET is_delete = '.$value.' WHERE template_id = '.$template_id.'';
	
		if(mysql_query($sql)){
			echo 1; exit;
		}else{
			echo 0; exit;
		}
	}
}

/*
 * Function to change the delete status of message communication templates
 * 
 */
if(!function_exists('changeDeleteStatusOfMessageTemplate')){
	
	function changeDeleteStatusOfMessageTemplate($template_id = '', $value = 0){
	
		if($template_id === ''){
			echo 0; exit;
		}
		
		$sql = 'UPDATE `message_templates` SET is_delete = '.$value.' WHERE template_id = '.$template_id.'';
	
		if(mysql_query($sql)){
			echo 1; exit;
		}else{
			echo 0; exit;
		}
	}
}

/**
  |---------------------------------------------------------------------------
  |      Function to get all employees having designation of area sales       |
  |      manager and asst. area sales manager                                 | 
  |---------------------------------------------------------------------------|
 */

	if(!function_exists('getAsmEmployees')){
						
		function getAsmEmployees(){
							
			$users = $employees = $designation_ids = array();

			$designation_in = "'area_sales_manager','asst_area_sales_manager'";

			$select_designation_ids_sql = 'SELECT id, designation, designation_slug FROM `designationmaster` WHERE designation_slug IN ('.$designation_in.')';

			$result = mysql_query($select_designation_ids_sql);

			if($result && mysql_num_rows($result) > 0){

				while($row = mysql_fetch_assoc($result)){

					array_push($designation_ids,$row['id']);
					array_push($users, $row);
				}
			}
							
			if(!empty($users)){

				$current_month = (int) date('m') - 1;
				$current_year = date('Y');

				$select_employee = 'SELECT emp.id, emp.firstname, emp.lastname, emp.email, emp.designation, emp.total_capacity, '
						. ' (CASE when (cap.userId IS NOT NULL) THEN 1 ELSE 0 END) as is_capacity_assigned_this_month'
						. ' FROM employees as emp'
						. ' LEFT JOIN capacity_master as cap ON (emp.id = cap.userId AND cap.capacity_month = '.$current_month.' AND capacity_year = '.$current_year.')'
						. ' WHERE designation IN ('.implode(',',$designation_ids).')'
						. ' GROUP BY emp.id';


				$result1 = mysql_query($select_employee);

				if($result1 && mysql_num_rows($result1) > 0){

					while($employee = mysql_fetch_assoc($result1)){
						array_push($employees, $employee);
					}
				}
			}

			echo json_encode(array('success' => 1, 'users' => $users,'employees' => $employees),true); exit;
		}		
	}
					
/**
 * Function to get assigned projects to area sales manager of specific month and year 
 * @param integer $month month number
 * @param string $year year  
 */					

	if(!function_exists('get_area_sales_manager_projects')){
		
		/**
		 * 
		 * @param array $asm_user
		 * @return array
		 */
		
		function get_area_sales_manager_projects($asm_user, $month='', $year=''){
			
			$user_id	= $asm_user; 
			$projects	= array();
			$capacity_month		= '';
			$capacity_year		= '';
			
			
			if($user_id != ''){
				
				if($month == ''){
					$capacity_month = (int) date('m') - 1; // current month 
				}else{
					$capacity_month = (int) $month;
				}
				
				if($year === ''){
					$capacity_year = date('Y');
				}else{
					$capacity_year = $year;
				}
				
				$sql = 'SELECT * FROM capacity_master '
						. ' WHERE userId = '.$user_id.' AND capacity_month = '.$capacity_month.' AND capacity_year = "'.$capacity_year.'"';
				
				$result = mysql_query($sql);
				
				if($result && mysql_num_rows($result) > 0){
					
					while($row = mysql_fetch_assoc($result)){
						
						array_push($projects, $row);
					}	
				}
			}
			echo json_encode($projects, true);
		}
	}
	
	/**
	 * Fucntion to check if all area sales manager has assigned capacity or not
	 */
	
	if(!function_exists('is_all_asm_users_has_capacity')){
		
		function is_all_asm_users_has_capacity($scope = 'asm'){
			
			// default scope
            if($scope == ''){
                $scope = 'asm';
            }
            
			$current_month	= date('m') - 1;
			
			$current_year	= date('Y');
			
			// get all asm ids 
			$select_asm = 'SELECT id FROM `employees` WHERE designation IN (SELECT id FROM designationmaster WHERE designation_slug = "area_sales_manager")';
			
			$result = mysql_query($select_asm);
			
			$users = array();
			
			if($result && mysql_num_rows($result) > 0){
				
				while($row = mysql_fetch_assoc($result)){
					array_push($users, $row['id']);
				}
			}
			
			if(!empty($users)){
				
				$user_id_str = "'".implode("','", $users)."'";

				$sql = 'SELECT DISTINCT(userId) FROM capacity_master WHERE userId IN ('.$user_id_str.')'
						. ' AND capacity_month = '.$current_month.' AND capacity_year = "'.$current_year.'"';
				
				$sql_result = mysql_query($sql);
				
				$capacity_assignd_users = array();
				
				if($sql_result && mysql_num_rows($sql_result)){
					
					while($row = mysql_fetch_assoc($sql_result)){
						array_push($capacity_assignd_users, $row['userId']);
					}
				}
				
				// get the difference between all asm users and capaciy assigned asm users 
				
				$capacity_not_assigned_user = array_diff($users, $capacity_assignd_users);
                
				
                if($scope == 'sp'){
                    
                    if(count($users) == count($capacity_not_assigned_user)){
                        // none of asm has assigned this month capacities
                        // return button state as disabled
                        echo json_encode(array('success' => 1, 'btn_state' => 'disable')); exit;
                    }else{
                        echo json_encode(array('success' => 1, 'btn_state' => 'enable')); exit;
                    }
                }
                else if ($scope == 'asm'){
                    
                    if(count($capacity_not_assigned_user) > 0){
					   echo json_encode(array('success' => 1, 'btn_state' => 'enable')); exit;
                    }else{
                        // response to enable the add button
                        echo json_encode(array('success' => 1, 'btn_state' => 'disable')); exit;
                    }    
                }
                else{
                    // Response when no scope is defined
                    echo json_encode(array('success' => 1, 'btn_state' => 'enable')); exit;
                }
			}else{
				// response to enable the add button 
				echo json_encode(array('success' => 1, 'btn_state' => 'enable')); exit;
			}
		}
	}
	
	/**
	 * Function to get Maximum capacity that could be assigned to a sales person
	 */
		
	if(!function_exists('getSalesPersonMaxCapacity')){
		
		function getSalesPersonMaxCapacity($manager_id= '',$sales_person_id='', $manager_capacity=''){
			
			if($manager_id == '' || $sales_person_id == '' || $manager_capacity == ''){
				echo 0; exit;
			}
			
			$current_month	= (int) date('m') - 1;
			$current_year	= date('Y');
	
			$sql = 'SELECT SUM(capacity) as total_capacity
					FROM `sales_person_capacities`
					WHERE sales_person_id IN (SELECT id FROM employees WHERE reportingTo = '.$manager_id.' AND id NOT IN ('.$sales_person_id.')) AND month = '.$current_month.' AND year = "'.$current_year.'"';
		
			$result  = mysql_query($sql);
            
			$total_remaining_capacity = mysql_fetch_assoc($result);
			
			echo (int)$manager_capacity - (int)$total_remaining_capacity['total_capacity']; exit;
		}
	}

/**
 * Function to get details about sales person and its uper level details
 * @param <string> $user_id
 * @return array An array of data about sales person
 */	
	
if(!function_exists('get_sales_person_details')){
	
	function get_sales_person_details($user_id = ''){
		
		if($user_id === ''){
			echo ''; exit;
		}
	
        $current_month = date('m') - 1;
        $current_year  = date('Y');
        
		$sales_person_detail = array();
		
		$sql = 'SELECT emp1.id, CONCAT(emp1.firstname," ",emp1.lastname) as name, emp1.reportingTo,spc.capacity as sales_person_capacity , CONCAT(emp2.firstname," ", emp2.lastname) as manager_name, emp2.total_capacity as manager_capacity'
				. ' FROM employees as emp1 '
				. ' LEFT JOIN employees as emp2 ON (emp1.reportingTo = emp2.id)'
				. ' LEFT JOIN sales_person_capacities as spc ON (emp1.id = spc.sales_person_id)'
				. ' WHERE emp1.id = '.$user_id.' AND month = '.$current_month.' AND year = "'.$current_year.'" LIMIT 1';
        
        
		$result = mysql_query($sql);
		
		if($result && mysql_num_rows($result) > 0){
			
			$sales_person_detail = mysql_fetch_assoc($result);
		}
		
		echo json_encode($sales_person_detail, true); exit;
	}
}	
	
/**
 * Function to get ASM MPL lead type capacity 
 * 
 */

if(!function_exists('get_mpl_capacity')){
	
	function get_mpl_capacity($user_id = ''){
		
		$response = array('success' => '', 'capacity' => '');
		
		if($user_id == ''){
			
			$response['success']	= 0;
			$response['capacity']	= 0;
		}
		
		$get_capacity = 'SELECT `capacity` FROM `mpl_capacity` WHERE user_id = '.$user_id.' LIMIT 1';
		
		$result = mysql_query($get_capacity);
		
		if($result && mysql_num_rows($result) > 0){
			$response['success']	= 1;
			$response['capacity']	= mysql_fetch_object($result) -> capacity;
			
		}else{
			$response['success']	= 1;
			$response['capacity']	= 0;
		}
		
		echo json_encode($response, true); exit;
	}
}

if( !function_exists('getSiteVisitProject')){
	
	function getSiteVisitProject($enquiry_id = ''){
		
		if($enquiry_id === ''){
			echo json_encode(array('success' => 0), TRUE); exit;
		}
		
		// find project 
		
		$query = 'SELECT `project_id`, `project_name`, `project_url`, `project_city` FROM `lead_enquiry_projects` WHERE enquiry_id = '.$enquiry_id.'';
		
		$result = mysql_query( $query );
		
		$projects = array();
		
		if($result && mysql_num_rows($result) > 0){
		
			while($row = mysql_fetch_assoc($result)){
				array_push($projects, $row);
			}
		}
		
		echo json_encode(array('success' => 1, 'projects' => $projects), true); exit;
		
	}
	
}


/**
 * Function to get sub status of an enuiry status 
 */

if( !function_exists('get_enquiry_sub_status')){
	
	function get_enquiry_sub_status($enquiry_status_id = '', $enquiry_sub_status_id = ''){
		
		if($enquiry_status_id === ''){
			echo json_encode(array('success' => 1,'sub_status' => ''), true); exit;
		}

		$query = 'SELECT * FROM `disposition_status_substatus_master` WHERE id = '.$enquiry_sub_status_id.' AND parent_status = '.$enquiry_status_id.' LIMIT 1';

		$result = mysql_query($query);

		if($result && mysql_num_rows($result) > 0){

			$data = mysql_fetch_assoc($result);

			echo json_encode(array('success' => 1, 'sub_status' => $data), true); exit;

		}
		else{
			echo json_encode(array('success' => 1,'sub_status' => ''), true); exit;
		}
	}
}

if( !function_exists('getProfilePicture')){
	
	function getProfilePicture($user_id = ''){
		
		if($user_id === ''){
			echo ''; exit;
		}
		
		$get_image = 'SELECT profile_image FROM employees WHERE id = '.$user_id.' LIMIT 1';
		
		$result = mysql_query($get_image);
		
		if($result && mysql_num_rows($result) > 0){
			
			$data = mysql_fetch_object($result);
		
			echo 'data:image/jpeg;base64,'.base64_encode( $data -> profile_image );
		}else{
			echo ''; exit;
		}
	}
}

if( !function_exists('getCRMUsersByDesignation')){
	
	function getCRMUsersByDesignation($designation_slug = ''){
		
		$users = getEmployeeByDesignation($designation_slug);
		echo json_encode(array("success" => 1, 'data' => $users), true); exit;
	}
}

if( !function_exists('getLeadMeetingID')){
	function getLeadMeetingID($enquiry_id = '', $id_type = 'meeting'){
		
		if($enquiry_id == ''){
			echo ''; exit;
 		}
		
		$query = 'SELECT meeting_id, site_visit_id FROM lead WHERE enquiry_id = '.$enquiry_id.' LIMIT 1';
		
		$result = mysql_query($query);
		
		if($result && mysql_num_rows($result) > 0){
			
			$data = mysql_fetch_object($result);
			
            if($id_type === 'meeting'){
                echo $data -> meeting_id; exit;    
            }
			else if ($id_type === 'site_visit'){
                echo $data -> site_visit_id; exit;
            }
            else{
                echo json_encode(array('meeting_id' => $data -> meeting_id, 'site_visit_id' => $data ->site_visit_id ), true);
            }
            
		}else{
			echo ''; exit;
		}
	}
}

if(!function_exists('get_sales_person_manager_details')){
	
	function get_sales_person_manager_details($user_id = ''){
		
		if($user_id === ''){
			echo ''; exit;
		}
		
		$manager_detail = array();
		
		$sql = 'SELECT 
					emp.reportingTo as manager_id,
					manager_emp.total_capacity as manager_capacity,
				CONCAT(manager_emp.firstname," ",manager_emp.lastname) as manager_name
				FROM employees as emp 
				LEFT JOIN employees as manager_emp ON (emp.reportingTo = manager_emp.id)
				WHERE emp.id = '.$user_id.' LIMIT 1';
		
		$result = mysql_query($sql);
		
		if($result && mysql_num_rows($result) > 0){
			
			$manager_detail = mysql_fetch_assoc($result);
			
		}
		
		echo json_encode($manager_detail, true); exit;
	}
}	
	
if(!function_exists('is_all_sp_user_has_capacity_assigned')){

    function is_all_sp_user_has_capacity_assigned (){
        
        $current_month	= date('m') - 1;
			
        $current_year	= date('Y');
			
        // get all asm ids 
        $select_asm = 'SELECT id FROM `employees` WHERE designation IN (SELECT id FROM designationmaster WHERE designation_slug = "sales_person")';
			
        $result = mysql_query($select_asm);
			
        $users = array();
			
        if($result && mysql_num_rows($result) > 0){
				
            while($row = mysql_fetch_assoc($result)){
                array_push($users, $row['id']);
            }
        }
        
        $capacity_assigned_users = array();
        
        if(count($users) > 0){
            
            $user_id_str = "'".implode("','", $users)."'";

            $sql = 'SELECT DISTINCT(sales_person_id) FROM sales_person_capacities WHERE sales_person_id IN ('.$user_id_str.')'
						. ' AND month = '.$current_month.' AND year = "'.$current_year.'"';
				
            $sql_result = mysql_query($sql);
				
            if($sql_result && mysql_num_rows($sql_result)){
					
				while($row = mysql_fetch_assoc($sql_result)){
				    array_push($capacity_assigned_users, $row['sales_person_id']);
				}
            }
        }
        
        // return the list of user ids 
            
        echo json_encode( array('success' => 1, 'data' => $capacity_assigned_users), true); exit;
        //echo json_encode( array('success' => 1, 'data' => $users), true); exit;
    }
}

/**----------------------------------------------------------

Helper function to check email id exists or not
------------------------------------------------------------*/
if(!function_exists('isEmailExists')){
    
    function isEmailExists($email = '') {
        
        $is_email_exists = false;
        
        if($email === ''){
            $is_email_exists = false;
        }
        
        $data = getEmployeeByEmail($email);
        
        if($data != ''){
            $is_email_exists = true;    
        }
        else{
            $is_email_exists = false;
        }
        
        // send response
        echo json_encode(array('success' => 1, 'is_email_exists' => $is_email_exists), true); exit;
    }
}


if(!function_exists('isEmployeeMobileNumberExists')){
    
    function isEmployeeMobileNumberExists($mobile_number = ''){
        
        if($mobile_number === ''){
            echo json_encode(array('is_exists' => 0), true); exit;       
        }
    
        $query = 'SELECT id FROM employees WHERE contactNumber = "'.$mobile_number.'" AND isDelete = 0 LIMIT 1';

        $result = mysql_query($query);
        
        if($result && mysql_num_rows($result) > 0){
             echo json_encode(array('is_exists' => 1), true); exit;
        }
        else {
             echo json_encode(array('is_exists' => 0), true); exit;
        }
    }
}

if(!function_exists('isLeadClosed')){
    
    function isLeadClosed($enquiry_id = ''){
        
        if($enquiry_id == ''){
            return false;
        }
        
        $query = 'SELECT `lead_closure_date` FROM lead WHERE enquiry_id = '.$enquiry_id.' LIMIT 1';
        
        $result = mysql_query($query);
        
        if($result && mysql_num_rows($result) > 0){
            
            $data = mysql_fetch_object($result);
            
            if($data -> lead_closure_date != ''){
                echo 'closed'; exit;
            }else{
                echo 'open'; exit;
            }
        }else{
            echo 'open'; exit;
        }
    }   
}

if(!function_exists('getAllEnquiryRemarks')){
    
    function getAllEnquiryRemarks($enquiry_id = ''){
     
        
        $enquiry_remarks = mysql_query('SELECT * FROM enquiry_remarks_log WHERE enquiry_id = '. $enquiry_id . ' ORDER BY remark_creation_date DESC');
        
        $remarks = array();
        
        if($enquiry_remarks && mysql_num_rows($enquiry_remarks) > 0){
            
            while($row = mysql_fetch_assoc($enquiry_remarks)){
                
                $row['employee_name'] = getEmployeeName($row['employee_id']);
                
                array_push($remarks, $row);
            }
        }
        
        echo json_encode($remarks,true);
    }
}

// function to set callback done status
if(!function_exists('setCallbackDone')){
	function setCallbackDone($enquiry_id = ''){

		if($enquiry_id == '')
		{
			echo 0; exit;
		}

		// Update callback done status 
		mysql_query('UPDATE lead SET is_callback_done = 1 WHERE enquiry_id = '.$enquiry_id.' LIMIT 1');
		mysql_query('UPDATE future_references SET is_callback_done = 1 WHERE enquiry_id = '.$enquiry_id.' LIMIT 1');

		echo 1; exit;
	}
}



if(!function_exists('changeDispositionState')){
    function changeDispositionState($disposition_id= '', $state= ''){
        
        if($disposition_id == ''){
            return '';    
        }
        
        if($state == ''){
            return '';
        }
        
        $query = 'UPDATE `disposition_status_substatus_master` SET `active_state` = '.$state.' WHERE id = '.$disposition_id.' LIMIT 1 ';
        if(mysql_query($query)){
            echo 1;
        }else{
            echo 0;
        }
    }
}

if(!function_exists('changeDispositionActivityStatus')){
    
    function changeDispositionActivityStatus($disposition_id = '', $status = ''){
            if($disposition_id == ''){
                return '';    
            }
            
            if($status == ''){
                return '';
            }
        
            $query = 'UPDATE `disposition_status_substatus_master` SET `is_activity_status` = '.$status.' WHERE id = '.$disposition_id.' LIMIT 1 ';
            if(mysql_query($query)){
                    echo 1;
            }else{
                    echo 0;
            }
            
    }
}


// Function to get Enquiry Status like hot warm cold
if(!function_exists('getEnquiryHWCStatus')){
    
    function getEnquiryHWCStatus($enquiry_number = ''){
        
        if($enquiry_number == ''){
            echo ''; exit;
        }
        
        $status = mysql_query('SELECT leadStatus FROM lead WHERE enquiry_id = ' . $enquiry_number . ' LIMIT 1');
        
        $data = mysql_fetch_row($status);
        
        echo $data[0]; // Status 

    }
}

if(!function_exists('validateDate')){
    
    function validateDate($d1 = '', $d2 = ''){
    
        
        if(stripos($d1,'/') >=0 && stripos($d1,'/') !== FALSE){
            echo json_encode(array('success' => 0, 'error' => 'Please enter start date in preffered format')); exit;
        }
        
        if(stripos($d2,'/') >=0 && stripos($d2,'/') !== FALSE){
            echo json_encode(array('success' => 0, 'error' => 'Please enter end date in preffered format')); exit;
        }
        
        
        $d1_timestamp = strtotime($d1);
        $d2_timestamp = strtotime($d2);
        
        $base_format = 'Y-m-d';
        $result_d1 = date($base_format, strtotime($d1));
        $result_d2 = date($base_format, strtotime($d2));
        
        if(stripos($result_d1,'1970') >= 0 && stripos($result_d1, '1970') !== FALSE){
            echo json_encode(array('success' => 0, 'error' => 'Invalid start date passed')); exit;
        }
        
        
        if(stripos($result_d2,'1970') >= 0 && stripos($result_d2, '1970') !== FALSE){
            echo json_encode(array('success' => 0, 'error' => 'Invalid end date passed')); exit;
        }
        
        if($d1_timestamp > $d2_timestamp){
            echo json_encode(array('success' => 0, 'error' => 'Start date must be shorter then end date')); exit;
        }
        
        echo json_encode(array('success' => 1,'start_date' => $result_d1,'end_date' => $result_d2)); exit; 
        
    }
}

// Call methods defined 
call_user_func_array($method, $args);
