<?php

session_start();

$json_string = file_get_contents('php://input');

require '../function.php';

require '../user_authentication.php';

$data = json_decode(file_get_contents('php://input'),true);

$errors = array();

$team = array();

if(!empty($data)){

    // Create new team 

    if($data['team_name'] == ''){
        $errors['team_name'] = 'Team name is required';
    }else{
        $team['team_name'] = $data['team_name'];
    }

    if($data['asm_id'] == ''){
        $errors['asm_id'] = 'Area Sales Manager is required';
    }else{
        $team['asm_id'] = $data['asm_id'];
        $team['asm_name'] = $data['asm_name'];
    }

    //     
    if(empty($data['team_members'])){
        $errors['team_members'] = 'Please select team member';
    }else{
        $team['team_members'] = $data['team_members'];
    }

    if(empty($errors)){
        // Create a team and update employee with their team id

        $create_team = mysql_query('INSERT INTO `crm_teams` (team_name,asm_id,asm_name,is_active,created_on) VALUES ("'.$team['team_name'].'",'.$team['asm_id'].',"'.$team['asm_name'].'",1,"'.date('Y-m-d H:i:s').'")');

        if($create_team){

            $team_id = mysql_insert_id();

            // update employee's team
            if($team_id){

                $crm_employees = array();

                foreach($team['team_members'] as $team_member){
                    array_push($crm_employees, $team_member['crm_id']);
                }

                $update_employee = implode(',', $crm_employees);

                mysql_query('UPDATE `employees` SET `crm_team` = '.$team_id.' , reportingTo = '.$team['asm_id'].' WHERE id IN ('.$update_employee.') ');
                
                
                // Now Updates those crm agents who have not selected in team but reports to the ASM
                
                $get_direct_reporting_without_team = mysql_query('SELECT id FROM employees WHERE reportingTo = '.$team['asm_id'].' AND crm_team = 0 AND designation = (SELECT id FROM designationmaster WHERE designation_slug = "agent") ');
                
                if($get_direct_reporting_without_team && mysql_num_rows($get_direct_reporting_without_team) > 0){
                    
                    while($agent = mysql_fetch_assoc($get_direct_reporting_without_team)){
                        mysql_query('UPDATE `employees` SET `crm_team` = '.$team_id.' , reportingTo = '.$team['asm_id'].' WHERE id = '.$agent['id'].' ');
                    }
                    
                }
                
            }

            // send response to client 

            $response = array(
                'success' => 1,
                'message' => 'Team has been created successfully',
            );

            header('Content-Type', 'application/json');
            echo json_encode($response,true); exit;
        }else{
            
            if((int) mysql_errno() === 1062){
                $response = array(
                    'success' => 0,
                    'message' => 'Team is already exists'
                );

                header('Content-Type', 'application/json');
                echo json_encode($response,true); exit;
            }
        }
    }
    // Display errors 
    else{
        
        $response = array(
            'success' => 0,
            'is_error' => 1,
            'errors' => $errors
        );

        header('Content-Type', 'application/json');
        echo json_encode($response,true); exit;
    }

}