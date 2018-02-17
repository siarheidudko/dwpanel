//settings project
window.ee = new EventEmitter();
var config = {
    apiKey: "AIzaSyBPBoZyDD9Q51aiRjyL1mNjgAyUfQeEedc",
    authDomain: "vpnsergdudkotk.firebaseapp.com",
    databaseURL: "https://vpnsergdudkotk.firebaseio.com",
    storageBucket: "vpnsergdudkotk.appspot.com"
};
firebase.initializeApp(config);
var user;

//Авторизация на сервере
function AuthUser(email,pass){
  firebase.auth().signInWithEmailAndPassword(email, pass).then(function() {
    TestAuth();
	window.ee.emit('FirebaseAuth', true);
    var date = new Date(new Date().getTime() + 24 * 60 * 60 * 1000);
	document.cookie = "username=" + md5(email+'vpnsergdudkotk') + "; path=/; expires=" + date.toUTCString() + ";secure";
    document.cookie = "password=" + md5(pass+'vpnsergdudkotk') + "; path=/; expires=" + date.toUTCString() + ";secure";
  }).catch(function(error) {
    var errorCode = error.code;
    var errorMessage = error.message;
    console.log('error -> auth failed:' + error.message);
    window.ee.emit('SendPostResult', "Вы не авторизованы, по причине:" + error.message);
	LogoutUser();
  });
};

//Выход из аккаунта
function LogoutUser(){
  firebase.auth().signOut().then(function() {
    window.ee.emit('server', []);
    window.ee.emit('adminmail', []);
    window.ee.emit('FirebaseAuth', false);
    deleteCookie("username");
    deleteCookie("password");
  }).catch(function(error) {
    // An error happened.
  });
};

//Получить данные из firebase
function GetData(link){ 
	return new Promise(function (resolve){
		var message_arr = [];
		firebase.database().ref(link).once('value', function(snapshot) {
			snapshot.forEach(function(childSnapshot) {
				message_arr.push(childSnapshot.val());
			});
			resolve(message_arr);
		});
	});
}

//проверка авторизации и обновление полученных объектов
function TestAuth(){
  return new Promise(function (resolve){
    try {
      user = firebase.auth().currentUser;
      if (user) {
          GetData('/'+user.uid+'/server').then(function(value){ 
            value.push({
              "CA_EXPIRE" : "",
              "KEY_ALTNAMES" : "",
              "KEY_CITY" : "",
              "KEY_CN" : "",
              "KEY_COUNTRY" : "",
              "KEY_EMAIL" : "",
              "KEY_EXPIRE" : "",
              "KEY_NAME" : "",
              "KEY_ORG" : "",
              "KEY_OU" : "",
              "KEY_PROVINCE" : "",
              "KEY_SIZE" : "",
              "cipher" : "",
              "dev" : "",
              "hostname" : ""
            });
            window.ee.emit('server', value); 
          });
          GetData('/'+user.uid+'/adminmail').then(function(value){ 
            if(value.length === 0){ 
               value.push("","","","","","","","","");
            }
            window.ee.emit('adminmail', value);
          });
        resolve('auth');
      } else {
        console.log('error -> please auth!');
        window.ee.emit('SendPostResult', "Вы не авторизованы!");
        LogoutUser();
        resolve('noauth');
      }
    } catch(e) {
      console.log(e);
      LogoutUser();
      resolve('noauth');
    }
  });
}

//Отправка данных на сервер
function SendData(link, DataBody){
    user = firebase.auth().currentUser;
    if (user) {
      	var tokendata = new Date();
        tokendatastr = tokendata.toString();
        firebase.database().ref(link).set(DataBody).then(function(value){TestAuth(); window.ee.emit('SendPostResult', "Данные в firebase успешно обновлены!");});
    } else {
      console.log('error -> please auth!');
      window.ee.emit('SendPostResult', "Вы не авторизованы!");
	  LogoutUser();
    }
}

//проверка наличия кук у юзера
function PreLogin(){
  try{  	
    TestAuth().then(function(value){
		if(value === 'auth'){
		  window.ee.emit('FirebaseAuth', true);
		}
	});
  } catch(e){console.log(e)}
}

//работа с куками
function getCookie(name) {
  var matches = document.cookie.match(new RegExp(
    "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
  ));
  return matches ? decodeURIComponent(matches[1]) : undefined;
}
function deleteCookie(name) {
  document.cookie = name + "=null; path=/; expires=-1;secure";
}

//работа с бэкэндом
function SendPostRequest(Url, Request){
  if((typeof(getCookie("username")) !== 'undefined') && (typeof(getCookie("password")) !== 'undefined')){
  	var username = getCookie("username");
  	var password = getCookie("password");
  }
  try{
    window.ee.emit('InsertEmailAnswer', "");
    Request.auth = {"username":username,"password":password};
    xmlhttp=new XMLHttpRequest(); 
    xmlhttp.onreadystatechange=function() {
      if (xmlhttp.readyState==4 && xmlhttp.status==200) {
		if((Request.send != "toemail") && (typeof(Request.status) === "undefined")){
        	window.ee.emit('SendPostResult', xmlhttp.responseText);
        }else if(Request.send == "toemail"){
            window.ee.emit('AwaitPostResult',true);
          	window.ee.emit('InsertEmailAnswer', xmlhttp.responseText);
        }else if(typeof(Request.status) !== "undefined"){
          	window.ee.emit('AwaitPostResult',true);
            window.ee.emit('ServerInfo', xmlhttp.responseText);
        } else {
            window.ee.emit('AwaitPostResult',true);
        }
      }
    }  
    xmlhttp.open("POST",'https://'+ Url + '/core-web-ctrl.php',true);
    xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset=utf-8');
    xmlhttp.send(JSON.stringify(Request));
  } catch(e) {
    window.ee.emit('SendPostResult', 'Запрос завершен с ошибкой: ' + e);
  }
}

//md5() php функция, без поддержки кирилицы
var md5=new function(){
  var l='length',
  h=[
   '0123456789abcdef',0x0F,0x80,0xFFFF,
    0x67452301,0xEFCDAB89,0x98BADCFE,0x10325476
  ],
  x=[
    [0,1,[7,12,17,22]],
    [1,5,[5, 9,14,20]],
    [5,3,[4,11,16,23]],
    [0,7,[6,10,15,21]]
  ],
  A=function(x,y,z){
    return(((x>>16)+(y>>16)+((z=(x&h[3])+(y&h[3]))>>16))<<16)|(z&h[3])
  },
  B=function(s){
    var n=((s[l]+8)>>6)+1,b=new Array(1+n*16).join('0').split('');
    for(var i=0;i<s[l];i++)b[i>>2]|=s.charCodeAt(i)<<((i%4)*8);
    return(b[i>>2]|=h[2]<<((i%4)*8),b[n*16-2]=s[l]*8,b)
  },
  R=function(n,c){return(n<<c)|(n>>>(32-c))},
  C=function(q,a,b,x,s,t){return A(R(A(A(a,q),A(x,t)),s),b)},
  F=function(a,b,c,d,x,s,t){return C((b&c)|((~b)&d),a,b,x,s,t)},
  G=function(a,b,c,d,x,s,t){return C((b&d)|(c&(~d)),a,b,x,s,t)},
  H=function(a,b,c,d,x,s,t){return C(b^c^d,a,b,x,s,t)},
  I=function(a,b,c,d,x,s,t){return C(c^(b|(~d)),a,b,x,s,t)},
  _=[F,G,H,I],
  S=(function(){
    with(Math)for(var i=0,a=[],x=pow(2,32);i<64;a[i]=floor(abs(sin(++i))*x));
    return a
  })(),
  X=function (n){
    for(var j=0,s='';j<4;j++)
      s+=h[0].charAt((n>>(j*8+4))&h[1])+h[0].charAt((n>>(j*8))&h[1]);
    return s
  };
  return function(s){
    var $=B(''+s),a=[0,1,2,3],b=[0,3,2,1],v=[h[4],h[5],h[6],h[7]];
    for(var i,j,k,N=0,J=0,o=[].concat(v);N<$[l];N+=16,o=[].concat(v),J=0){
      for(i=0;i<4;i++)
        for(j=0;j<4;j++)
          for(k=0;k<4;k++,a.unshift(a.pop()))
            v[b[k]]=_[i](
              v[a[0]],
              v[a[1]],
              v[a[2]],
              v[a[3]],
              $[N+(((j*4+k)*x[i][1]+x[i][0])%16)],
              x[i][2][k],
              S[J++]
            );
      for(i=0;i<4;i++)
        v[i]=A(v[i],o[i]);
    };return X(v[0])+X(v[1])+X(v[2])+X(v[3]);
}};

//шифрование с помощью ключа (cryptoAES.php бэк)
function MyCryptoAES(mkey, salt, message, command){
  try{ 
    var key = CryptoJS.enc.Hex.parse(md5(mkey)); 
    var iv = CryptoJS.enc.Hex.parse(md5(salt)); 
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