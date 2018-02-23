# Dudko Web Panel (dwpanel)
Приложение для работы (развертывание и обслуживание) с openvpn на CentOS 7.x сервере.  
Пример можно посмотреть на [сайте проекта](https://vpn.sergdudko.tk "кликните для перехода").  
Для пользователя testuser@sergdudko.tk запись в FIREBASE ограничена настройками базы данных.  

## Пакетная установка

rpm --import https://vpn.sergdudko.tk/releases/RPM-GPG-KEY-SERGDUDKOTK  
rpm -ivh https://vpn.sergdudko.tk/releases/dwpanel-2.5.0-1.noarch.rpm  

rpm --import https://raw.githubusercontent.com/siarheidudko/dwpanel/master/releases/RPM-GPG-KEY-SERGDUDKOTK  
rpm -ivh https://raw.githubusercontent.com/siarheidudko/dwpanel/master/releases/dwpanel-2.5.0-1.noarch.rpm  

## Ручная установка

Обязательные пакеты:  php (5 или 7), php-mcrypt, curl, zip, openvpn, easy-rsa (версия 2, есть в ../install)  
Рекомендуемые пакеты: firewalld  

+ Очистить каталог /etc/openvpn/:
```
	rm -rf /etc/openvpn/*
```
+ Создать каталоги:
```
	mkdir -p /etc/openvpn/scripts/
	mkdir -p /var/dwpanel/
```
+ Скопировать файлы:
```
	cp -R .../backend/scripts/* /etc/openvpn/scripts/
	cp -R .../frontend/* /var/dwpanel/
	cp .../backend/bin/dwpanel /usr/bin/dwpanel
	cp .../backend/unit/dwpanel-server.service /etc/systemd/system/dwpanel-server.service
```
+ Запустить внутренний сервер приложения:
```
	systemctl daemon-reload
	systemctl start dwpanel-server
	systemctl enable dwpanel-server
```
+ Настроить firewalld. Порт 999 должен быть открыт только для localhost, т.к. на нем крутится внутренний сервер от root. Порты 1700-1954 для 255 VPN серверов (13.0.0.0 - 13.254.0.0). Порт 443 для внешнего web-сервера.
```
	firewall-cmd --zone=trusted --add-interface=lo
	firewall-cmd --zone=public --add-interface=eth0
	firewall-cmd --permanent --zone=public --add-port=443/tcp
	firewall-cmd --permanent --zone=public --add-port=1700-1954/tcp
	firewall-cmd --permanent --zone=trusted --add-port=999/tcp
	firewall-cmd --reload
```
+ Настроить ваш веб-сервер на папку /var/dwpanel

## Работа с консолью через команду dwpanel. Обязательные аргументы (порядок не важен) для dwpanel

### Добавление/Удаление сервера:
```
	build:server
	server:1(численный номер сервера)
	com:add или remove(добавить или удалить соответственно)
```
### Добавление/Удаление сертификатов клиента:
```
	build:client
	server:1(численный номер сервера)
	com:add или remove(добавить или удалить соответственно)
	client:1(численный номер клиентского сертификата)
	email:slavianich@gmail.com(клиентское мыло, можно не указывать при удалении)
```
### Отправка сертификатов на email:
```
	send:toemail
	server:1(численный номер сервера)
	client:1(численный номер клиентского сертификата)
	email:slavianich@gmail.com(клиентское мыло)
```
### Настройка подключения к БД MySQL
```
	-settings
	db_key:token(токен проекта firebase) 
	db_projectname:projectname(имя проекта firebase)
	db_user:user(пользователь БД) 
	db_pass:password(пароль пользователя БД) 
	encoding:Europe/Minsk(таймзона)
```

## Работа с внутренним/внешним веб-сервером. Обязательные аргументы (порядок не важен) для json:
type: POST
format: RAW
Content-Type: application/json
url: https://адрес_вашего_сайта/core-web-ctrl.php
Запросы аналогично для dwpanel.

Для работы внешнего веб-сервера скопировать настроить ваш веб-сервер на папку /var/dwpanel/
  
### Создание сервера OpenVPN:
```
	{ 	"build":"server",
		"com":"add",
		"server":"0", 
	    "auth":{
			"username":"user",
			"password":"password"
		}
	}
```
### Удаление сервера OpenVPN:
```
	{ 	"build":"server",
		"com":"remove",
		"server":"0", 
	    "auth":{
			"username":"user",
			"password":"password"
		}
	}
```
### Создание сертификатов клиента:
```
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
```
### Удаление сертификатов клиента:
```
	{ 	"build":"client",
		"com":"remove",
		"server":"0",
	    "client":"1",
	    "auth":{
			"username":"user",
			"password":"password"
		}
	}
```
### Отправка сертификатов на email:
```
	{ 	"send":"toemail",
		"email":"admin@sergdudko.tk",
		"server":"0",
	    "client":"1",
	    "auth":{
		"username":"user",
		"password":"password"
	    }
	}
```

## ТОЛЬКО ДЛЯ ОТЛАДКИ

### создание и удаление сервера
```
	php /etc/openvpn/scripts/build_server.php --args -add 1
	php /etc/openvpn/scripts/build_server.php --args -remove 1
```
### создание и удаление сертификатов клиента
```
	php /etc/openvpn/scripts/build_client.php --args -add 1 1 slavianich@email.by
	php /etc/openvpn/scripts/build_client.php --args -remove 1 1 slavianich@email.by
```
### отправка сертификатов клиента на email
```
	php /etc/openvpn/scripts/cert_send_toemail.php --args 1 1 slavianich@gmail.com
```

# UPDATE

## version 2.1.0
+ В запросах изменилась авторизация
```
	"auth":{
	    "username":"user",
	    "password":"password"
	}
```
	user - md5 имени пользователя БД 
	password - md5 пароля пользователя БД
+ При запросе пароля на электронную почту теперь используется шифрование, пароль в базе хранится в зашифрованном виде.
Ключевое слово для декриптора состоит из учетных данных пользователя, при смене пароля к БД - пароли к email перестанут 
дешифроваться.
+ Получение статуса сервера запросом:
```
	{ 	"status":{
			"server":"0"
		},
		"auth":{
			"username":"user",
			"password":"password"
		}
	}
```
	где 0 - номер сервера.
+ Реализована панель управления на javascript (фреймворк react), требует https для работы с бэкэндом (работа с базой данных доступна и в http режиме).  
Панель управления поддерживает:
  + авторизацию пользователя в firebase по учетным данным
  + создание настроек сервера в базе данных
  + редактирование настроек сервера в базе данных
  + удаление настроек сервера в базе данных (ВНИМАНИЕ: при удалении весь пуль сдвигается и потребуется пересоздание всех серверов с 
id равному или выше, чем удаляемый)
  + создание и редактирование настроек email для отправки сертификатов
  + создание VPN-сервера (запрос к бэкэнду)
  + удаление VPN-сервера (запрос к бэкэнду)
  + создание клиентского сертификата (запрос к бэкэнду)
  + удаление клиентского сертификата (запрос к бэкэнду)
  + отправка клиентского сертификата на email (запрос к бэкэнду)
  + получение статуса VPN-сервера (запрос к бэкэнду)

## version 2.1.1
+ Доработана верстка:
  + увеличен размер уведомлений для мобильных девайсов
  + увеличен размер слоя блокировки (при запросах) до 100% на мобильных девайсах
+ Добавлена регистрация пользователя при новой установке:
  + пользователь по умолчанию "testuser@sergdudko.tk"
  + пароль по умолчанию "password"
+ Доработана смена имени пользователя и пароля json-запросом, теперь пароль летает в зашифрованном виде.
+ Отныне в сборке будет присутствовать RPM-пакет и GPG-ключ

## version 2.2.0
+ Немного доработана верстка
+ В форму регистрации теперь автоматически подставляется адрес хоста
+ Добавлена папка install с скриптами установки (протестировано на чистой сборке CentOS-7-x86_64-Minimal-1611.iso)
+ В проект добавлен easy-rsa 2.2.2 (т.к. из стандартного репозитория устанавливается easy-rsa 3, поддержку которого пока не реализовал)
+ Добавлено настройка selinux
+ Добавлена настройка firewalld
+ Добавлена ТЕСТОВАЯ настройка httpd на 443 порт на папку /var/dwpanel
+ Реализован диалог с пользователем перед установкой (какие системные компоненты трогать, какие нет)

## version 2.5.0
+ Клиентская часть переписана под react 16.2.0
+ Код клиентской части переписан с ES5 на ES6
+ Обновлены внешние библиотеки клиентской части