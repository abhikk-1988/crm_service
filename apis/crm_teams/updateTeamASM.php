<?php

/**
 * @API: updateTeamASM
 * @author: Abhishek Agrawal
 * @created on: 21/06/2017
 */

    require '../function.php';

    $post_data = json_decode(file_get_contents('php://input'),true);

    $errors = array();

    if($post_data['team_id'] == ''){
        $errors['team_id'] = 'Team is not selected';
    }

    if($post_data['asm_id'] == ''){
        $errors['team_id'] = 'Area Sales Manager is not selected';
    }
    
    // If error is there
    if(!empty($errors)){

        echo json_encode(array(
            'success' => 0,
            'is_errors' => 1,
            'errors' => $errors,
            'message' => 'Please correct following errors'
        ),true);
        exit;
    }

    // Get asm name 
    $asm_name = getEmployeeName($post_data['asm_id']);

    // Update ASM
    $update_asm = mysql_query('UPDATE `crm_teams` SET asm_id = '.$post_data['asm_id'].' , asm_name = "'.$asm_name.'" WHERE id = '.$post_data['team_id'].' LIMIT 1');

    if($update_asm){
        echo json_encode(
            array(
                'success' => 1,
                'message' => 'Team ASM has been changed successfully'
            )
        ,true); exit;
    }
?>