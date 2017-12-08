<?php
session_start();
require_once 'function.php';

$post = json_decode(file_get_contents('php://input'),true);

$enquiry_id = '';

$lead_detail = array();

if(!empty($post)){

	$enquiry_id		= $post['enquiry_id'];

	$select_lead		= 'SELECT '
				. ' leadPrimarySource, leadSecondarySource, lead_added_by_user, reassign_user_id,'
				. ' disposition_status_id, disposition_sub_status_id, future_followup_date, '
				. '	last_followup_date, future_followup_time, leadAddDate, enquiry_status_remark,'
				. ' lead_assigned_to_asm, lead_assigned_to_sp, meeting_id, site_visit_id, lead_closure_date,lead.client_property_preferences'
				. ' FROM lead as lead'
				. ' WHERE lead.enquiry_id = '.$enquiry_id.' ';
    
	$result = mysql_query($select_lead);
	
	if($result && mysql_num_rows($result) > 0){
		
		$lead_detail = mysql_fetch_assoc($result);
		
		$lead_detail['lead_owner'] = getEmployeeName($lead_detail['lead_added_by_user']);
		
		$lead_detail['assign_to'] = getEmployeeName($lead_detail['reassign_user_id']);
		
		$lead_detail['lead_assigned_to_asm'] = getEmployeeName($lead_detail['lead_assigned_to_asm']);
		
		$lead_detail['lead_assigned_to_sp'] = getEmployeeName($lead_detail['lead_assigned_to_sp']);
	
		$lead_detail['primary_campaign_source_text'] = getCampaignText($lead_detail['leadPrimarySource']);
		
		$lead_detail['primary_disposition_status_text'] = getStatusLabel($lead_detail['disposition_status_id'],'parent');
		
		$lead_detail['secondary_disposition_status_text'] = getStatusLabel($lead_detail['disposition_sub_status_id'],'child');
		
        if($lead_detail['lead_closure_date'] != ''){
            
            $lead_close_date = date('d-m-Y', strtotime($lead_detail['lead_closure_date']));
            unset($lead_detail['lead_closure_date']);
            $lead_detail['lead_closure_date'] = $lead_close_date;
        }
        
		// get future followup dates 
		// check primary and secondary status and according to that get followup dates 
		
		switch(str_replace(' ','_',strtolower($lead_detail['primary_disposition_status_text']))){
			
			case 'site_visit':
				$lead_detail['status_slug'] = 'site_visit';
                $lead_detail['event_timestamp'] = getEventTime('site_visit',$lead_detail['site_visit_id']);
				$lead_detail['site_visit']      = getLeadSiteVisitData($enquiry_id , $lead_detail['site_visit_id']);
				break;
		
			case 'meeting':
				$lead_detail['status_slug'] = 'meeting';
                $lead_detail['event_timestamp'] = getEventTime('meeting',$lead_detail['meeting_id']);
				$lead_detail['meeting']         = getLeadMeetingData($enquiry_id, $lead_detail['meeting_id']);
				break;
		}
	}
}

echo json_encode($lead_detail,true);

