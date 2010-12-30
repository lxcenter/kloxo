#!/bin/sh
cp -a /home/lxadmin/mail ~/vpopmail-backup
rpm -e courier-imap vpopmail
up2date --nosig vpopmail courier-imap
cp ~/vpopmail-backup/etc/vpopmail.mysql /home/lxadmin/mail/etc/
service courier restart

