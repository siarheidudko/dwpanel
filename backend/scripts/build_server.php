<?php
/*
Dudko Web Panel v2.2.2
https://github.com/siarheidudko/dwpanel
(c) 2017-2018 by Siarhei Dudko.
https://github.com/siarheidudko/dwpanel/LICENSE
*/

//php /etc/openvpn/scripts/build_server.php --args -add 1
//php /etc/openvpn/scripts/build_server.php --args -remove 1
include(__DIR__ . '/settings.php'); 
$current = 'php /etc/openvpn/scripts/build_server.php --args '.$argv[2].' '.$argv[3] . PHP_EOL;
if(!file_exists('/etc/openvpn/dudko-web-panel/')) { 
  if(!mkdir('/etc/openvpn/dudko-web-panel/', 0755, true)) {
    echo 'Не удалось создать каталог .../dudko-web-panel/!';
    exit;
  }
}
if(!is_dir('/etc/openvpn/dudko-web-panel/logs')){ mkdir('/etc/openvpn/dudko-web-panel/logs');}
$file = '/etc/openvpn/dudko-web-panel/logs/server'.$argv[3].date("Y-m-d").'.log';
if(file_exists($file)){ $current .= file_get_contents($file); }

if($argv[2]=='-add'){
  	GetServerConf($db_key, $db_user, $db_pass, $argv[3], $db_projectname, $variable);
	
  	if(!isset($variable['Id'])){
      	echo "Сервер отсутствует в базе данных!";
    	$current .= date("Y-m-d H:i:s").'     '."Сервер отсутствует в базе данных!";
        $current .= PHP_EOL;
      	echo PHP_EOL;
        file_put_contents($file, $current);  
        exit;
    }
	CreateServer('server'.$variable['Id'], intval($variable['Id']), $current, $variable);
}
if($argv[2]=='-remove'){
	RemoveServer('server'.$argv[3], $current);
}
$current .= PHP_EOL;
file_put_contents($file, $current);
echo PHP_EOL;
exit;

function cmd_exec($operation, $cmd, &$stdout, &$stderr)
{
    $outfile = tempnam(".", "cmd");
    $errfile = tempnam(".", "cmd");
    $descriptorspec = array(
        0 => array("pipe", "r"),
        1 => array("file", $outfile, "w"),
        2 => array("file", $errfile, "w")
    );
    $proc = proc_open($cmd, $descriptorspec, $pipes);
    
    if (!is_resource($proc)) return 255;
	if($operation == 'ca'){
		for($i=0;$i<8;$i++){ //8 переходов на новую строку для CA
			fwrite($pipes[0], "\n");
			usleep(200);
		}
	}
	if($operation == 'server'){
		for($i=0;$i<10;$i++){ //10 переходов на новую строку для SERVER, потом два раза yes и переход
			fwrite($pipes[0], "\n");
			usleep(200);
		}
		sleep(3);
		fwrite($pipes[0], "y\n");
		usleep(500);
		fwrite($pipes[0], "y\n");
	}
    fclose($pipes[0]); 
	
    $exit = proc_close($proc);
	$stdout = file($outfile);
    $stderr = file($errfile);

    unlink($outfile);
    unlink($errfile);
    return $exit;
}

function CreateServer($servername, $servernum, &$log, $variable){
	/* предустановки */
	if(!file_exists('/etc/openvpn/dudko-web-panel/settings/')) { 
		if(!mkdir('/etc/openvpn/dudko-web-panel/settings/', 0755, true)) {
			echo 'Не удалось создать каталог .../settings/!';
          	$log .= date("Y-m-d H:i:s").'     '.'Не удалось создать каталог .../settings/!';
  			$log .= PHP_EOL;
			return;
		}
	}
	if(!file_exists('/etc/openvpn/dudko-web-panel/rsa-key/')) { 
		if(!mkdir('/etc/openvpn/dudko-web-panel/rsa-key/', 0755, true)) {
			echo 'Не удалось создать каталог .../rsa-key/!';
          	$log .= date("Y-m-d H:i:s").'     '.'Не удалось создать каталог .../rsa-key/!';
  			$log .= PHP_EOL;
			return;
		}
	}
	if(!file_exists('/etc/openvpn/dudko-web-panel/easy-rsa/')) { 
		if(!mkdir('/etc/openvpn/dudko-web-panel/easy-rsa/', 0755, true)) {
			echo 'Не удалось создать каталог .../easy-rsa/!';
          	$log .= date("Y-m-d H:i:s").'     '.'Не удалось создать каталог .../easy-rsa/!';
  			$log .= PHP_EOL;
			return;
		} else {
			exec('cp -rp /usr/share/easy-rsa/2.0/* /etc/openvpn/dudko-web-panel/easy-rsa/', $callback);
			if(count($callback) != 0) {
				echo 'Возникли неполадки при копировании, выполните команду "cp -rp /usr/share/easy-rsa/2.0/* /etc/openvpn/dudko-web-panel/easy-rsa/" в shell!';
              	$log .= date("Y-m-d H:i:s").'     '.'Возникли неполадки при копировании, выполните команду "cp -rp /usr/share/easy-rsa/2.0/* /etc/openvpn/dudko-web-panel/easy-rsa/" в shell!';
  				$log .= PHP_EOL;
			};
		}
	}
   if(!file_exists('/etc/openvpn/dudko-web-panel/logs/')) { 
		if(!mkdir('/etc/openvpn/dudko-web-panel/logs/', 0755, true)) {
			echo 'Не удалось создать каталог .../logs/!';
          	$log .= date("Y-m-d H:i:s").'     '.'Не удалось создать каталог .../logs/!';
  			$log .= PHP_EOL;
			return;
		}
	}

	/* проверяем зависимости */
	if(file_exists('/etc/openvpn/dudko-web-panel/settings/'.$servername.'/')) { 
		echo 'Сервер уже существует(settings)!';
      	$log .= date("Y-m-d H:i:s").'     '.'Сервер уже существует(settings)!';
  		$log .= PHP_EOL;
		return;
	}
	if(file_exists('/etc/openvpn/dudko-web-panel/rsa-key/'.$servername.'/')) { 
		echo 'Сервер уже существует(rsa-key)!';
      	$log .= date("Y-m-d H:i:s").'     '.'Сервер уже существует(rsa-key)!';
  		$log .= PHP_EOL;
		return;
	}
	if(file_exists('/etc/openvpn/dudko-web-panel/easy-rsa/vars-'.$servername)) { 
		echo 'Сервер уже существует(vars)!';
      	$log .= date("Y-m-d H:i:s").'     '.'Сервер уже существует(vars)!';
  		$log .= PHP_EOL;
		return;
	}
	if(file_exists('/etc/openvpn/'.$servername.'.conf')) { 
		echo 'Сервер уже существует(config)!';
      	$log .= date("Y-m-d H:i:s").'     '.'Сервер уже существует(config)!';
  		$log .= PHP_EOL;
		return;
	}

	/* настраиваем каталоги сервера */
	if(!mkdir('/etc/openvpn/dudko-web-panel/settings/'.$servername.'/', 0755, true)) {
		echo 'Не удалось создать каталог .../settings/'.$servername.'/!';
      	$log .= date("Y-m-d H:i:s").'     '.'Не удалось создать каталог .../settings/'.$servername.'/!';
  		$log .= PHP_EOL;
		return;
	}
	if(!mkdir('/etc/openvpn/dudko-web-panel/rsa-key/'.$servername.'/', 0755, true)) {
		echo 'Не удалось создать каталог .../rsa-key/'.$servername.'/!';
      	$log .= date("Y-m-d H:i:s").'     '.'Не удалось создать каталог .../rsa-key/'.$servername.'/!';
  		$log .= PHP_EOL;
		return;
	}
	
	/* конфигурационный файл генерации сертификатов*/
	$vars_file = 'export EASY_RSA="`pwd`"' . PHP_EOL .
	'export OPENSSL="openssl"' . PHP_EOL .
	'export PKCS11TOOL="pkcs11-tool"' . PHP_EOL .
	'export GREP="grep"' . PHP_EOL .
	'export KEY_CONFIG=`$EASY_RSA/whichopensslcnf $EASY_RSA`' . PHP_EOL .
	'export KEY_DIR="/etc/openvpn/dudko-web-panel/rsa-key/'.$servername.'/"' . PHP_EOL .
	'export PKCS11_MODULE_PATH="dummy"' . PHP_EOL .
	'export PKCS11_PIN="dummy"' . PHP_EOL .
	'export KEY_SIZE='.$variable['KEY_SIZE'].'' . PHP_EOL .
	'export CA_EXPIRE='.$variable['CA_EXPIRE'].'' . PHP_EOL .
	'export KEY_EXPIRE='.$variable['KEY_EXPIRE'].'' . PHP_EOL .
	'export KEY_COUNTRY="'.$variable['KEY_COUNTRY'].'"' . PHP_EOL .
	'export KEY_PROVINCE="'.$variable['KEY_PROVINCE'].'"' . PHP_EOL .
	'export KEY_CITY="'.$variable['KEY_CITY'].'"' . PHP_EOL .
	'export KEY_ORG="'.$variable['KEY_ORG'].'"' . PHP_EOL .
	'export KEY_EMAIL="'.$variable['KEY_EMAIL'].'"' . PHP_EOL .
	'export KEY_OU="'.$variable['KEY_OU'].'"' . PHP_EOL .
	'export KEY_NAME="'.$variable['KEY_NAME'].'"' . PHP_EOL .
	'export KEY_CN="'.$variable['KEY_CN'].'"' . PHP_EOL .
	'export KEY_ALTNAMES="'.$variable['KEY_ALTNAMES'].'"' . PHP_EOL .
	'echo OK: If you run ./clean-all, I will be doing a rm -rf on $KEY_DIR' . PHP_EOL;

	if(!file_put_contents('/etc/openvpn/dudko-web-panel/easy-rsa/vars-'.$servername, $vars_file)){
		echo 'Не удалось записать файл vars-'.$servername.'!';
      	$log .= date("Y-m-d H:i:s").'     '.'Не удалось записать файл vars-'.$servername.'!';
  		$log .= PHP_EOL;
		return;
	} 
	chmod('/etc/openvpn/dudko-web-panel/easy-rsa/vars-'.$servername, 0755);

	cmd_exec('ca', 'cd /etc/openvpn/dudko-web-panel/easy-rsa/ && source ./vars-'.$servername .' && ./clean-all  && ./build-ca', $callback, $err);
	unset($callback);unset($err);
	cmd_exec('server','cd /etc/openvpn/dudko-web-panel/easy-rsa/ && source ./vars-'.$servername .' && ./build-key-server server', $callback, $err);
	$result_req = substr($err[31], 0, 17); //Data Base Updated
	unset($callback);unset($err);
	cmd_exec('dh','cd /etc/openvpn/dudko-web-panel/easy-rsa/ && source ./vars-'.$servername .' && ./build-dh', $callback, $err);
	unset($callback);unset($err);
	exec('openvpn --genkey --secret /etc/openvpn/dudko-web-panel/rsa-key/'.$servername.'/ta.key', $callback);

	/* конфигурационный файл сервера */
	$config_file = 'mode server' .PHP_EOL .
	'tls-server' .PHP_EOL .
	'proto tcp-server' .PHP_EOL .
	'dev tap' .PHP_EOL .
	'port '.strval(1700 + $servernum).' # Порт' .PHP_EOL .
	'daemon' .PHP_EOL .
	'tls-auth /etc/openvpn/dudko-web-panel/rsa-key/'.$servername.'/ta.key 0' .PHP_EOL .
	'ca /etc/openvpn/dudko-web-panel/rsa-key/'.$servername.'/ca.crt' .PHP_EOL .
	'cert /etc/openvpn/dudko-web-panel/rsa-key/'.$servername.'/server.crt' .PHP_EOL .
	'key /etc/openvpn/dudko-web-panel/rsa-key/'.$servername.'/server.key' .PHP_EOL .
	'dh /etc/openvpn/dudko-web-panel/rsa-key/'.$servername.'/dh2048.pem' .PHP_EOL .
	'ifconfig-pool-persist /etc/openvpn/dudko-web-panel/settings/'.$servername.'/ipp.txt' .PHP_EOL .
	'ifconfig 13.'.strval($servernum).'.0.1 255.255.255.0 # Внутренний IP сервера' .PHP_EOL .
	'ifconfig-pool 13.'.strval($servernum).'.0.30 13.'.strval($servernum).'.0.255 # Пул адресов.' .PHP_EOL .
	'client-to-client' .PHP_EOL .
	'client-config-dir /etc/openvpn/dudko-web-panel/settings/'.$servername.'/' .PHP_EOL .
	'push "route-gateway 13.'.strval($servernum).'.0.1"' .PHP_EOL .
	'duplicate-cn' .PHP_EOL .
	'verb 1' .PHP_EOL .
	'cipher '.$variable['cipher'].' # Тип шифрования.' .PHP_EOL .
	'persist-key' .PHP_EOL .
	'log-append /etc/openvpn/dudko-web-panel/settings/'.$servername.'/openvpn.log # Лог-файл.' .PHP_EOL .
	'persist-tun' .PHP_EOL .
	'comp-lzo' .PHP_EOL;

	if(!file_put_contents('/etc/openvpn/'.$servername.'.conf', $config_file)){
		echo 'Не удалось записать файл '.$servername.'.conf!';
      	$log .= date("Y-m-d H:i:s").'     '.'Не удалось записать файл '.$servername.'.conf!';
  		$log .= PHP_EOL;
		return;
	}
	chmod('/etc/openvpn/'.$servername.'.conf', 0755);
	$serverstring = '/etc/openvpn/dudko-web-panel/rsa-key/'.$servername;
	if(($result_req == 'Data Base Updated') && file_exists($serverstring.'/ca.crt')  && file_exists($serverstring.'/ta.key')  
		&& file_exists($serverstring.'/server.crt')  && file_exists($serverstring.'/server.key')  && file_exists($serverstring.'/dh2048.pem')
		&& file_exists('/etc/openvpn/'.$servername.'.conf')){
			$result = exec('systemctl start openvpn@'.$servername, $callback);
			if($result == ""){
				cmd_exec('systemctl','systemctl enable openvpn@'.$servername, $callback, $err);
				if(substr($err[0], 0, 7) == 'Created'){
					unset($callback);unset($err);
					echo 'Сервер успешно создан!';
                  	$log .= date("Y-m-d H:i:s").'     '.'Сервер успешно создан!';
  					$log .= PHP_EOL;
					return;
				}
				unset($callback);unset($err);
				echo 'Не удалось добавить юнит в автозагрузку!';
              	$log .= date("Y-m-d H:i:s").'     '.'Не удалось добавить юнит в автозагрузку!';
  				$log .= PHP_EOL;
				return;
			}
			echo 'Не удалось запустить сервер!';
      		$log .= date("Y-m-d H:i:s").'     '.'Не удалось запустить сервер!';
      		$log .= PHP_EOL;
			return;
		}
	else {
		echo 'Произошла ошибка в конфигурации сервера!';
      	$log .= date("Y-m-d H:i:s").'     '.'Произошла ошибка в конфигурации сервера!';
      	$log .= PHP_EOL;
		RemoveServer($servername, $log2);
      	$log .= $log2;
		return;
	}
}

function RemoveServer($servername, &$log2){
	exec('rm -rf /etc/openvpn/dudko-web-panel/rsa-key/'.$servername.'/ y');
	sleep(2);
	exec('rm -rf /etc/openvpn/dudko-web-panel/settings/'.$servername.'/ y');
	sleep(2);
	exec('rm -rf /etc/openvpn/dudko-web-panel/easy-rsa/vars-'.$servername.' y');
	exec('rm -rf /etc/openvpn/'.$servername.'.conf y');
	exec('systemctl stop openvpn@'.$servername);
	exec('systemctl disable openvpn@'.$servername);
	echo 'Конфигурационные файлы удалены!';
  	$log2 .= date("Y-m-d H:i:s").'     '.'Конфигурационные файлы удалены!';
  	$log2 .= PHP_EOL;
	return;
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