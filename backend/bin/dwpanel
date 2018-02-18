#!/usr/bin/env php
<?php
if(isset($argv)){
  for($i=0;$i<count($argv);$i++){
      if($argv[$i] == '-help'){
          helper();
      }
      if($argv[$i] == '-settings'){
          settings($argv);
      }
      $massive = explode(':', $argv[$i]);
      if(isset($massive[0]) && isset($massive[1])) {
      	  $command[$massive[0]] = $massive[1];
  	  }
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
} else {
  	helper();
}

function helper(){
    echo 'Доступные аргументы:' .PHP_EOL;
  	echo 'Вызов справки: -help' .PHP_EOL;
    echo 'Создать/удалить openvpn сервер: build:server com:add(или remove) server:1(численный номер сервера)' .PHP_EOL;
    echo 'Создать/удалить сертификаты клиента: build:client com:add(или remove) server:1(численный номер сервера, к которому будет привязан клиент)  email:admin@sergdudko.tk  client:1(численный номер клиентского сертификата)' .PHP_EOL;
    echo 'Отправить сертификаты клиента на email: send:toemail(отправка на email) server:1(численный номер сервера, к которому будет привязан клиент)  email:admin@sergdudko.tk  client:1(численный номер клиентского сертификата)' .PHP_EOL;
  	echo 'Для настройки программы, наберите -settings' .PHP_EOL;
    echo PHP_EOL;
    exit;
}

function settings($argv){
   for($i=0;$i<count($argv);$i++){
      if($argv[$i] != '-settings'){
          $massive = explode(':', $argv[$i]);
          if(isset($massive[0]) && isset($massive[1])) {
              $command[$massive[0]] = $massive[1];
          }
      }
   }
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
       if(isset($command['db_key']) && (substr($str[$i], 0, 7) == '$db_key')){
			$str[$i] = '$db_key = \''.$command['db_key'].'\';       //токен проекта firebase' . PHP_EOL;
         	$flag = 1;
       }  
       if(isset($command['db_projectname']) && (substr($str[$i], 0, 15) == '$db_projectname')){
			$str[$i] = '$db_projectname = \''.$command['db_projectname'].'\';       //имя проекта firebase' . PHP_EOL;
         	$flag = 1;
       }
       if(isset($command['db_user']) && (substr($str[$i], 0, 8) == '$db_user')){
			$str[$i] = '$db_user = \''.$command['db_user'].'\';         //пользователь firebase' . PHP_EOL;
         	$flag = 1;
       } 
       if(isset($command['db_pass']) && (substr($str[$i], 0, 8) == '$db_pass')){
			$str[$i] = '$db_pass = \''.$command['db_pass'].'\';         //пароль' . PHP_EOL;
         	$flag = 1;
       }  
       if(isset($command['timezone']) && (substr($str[$i], 0, 25) == 'date_default_timezone_set')){
			$str[$i] = 'date_default_timezone_set(\''.$command['timezone'].'\');' . PHP_EOL;
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
     	echo '-settings обязательный префикс, лишние параметры можно пропустить' . PHP_EOL;
   }
   exit;
}

exit;
?>