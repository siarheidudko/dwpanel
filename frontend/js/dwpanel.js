/*
Dudko Web Panel v2.5.0
https://github.com/siarheidudko/dwpanel
(c) 2017-2018 by Siarhei Dudko.
https://github.com/siarheidudko/dwpanel/LICENSE
*/

class BlockPopUp extends React.Component{
  
   constructor(props, context){
      super(props, context);
      this.state = {
        PopupStatus: true,
      };
    }
      
	componentDidMount() {
		var self = this;
		window.ee.addListener('AwaitPostResult', function(item) {
			self.setState({PopupStatus: item});
		});
        window.ee.addListener('SendPostResult', function(item) {
			self.setState({PopupStatus: true});
		});
	}
      
	componentWillUnmount() {
		window.ee.removeListener('AwaitPostResult');
      	window.ee.removeListener('SendPostResult');
	}
      
  	render() {
      return (
        <div className={(this.state.PopupStatus)?"blockpopup unshow":"blockpopup show"} >
  			<div className={(this.state.PopupStatus)?"blockpopupimage unshow":"blockpopupimage show"} >
  				<img src="img/AwaitPostResult.gif" alt="Выполняется запрос..." />
            </div>
        </div>
      );
	}
};

class MyPopUp extends React.Component{
  
   constructor(props, context) {
      super(props, context);
      this.state = {
        PopupText: '',
      };
      this.onDivClickHandler = this.onDivClickHandler.bind(this);
    }
      
	componentDidMount() {
		var self = this;
		window.ee.addListener('SendPostResult', function(item) {
			self.setState({PopupText: item});
          	setTimeout(function(){self.setState({PopupText: ''});}, 2000);
          	window.ee.emit('ServerLoadInfo',true);
		});
	}
      
	componentWillUnmount() {
		window.ee.removeListener('SendPostResult');
	}
      
  	onDivClickHandler(e) {
		this.setState({PopupText: ''});
	}
      
  	render() {
      return (
        <div className={(this.state.PopupText == "")?"popup unshow":"popup show"} onClick={this.onDivClickHandler}>
  			<span className="popuptext" id="myPopup">{this.state.PopupText}</span>
        </div>
      );
	}
};

class Server extends React.Component{
  
   constructor(props, context) {
      super(props, context);
      this.state = {
        MailEdit: '',
		ServerEdit: '',
      };
      this.onBtnClickHandler = this.onBtnClickHandler.bind(this);
      this.onChangeHandler = this.onChangeHandler.bind(this);
    }
      
/*	propTypes: {
		data: React.PropTypes.number.isRequired
	},*/
	componentDidMount() {
		var self = this;
		window.ee.addListener('mailedit', function(item) {
			self.setState({MailEdit: item});
		});
		window.ee.addListener('serveredit', function(item) {
			self.setState({ServerEdit: item});
		});
	}
      
	componentWillUnmount() {
		window.ee.removeListener('mailedit');
		window.ee.removeListener('serveredit');
	}
      
	onBtnClickHandler() {
		var EditedServer = {
			CA_EXPIRE: ReactDOM.findDOMNode(this.refs.CA_EXPIRE).value,
			KEY_ALTNAMES: ReactDOM.findDOMNode(this.refs.KEY_ALTNAMES).value,
			KEY_CITY: ReactDOM.findDOMNode(this.refs.KEY_CITY).value,
			KEY_CN: ReactDOM.findDOMNode(this.refs.KEY_CN).value,
			KEY_COUNTRY: ReactDOM.findDOMNode(this.refs.KEY_COUNTRY).value,
			KEY_EMAIL: ReactDOM.findDOMNode(this.refs.KEY_EMAIL).value,
			KEY_EXPIRE: ReactDOM.findDOMNode(this.refs.KEY_EXPIRE).value,
			KEY_NAME: ReactDOM.findDOMNode(this.refs.KEY_NAME).value,
			KEY_ORG: ReactDOM.findDOMNode(this.refs.KEY_ORG).value,
			KEY_OU: ReactDOM.findDOMNode(this.refs.KEY_OU).value,
			KEY_PROVINCE: ReactDOM.findDOMNode(this.refs.KEY_PROVINCE).value,
			KEY_SIZE: ReactDOM.findDOMNode(this.refs.KEY_SIZE).value,
			cipher: ReactDOM.findDOMNode(this.refs.cipher).value,
			dev: ReactDOM.findDOMNode(this.refs.dev).value,
			hostname: ReactDOM.findDOMNode(this.refs.hostname).value
		};
		var linkDb = user.uid + '/server/' + this.props.data + '/';
		SendData(linkDb, EditedServer);
		if(this.state.MailEdit != ''){
			var linkDb = user.uid + '/adminmail/';
			SendData(linkDb, this.state.MailEdit);
		}
	}
      
	onChangeHandler(e) {
		var EditedServer = {
			CA_EXPIRE: ReactDOM.findDOMNode(this.refs.CA_EXPIRE).value,
			KEY_ALTNAMES: ReactDOM.findDOMNode(this.refs.KEY_ALTNAMES).value,
			KEY_CITY: ReactDOM.findDOMNode(this.refs.KEY_CITY).value,
			KEY_CN: ReactDOM.findDOMNode(this.refs.KEY_CN).value,
			KEY_COUNTRY: ReactDOM.findDOMNode(this.refs.KEY_COUNTRY).value,
			KEY_EMAIL: ReactDOM.findDOMNode(this.refs.KEY_EMAIL).value,
			KEY_EXPIRE: ReactDOM.findDOMNode(this.refs.KEY_EXPIRE).value,
			KEY_NAME: ReactDOM.findDOMNode(this.refs.KEY_NAME).value,
			KEY_ORG: ReactDOM.findDOMNode(this.refs.KEY_ORG).value,
			KEY_OU: ReactDOM.findDOMNode(this.refs.KEY_OU).value,
			KEY_PROVINCE: ReactDOM.findDOMNode(this.refs.KEY_PROVINCE).value,
			KEY_SIZE: ReactDOM.findDOMNode(this.refs.KEY_SIZE).value,
			cipher: ReactDOM.findDOMNode(this.refs.cipher).value,
			dev: ReactDOM.findDOMNode(this.refs.dev).value,
			hostname: ReactDOM.findDOMNode(this.refs.hostname).value
		};
		this.setState({ServerEdit: EditedServer})
	}
      
	render() {
		if(this.state.ServerEdit !== ''){
			var DwpanelBodySettingsServerBody = new Array;
			var RealName = {"CA_EXPIRE":"Срок действия сертификата сервера(пример 3650): ", 
							"KEY_ALTNAMES":"Алтернативное имя ключа(пример VPNSERGDUDKOTK): ", 
							"KEY_CITY":"Город(пример: Minsk): ", 
							"KEY_CN":"Адрес сайта(пример: sergdudko.tk): ", 
							"KEY_COUNTRY":"Международный код страны(пример: BY): ", 
							"KEY_EMAIL":"Адрес электронной почты(пример: admin@sergdudko.tk): ", 
							"KEY_EXPIRE":"Срок действия сертификата ключа, дней(пример: 1095): ", 
							"KEY_NAME":"Имя ключа(пример: Searhei Dudko Key): ", 
							"KEY_ORG":"Наименование организации(пример: Siarhei Dudko Service): ",
							"KEY_OU":"Филиал организации(пример: VPN Service): ",
							"KEY_PROVINCE":"Международный код города(пример: MSQ): ",
							"KEY_SIZE":"Размер ключа(пример: 2048): ",
							"cipher":"Вид шифрования(пример: DES-EDE3-CBC): ",
							"dev":"Тип адаптера(реализован только: tap): ",
							"hostname":"Адрес VPN-сервера(пример: vpn.sergdudko.tk): "};
			for (var key in this.state.ServerEdit) {
				if(typeof(this.state.ServerEdit[key]) !== 'undefined'){ 
					DwpanelBodySettingsServerBody.push(<div key={key}> {RealName[key]} <input className={key} ref={key}  onChange={this.onChangeHandler}  value={this.state.ServerEdit[key]} placeholder={this.state.ServerEdit[key]} /> </div>);
				} else {
					DwpanelBodySettingsServerBody.push(<div key={key}> {RealName[key]} <input className={key} ref={key}  onChange={this.onChangeHandler}  value={key} placeholder={key} /> </div>);
				}
			}
		}
		return (
			<div className="DwpanelBodySettingsServer">
				<center><h3> Сервер №{this.props.data +1} </h3></center>
          		<br />
					{DwpanelBodySettingsServerBody}
				<button onClick={this.onBtnClickHandler} id='submit' disabled={false}>Применить</button>
			</div>
		);
	}
};

class Mail extends React.Component{
  
   constructor(props, context) {
      super(props, context);
    }
/*	propTypes: {
		data: React.PropTypes.array.isRequired
	}, */
	onChangeHandler(e){
		var EditedMail = {
			email_host: ReactDOM.findDOMNode(this.refs.Mail_0).value,
			email_host_reserve: ReactDOM.findDOMNode(this.refs.Mail_1).value,
			email_name: ReactDOM.findDOMNode(this.refs.Mail_2).value,
			email_pass: MyCryptoAES(getCookie("username")+getCookie("password"), 'email_pass', ReactDOM.findDOMNode(this.refs.Mail_3).value, 'encrypt'),
			email_pass_reserve: MyCryptoAES(getCookie("username")+getCookie("password"), 'email_pass_reserve', ReactDOM.findDOMNode(this.refs.Mail_4).value, 'encrypt'),
			email_port: ReactDOM.findDOMNode(this.refs.Mail_5).value,
			email_port_reserve: ReactDOM.findDOMNode(this.refs.Mail_6).value,
			email_user: ReactDOM.findDOMNode(this.refs.Mail_7).value,
			email_user_reserve: ReactDOM.findDOMNode(this.refs.Mail_8).value
		};
		window.ee.emit('mailedit', EditedMail);
	}
      
	render() {
		if(typeof(this.props.data) !== 'undefined'){
			var DwpanelBodySettingsMailBody = new Array;
			var RealName = {"0":"Основной SMTP-сервер: ", 
							"1":"Резервный SMTP-сервер: ", 
							"2":"Имя отправителя: ", 
							"3":"Пароль основного почтового ящика: ", 
							"4":"Пароль резервного почтового ящика: ", 
							"5":"Порт основного smtp-сервера: ", 
							"6":"Порт резервного smtp-сервера: ", 
							"7":"Логин основного почтового ящика: ", 
							"8":"Логин резервного почтового ящика: "};
			for (var key in this.props.data) {
				if(typeof(this.props.data[key]) !== 'undefined'){ 
					DwpanelBodySettingsMailBody.push(<div key={key}> {RealName[key]} <input type={((key != "3") && (key != "4"))?"text":"password"} className={key} onChange={this.onChangeHandler} ref={('Mail_' + key)} defaultValue={((key != "3") && (key != "4"))?this.props.data[key]:MyCryptoAES(getCookie("username")+getCookie("password"), ((key == 3)?"email_pass":"email_pass_reserve") , this.props.data[key], 'decrypt')} /> </div>);
				} else {
					DwpanelBodySettingsMailBody.push(<div key={key}> {RealName[key]} <input type={((key != "3") && (key != "4"))?"text":"password"} className={key} onChange={this.onChangeHandler} ref={('Mail_' + key)} defaultValue={key} /> </div>);
				}
			}
		}
		return (
			<div className="DwpanelBodySettingsMail">
				{DwpanelBodySettingsMailBody}
			</div>
		);
	}
};

class Settings extends React.Component{
  
   constructor(props, context) {
      super(props, context);
      PreLogin();
      this.state = {
        server: [],
		adminmail: [],
		thisServer: 0,
		FirebaseAuth: false,
        FirebaseRegistration: false,
        panel: 0,
        EmailAnswer: "",
        ServerInfo: "",
        ResurceRegistr: "",
      };
      this.onBtnClickHandler = this.onBtnClickHandler.bind(this);
    }
      
	componentDidMount() {
		var self = this;
		window.ee.addListener('server', function(item) {
			self.setState({server: item});
			window.ee.emit('serveredit', self.state.server[self.state.thisServer]);
		});
		window.ee.addListener('adminmail', function(item) {
			self.setState({adminmail: item});
			window.ee.emit('serveredit', self.state.server[self.state.thisServer]);
		});
		window.ee.addListener('FirebaseAuth', function(item) {
			self.setState({FirebaseAuth: item});
		});
        window.ee.addListener('InsertEmailAnswer', function(item) {
			self.setState({EmailAnswer: item});
		});
        window.ee.addListener('ServerInfo', function(item) {
			self.setState({ServerInfo: item});
		});
      	window.ee.addListener('ServerLoadInfo', function(item) {
            if(self.state.panel === 1){
				var Url = self.state.server[self.state.thisServer].hostname;
            	var Request = {"status":{"server":String(self.state.thisServer)}};
            	SendPostRequest(Url, Request);
            }
		});
	}
      
	componentWillUnmount() {
		window.ee.removeListener('server');
		window.ee.removeListener('adminmail');
        window.ee.removeListener('FirebaseAuth');
        window.ee.removeListener('InsertEmailAnswer');
        window.ee.removeListener('ServerInfo');
        window.ee.removeListener('ServerLoadInfo');
	}
      
	onBtnClickHandler(e) {
      	switch (e.target.id){
          case 'back_server':
            this.setState({thisServer: -- this.state.thisServer});
			window.ee.emit('serveredit', this.state.server[this.state.thisServer]);
            if(this.state.panel === 1){
            	var Url = this.state.server[this.state.thisServer].hostname;
            	var Request = {"status":{"server":String(this.state.thisServer)}};
            	window.ee.emit('AwaitPostResult',false);
            	SendPostRequest(Url, Request);
            }
            break;
          case 'next_server':
            this.setState({thisServer: ++ this.state.thisServer});
			window.ee.emit('serveredit', this.state.server[this.state.thisServer]);
            if(this.state.panel === 1){
            	var Url = this.state.server[this.state.thisServer].hostname;
            	var Request = {"status":{"server":String(this.state.thisServer)}};
            	window.ee.emit('AwaitPostResult',false);
            	SendPostRequest(Url, Request);
            }
            break;
          case 'firebase_logout':
            LogoutUser();
            break;
          case 'firebase_login':
            AuthUser(ReactDOM.findDOMNode(this.refs.firebase_login).value,ReactDOM.findDOMNode(this.refs.firebase_password).value);
            break;
          case 'firebase_registration':
            this.setState({FirebaseRegistration: true});
            break;
          case 'firebase_registration_go':
            window.ee.emit('AwaitPostResult',true);
            RegistrationServer(ReactDOM.findDOMNode(this.refs.firebase_login).value, ReactDOM.findDOMNode(this.refs.firebase_password).value, ReactDOM.findDOMNode(this.refs.server_address).value, this.state.ResurceRegistr);
            break;
          case 'firebase_registration_back':
            this.setState({FirebaseRegistration: false});
            break;
          case 'firebase_update':
            this.setState({server: this.state.server.splice(this.state.thisServer, 1)});
          	this.setState({server: this.state.server.pop()});
          	var linkDb = user.uid + '/server/';
			SendData(linkDb, this.state.server);
            break;
          case 'server_com':
            this.setState({panel: 1});
            var Url = this.state.server[this.state.thisServer].hostname;
            var Request = {"status":{"server":String(this.state.thisServer)}};
            window.ee.emit('AwaitPostResult',false);
            SendPostRequest(Url, Request);
            break;
          case 'server_settings':
            this.setState({panel: 0});
            break;
          case 'server_create':
            var Url = this.state.server[this.state.thisServer].hostname;
            var Request = {"build":"server","com":"add","server":String(this.state.thisServer)};
            window.ee.emit('AwaitPostResult',false);
            SendPostRequest(Url, Request);
            break;
          case 'server_delete':
            var Url = this.state.server[this.state.thisServer].hostname;
            var Request = {"build":"server","com":"remove","server":String(this.state.thisServer)};
            window.ee.emit('AwaitPostResult',false);
            SendPostRequest(Url, Request);
            break;
          case 'client_create':
            var Url = this.state.server[this.state.thisServer].hostname;
            var ClientEmail = ReactDOM.findDOMNode(this.refs.ServerParamEmail).value;
            var ClientNum = ReactDOM.findDOMNode(this.refs.ServerParamNum).value;
            var Request = {"build":"client","com":"add","server":String(this.state.thisServer),"client":String(ClientNum),"email":String(ClientEmail)};
            if ((Request.client != "") && (Request.email != "")){
               window.ee.emit('AwaitPostResult',false);
               SendPostRequest(Url, Request);
            }else {
               window.ee.emit('SendPostResult', "Заполните email и номер клиента!");
            }
            break;
          case 'client_delete':
            var Url = this.state.server[this.state.thisServer].hostname;
            var ClientNum = ReactDOM.findDOMNode(this.refs.ServerParamNum).value;
            var Request = {"build":"client","com":"remove","server":String(this.state.thisServer),"client":String(ClientNum)};
            if (Request.client != ""){
               window.ee.emit('AwaitPostResult',false);
               SendPostRequest(Url, Request);
            }else {
               window.ee.emit('SendPostResult', "Заполните номер клиента!");
            }
            break;
          case 'client_send_to':
            var Url = this.state.server[this.state.thisServer].hostname;
            var ClientEmail = ReactDOM.findDOMNode(this.refs.ServerParamEmail).value;
            var ClientNum = ReactDOM.findDOMNode(this.refs.ServerParamNum).value;
            var Request = {"send":"toemail","server":String(this.state.thisServer),"client":String(ClientNum),"email":String(ClientEmail)};
            if ((Request.client != "") && (Request.email != "")){
               window.ee.emit('AwaitPostResult',false);
               SendPostRequest(Url, Request);
            }else {
               window.ee.emit('SendPostResult', "Заполните email и номер клиента!");
            }
            break;
          case 'server_resurce_firebase':
            this.setState({ResurceRegistr : 'firebase'});
            break;
          case 'server_resurce_server':
            this.setState({ResurceRegistr : 'server'});
            break;
        }
	}
      
	render() {
        var DwpanelBodySettingsA =  <div>
          							  <div className={(this.state.panel === 1)?"none":""}>
              							<button onClick={this.onBtnClickHandler} id='server_com' className="ServerState" disabled={(this.state.thisServer >= (this.state.server.length-1))?true:false}>Управление настройками (FIREBASE)</button>
                                        <br />
          								<button onClick={this.onBtnClickHandler} id='back_server' disabled={(this.state.thisServer <= 0)?true:false} className={(this.state.thisServer <= 0)?"none":""}>Предыдущий</button>
                                        <button onClick={this.onBtnClickHandler} id='next_server' disabled={(this.state.thisServer >= (this.state.server.length-1))?true:false} className={(this.state.thisServer >= (this.state.server.length-1))?"none":""}>Следующий</button>
                                        <button onClick={this.onBtnClickHandler} id='firebase_logout'>Выход</button>
                                        <hr /><br />
                                        <Mail data={this.state.adminmail} />  {/* настройки исходящей почты */}
                                        <hr />
                                        <Server data={this.state.thisServer} />  {/* настройки openvpn сервера */}
                                        <text className="warning">Удаление сервера потребует пересоздать весь пул!</text> <button onClick={this.onBtnClickHandler} id='firebase_update' disabled={(this.state.thisServer >= (this.state.server.length-1))?true:false} className={(this.state.thisServer >= (this.state.server.length-1))?"none":""}>Удалить сервер</button>
                                     	<hr />
                                        <center><a href="tel:+375292402646" className="SiarheiContacts">2018©by siarhei dudko</a></center>
                                        <br />
                                     </div>
                                     <div className={(this.state.panel === 0)?"none":""}>
              							<button onClick={this.onBtnClickHandler} id='server_settings' className="ServerState">Управление сервером (VPN)</button>
                                        <br />
          								<button onClick={this.onBtnClickHandler} id='back_server' disabled={(this.state.thisServer <= 0)?true:false} className={(this.state.thisServer <= 0)?"none":""}>Предыдущий</button>
                                        <button onClick={this.onBtnClickHandler} id='next_server' disabled={(this.state.thisServer >= (this.state.server.length-1))?true:false} className={(this.state.thisServer >= (this.state.server.length-2))?"none":""}>Следующий</button>
                                        <button onClick={this.onBtnClickHandler} id='firebase_logout'>Выход</button>
                                        <hr /><br />
                                        <center><h3> Сервер №{this.state.thisServer +1} </h3></center>
                                        <center><text className={(this.state.ServerInfo.indexOf("active (running)") != -1)?"good":"warning"}>{this.state.ServerInfo}</text></center>
                                        <br /><br />
                                        <div className="ServerControlParam ServerControlLeft">
                                          <div>
                                            Введите email клиента: 
      										<input className="ServerParam" onChange={this.onChangeHandler} ref="ServerParamEmail" defaultValue="" /> 
                                          </div>
                                          <br />
                                          <div>
                                            Введите номер клиента: 
                                            <input className="ServerParam" onChange={this.onChangeHandler} ref="ServerParamNum" defaultValue="" /> 
                                          </div>
                                          <div>
                                              <textarea value={this.state.EmailAnswer} className={(this.state.EmailAnswer == "")?"none":"EmailAnswer"} />
                                          </div>
                                       </div>
                                       <div className="ServerControlRight">
                                         <div><button onClick={this.onBtnClickHandler} id='server_create' className="ServerControlPanel">Создать сервер</button></div>
                                         <div><button onClick={this.onBtnClickHandler} id='server_delete' className="ServerControlPanel">Удалить сервер</button></div>
                                         <div><button onClick={this.onBtnClickHandler} id='client_create' className="ServerControlPanel">Создать клиентский сертификат</button></div>
                                         <div><button onClick={this.onBtnClickHandler} id='client_delete' className="ServerControlPanel">Отозвать клиентский сертификат</button></div>
                                         <div><button onClick={this.onBtnClickHandler} id='client_send_to' className="ServerControlPanel">Выслать сертификаты на email</button></div>
                                       </div>
                                       <div className="ServerControlBottom">
                                         <hr />
                                         <center><a href="tel:+375292402646" className="SiarheiContacts">2018©by siarhei dudko</a></center>
                                         <br />
                                       </div>
                                     </div>
                                   </div>;
		var DwpanelBodySettings;
		if(this.state.FirebaseAuth){
			DwpanelBodySettings = 	<div className="DwpanelBodySettingsA">
              							<MyPopUp />
              							<BlockPopUp />
              							{DwpanelBodySettingsA}
                                    </div>;
		} else { 
          if(this.state.FirebaseRegistration){
              DwpanelBodySettings = 	<div className="DwpanelBodySettingsC">
                						  <MyPopUp />
                						  <BlockPopUp />
                                          <div className="DwpanelBodySettingsCIn">
                                              <div key="firebase_login">    Email:  <input className="firebase_login" ref="firebase_login" defaultValue="testuser@sergdudko.tk" /> </div>
                                              <div key="firebase_password"> Пароль: <input type="password" className="firebase_password" ref="firebase_password" defaultValue="password" /> </div>
                                              <div key="server_address"> Адрес: <input className="server_address" ref="server_address" defaultValue={window.location.hostname} /> </div>
                                              <div key="server_resurce">FIREBASE: <input name="server_resurce" className="server_resurce" type="radio" onClick={this.onBtnClickHandler} id="server_resurce_firebase" /> VPN-сервер: <input name="server_resurce" className="server_resurce" type="radio" onClick={this.onBtnClickHandler} id="server_resurce_server" /></div>
                                              <div key="firebase_login_btn"><br /><button onClick={this.onBtnClickHandler} id='firebase_registration_go'>Зарегистрироваться</button> <button onClick={this.onBtnClickHandler} id='firebase_registration_back'>Назад</button> </div>
                                          </div>
                                      </div>;
          } else {
              DwpanelBodySettings = 	<div className="DwpanelBodySettingsB">
                						  <MyPopUp />
                						  <BlockPopUp />
                                          <div className="DwpanelBodySettingsBIn">
                                              <div key="firebase_login">    Логин:  <input className="firebase_login" ref="firebase_login" defaultValue="testuser@sergdudko.tk" /> </div>
                                              <div key="firebase_password"> Пароль: <input type="password" className="firebase_password" ref="firebase_password" defaultValue="password" /> </div>
                                              <div key="firebase_login_btn"><br /><button onClick={this.onBtnClickHandler} id='firebase_registration'>Регистрация</button> <button onClick={this.onBtnClickHandler} id='firebase_login'>Войти</button> </div>
                                          </div>
                                      </div>;
          }
    	}
		return (
			<div className="DwpanelBodySettings">
				{DwpanelBodySettings}
			</div>
		);
	}
};

ReactDOM.render(
	<Settings />,
	document.getElementById('DwpanelBody')
);
