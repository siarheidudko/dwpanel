#!/bin/bash
echo Начинаю подготовку системы. 
echo 
if [ $1 == true ]
then
  	echo Устанавливаню репозиторий epel.
  	if [ -f /var/lib/rpm/.rpm.lock ]; then
      	rm /var/lib/rpm/.rpm.lock 
  	fi
  	rpm -ivh https://dl.fedoraproject.org/pub/epel/7/x86_64/Packages/e/epel-release-7-11.noarch.rpm
  	sleep 10
fi

if [ $2 == true ]
then
	echo Устанавливаю зависимости. 
	yum install curl firewalld curl zip openvpn php php-mcrypt mod_ssl -y 
	sleep 10
fi

echo Проверяю необходимые каталоги. 
if ! [ -d /etc/openvpn/ ]; then
	echo Создаю директорию /etc/openvpn/ 
    mkdir -p /etc/openvpn/ 
else
	echo Очищаю директорию /etc/openvpn/ 
    rm -rf /etc/openvpn/* 
fi

if ! [ -d /usr/share/easy-rsa/ ]; then
	echo Создаю директорию /usr/share/easy-rsa/
    mkdir -p /usr/share/easy-rsa/
    echo Создаю директорию /usr/share/easy-rsa/2.0/
    mkdir -p /usr/share/easy-rsa/2.0/
else
	if ! [ -d /usr/share/easy-rsa/2.0/ ]; then
    	echo Создаю директорию /usr/share/easy-rsa/2.0/
    	mkdir -p /usr/share/easy-rsa/2.0/
    else
    	echo Очищаю директорию /usr/share/easy-rsa/2.0/
    	rm -rf /usr/share/easy-rsa/2.0/*
    fi 
fi
echo Копирую файлы easy-rsa.
cp -R /usr/share/dwpanel/install/easy-rsa/* /usr/share/easy-rsa/2.0/
sleep 5

if [ $3 == true ]
then
    if ! [ -d /cert/ ]; then
        echo Создаю директорию /cert/
        mkdir -p /cert/
    fi

	echo Копирую сертификаты сервера.
	cp -rp /usr/share/dwpanel/install/dwpanel-selfsigned.crt /cert/dwpanel-selfsigned.crt
	cp -rp /usr/share/dwpanel/install/dwpanel-selfsigned.key /cert/dwpanel-selfsigned.key

	echo Даю права на папку /cert/.
	chmod -R 0666 /cert/*

	echo Делаю бэкап настроек httpd в /etc/httpd/conf/httpd.conf.back
	mv /etc/httpd/conf/httpd.conf /etc/httpd/conf/httpd.conf.back
	echo Устанавливаю собственные настройки httpd.conf
	cp /usr/share/dwpanel/install/httpd.conf /etc/httpd/conf/httpd.conf
fi

if [ $4 == true ]
then
	echo Устанавливаю собственные настройки selinux.
	setenforce 0
    sleep 5
fi

if [ $5 == true ]
then
	echo Настраиваю firewalld для интерфейса $6
	firewall-cmd --zone=trusted --add-interface=lo
	firewall-cmd --permanent --zone=public --add-port=443/tcp
	firewall-cmd --permanent --zone=public --add-port=1700-1954/tcp
	firewall-cmd --permanent --zone=trusted --add-port=999/tcp
    firewall-cmd --zone=public --add-interface=$6
    firewall-cmd --reload
fi
sleep 10
echo 
echo Подготовка системы завершена.