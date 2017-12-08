<?php
function download($filename, $folder, $ext){
    
  if(!empty($filename)){
    
    // Specify file path.
    $path = 'upload/';
      
    $file_path =  $path.$folder.'/'.$filename;      
      
    $localPath = realpath("../$file_path");
      
      if (!file_exists($localPath)) {
          exit("Cannot find file located at '$localPath'");
      }else{  
          
        header('Pragma: public'); // required   
        header('Content-Length: '.filesize($localPath));  
        header('Content-Type: application/octet-stream');  
        header('Content-Disposition: attachment; filename="'.md5($localPath).'.'.$ext.'"');  
        header('Content-Transfer-Encoding: binary');  
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0', false);  
        header('Cache-Control: private', false); // required for certain browsers  

        readfile($localPath);
        exit;
      }
    }
    else
    {
      echo 'File does not exists '; exit;
    }
 }


$file = '';

if(isset($_GET['file'])){
    
    $file = $_GET['file'];
    
    
    // get file extension 
    
    $uri = explode('/', $file);
    
    $file_name = end($uri);
    $folder = prev($uri);
    
    $extension = explode('.', $file_name);
    $extension = $extension[1];
    
    download($file_name, $folder, $extension);
}