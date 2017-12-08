<?php
session_start();
require_once 'function.php';
require_once 'db_connection.php';

$designation_id = '';

$get_data = filter_input_array(INPUT_GET);

if(isset($get_data['designation_id']) && $get_data['designation_id'] != ''){
    
    $designation_id = $get_data['designation_id'];
}

// if login user role is admin then get all modules 

if($designation_id == 2){
	
    $modules = 'SELECT module.* FROM `crmmodules` as module WHERE parent IS NULL ORDER BY display_order ASC ';
	
}else{
    
        //     -------------------> Modules that marked as deleted will not be fetched
    
    $modules = 'SELECT tbl1.* , tbl2.*
				FROM `designationmodulemaster` as tbl1
				LEFT JOIN crmmodules as tbl2 on (tbl1.ModuleId = tbl2.id)
				WHERE designationId = '.$designation_id.' AND tbl2.parent IS NULL' ;

}

//echo $modules; exit;

$result = mysql_query($modules);

$rows   = mysql_num_rows($result);

$modules = array();

$title = '';

$parent_modules_ids = array();
$child_modules_ids = array();

if($rows > 0){
   
    while($row = mysql_fetch_assoc($result)){
        
        global $title;

		$title = $row['title'];
		$modules['modules'][$title] = array();
		$modules['modules'][$title]['title']    = $row['title'];
        $modules['modules'][$title]['id'] = str_replace(' ','_',strtolower($row['title']));
		$modules['modules'][$title]['link']     = ($row['link'] != '' ? $row['link']: strtolower($row['title'])); 
		$modules['modules'][$title]['params']   =   $row['params'];
		$modules['modules'][$title]['parent']   =   $row['parent'];
		if($designation_id != 2){
			$modules['modules'][$title]['submenu'] = getAssignedChildModules($row['id'],$designation_id); 
		}else{
		
			// geeting sub modules for admin user 
			$sub_modules_ids = getAssignedChildModules($row['id'],$designation_id);
			$modules['modules'][$title]['submenu'] = getSubModules($sub_modules_ids,$designation_id);
		}
    } // End of while	
}

echo json_encode($modules,true);