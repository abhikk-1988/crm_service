<?php
    ini_set('display_errors', 1);
    define("HOST", "localhost"); //Define a hostname
    define("USER", "root");      //Define a username
    define("PASSWORD", "bmhproduction@123!");		//Define a password
    define("DB", "bmh_crm");  //Define a database

    $con = mysqli_connect(HOST, USER, PASSWORD, DB) OR DIE("Error in  connecting DB : " . mysqli_connect_error());  //Database connection

    if((!empty($_GET))) {	// Check whether the date is empty	
        if(isset($_GET['startDate'])){		
            $startDate = date('Y-m-d',strtotime($_GET['startDate']));
        }
        if(isset($_GET['endDate'])){
            $endDate = date('Y-m-d',strtotime($_GET['endDate']));
        }
        if(isset($_GET['updatedDate'])){
            $updateDate = date('Y-m-d',strtotime($_GET['updatedDate']));
        } 
        if(isset($startDate) && isset($endDate)){
            //            Old query
            //$query = "SELECT a.lead_id, a.enquiry_id, a.customerMobile, a.customerEmail, a.customerName, a.customerProfession, a.customer_gender, a.customerState, a.customerCity, a.customerAddress, a.customerRemark, a.leadPrimarySource, s.source as primary_campaign_source, a.leadSecondarySource as secondary_campaign_source, a.lead_added_by_user, a.leadAddDate, a.lead_assigned_to_asm, a.lead_assigned_to_asm_on, a.lead_assigned_to_sp, a.lead_assigned_to_sp_on, a.disposition_status_id, a.disposition_sub_status_id, a.enquiry_status_remark, b.firstname AS lead_added_by_agent_name, c.status_title AS disposition_title, c1.sub_status_title AS disposition_sub_status_title, b1.firstname as assigned_to_asm, b2.firstname as assigned_to_sp, GROUP_CONCAT(p.project_name) as enquiry_projects
//            FROM lead AS a
//            LEFT JOIN employees AS b ON ( a.lead_added_by_user = b.id ) 
//            LEFT JOIN employees AS b1 ON ( a.lead_assigned_to_asm = b1.id ) 
//            LEFT JOIN employees AS b2 ON ( a.lead_assigned_to_sp = b2.id ) 
//
//            LEFT JOIN disposition_status_substatus_master AS c ON ( a.disposition_status_id = c.id) 
//            LEFT JOIN disposition_status_substatus_master AS c1 ON (a.disposition_sub_status_id = c1.id ) 
//            LEFT JOIN lead_enquiry_projects as p ON (a.enquiry_id = p.enquiry_id)
//            LEFT JOIN leadsourcemaster as s ON (a.leadPrimarySource = s.id)
//            WHERE (
//            a.disposition_status_id =6
//            OR a.disposition_status_id
//            OR 3
//            )
//            AND a.leadAddDate between '$startDate' AND '$endDate'
//            GROUP BY a.enquiry_id";

            //       New Query   
          // $query = "SELECT a.lead_id, a.enquiry_id, a.customerMobile, a.customerEmail, a.customerName, a.customerProfession, a.customer_gender, a.customerState, a.customerCity, a.customerAddress, a.customerRemark, a.leadPrimarySource, s.source as primary_campaign_source, a.leadSecondarySource as secondary_campaign_source, a.lead_added_by_user, a.leadAddDate,a.leadUpdateDate,a.lead_assigned_to_asm, a.lead_assigned_to_asm_on, a.lead_assigned_to_sp, a.lead_assigned_to_sp_on, a.disposition_status_id, a.disposition_sub_status_id, a.enquiry_status_remark, CONCAT(b.firstname,' ',b.lastname) AS lead_added_by_agent_name, c.status_title AS disposition_title, c1.sub_status_title AS disposition_sub_status_title, CONCAT(b1.firstname,' ',b1.lastname) as assigned_to_asm, CONCAT(b2.firstname,' ',b2.lastname) as assigned_to_sp, GROUP_CONCAT(p.project_name) as enquiry_projects
//            FROM lead AS a
//            LEFT JOIN employees AS b ON ( a.lead_added_by_user = b.id ) 
//            LEFT JOIN employees AS b1 ON ( a.lead_assigned_to_asm = b1.id ) 
//            LEFT JOIN employees AS b2 ON ( a.lead_assigned_to_sp = b2.id ) 
//
//            LEFT JOIN disposition_status_substatus_master AS c ON ( a.disposition_status_id = c.id) 
//            LEFT JOIN disposition_status_substatus_master AS c1 ON (a.disposition_sub_status_id = c1.id ) 
//            LEFT JOIN lead_enquiry_projects as p ON (a.enquiry_id = p.enquiry_id)
//            LEFT JOIN leadsourcemaster as s ON (a.leadPrimarySource = s.id)
//            WHERE
//            a.leadAddDate >=  '$startDate' OR a.leadUpdateDate LIKE '%$endDate%' 
//            GROUP BY a.enquiry_id";
          
          
//          Updated On 24/04/2017
          
           $query = "SELECT a.enquiry_id,DATE(a.leadAddDate) as lead_Add_Date,TIME(a.leadAddDate) as lead_Add_Time,DATE(a.leadUpdateDate) as lead_update_date,TIME(a.leadUpdateDate) as lead_update_time,CONCAT(b.firstname,' ',b.lastname) AS lead_added_by_agent_name,a.customerName,a.customerMobile,a.customer_alternate_mobile,a.customerProfession,GROUP_CONCAT(p.project_name) as enquiry_projects,a.customerCity, a.customerAddress,a.customerEmail,CONCAT(b1.firstname,' ',b1.lastname) as assigned_to_asm, CONCAT(b2.firstname,' ',b2.lastname) as assigned_to_sp,c.status_title AS disposition_title, c1.sub_status_title AS disposition_sub_status_title, a.leadPrimarySource, a.leadSecondarySource as secondary_campaign_source FROM lead AS a LEFT JOIN employees AS b ON ( a.lead_added_by_user = b.id ) LEFT JOIN employees AS b1 ON ( a.lead_assigned_to_asm = b1.id ) LEFT JOIN employees AS b2 ON ( a.lead_assigned_to_sp = b2.id ) LEFT JOIN disposition_status_substatus_master AS c ON ( a.disposition_status_id = c.id) LEFT JOIN disposition_status_substatus_master AS c1 ON (a.disposition_sub_status_id = c1.id ) LEFT JOIN lead_enquiry_projects as p ON (a.enquiry_id = p.enquiry_id) LEFT JOIN leadsourcemaster as s ON (a.leadPrimarySource = s.id) WHERE a.leadAddDate between '$startDate' AND '$endDate' OR a.leadUpdateDate LIKE '%$endDate%' GROUP BY a.enquiry_id";
          
            
            $header = [];
            $header[] = 'Enquiry Id';
            $header[] = 'Lead Add Date';
            $header[] = 'Lead Add Time';
            $header[] = 'Lead Update Date';
            $header[] = 'Lead Update Time';
            $header[] = 'CRM';
            $header[] = 'Customer';
            $header[] = 'Mobile No.';
            $header[] = 'Alternate Contact';
            $header[] = 'Profession';
            $header[] = 'Project';
            $header[] = 'City';
            $header[] = 'Address';
            $header[] = 'Email ID';
            $header[] = 'TM Name';
            $header[] = 'Sales Manager';
            $header[] = 'Site Visit';
            $header[] = 'Site Visit Status';  
            $header[] = 'Lead Source';
            $header[] = ' Lead Secondary Source';
            
        }

        if(isset($updateDate)){
            //        Old Query    
            // $query = "SELECT a.lead_id, a.enquiry_id, a.customerMobile, a.customerEmail, a.customerName, a.customerProfession, a.customer_gender, a.customerState, a.customerCity, a.customerAddress, a.customerRemark, a.leadPrimarySource, s.source as primary_campaign_source, a.leadSecondarySource as secondary_campaign_source, a.lead_added_by_user, a.leadAddDate,a.leadUpdateDate,a.lead_assigned_to_asm, a.lead_assigned_to_asm_on, a.lead_assigned_to_sp, a.lead_assigned_to_sp_on, a.disposition_status_id, a.disposition_sub_status_id, a.enquiry_status_remark, CONCAT(b.firstname,'',b.lastname) AS lead_added_by_agent_name, c.status_title AS disposition_title, c1.sub_status_title AS disposition_sub_status_title, CONCAT(b1.firstname,'',b1.lastname) as assigned_to_asm, CONCAT(b2.firstname,'',b2.lastname) as assigned_to_sp, GROUP_CONCAT(p.project_name) as enquiry_projects
            //            FROM lead AS a
            //            LEFT JOIN employees AS b ON ( a.lead_added_by_user = b.id ) 
            //            LEFT JOIN employees AS b1 ON ( a.lead_assigned_to_asm = b1.id ) 
            //            LEFT JOIN employees AS b2 ON ( a.lead_assigned_to_sp = b2.id ) 
            //
            //            LEFT JOIN disposition_status_substatus_master AS c ON ( a.disposition_status_id = c.id) 
            //            LEFT JOIN disposition_status_substatus_master AS c1 ON (a.disposition_sub_status_id = c1.id ) 
            //            LEFT JOIN lead_enquiry_projects as p ON (a.enquiry_id = p.enquiry_id)
            //            LEFT JOIN leadsourcemaster as s ON (a.leadPrimarySource = s.id)
            //            WHERE (
            //            a.disposition_status_id =6
            //            OR a.disposition_status_id
            //            OR 3
            //            )
            //            AND a.leadAddDate >=  '$updateDate' and a.leadUpdateDate >= '$updateDate'
            //            GROUP BY a.enquiry_id";

            //        Updated Query on 17-april-2017   
            //$query = "SELECT a.lead_id, a.enquiry_id, a.customerMobile, a.customerEmail, a.customerName, a.customerProfession, a.customer_gender, a.customerState, a.customerCity, a.customerAddress, a.customerRemark, a.leadPrimarySource, s.source as primary_campaign_source, a.leadSecondarySource as secondary_campaign_source, a.lead_added_by_user, a.leadAddDate,a.leadUpdateDate,a.lead_assigned_to_asm, a.lead_assigned_to_asm_on, a.lead_assigned_to_sp, a.lead_assigned_to_sp_on, a.disposition_status_id, a.disposition_sub_status_id, a.enquiry_status_remark, CONCAT(b.firstname,' ',b.lastname) AS lead_added_by_agent_name, c.status_title AS disposition_title, c1.sub_status_title AS disposition_sub_status_title, CONCAT(b1.firstname,' ',b1.lastname) as assigned_to_asm, CONCAT(b2.firstname,' ',b2.lastname) as assigned_to_sp, GROUP_CONCAT(p.project_name) as enquiry_projects
            //                FROM lead AS a
            //                LEFT JOIN employees AS b ON ( a.lead_added_by_user = b.id ) 
            //                LEFT JOIN employees AS b1 ON ( a.lead_assigned_to_asm = b1.id ) 
            //                LEFT JOIN employees AS b2 ON ( a.lead_assigned_to_sp = b2.id ) 
            //
            //                LEFT JOIN disposition_status_substatus_master AS c ON ( a.disposition_status_id = c.id) 
            //                LEFT JOIN disposition_status_substatus_master AS c1 ON (a.disposition_sub_status_id = c1.id ) 
            //                LEFT JOIN lead_enquiry_projects as p ON (a.enquiry_id = p.enquiry_id)
            //                LEFT JOIN leadsourcemaster as s ON (a.leadPrimarySource = s.id)
            //                WHERE
            //                a.leadAddDate >=  '$updateDate' OR a.leadUpdateDate LIKE '%$updateDate%' 
            //                GROUP BY a.enquiry_id";  

            //        Updated on 17 april 2nd times
             //$query ="SELECT a.lead_id, a.enquiry_id, a.customerMobile, a.customerEmail, a.customerName, a.customerProfession, a.customer_gender, a.customerState, a.customerCity, a.customerAddress, a.customerRemark, a.leadPrimarySource, s.source as primary_campaign_source, a.leadSecondarySource as secondary_campaign_source, a.lead_added_by_user, a.leadAddDate,a.leadUpdateDate,a.lead_assigned_to_asm, a.lead_assigned_to_asm_on, a.lead_assigned_to_sp, a.lead_assigned_to_sp_on, a.disposition_status_id, a.disposition_sub_status_id, a.enquiry_status_remark, CONCAT(b.firstname,' ',b.lastname) AS lead_added_by_agent_name, c.status_title AS disposition_title, c1.sub_status_title AS disposition_sub_status_title, CONCAT(b1.firstname,' ',b1.lastname) as assigned_to_asm, CONCAT(b2.firstname,' ',b2.lastname) as assigned_to_sp, GROUP_CONCAT(p.project_name) as enquiry_projects
//            FROM  `lead_meeting` as lm
//            LEFT JOIN lead AS a ON (lm.enquiry_id = a.enquiry_id)
//            LEFT JOIN employees AS b ON ( a.lead_added_by_user = b.id ) 
//            LEFT JOIN employees AS b1 ON ( a.lead_assigned_to_asm = b1.id ) 
//            
//            LEFT JOIN employees AS b2 ON ( a.lead_assigned_to_sp = b2.id ) 
//            LEFT JOIN disposition_status_substatus_master AS c ON ( a.disposition_status_id = c.id) 
//            LEFT JOIN disposition_status_substatus_master AS c1 ON (a.disposition_sub_status_id = c1.id ) 
//            LEFT JOIN lead_enquiry_projects as p ON (a.enquiry_id = p.enquiry_id)
//            LEFT JOIN leadsourcemaster as s ON (a.leadPrimarySource = s.id)
//            WHERE
//            from_unixtime(lm.meeting_timestamp/1000) LIKE '%$updateDate%' AND (a.disposition_status_id = 3 AND a.disposition_sub_status_id = 22) OR (a.disposition_status_id = 6 AND a.disposition_sub_status_id = 23)
//            GROUP BY a.enquiry_id";

//        Update Query on 20/04/2017

             //   $query = "SELECT a.lead_id, a.enquiry_id, a.customerMobile, a.customerEmail, a.customerName, a.customerProfession, a.customer_gender, a.customerState, a.customerCity, a.customerAddress, a.customerRemark, a.leadPrimarySource, s.source as primary_campaign_source, a.leadSecondarySource as secondary_campaign_source, a.lead_added_by_user, a.leadAddDate,a.leadUpdateDate,a.lead_assigned_to_asm, a.lead_assigned_to_asm_on, a.lead_assigned_to_sp, a.lead_assigned_to_sp_on, a.disposition_status_id, a.disposition_sub_status_id, a.enquiry_status_remark, CONCAT(b.firstname,' ',b.lastname) AS lead_added_by_agent_name, c.status_title AS disposition_title, c1.sub_status_title AS disposition_sub_status_title, CONCAT(b1.firstname,' ',b1.lastname) as assigned_to_asm, CONCAT(b2.firstname,' ',b2.lastname) as assigned_to_sp, GROUP_CONCAT(p.project_name) as enquiry_projects
//
//                FROM  `lead` as a
//
//
//                LEFT JOIN employees AS b ON ( a.lead_added_by_user = b.id ) 
//
//                LEFT JOIN employees AS b1 ON ( a.lead_assigned_to_asm = b1.id ) 
//
//                LEFT JOIN employees AS b2 ON ( a.lead_assigned_to_sp = b2.id ) 
//
//                LEFT JOIN disposition_status_substatus_master AS c ON ( a.disposition_status_id = c.id) 
//
//                LEFT JOIN disposition_status_substatus_master AS c1 ON (a.disposition_sub_status_id = c1.id ) 
//
//                LEFT JOIN lead_enquiry_projects as p ON (a.enquiry_id = p.enquiry_id)
//
//                LEFT JOIN leadsourcemaster as s ON (a.leadPrimarySource = s.id)
//
//                WHERE
//
//                a.enquiry_id IN (SELECT a.enquiry_id as enquiry_id
//
//                FROM  `lead_meeting` as lm
//
//                LEFT JOIN lead AS a ON (lm.enquiry_id = a.enquiry_id)
//
//                WHERE
//
//                from_unixtime(lm.meeting_timestamp/1000) LIKE '%$updateDate%' AND a.disposition_status_id = 3 AND a.disposition_sub_status_id = 22
//
//                UNION ALL
//
//                SELECT a11.enquiry_id as enquiryid
//
//                FROM  `site_visit` as sv
//
//                LEFT JOIN lead AS a11 ON (sv.enquiry_id = a11.enquiry_id)
//
//                WHERE
//
//                from_unixtime(sv.site_visit_timestamp/1000) LIKE '%$updateDate%' AND a11.disposition_status_id = 6 AND a11.disposition_sub_status_id = 23
//                )
//
//                GROUP BY a.enquiry_id";

            //Update Query
           // $query ="SELECT  a.leadAddDate,a.leadUpdateDate, CONCAT(b.firstname,' ',b.lastname) AS lead_added_by_agent_name, c.status_title AS disposition_title, c1.sub_status_title AS disposition_sub_status_title, CONCAT(b1.firstname,' ',b1.lastname) as assigned_to_asm, CONCAT(b2.firstname,' ',b2.lastname) as assigned_to_sp, GROUP_CONCAT(p.project_name) as enquiry_projects
//
//            FROM  `lead` as a
//
//
//            LEFT JOIN employees AS b ON ( a.lead_added_by_user = b.id ) 
//
//            LEFT JOIN employees AS b1 ON ( a.lead_assigned_to_asm = b1.id ) 
//
//            LEFT JOIN employees AS b2 ON ( a.lead_assigned_to_sp = b2.id ) 
//
//            LEFT JOIN disposition_status_substatus_master AS c ON ( a.disposition_status_id = c.id) 
//
//            LEFT JOIN disposition_status_substatus_master AS c1 ON (a.disposition_sub_status_id = c1.id ) 
//
//            LEFT JOIN lead_enquiry_projects as p ON (a.enquiry_id = p.enquiry_id)
//
//            LEFT JOIN leadsourcemaster as s ON (a.leadPrimarySource = s.id)
//
//            WHERE
//
//            a.enquiry_id IN (SELECT a.enquiry_id as enquiry_id
//
//            FROM  `lead_meeting` as lm
//
//            LEFT JOIN lead AS a ON (lm.enquiry_id = a.enquiry_id)
//
//            WHERE
//
//            from_unixtime(lm.meeting_timestamp/1000) LIKE '%2017-04-17%' AND a.disposition_status_id = 3 AND a.disposition_sub_status_id = 22
//
//            UNION ALL
//
//            SELECT a11.enquiry_id as enquiryid
//
//            FROM  `site_visit` as sv
//
//            LEFT JOIN lead AS a11 ON (sv.enquiry_id = a11.enquiry_id)
//
//            WHERE
//
//            from_unixtime(sv.site_visit_timestamp/1000) LIKE '%$updateDate%' AND a11.disposition_status_id = 6 AND a11.disposition_sub_status_id = 23
//            )
//
//            GROUP BY a.enquiry_id"; 

//            Update query of 20/04/2017 ::
               // $query = "SELECT a.lead_id, a.enquiry_id, a.customerMobile, a.customerEmail, a.customerName, a.customerProfession, a.customer_gender, a.customerState, a.customerCity, a.customerAddress, a.customerRemark, a.leadPrimarySource, s.source as primary_campaign_source, a.leadSecondarySource as secondary_campaign_source, a.lead_added_by_user, a.leadAddDate,a.leadUpdateDate,a.lead_assigned_to_asm, a.lead_assigned_to_asm_on, a.lead_assigned_to_sp, a.lead_assigned_to_sp_on, a.disposition_status_id, a.disposition_sub_status_id, a.enquiry_status_remark, CONCAT(b.firstname,' ',b.lastname) AS lead_added_by_agent_name, c.status_title AS disposition_title, c1.sub_status_title AS disposition_sub_status_title, CONCAT(b1.firstname,' ',b1.lastname) as assigned_to_asm, CONCAT(b2.firstname,' ',b2.lastname) as assigned_to_sp, GROUP_CONCAT(p.project_name) as enquiry_projects
//                FROM  `lead` as a
//                LEFT JOIN employees AS b ON ( a.lead_added_by_user = b.id ) 
//                LEFT JOIN employees AS b1 ON ( a.lead_assigned_to_asm = b1.id ) 
//                LEFT JOIN employees AS b2 ON ( a.lead_assigned_to_sp = b2.id ) 
//                LEFT JOIN disposition_status_substatus_master AS c ON ( a.disposition_status_id = c.id) 
//                LEFT JOIN disposition_status_substatus_master AS c1 ON (a.disposition_sub_status_id = c1.id ) 
//                LEFT JOIN lead_enquiry_projects as p ON (a.enquiry_id = p.enquiry_id)
//                LEFT JOIN leadsourcemaster as s ON (a.leadPrimarySource = s.id)
//                WHERE
//                a.enquiry_id IN (SELECT a.enquiry_id as enquiry_id
//                FROM  `lead_meeting` as lm
//                LEFT JOIN lead AS a ON (lm.enquiry_id = a.enquiry_id)
//                WHERE
//                from_unixtime(lm.meeting_timestamp/1000) LIKE '%$updateDate%' AND a.disposition_status_id = 3 AND a.disposition_sub_status_id IN( 11,12,22)
//                UNION ALL
//                SELECT a11.enquiry_id as enquiryid
//                FROM  `site_visit` as sv
//                LEFT JOIN lead AS a11 ON (sv.enquiry_id = a11.enquiry_id)
//                WHERE
//                from_unixtime(sv.site_visit_timestamp/1000) LIKE '%$updateDate%' AND a11.disposition_status_id = 6 AND a11.disposition_sub_status_id IN (14,15,23)
//                )
//                GROUP BY a.enquiry_id";
       
        
//        Updated on 21-apr-2017
       $query = "SELECT a.enquiry_id,DATE(a.leadAddDate) as lead_Add_Date,TIME(a.leadAddDate) as lead_Add_Time,DATE(a.leadUpdateDate) as lead_update_date,TIME(a.leadUpdateDate) as lead_update_time,CONCAT(e.firstname,' ',e.lastname) AS CRM_Name, CONCAT(umesh.firstname,' ',umesh.lastname) AS CRM_Agent_Name, a.customerName as Customer, a.customerMobile as Mobile,a.customer_alternate_mobile,a.customerProfession as Profession,GROUP_CONCAT(DISTINCT p.project_name) AS Enquire_Project,a.customerCity as City,a.customerAddress as Address,a.customerEmail as Email,
        
        CONCAT(e1.firstname,' ',e1.lastname) AS TM_NAME,CONCAT(e2.firstname,' ',e2.lastname) As Sales_Manager, 
        d.status_title as Site_Visit, d1.sub_status_title as Site_Visit_Status , 
        CONCAT_WS('',DATE(from_unixtime(m.meeting_timestamp/1000 )),DATE(from_unixtime(sv.site_visit_timestamp/1000))) as site_visit_date,
        CONCAT_WS('',TIME(from_unixtime(m.meeting_timestamp/1000 )),TIME(from_unixtime(sv.site_visit_timestamp/1000))) as site_visit_time
        FROM  `lead` as a
        LEFT JOIN employees as e ON (a.lead_added_by_user = e.id)
        LEFT JOIN employees as umesh ON (a.reassign_user_id = umesh.id)
        LEFT JOIN employees as e1 ON (a.lead_assigned_to_asm = e1.id)
        LEFT JOIN employees as e2 ON (a.lead_assigned_to_sp = e2.id)
        LEFT JOIN disposition_status_substatus_master as d ON (a.disposition_status_id = d.id)
        LEFT JOIN disposition_status_substatus_master as d1 ON (a.disposition_sub_status_id = d1.id)
        LEFT JOIN lead_enquiry_projects as p ON (a.enquiry_id = p.enquiry_id)
        LEFT JOIN lead_meeting as m ON (a.meeting_id = m.meetingId)
        LEFT JOIN site_visit as sv ON (a.site_visit_id = sv.site_visit_id)
        where a.lead_assigned_to_asm_on  LIKE '%$updateDate%'
        GROUP BY a.enquiry_id";
        
        $header = [];
        $header[] = 'Enquiry Id';
        $header[] = 'Lead Add Date';
        $header[] = 'Lead Add Time';
        $header[] = 'Lead Update Date';
        $header[] = 'Lead Update Time';
        $header[] = 'CRM Created BY';
        $header[] = 'CRM Assign To';
        $header[] = 'Customer';
        $header[] = 'Mobile No.';
        $header[] = 'Alternate Contact';
        $header[] = 'Profession';
        $header[] = 'Project';
        $header[] = 'City';
        $header[] = 'Address';
        $header[] = 'Email ID';
        $header[] = 'TM Name';
        $header[] = 'Sales Manager';
        $header[] = 'Site Visit';
        $header[] = 'Site Visit Status';
        $header[] = 'site_visit_date';
        $header[] = 'site_visit_time';            
        }

        $result = mysqli_query($con,$query);  // Execute the query
        $num_rows = mysqli_num_rows($result); //Check whether the result is 0 or greater than 0.

        if($num_rows > 0){

            $str = '<div class="media">';
            // header("Content-Disposition: attachment; filename=\"report.xls\"");
            //header("Content-Type: application/vnd.ms-excel");
            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename=file.csv");
            header("Pragma: no-cache");
            header("Expires: 0");
            $data = array();
          



            foreach($header as $h){
                echo $h.',';
            }
            echo PHP_EOL;
            while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)){  //Fetching the data from the database

                foreach($row as $key => $value){
                    $value = str_replace(',','-',$value);
                    echo $value.',';
                }
                echo PHP_EOL;
            }

            //echo $str;
        }else{
            echo "<p>No record found</p>";	
        }

    } else{
        echo "<p>No record found</p>";
    }

?>