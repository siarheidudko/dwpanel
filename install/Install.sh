#!/bin/bash
echo
while true; do
read -p "Для корректной настройки понадобится epel-release-7-11, установить? (y/n):" yn
    case $yn in
        [Yy]* ) epel=true; break;;
        [Nn]* ) epel=false; break;;
        * ) echo "Пожалуйста ответьте y или n.";;
    esac
done
while true; do
    read -p "Для корректной работы понадобятся зависимости firewalld, curl, zip, openvpn, php, php-mcrypt, mod_ssl. Установить? (y/n):" yn
    case $yn in
        [Yy]* ) package=true; break;;
        [Nn]* ) package=false; break;;
        * ) echo "Пожалуйста ответьте y или n.";;
    esac
done
while true; do
    read -p "Установить тестовый веб-сервер apache? (y/n):" yn
    case $yn in
        [Yy]* ) apache=true; break;;
        [Nn]* ) apache=false; break;;
        * ) echo "Пожалуйста ответьте y или n.";;
    esac
done
while true; do
    read -p "Настроить selinux? (y/n):" yn
    case $yn in
        [Yy]* ) selinux=true; break;;
        [Nn]* ) selinux=false; break;;
        * ) echo "Пожалуйста ответьте y или n.";;
    esac
done
while true; do
    read -p "Настроить firewalld? (y/n):" yn
    case $yn in
        [Yy]* ) firewalld=true; break;;
        [Nn]* ) firewalld=false; break;;
        * ) echo "Пожалуйста ответьте y или n.";;
    esac
done
if [ $firewalld == true ]
then
	while true; do
        read -p "Имя внешнего интерфейса, например eth0:" interface
        break;
    done
fi
echo 
echo Запускаю подготовку системы: PreInstall.sh
/bin/bash /usr/share/dwpanel/install/PreInstall.sh $epel $package $apache $selinux $firewalld $interface
echo
echo "-----------------------------------------------------"
echo 
echo Запускаю установку бэкэнда: InstallBackend.sh
/bin/bash /usr/share/dwpanel/install/InstallBackend.sh
echo 
echo "-----------------------------------------------------"
echo 
echo Запускаю установку фронтэнда: InstallFrontend.sh
/bin/bash /usr/share/dwpanel/install/InstallFrontend.sh $apache
echo 
echo "-----------------------------------------------------"
echo 
echo Запускаю установку постустановочный скрипт: PostInstall.sh
/bin/bash /usr/share/dwpanel/install/PostInstall.sh
echo 