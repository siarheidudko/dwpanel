<?php
/*
Dudko Web Panel v2.2.2
https://github.com/siarheidudko/dwpanel
(c) 20017-2018 by Siarhei Dudko.
https://github.com/siarheidudko/dwpanel/LICENSE
*/

$json = file_get_contents("php://input");
$url = 'http://127.0.0.1:999/core_web.php';

$ch = curl_init($url);                                                                      
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
curl_setopt($ch, CURLOPT_POSTFIELDS, $json);                                                                  
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                                                                                                                                    
curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
	 'Content-Type: application/json',)                                                                  
	);  	
$result = curl_exec($ch);
header("Access-Control-Allow-Origin: *");
header("Content-type: text/txt; charset=UTF-8");
echo $result;

exit;
?>