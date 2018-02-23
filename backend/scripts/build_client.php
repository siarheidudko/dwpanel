<?php
/*
Dudko Web Panel v2.2.2
https://github.com/siarheidudko/dwpanel
(c) 2017-2018 by Siarhei Dudko.
https://github.com/siarheidudko/dwpanel/LICENSE
*/

//php /etc/openvpn/scripts/build_client.php --args -add 1 3 test@email.by
include(__DIR__ . '/settings.php'); 
$current = 'php /etc/openvpn/scripts/build_client.php --args '.$argv[2].' '.$argv[3].' '.$argv[4];
if(isset($argv[5])){
  	$current .= ' '.$argv[5];
}
$current .= PHP_EOL;
if(!file_exists('/etc/openvpn/dudko-web-panel/')) { 
  if(!mkdir('/etc/openvpn/dudko-web-panel/', 0755, true)) {
    echo 'Не удалось создать каталог .../dudko-web-panel/!';
    exit;
  }
}
if(!is_dir('/etc/openvpn/dudko-web-panel/logs')){ mkdir('/etc/openvpn/dudko-web-panel/logs');}
$file = '/etc/openvpn/dudko-web-panel/logs/server'.$argv[3].date("Y-m-d").'.log';
if(file_exists($file)){ $current .= file_get_contents($file); }
$servername = 'server'.$argv[3];

if(!file_exists('/etc/openvpn/dudko-web-panel/rsa-key/'.$servername.'/')) {
    echo 'Отсутствует каталог .../rsa-key/'.$servername.'/!';
    $current .= date("Y-m-d H:i:s").'     '.'Отсутствует каталог .../rsa-key/'.$servername.'/!';
    $current .= PHP_EOL;
  	echo PHP_EOL;
  	file_put_contents($file, $current);
  	exit;
}
if(!file_exists('/etc/openvpn/dudko-web-panel/settings/'.$servername.'/')) {
    echo 'Отсутствует каталог .../settings/'.$servername.'/!';
    $current .= date("Y-m-d H:i:s").'     '.'Отсутствует каталог .../settings/'.$servername.'/!';
    $current .= PHP_EOL;
  	echo PHP_EOL;
  	file_put_contents($file, $current);
  	exit;
}
if(!file_exists('/etc/openvpn/dudko-web-panel/easy-rsa/')) {
    echo 'Отсутствует каталог .../easy-rsa/!';
    $current .= date("Y-m-d H:i:s").'     '.'Отсутствует каталог .../easy-rsa/!';
    $current .= PHP_EOL;
  	echo PHP_EOL;
  	file_put_contents($file, $current);
  	exit;
}
if(!file_exists('/etc/openvpn/dudko-web-panel/easy-rsa/vars-'.$servername)) {
    echo 'Отсутствует файл конфигурации .../easy-rsa/vars-'.$servername.'!';
    $current .= date("Y-m-d H:i:s").'     '.'Отсутствует файл конфигурации .../easy-rsa/vars-'.$servername.'!';
    $current .= PHP_EOL;
  	echo PHP_EOL;
  	file_put_contents($file, $current);
  	exit;
}

if($argv[2] == '-add'){
  $clientstring = '/etc/openvpn/dudko-web-panel/rsa-key/'.$servername.'/client'.$argv[4].'.';
  if(file_exists($clientstring.'key') || file_exists($clientstring.'crt')) {
      echo 'Для клиента уже создан сертификат!';
      $current .= date("Y-m-d H:i:s").'     '.'Для клиента уже создан сертификат!';
      $current .= PHP_EOL;
      echo PHP_EOL;
      file_put_contents($file, $current);
      exit;
  }
  CreateClient($argv[3], $current, $argv[4], $argv[5]);
}
if($argv[2] == '-remove'){
  $clientstring = '/etc/openvpn/dudko-web-panel/rsa-key/'.$servername.'/client'.$argv[4].'.';
  if(!file_exists($clientstring.'key') || !file_exists($clientstring.'crt')) {
      echo 'Для клиента отсутствует сертификат!';
      $current .= date("Y-m-d H:i:s").'     '.'Для клиента отсутствует сертификат!';
      $current .= PHP_EOL;
      echo PHP_EOL;
      file_put_contents($file, $current);
      exit;
  }
  RemoveClient($argv[3], $current, $argv[4]);
}

echo PHP_EOL;
$current .= PHP_EOL;
file_put_contents($file, $current);
exit;

function cmd_exec($usermail, $cmd, &$stdout, &$stderr)
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
	if($usermail != ''){
		for($i=0;$i<7;$i++){ //7 переходов
			fwrite($pipes[0], "\n");
			usleep(200);
		}
      	fwrite($pipes[0], $usermail."\n");
        usleep(200);
        fwrite($pipes[0], "\n");
        usleep(200);
        fwrite($pipes[0], "\n");
        usleep(200);
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

function CreateClient($servernum, &$log, $clientnum, $clientmail){
	cmd_exec($clientmail, strval('cd /etc/openvpn/dudko-web-panel/easy-rsa/ && source ./vars-server'.$servernum .' && ./build-key client'.$clientnum), $callback, $err);
  	$result_req = substr($err[31], 0, 17);
  	unset($callback);unset($err);
  	$clientstring = '/etc/openvpn/dudko-web-panel/rsa-key/server'.$servernum.'/client'.$clientnum;
  	if(($result_req == 'Data Base Updated') && file_exists($clientstring.'.key') && file_exists($clientstring.'.crt')){
      	echo 'Сертификаты успешно созданы!';
      	exec('systemctl restart openvpn@server'.$servernum);
      	$log .= date("Y-m-d H:i:s").'     '.'Сертификаты успешно созданы!';
      	$log .= PHP_EOL;
    	return;
    }
  	echo 'Ошибка создания сертификатов!';
  	$log .= date("Y-m-d H:i:s").'     '.'Ошибка создания сертификатов!';
  	$log .= PHP_EOL;
  	return;
}

function RemoveClient($servernum, &$log, $clientnum){
  	$clientstring = '/etc/openvpn/dudko-web-panel/rsa-key/server'.$servernum;
  	if(!file_exists($clientstring.'/crl.pem')){
    	$new_conf = file_get_contents('/etc/openvpn/server'.$servernum.'.conf');
      	$new_conf = $new_conf . 'crl-verify ' . $clientstring.'/crl.pem' . PHP_EOL;
    }
	cmd_exec('', strval('cd /etc/openvpn/dudko-web-panel/easy-rsa/ && source ./vars-server'.$servernum .' && ./revoke-full client'.$clientnum), $callback, $err);
  	$result_req = substr($err[2], 0, 17);
  	unset($callback);unset($err);
  	if(($result_req == 'Data Base Updated') && file_exists($clientstring.'/crl.pem')){
      	if(isset($new_conf)){
        	file_put_contents('/etc/openvpn/server'.$servernum.'.conf', $new_conf);
        }
      	echo 'Сертификат успешно отозван!';
      	exec('systemctl restart openvpn@server'.$servernum);
      	$log .= date("Y-m-d H:i:s").'     '.'Сертификат успешно отозван!';
      	$log .= PHP_EOL;
    	return;
    }
  	echo 'Ошибка отзыва сертификата!';
  	$log .= date("Y-m-d H:i:s").'     '.'Ошибка отзыва сертификата!';
  	$log .= PHP_EOL; 
  	return;
}
exit;
?>