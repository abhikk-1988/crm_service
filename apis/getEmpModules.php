<?php
session_start();
//error_reporting(E_ALL);ini_set('display_errors', 1);

require_once 'db_connection.php';

$designation_id = '';

if(isset($_GET['designation_id']) && $_GET['designation_id'] != ''){
    
    $designation_id = $_GET['designation_id'];
}


if($designation_id == 2){
    
    $modules = 'SELECT module.*'
        . ' FROM `crmmodules` as module';
}else{
    
    // -------------------> Modules that marked as deleted will not be fetched
    
    $modules = 'SELECT priv.moduleId as module_id,priv.permission as permission, module.*'
        . ' FROM `designationmodulemaster` as priv '
        . ' LEFT JOIN crmmodules as module ON (priv.moduleId = module.id)'
        . ' WHERE priv.designationId = '.$designation_id.' AND module.markAsDelete != 1';
}
//echo $modules; exit;

$result = mysql_query($modules);

$rows   = mysql_num_rows($result);

$modules = array();

$title = '';
if($rows > 0){
    
    while($row = mysql_fetch_assoc($result)){
        
        global $title;
        
        if($row['parent'] == NULL){
        
		   $title = $row['title'];

		   $modules['modules'][$title] = array();
		   $modules['modules'][$title]['title']    = $row['title'];
		   $modules['modules'][$title]['link']     = ($row['link'] != '' ? $row['link']: strtolower($row['title'])); 
		   $modules['modules'][$title]['params']   =   $row['params'];
		   $modules['modules'][$title]['parent']   =   $row['parent'];
            
        }else{
            
		   $title = file_get_contents( BASE_URL .'apis/helper.php?method=getParentTitle&params=id:'.$row['parent'].'/response_type:plain' );
		   
		   $modules['modules'][$title]['submenu'][$row['title']]['title']           = $row['title'];
		   $modules['modules'][$title]['submenu'][$row['title']]['link']            = $row['link'];
		   $modules['modules'][$title]['submenu'][$row['title']]['params']          = $row['params'];
		   if($designation_id == 2){
			 $modules['modules'][$title]['submenu'][$row['title']]['permission']      = 7;
		   }else{
			 $modules['modules'][$title]['submenu'][$row['title']]['permission']      = $row['permission'];
		   }
		   
        }
	  
	} // End of while 	
    
}

echo json_encode($modules,true);