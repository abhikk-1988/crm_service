<?php
session_start();
require_once 'function.php';

$user = $_SESSION['currentUser'];

$post = json_decode(file_get_contents('php://input'),true);

if(!empty($post) && isset($post['enquiry_id'])){

   $enquiry_id = $post['enquiry_id'];

   $projects = $post['projects'];

   // Total projects selected 
   $total_projects_selected = count($projects);

   $lead_number = getLeadNumber($enquiry_id);

   if($lead_number == ''){
       $lead_number = "NULL";
   }

   $projet_name_log = array();
   $client_pref_log = array();

   foreach($projects as $p)
   {
        // For project name log purpose
        array_push($projet_name_log, $p['project_name']);

        $project_city = get_project_city($p['project_id']);    
        mysql_query('INSERT INTO lead_enquiry_projects SET enquiry_id = '.$enquiry_id.' , lead_number = "'.$lead_number.'" , project_id = "'.$p['project_id'].'", project_name = "'.$p['project_name'].'" , project_url = "'.$p['project_url'].'", project_city = "'.$project_city.'"');
   }

   $client_pref = array();

   // Save client preferences 
   if(isset($post['filters'])){

    // We have to capture filters for keeping track of user's last selected preferences for any project 
    
    $budget_string = '';
    foreach($post['filters']['budget'] as $key => $val)
    {
        if($key == 'min_label' && $val != ''){
            $budget_string .= $val;
        }

        if($key == 'max_label' && $val != ''){
            $budget_string .= ' - '.$val;
        }
    }

    if($budget_string != ''){

        array_push($client_pref_log, $budget_string);

        $client_pref['client_property_preferences']['budget'] = array();
        array_push($client_pref['client_property_preferences']['budget'], $budget_string);
    }

    // BHK Filter 
    if(isset($post['filters']['bhk'])){
        
        $client_pref['client_property_preferences']['bhk'] = array();

        foreach($post['filters']['bhk'] as $key => $val)
        {
            // $Key will be numeric indexes 
            // $val will be an array 
            // We have to put the label of BHK filter in array
            array_push($client_pref['client_property_preferences']['bhk'], $val['label']);

            array_push($client_pref_log, $val['label']);
        }

        // To remove bhk key if empty
        if(empty($client_pref['client_property_preferences']['bhk'])){
            unset($client_pref['client_property_preferences']['bhk']);
        }
    }

    // Property Status Filter
    if(isset($post['filters']['property_status'])){
        
        $client_pref['client_property_preferences']['property_status'] = array();

        foreach($post['filters']['property_status'] as $key => $val)
        {
            // $Key will be numeric indexes 
            // $val will be an array 
            // We have to put the label of Property Status filter in array
            array_push($client_pref['client_property_preferences']['property_status'], $val['label']);

            array_push($client_pref_log, $val['label']);
        }

        // To remove property_status key if empty
        if(empty($client_pref['client_property_preferences']['property_status'])){
            unset($client_pref['client_property_preferences']['property_status']);
        }
    }

    // Property Type Filter
    if(isset($post['filters']['property_types'])){
        
        $client_pref['client_property_preferences']['property_types'] = array();

        foreach($post['filters']['property_types'] as $key => $val)
        {
            // $Key will be numeric indexes 
            // $val will be an array 
            // We have to put the label of Property Types filter in array
            array_push($client_pref['client_property_preferences']['property_types'], $val['label']);

            array_push($client_pref_log, $val['label']);
        }

        // To remove property_types key if empty
        if(empty($client_pref['client_property_preferences']['property_types'])){
            unset($client_pref['client_property_preferences']['property_types']);
        }
    }
}

    if(!empty($client_pref['client_property_preferences'])){

         $filter_json = json_encode($client_pref['client_property_preferences'],true);

         // Update client preference
         mysql_query('UPDATE lead SET client_property_preferences = "'.mysql_real_escape_string($filter_json).'" WHERE enquiry_id = '.$enquiry_id.'');
    }


   // updating lead category for projects
   if($total_projects_selected > 1){
        mysql_query('UPDATE lead SET lead_category = "MPL" WHERE enquiry_id = '.$enquiry_id.' LIMIT 1');
   }
   else{
       mysql_query('UPDATE lead SET lead_category = "SPL" WHERE enquiry_id = '.$enquiry_id.' LIMIT 1');
   }

   // Update change log
   
   $like_client_preference = ( !empty($client_pref_log) ? ' and client Preference Like '. implode(',',$client_pref_log) : '');

   $log_text = 'Lead has been update for project ('.implode(',', $projet_name_log).') '.$like_client_preference.'  on '. date('d/m/Y H:i A') .' by '. $user['firstname'].' '.$user['lastname'];

   $log = array(
       'details' => $log_text,
       'enquiry_id' => $enquiry_id,
       'lead_number' => $lead_number,
       'employee_id' => $user['id'],
       'type' => 'edit' 
   );
   
   createLog($log);

   echo json_encode(array('success' => (int) 1),true); 
}else{
    echo json_encode(array('success' => (int) 0),true);
}

