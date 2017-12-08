<?php

require_once 'db_connection.php';

$data = file_get_contents('php://input');

if($data == ''){
    echo json_encode(array('success' => 0,'message' => 'No data Recieved'),true); exit;
}

$form_data_decoded = json_decode($data,true);

// Form validation 
$form_errors = array();

if($form_data_decoded['title'] == ''){
    $form_errors['title'] = 'Module title is required';
}

if($form_data_decoded['link'] == ''){
    $form_errors['link'] = 'Module link is required';
}

if(isset($form_data_decoded['parent_module']) && $form_data_decoded['parent_module'] == ''){
    $form_errors['parent_module'] = 'Please select a parent module';
}

if(isset($form_data_decoded['parent']) && $form_data_decoded['parent'] == ''){
    $form_errors['parent'] = 'Please select a parent module';
}


if(!empty($form_errors)){
    
    echo json_encode(array('success' => -1,'errors' => $form_errors,'message' => 'Form has some errors'),true); exit;
}else{
    
    // save module 
    
    if(isset($form_data_decoded['id']) && $form_data_decoded['id'] != ''){
        $save_module = 'UPDATE crmmodules '
            . ' SET title = "'.$form_data_decoded['title'].'" '
                . ' WHERE id = '.$form_data_decoded['id'].'';
    }else{
        
        $parent = '';
        if($form_data_decoded['parent_module'] == null ){
           $save_module = 'INSERT INTO crmmodules (title,link,insertDate) '
            . ' VALUES ("'.$form_data_decoded['title'].'","'.$form_data_decoded['link'].'","'.date('Y-m-d').'")';
        }else{
            
            $save_module = 'INSERT INTO crmmodules (parent,title,link,insertDate) '
            . ' VALUES ('.$form_data_decoded['parent_module'].',"'.$form_data_decoded['title'].'","'.$form_data_decoded['link'].'","'.date('Y-m-d').'")';
        }
    
    }
    
    if(mysql_query($save_module)){
        echo json_encode(array('success' => 1,'message' => 'Module has been created successfully'),true); exit;
    }else{
        echo json_encode(array('success' => 0,'message' => 'Module Couldn\'t be added. Please try again.'),true); exit;
    }
    
}

