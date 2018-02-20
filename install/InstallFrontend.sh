#!/bin/bash
echo Начинаю установку фронтэнда.
echo 

echo Проверяю необходимые каталоги.
if ! [ -d /var/dwpanel/ ]; then
	echo Создаю директорию /var/dwpanel/
    mkdir -p /var/dwpanel/
else
	echo Очищаю директорию /etc/openvpn/
    rm -rf /var/dwpanel/*
fi

echo Копирую файлы приложения.
cp -R /usr/share/dwpanel/frontend/* /var/dwpanel/

sleep 10
echo Даю права на папку /var/dwpanel/
chmod -R 0777 /var/dwpanel/*

if [ $1 == true ]
then
	echo Запускаем внешний сервер.
	systemctl start httpd
	echo Добавляем внешний сервер в автозапуск.
	systemctl enable httpd
	sleep 10
fi
echo
echo Установка фронтэнда завершена.