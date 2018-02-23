<?php 
/*
Dudko Web Panel v2.2.2
https://github.com/siarheidudko/dwpanel
(c) 2017-2018 by Siarhei Dudko.
https://github.com/siarheidudko/dwpanel/LICENSE
*/

function MyCryptoAES($mkey, $salt, $message, $command){
  try {
    if($command == 'encrypt') {
      $key = pack("H*", md5($mkey)); 
      $iv =  pack("H*", md5($salt)); 
      $shown = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $message, MCRYPT_MODE_CBC, $iv);
      return base64_encode($shown);
    } elseif($command == 'decrypt'){
      $key = pack("H*", md5($mkey));
      $iv =  pack("H*", md5($salt)); 
      $encrypted = base64_decode($message);
      $shown = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $encrypted, MCRYPT_MODE_CBC, $iv);
      $dec_s = strlen($shown); 
      $padding = ord($shown[$dec_s-1]); 
      $shown = substr($shown, 0, -$padding);
      return trim($shown);
    } else {
      return;
    }
  } catch(Exception $e) {
     return $e;
  }
}
?>