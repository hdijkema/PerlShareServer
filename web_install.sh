#!/bin/bash
INSTDIR=/usr/share/perlshare
mkdir -p $INSTDIR
tar cf - htdocs | (cd $INSTDIR; tar xf -)
rm -f /var/www/perlshare
ln -s -f $INSTDIR/htdocs /var/www/perlshare
chmod 644 $INSTDIR/htdocs/*
chmod 755 $INSTDIR/htdocs

