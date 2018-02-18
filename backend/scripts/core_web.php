<?php
$content = file_get_contents("php://input");
$command = json_decode($content, TRUE);
header("Content-type: text/txt; charset=utf-8");

/* проверка имени пользователя и пароля */
include(__DIR__ . '/settings.php');
if(($db_user != 'testuser@sergdudko.tk') && ($db_pass != 'password')){
  if(($command['auth']['username'] != md5($db_user.'vpnsergdudkotk')) || ($command['auth']['password'] != md5($db_pass.'vpnsergdudkotk'))){
      echo 'Авторизация не пройдена!' . PHP_EOL;
      exit;
  }
}

if(isset($command['build'])){
    $exec = 'php /etc/openvpn/scripts/build_'.$command['build'].'.php --args ';
    if(!isset($command['server']) || !isset($command['com'])){
		helper();
    }
    if($command['build'] == 'server'){
        $exec .= '-'.$command['com'] . ' ' . $command['server'];
    }
    if($command['build'] == 'client'){
      	if(!isset($command['client'])){
            helper();
        }
      	if($command['com'] != 'remove'){
          	if(!isset($command['email'])){
              	helper();
            }
        }
     	$exec .= '-'.$command['com'] . ' ' . $command['server'] . ' ' . $command['client'];
      	if(isset($command['email'])){
          	 $exec .= ' ' . $command['email'];
        }
    }
}

if(isset($command['send'])){
  if($command['send'] == 'toemail'){
    	if(!isset($command['server']) || !isset($command['email']) || !isset($command['client'])){
            helper();
        }
        $exec = 'php /etc/openvpn/scripts/cert_send_'.$command['send'].'.php --args '.$command['server'].' '.$command['client'].' '.$command['email'].'';
  }
}

if(isset($exec)){
	exec($exec, $callback);
  	for($i=0;$i<count($callback);$i++){
      	echo $callback[$i];
      	if(($i+1)<count($callback)){
          	echo PHP_EOL;
        }
    }
  	echo PHP_EOL;
} elseif(isset($command['settings'])){
    settings($command);
} elseif(isset($command['status'])){
    status($command);
} else {
    helper();
}

function helper(){
    echo 'Доступные аргументы:' .PHP_EOL;
  	echo 'Вызов справки: -help' .PHP_EOL;
    echo 'Создать/удалить openvpn сервер: build:server com:add(или remove) server:1(численный номер сервера)' .PHP_EOL;
    echo 'Создать/удалить сертификаты клиента: build:client com:add(или remove) server:1(численный номер сервера, к которому будет привязан клиент)  email:admin@sergdudko.tk  client:1(численный номер клиентского сертификата)' .PHP_EOL;
    echo 'Отправить сертификаты клиента на email: send:toemail(отправка на email) server:1(численный номер сервера, к которому будет привязан клиент)  email:admin@sergdudko.tk  client:1(численный номер клиентского сертификата)' .PHP_EOL;
  	echo 'Для настройки программы, наберите settings: {db_host:hostname(адрес БД MySQL или ip) db_name:name(имя БД) db_user:user(пользователь БД) db_pass:password(пароль пользователя БД) encoding:Europe/Minsk(таймзона)}' .PHP_EOL;
    echo PHP_EOL;
    exit;
}

function settings($command){
   include(__DIR__ . '/cryptoAES.php');
   $settings_file = "/etc/openvpn/scripts/settings.php";
   if(!file_exists($settings_file)){
     	echo 'Файл с настройками не найден!' . PHP_EOL;
     	exit;
   }
   $handle = @fopen($settings_file, "r");
   if ($handle) {
     	$i=0;
        while (($buffer = fgets($handle, 4096)) !== false) {
            $str[$i] = $buffer;
            $i++;
        }
        if (!feof($handle)) {
            echo "Error: unexpected fgets() fail\n";
        }
   		fclose($handle);
   }
   $current = '';
   $flag = 0;
   for($i=0;$i<count($str);$i++){
       if(isset($command['settings']['db_key']) && (substr($str[$i], 0, 7) == '$db_key')){
			$str[$i] = '$db_key = \''.$command['settings']['db_key'].'\';       //токен проекта firebase' . PHP_EOL;
			$flag = 1;
		}
       if(isset($command['settings']['db_projectname']) && (substr($str[$i], 0, 15) == '$db_projectname')){
			$str[$i] = '$db_projectname = \''.$command['settings']['db_projectname'].'\';       //название проекта firebase' . PHP_EOL;
			$flag = 1;
		}
       if(isset($command['settings']['db_user']) && (substr($str[$i], 0, 8) == '$db_user')){
			$str[$i] = '$db_user = \''.$command['settings']['db_user'].'\';         //пользователь firebase' . PHP_EOL;
         	$flag = 1;
       } 
       if(isset($command['settings']['db_pass']) && (substr($str[$i], 0, 8) == '$db_pass')){
         	try{
         		$realpass = MyCryptoAES(md5($command['settings']['db_user'].'vpnsergdudkotk'), 'settings', $command['settings']['db_pass'], 'decrypt');
            } catch (Exception $e) {
                echo 'Произошла ошибка: '.$e;
                exit;
            }
			$str[$i] = '$db_pass = \''.$realpass.'\';         //пароль' . PHP_EOL;
         	$flag = 1;
       }  
       if(isset($command['settings']['timezone']) && (substr($str[$i], 0, 25) == 'date_default_timezone_set')){
			$str[$i] = 'date_default_timezone_set(\''.$command['settings']['timezone'].'\');' . PHP_EOL;
         	$flag = 1;
       }
       $current .= $str[$i];
   }
   if($flag == 1) {
     	if(file_put_contents($settings_file, $current)){
          	echo 'Настройки изменены!' . PHP_EOL;
        }
   } else {
     	echo 'Параметры: db_key:ключ API проекта(firebase) db_user:user(пользователь БД) db_pass:password(пароль пользователя БД) encoding:Europe/Minsk(таймзона)' . PHP_EOL;
     	echo 'settings: обязательный префикс, лишние параметры можно пропустить' . PHP_EOL;
   }
   exit;
}

function status($command){
   echo exec('systemctl status openvpn@server'.$command['status']['server'].'.service |grep Active');
   exit;
}

exit;
?>