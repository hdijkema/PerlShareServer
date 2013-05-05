#!/bin/bash

# Install on debian systems
INSTDIR=/usr/share/perlshare

# Install dependend modules
apt-get -y install liblockfile-simple-perl libexpect-perl \
                    inotify-tools apache2 php5-auth-pam

# configure php5-auth-pam
usermod -a -G shadow www-data

# Install pershare server
mkdir -p $INSTDIR
tar cf - PerlShareServer.pl unison_umask htdocs etc init.d PerlShareCommon | (cd $INSTDIR; tar xf -)
chown -R root.root $INSTDIR/
chmod 755 $INSTDIR/unison_umask
chmod 755 $INSTDIR/PerlShareServer.pl
rm -f /var/www/perlshare
ln -s -f $INSTDIR/htdocs /var/www/perlshare
chmod 644 $INSTDIR/htdocs/*
chmod 755 $INSTDIR/htdocs

# Install proxy configuration 
a2enmod proxy
a2enmod proxy_connect
mkdir -p /etc/perlshare
cp $INSTDIR/etc/perlshare/* /etc/perlshare
chown -R root.root /etc/perlshare
chmod 644 /etc/perlshare/*
(cd /etc/apache2/conf.d/; ln -s -f /etc/perlshare/perlshare_apache.conf .)
/etc/init.d/apache2 restart

# Install init.d daemon starter
cp $INSTDIR/init.d/perlshare /etc/init.d
chown root.root /etc/init.d/perlshare
chmod 755 /etc/init.d/perlshare
update-rc.d perlshare defaults

