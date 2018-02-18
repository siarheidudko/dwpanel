<?php 
/*Функция для шифрования/дешифрования строки по ключу
MyCryptoAES(md5('admin@sergdudko.tk').md5('999121'), 'email_pass', '!@#$1234', 'encrypt');
MyCryptoAES(md5('admin@sergdudko.tk').md5('999121'), 'email_pass', 'deqKkb9BQVzRXqjDefPrNA==', 'decrypt');

Аналог в js(требует CryptoJS):
//шифрование с помощью ключа (cryptoAES.php бэк)
function MyCryptoAES(mkey, salt, message, command){
  try{ 
    var key = CryptoJS.enc.Hex.parse(md5(mkey)); console.log('key: ' + md5(mkey));
    var iv = CryptoJS.enc.Hex.parse(md5(salt)); console.log('iv: ' + md5(salt));
    if(command == 'encrypt'){
      var encrypted = CryptoJS.AES.encrypt(
        message,key,
        {
          iv: iv,
          mode: CryptoJS.mode.CBC,
          padding: CryptoJS.pad.Pkcs7
        }
      );
      return (encrypted.toString());
    } else if(command == 'decrypt'){
      var decrypted = CryptoJS.AES.decrypt(
        message,key,
        {
          iv: iv,
          mode: CryptoJS.mode.CBC,
          padding: CryptoJS.pad.Pkcs7
        }
      );
      return (decrypted.toString(CryptoJS.enc.Utf8));
    } else {return;}
  } catch(e) {return e;}
}

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