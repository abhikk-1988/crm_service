<?php

/**
 * @API: deleteTeam
 * @author: Abhishek Agrawal
 * @created on: 21/06/2017
 */

 require '../function.php';

 $post_data = json_decode(file_get_contents('php://input'),true);

 if(isset($post_data['team_id'])){

    // Remove the whole team and its member mappings
     
    $remove_team_member_mapping = mysql_query('UPDATE employees SET crm_team = 0, reportingTo = NULL WHERE crm_team  = '.$post_data['team_id'].'');

    if($remove_team_member_mapping){
        $delete_team = mysql_query('DELETE FROM crm_teams WHERE id = '.$post_data['team_id'].' LIMIT 1');

        if($delete_team){
            echo 'Team has been deleted successfully'; exit;
        }
    }
 }
 else{
     echo 'Team not found'; exit;
 }

?>