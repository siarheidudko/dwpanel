<?php
/*
Dudko Web Panel v2.2.2
https://github.com/siarheidudko/dwpanel
(c) 2017-2018 by Siarhei Dudko.
https://github.com/siarheidudko/dwpanel/LICENSE
*/

// php /etc/openvpn/scripts/cert_send_toemail.php --args 1 1 admin@sergdudko.tk
$servername = 'server'.$argv[2];
$servernum = $argv[2];
$usermail = $argv[4];
$certificat = 'client'.$argv[3];

/* загружаем настройки и настраиваем логгирование  */
include(__DIR__ . '/settings.php'); 
include(__DIR__ . '/cryptoAES.php');
$current = 'php /etc/openvpn/scripts/cert_send_toemail.php --args '.$argv[2].' '.$argv[3].' '.$argv[4] . PHP_EOL;
if(!file_exists('/etc/openvpn/dudko-web-panel/')) { 
  if(!mkdir('/etc/openvpn/dudko-web-panel/', 0755, true)) {
    echo 'Не удалось создать каталог .../dudko-web-panel/!';
    exit;
  }
}
if(!is_dir('/etc/openvpn/dudko-web-panel/logs/')){ mkdir('/etc/openvpn/dudko-web-panel/logs/');}
$file = '/etc/openvpn/dudko-web-panel/logs/'.$servername.date("Y-m-d").'.log';
if(file_exists($file)){ $current .= file_get_contents($file); }
/* проверяем, что аргументы получены, требуемый сертификат имеется на сервере и  не принадлежит серверу */ 
if(!isset($usermail) || !isset($certificat)|| !isset($servername)){
  	echo 'Некорректные параметры для отправки!';
  	exit;
} 
if(!file_exists('/etc/openvpn/dudko-web-panel/rsa-key/'.$servername.'/'.$certificat.'.key') || !file_exists('/etc/openvpn/dudko-web-panel/rsa-key/'.$servername.'/'.$certificat.'.crt')){ 
  	echo 'Сертификаты для данного клиента отсутствуют!';
  	exit; 
}

/* получаем переменные из БД для авторизации на почтовом сервере */
GetPostConf($db_key, $db_user, $db_pass, $db_projectname, $variable);

/* создаем архив с файлами  */
//генерация пароля на архив
$passwd = ''; 
$array = array_merge(range('A','Z'),range('a','z'),range('0','9')); 
$c = count($array); 
$longpass = rand(70,100);
for($i=0;$i<$longpass;$i++) {$passwd .= $array[rand(0,$c-1)];}
//создадим временный файл в памяти
$zip_file = '/tmp/zip-cert-'.md5(time()).'.zip';
$config_file = '/tmp/'.$certificat.'.ovpn';
file_put_contents($config_file, generate_config($servernum, $certificat)); 
//вариант через shell(с паролем)
$files_to_zip = '';
if(file_exists('/etc/openvpn/dudko-web-panel/rsa-key/'.$servername.'/'.$certificat.'.key')){ 
  $files_to_zip .= $certificat.'.key ';
}
if(file_exists('/etc/openvpn/dudko-web-panel/rsa-key/'.$servername.'/'.$certificat.'.crt')){ 
  $files_to_zip .= $certificat.'.crt ';
}
if(file_exists('/etc/openvpn/dudko-web-panel/rsa-key/'.$servername.'/ta.key')){ 
  $files_to_zip .= 'ta.key ';
}
if(file_exists('/etc/openvpn/dudko-web-panel/rsa-key/'.$servername.'/ca.crt')){ 
  $files_to_zip .= 'ca.crt ';
}
$answr = explode(' ', exec('cd  /etc/openvpn/dudko-web-panel/rsa-key/'.$servername.'/ && zip '.$zip_file.' '.$files_to_zip.' -P '.$passwd));
if ($answr[2] != 'adding:') {
    $current .= date("Y-m-d H:i:s").'     '."Ошибка создания архива";
  	$current .= PHP_EOL;
  	unlink($zip_file);
	unlink($config_file);
  	file_put_contents($file, $current);  
  	exit;
}
if(file_exists($config_file)){ 
  $files_to_zip = $certificat.'.ovpn';
}
$answr = explode(' ', exec('cd  /tmp/ && zip '.$zip_file.' '.$files_to_zip.' -P '.$passwd));
if ($answr[2] != 'adding:') {
    $current .= date("Y-m-d H:i:s").'     '."Ошибка добавления конфиг-файла в архив";
  	$current .= PHP_EOL;
  	unlink($zip_file);
	unlink($config_file);
  	file_put_contents($file, $current);  
  	exit;
}

/* отправка архива на почту  */
$_SERVER["SERVER_NAME"] = 'sergdudko.tk';
require_once ("/etc/openvpn/scripts/SendMailSmtpClass.php"); // подключаем класс
$mailSMTP = new SendMailSmtpClass($variable['email_user'], MyCryptoAES(md5($db_user.'vpnsergdudkotk').md5($db_pass.'vpnsergdudkotk'), 'email_pass', $variable['email_pass'], 'decrypt'), $variable['email_host'], $variable['email_name'], $variable['email_port']); // создаем экземпляр класса
$headers= "MIME-Version: 1.0\r\n"; 
$un = rand(1000,9999);
//$headers .= "Content-Type: multipart/alternative;boundary=\"----------".$un."\"".PHP_EOL; // кодировка письма
$headers .= "Content-Type: multipart/mixed;boundary=\"----------".$un."\"".PHP_EOL; // кодировка письма
$headers .= "Content-Language: ru". PHP_EOL;
$headers .= 'From: '.$variable['email_name'].' <'.$variable['email_user'].'>'.PHP_EOL; // от кого письмо
$headers .= 'To: '.$usermail.PHP_EOL; // от кого письмо
$message = 	"------------".$un. PHP_EOL .
  			"Content-Type:text/plain; charset=utf-8". PHP_EOL .
  			"Content-Transfer-Encoding: 8bit". PHP_EOL .
  			"Архив с сертификатами во вложении, пароль на архив был предоставлен на сайте.". PHP_EOL .
  			"Обращаю внимание на то, что длинна не все архиваторы поддерживают заданную длинну пароля. Если вы получили сообщение о том, что пароль не верен - попробуйте другим архиватором.". PHP_EOL .
"------------".$un. PHP_EOL .
  			"Content-Type: application/octet-stream;name=\"file.zip\"". PHP_EOL .
  			"Content-Transfer-Encoding:base64". PHP_EOL .
  			"Content-Disposition:attachment;filename=\"file.zip\"". PHP_EOL .chunk_split(base64_encode(file_get_contents($zip_file))). PHP_EOL ."
------------".$un."--";
$subject = 'Ключи доступа'.PHP_EOL;
$result =  $mailSMTP->send($usermail, $subject, $message, $headers); // отправляем письмо
if($result === true){
  echo 'Сертификаты были успешно отправлены на '. $usermail . ', пароль на архив:' . PHP_EOL . $passwd .PHP_EOL; 
  $current .= date("Y-m-d H:i:s").'     '.'Сертификаты были успешно отправлены на '. $usermail .PHP_EOL;
}else{
  $current .= date("Y-m-d H:i:s").'     '.'Попытка отправить сертификаты через резервный ящик!'.PHP_EOL;
  $mailSMTP_reserve = new SendMailSmtpClass($variable['email_user_reserve'], $variable['email_pass_reserve'], MyCryptoAES(md5($db_user.'vpnsergdudkotk').md5($db_pass.'vpnsergdudkotk'), 'email_host_reserve', $variable['email_host_reserve'], 'decrypt'), $variable['email_name'], $variable['email_port_reserve']); // создаем экземпляр класса
  $result2 =  $mailSMTP_reserve->send($usermail, $subject, $message, $headers); // отправляем письмо с резервного ящика
  if($result2 === true){
    echo 'Сертификаты были успешно отправлены на '. $usermail . ', пароль на архив:' . PHP_EOL . $passwd .PHP_EOL; 
    $current .= date("Y-m-d H:i:s").'     '.'Сертификаты были успешно отправлены на '. $usermail .PHP_EOL;
  }else{
    echo 'Ошибка отправки сертификатов на '. $usermail . '!' . PHP_EOL; 
    $current .= date("Y-m-d H:i:s").'     '.'Ошибка отправки сертификатов на '. $usermail . '!' . PHP_EOL;    
  }
}

/* Удаляем архив и пишем содержимое лога в файл */
unlink($zip_file);
unlink($config_file);
$current .= PHP_EOL;
file_put_contents($file, $current);

function generate_config($server_num, $certificat){
  	include(__DIR__ . '/settings.php'); 
    GetServerConf($db_key, $db_user, $db_pass, $server_num, $db_projectname, $variable_func);
	$variable_func['port'] = 1700+intval($variable_func['Id']);
  	$conf_file = '';
    $conf_file .= 'tls-client'.PHP_EOL;
    $conf_file .= 'proto tcp-client'.PHP_EOL;
  	$conf_file .= 'remote '.$variable_func['hostname'].PHP_EOL;
  	$conf_file .= 'dev '.$variable_func['dev'].PHP_EOL;
	$conf_file .= 'port '.$variable_func['port'].PHP_EOL;
  	$conf_file .= 'pull '.PHP_EOL;
    $conf_file .= 'tls-auth ta.key 1'.PHP_EOL;
  	$conf_file .= 'ca ca.crt'.PHP_EOL;
	$conf_file .= 'cert '.$certificat.'.crt'.PHP_EOL;
  	$conf_file .= 'key '.$certificat.'.key'.PHP_EOL;
  	$conf_file .= 'cipher '.$variable_func['cipher'].PHP_EOL;
  	$conf_file .= 'comp-lzo'.PHP_EOL;
  	return $conf_file;
}

function GetPostConf($db_key_this, $db_user_this, $db_pass_this, $db_projectname_this, &$variable_this){
	$user_token = GetFirebaseUserToken($db_key_this, $db_user_this, $db_pass_this);
	if($user_token == 'auth_firebase_error'){
		echo 'Ошибка авторизации в базе данных.';
		exit;
	}
	$url = 'https://'.$db_projectname_this.'.firebaseio.com/'.$user_token[1].'.json?auth='.$user_token[0];

	$ch = curl_init($url);                                                                      
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");                                                                                                                                      
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                                                                                                                                      
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
		 'Content-Type: application/json',)                                                                  
		);  	
	$result = curl_exec($ch);
	try {
		$result_arr = json_decode($result, true);
		$variable_this = $result_arr['adminmail'];
	} catch(Exception $e) {
	}
}

function GetServerConf($db_key_this, $db_user_this, $db_pass_this, $id_server_this, $db_projectname_this, &$variable_this){
	$user_token = GetFirebaseUserToken($db_key_this, $db_user_this, $db_pass_this);
	if($user_token == 'auth_firebase_error'){
		echo 'Ошибка авторизации в базе данных.';
		exit;
	}
	$url = 'https://'.$db_projectname_this.'.firebaseio.com/'.$user_token[1].'.json?auth='.$user_token[0];

	$ch = curl_init($url);                                                                      
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");                                                                                                                                      
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                                                                                                                                      
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
		 'Content-Type: application/json',)                                                                  
		);  	
	$result = curl_exec($ch);
	try {
		$result_arr = json_decode($result, true);
		$variable_this = $result_arr['server'][$id_server_this];
		if(isset($variable_this['dev'])){
			$variable_this['Id'] = $id_server_this;
		}
	} catch(Exception $e) {
	}
}

function GetFirebaseUserToken($db_key_this, $db_user_this, $db_pass_this){
	$url = 'https://www.googleapis.com/identitytoolkit/v3/relyingparty/verifyPassword?key='.$db_key_this;
	$json = '{"email": "'.$db_user_this.'","password": "'.$db_pass_this.'","returnSecureToken": true}';
	$ch = curl_init($url);                                                                      
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, $json);                                                                  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                                                                                                                                      
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
		 'Content-Type: application/json',)                                                                  
		);  	
	$result = curl_exec($ch);
	try {
		$result_arr = json_decode($result, true);
		return [$result_arr['idToken'], $result_arr['localId']];
	} catch(Exception $e) {
		return 'auth_firebase_error';
	}
}
exit;
?>