# for version 0.0.2
# управление через /usr/bin/dwpanel
# обязательные аргументы (порядок не важен) для dwpanel
Добавление/Удаление сервера:
build:server
server:1(численный номер сервера)
com:add или remove(добавить или удалить соответственно)

Добавление/Удаление сертификатов клиента:
build:client
server:1(численный номер сервера)
com:add или remove(добавить или удалить соответственно)
client:1(численный номер клиентского сертификата)
email:slavianich@gmail.com(клиентское мыло, можно не указывать при удалении)

Отправка сертификатов на email:
send:toemail
server:1(численный номер сервера)
client:1(численный номер клиентского сертификата)
email:slavianich@gmail.com(клиентское мыло)

Настройка подключения к БД MySQL
-settings
db_key:token(токен проекта firebase) 
db_projectname:projectname(имя проекта firebase)
db_user:user(пользователь БД) 
db_pass:password(пароль пользователя БД) 
encoding:Europe/Minsk(таймзона)

# обязательные аргументы (порядок не важен) для json
# для работы скопировать /usr/share/dudko-web-panel/core-web-ctrl.php в папку подключенную к вашему веб-серверу
# type: POST
format: RAW
# Content-Type: application/json
# url: https://адрес_вашего_сайта/core-web-ctrl.php

# Запросы аналогично для dwpanel. 
# Создание сервера OpenVPN:
{ 	"build":"server",
	"com":"add",
	"server":"0", 
    "auth":{
		"username":"user",
		"password":"password"
	}
}
# Удаление сервера OpenVPN:
{ 	"build":"server",
	"com":"remove",
	"server":"0", 
    "auth":{
		"username":"user",
		"password":"password"
	}
}
# Создание сертификатов клиента:
{ 	"build":"client",
	"com":"add",
	"server":"0",
    "email":"admin@sergdudko.tk",
    "client":"1",
    "auth":{
		"username":"user",
		"password":"password"
	}
}
# Удаление сертификатов клиента:
{ 	"build":"client",
	"com":"remove",
	"server":"0",
    "client":"1",
    "auth":{
		"username":"user",
		"password":"password"
	}
}
# Отправка сертификатов на email:
{ 	"send":"toemail",
	"email":"admin@sergdudko.tk",
	"server":"0",
    "client":"1",
    "auth":{
    	"username":"user",
        "password":"password"
    }
}

# ТОЛЬКО ДЛЯ ОТЛАДКИ

# создание и удаление сервера
php /etc/openvpn/scripts/build_server.php --args -add 1
php /etc/openvpn/scripts/build_server.php --args -remove 1

# создание и удаление сертификатов клиента
php /etc/openvpn/scripts/build_client.php --args -add 1 1 slavianich@email.by
php /etc/openvpn/scripts/build_client.php --args -remove 1 1 slavianich@email.by

# отправка сертификатов клиента на email
php /etc/openvpn/scripts/cert_send_toemail.php --args 1 1 slavianich@gmail.com

# UPDATE

# version 2.0.0
1.В запросах изменилась авторизация
"auth":{
    "username":"user",
    "password":"password"
}
user - md5 имени пользователя БД
password - md5 пароля пользователя БД
2.При запросе пароля на электронную почту теперь используется шифрование, пароль в базе хранится в зашифрованном виде.
Ключевое слово для декриптора состоит из учетных данных пользователя, при смене пароля к БД - пароли к email перестанут 
дешифроваться.
3. получение статуса сервера запросом:
{ 	"status":{
		"server":"0"
        },
	"auth":{
		"username":"user",
		"password":"password"
	}
}
где 0 - номер сервера.
4.Реализована панель управления на javascript (фреймворк react), требует https для работы с бэкэндом (работа с базой данных доступна и в http режиме).
Панель управления поддерживает:
- авторизацию пользователя в firebase по учетным данным
- создание настроек сервера в базе данных
- редактирование настроек сервера в базе данных
- удаление настроек сервера в базе данных (ВНИМАНИЕ: при удалении весь пуль сдвигается и потребуется пересоздание всех серверов с 
id равному или выше, чем удаляемый)
- создание и редактирование настроек email для отправки сертификатов
- создание VPN-сервера (запрос к бэкэнду)
- удаление VPN-сервера (запрос к бэкэнду)
- создание клиентского сертификата (запрос к бэкэнду)
- удаление клиентского сертификата (запрос к бэкэнду)
- отправка клиентского сертификата на email (запрос к бэкэнду)
- получение статуса VPN-сервера (запрос к бэкэнду)