#!/bin/sh
# Kloxo, a light and efficient webhosting platform.
#
# Copyright (C) 2000-2009 LxLabs
# Copyright (C) 2009-2014 LxCenter
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as
# published by the Free Software Foundation, either version 3 of the
# License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU Affero General Public License
# along with this program. If not, see <http://www.gnu.org/licenses/>.
#
# author: Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
#
# Install and deploy a develoment version on a local enviroment
#
# Version 0.5 Copied and patched for Kloxo by dkstiler [ Dionysis Kladis <dkstiler@gmail.com> ]
# Version 0.4 Added which, zip and unzip as requirement [ Danny Terweij <d.terweij@lxcenter.org> ]
# Version 0.3 Added perl-ExtUtils-MakeMaker as requirement to install_GIT [ Danny Terweij <d.terweij@lxcenter.org> ]
# Version 0.2 Changed git version [ Danny Terweij <d.terweij@lxcenter.org> ]
# Version 0.1 Initial release [ Ángel Guzmán Maeso <angel.guzman@lxcenter.org> ]
#
KLOXO_PATH='/usr/local/lxlabs'

usage(){
    echo "Usage: $0 [BRANCH] [REPOSITORY] [-h]"
    echo 'BRANCH: master or dev or 6.1.x'
    echo 'REPOSITORY: lxcenter or the repo you want to use'
    echo 'h: shows this help.'
    exit 1
}

install_GIT()
{
# Redhat based
if [ -f /etc/redhat-release ] ; then
# Install git with curl and expat support to enable support on github cloning
yum install -y gcc gettext-devel expat-devel curl-devel zlib-devel openssl-devel perl-ExtUtils-MakeMaker
# Debian based
elif [ -f /etc/debian_version ] ; then
# No tested
apt-get install gcc
fi

# @todo Try to get the lastest version from some site. LATEST file?
## GIT_VERSION='1.8.3.4'
GIT_VERSION='1.9.0'

echo "Downloading and compiling GIT ${GIT_VERSION}"
wget http://git-core.googlecode.com/files/git-${GIT_VERSION}.tar.gz
tar xvfz git-*.tar.gz; cd git-*;
./configure --prefix=/usr --with-curl --with-expat
make all
make install

echo 'Cleaning GIT files.'
cd ..; rm -rf git-*
}

require_root()
{
if [ `/usr/bin/id -u` -ne 0 ]; then
echo 'Please, run this script as root.'
     usage
fi
}

require_requirements()
{
    #
    # without them, it will compile each run git and does not create/unzip the development files.
    #
    yum -y install which zip unzip
}


require_root

require_requirements

echo 'Installing Kloxo development version.'

if which git >/dev/null; then
echo 'GIT support detected.'
else
echo 'No GIT support detected. Installing GIT.'
    install_GIT
fi

case $1 in
master )
# Clone from GitHub the last version using git transport (no http or https)
echo "Installing branch kloxo/master from $2 repository"
mkdir -p ${KLOXO_PATH}
git clone git://github.com/$2/kloxo.git ${KLOXO_PATH}
cd ${KLOXO_PATH}
git checkout master
cd ${KLOXO_PATH}/kloxo-install
sh ./make-distribution.sh
cd ${KLOXO_PATH}/kloxo
sh ./make-development-distribution.sh
printf "Done.\nInstall Kloxo:\ncd ${KLOXO_PATH}/kloxo-install/\nsh kloxo-installer.sh with args\n"
;;
dev )
# Clone from GitHub the last version using git transport (no http or https)
echo "Installing branch kloxo/dev from $2 repository"
git clone git://github.com/$2/kloxo.git ${KLOXO_PATH}
cd ${KLOXO_PATH}
git checkout dev -f
cd ${KLOXO_PATH}/kloxo-install
sh ./make-distribution.sh
cd ${KLOXO_PATH}/kloxo
sh ./make-development-distribution.sh
printf "Done.\nInstall Kloxo:\ncd ${KLOXO_PATH}/kloxo-install/\nsh kloxo-installer.sh with args\n"
;;
6.1.x )
# Clone from GitHub the last version using git transport (no http or https)
echo "Installing branch kloxo/6.1.x from $2 repository"
git clone git://github.com/$2/kloxo.git ${KLOXO_PATH}
cd ${KLOXO_PATH}
git checkout 6.1.x -f
cd ${KLOXO_PATH}/kloxo-install
sh ./make-distribution.sh
cd ${KLOXO_PATH}/kloxo
sh ./make-development-distribution.sh
printf "Done.\nInstall Kloxo:\ncd ${KLOXO_PATH}/kloxo-install/\nsh kloxo-installer.sh with args\n"
;;
* )
usage
return 1 ;;
esac
