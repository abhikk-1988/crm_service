<?php
require_once 'db_connection.php';
$file_path		=		"../queryupload/enquery_report.csv";
$file = fopen($file_path , "r");
while(! feof($file))
{
	echo "<pre>";
	print_r(fgetcsv($file));
}

fclose($file);