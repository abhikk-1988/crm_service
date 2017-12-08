<?php

require '../function.php';

$team_id = '';

if(isset($_GET['team_id'])){
    $team_id = $_GET['team_id'];
}

if($team_id == ''){
    echo 'No ASM Found'; exit;
}

$query = 'SELECT asm_id, asm_name FROM crm_teams WHERE id = '.$team_id.' LIMIT 1';

$result = mysql_query($query);

if($result && mysql_num_rows($result) > 0){

    $team_asm = mysql_fetch_assoc($result);

    echo json_encode($team_asm,true); exit;

}

