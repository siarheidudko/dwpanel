#!/bin/bash
echo Начинаю установку бэкэнда.
echo 

echo Проверяю необходимые каталоги.
if ! [ -d /etc/openvpn/scripts/ ]; then
	echo Создаю директорию /etc/openvpn/scripts/
    mkdir -p /etc/openvpn/scripts/
else
	echo Очищаю директорию /etc/openvpn/scripts/
    rm -rf /etc/openvpn/scripts/*
fi

echo Копирую файлы скриптов.
cp -R /usr/share/dwpanel/backend/scripts/* /etc/openvpn/scripts/

sleep 10
echo Копирую приложение в папку bin.
cp /usr/share/dwpanel/backend/bin/dwpanel /usr/bin/dwpanel

echo Копирую демон внутреннего сервера.
cp /usr/share/dwpanel/backend/unit/dwpanel-server.service /etc/systemd/system/dwpanel-server.service

echo Перезагрузка демонов.
systemctl daemon-reload
echo Запускаем внутренний сервер.
systemctl start dwpanel-server
echo Добавляем демона в автозапуск.
systemctl enable dwpanel-server

sleep 5
echo 
echo Установка бэкэнда завершена.