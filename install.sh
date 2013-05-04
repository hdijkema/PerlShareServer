#!/bin/bash

# Install on debian systems
INSTDIR = /usr/share/perlshare

# Install proxy configuration 
a2enmod proxy
a2enmod proxy_connect
mkdir -p /etc/perlshare
cp $INSTDIR/etc/perlshare/* /etc/perlshare
chown -R root.root /etc/perlshare
chmod 644 /etc/perlshare/*
(cd /etc/apach2/conf.d/; ln -s /etc/perlshare/perlshare_apache.conf .)
/etc/init.d/apache2 restart


# Install init.d daemon starter
cp $INSTDIR/init.d/perlshare /etc/init.d
chown root.root /etc/init.d/perlshare
chmod 755 /etc/init.d/perlshare

for i in 2 3 4 5 
do
  ln -s /etc/init.d/perlshare /etc/rc$i.d/S70perlshare
done

for i in 1 6
do
  ln -s /etc/init.d/perlshare /etc/rc$i.d/K10perlshare
done

update-rc.d perlshare defaults

