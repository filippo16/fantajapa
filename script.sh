#!/bin/bash

apt update

mkdir /opt/nodejs
curl https://nodejs.org/dist/v22.14.0/node-v22.14.0-linux-x64.tar.gz | sudo tar xvzf - -C /opt/nodejs --strip-components=1
ln -s /opt/nodejs/bin/node /usr/bin/node
ln -s /opt/nodejs/bin/npm /usr/bin/npm

apt install mysql-server

#mysql -u root -p < ./backend/sql/my_notesync2.sql
