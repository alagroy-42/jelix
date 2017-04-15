#!/bin/bash

ROOTDIR="/jelixapp"
MYSQL_VERSION="5.5"
PHP_VERSION="7.1"
APPNAME="testapp"
APPDIR="$ROOTDIR/$APPNAME"
VAGRANTDIR="/vagrantscripts"
APPHOSTNAME="testapp16.local"
APPHOSTNAME2=""
LDAPCN="testapp16"
FPM_SOCK="php\\/php7.1-fpm.sock"
POSTGRESQL_VERSION=9.4

source $VAGRANTDIR/common_provision.sh

