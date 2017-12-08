<?php 
session_start();

require 'function.php';

$_get_data = filter_input_array(INPUT_GET);

$employee_id = '';

// user is mandatory to access this API

if ( isset ($_get_data['user_id']) && !empty($_get_data['user_id']) ){
	$employee_id = $_get_data['user_id'];
}

// Fetch closed leads according to user 

// if user is agent or executive then fetch only those closed leads which were added by them.

// if user is sales person then fetch only those leads which is closed by them 

// if user is asm then fetch leads which are closed by their sales persons or itself 

// if user is TL CRM(sr. team leader) then fetch all leads which is closed 

// if user is team leader then fetch all leads which is cloded by him or his reportees

// if user is admin then fetch all leads which is closed

// if user is Director Sales then fetch all leads which is closed.

// if user is Head Customer Support then fetch all leads which is closed

$user_role = '';

$user_role = getEmployeeDesignation($employee_id);

$designation_slug	= ( isset($user_role[1]) ? $user_role[1] : '');
$designation		= ( isset($user_role[0]) ? $user_role[0]: '');


switch($designation_slug){
	
	case 'sales_person':
		
		$query = 'SELECT * FROM lead WHERE lead_closure_date IS NOT NULL AND lead_closed_by = '.$employee_id.'';
		break;
	
	case 'area_sales_manager':
		
		$direct_reporting_persons = getDirectReportings($employee_id);
		array_push($direct_reporting_persons, $employee_id);
		$ids = implode("','", $direct_reporting_persons);
		$query = "SELECT * FROM `lead` WHERE lead_closure_date IS NOT NULL AND `lead_closed_by` IN ('".$ids."')";
		break;
	
    case 'admin':
        $query = 'SELECT * FROM lead WHERE lead_closure_date IS NOT NULL';
        break;
           
    case 'agent':
        $query = 'SELECT * FROM lead WHERE lead_closure_date IS NOT NULL AND lead_added_by_user = '.$employee_id.'';
        break;
        
    case 'executive':
        $query = 'SELECT * FROM lead WHERE lead_closure_date IS NOT NULL AND lead_added_by_user = '.$employee_id.'';
        break;    
        
    case 'sr_team_leader':
        $query = 'SELECT * FROM lead WHERE lead_closure_date IS NOT NULL';
        break;
    
    case 'team_leader':
        
        $direct_reporting_persons = getDirectReportings($employee_id);
		array_push($direct_reporting_persons, $employee_id);
		$ids = implode("','", $direct_reporting_persons);
		$query = "SELECT * FROM `lead` WHERE lead_closure_date IS NOT NULL AND `lead_closed_by` IN ('".$ids."')";
        break;
        
    case 'head_customer_support':
        $query = 'SELECT * FROM lead WHERE lead_closure_date IS NOT NULL';
        break;
        
    case 'director_sales':
         $query = 'SELECT * FROM lead WHERE lead_closure_date IS NOT NULL';
        break;
	default :	
}

	$result = mysql_query($query);	
	
	$lead_data = array ();
	
	if($result && mysql_num_rows($result) > 0){
		
		while($row = mysql_fetch_assoc($result)){
            
            $lead_closure_date = strtotime($row['lead_closure_date'])*1000;
            $row['lead_closure_timestamp'] = $lead_closure_date;
            $row['lead_closed_by_employee'] = getEmployeeName($row['lead_closed_by']);
			array_push($lead_data, $row);
		}
	}

	echo json_encode(array(
		'success' => 1, 'data' => $lead_data
	), true);