#!/bin/sh
# Kloxo, a light and efficient webhosting platform.
#
# Copyright (C) 2000-2009 LxLabs
# Copyright (C) 2009-2019 LxCenter
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
# Do a full yum update first before running this script!
#
# Note:
# On C5.11 (yum update via the vault) manualy install yum install make first.
#
# Add this to your /etc/hosts file:
# 66.160.179.101 download.lxcenter.org download.lxlabs.com
#
# version 0.7 Changed to a working GIT URL and newest version
# Version 0.6 Added better argument handling and --single-branch to git by Semir 
# Version 0.5 Copied and patched for Kloxo by dkstiler [ Dionysis Kladis <dkstiler@gmail.com> ]
# Version 0.4 Added which, zip and unzip as requirement [ Danny Terweij <d.terweij@lxcenter.org> ]
# Version 0.3 Added perl-ExtUtils-MakeMaker as requirement to install_GIT [ Danny Terweij <d.terweij@lxcenter.org> ]
# Version 0.2 Changed git version [ Danny Terweij <d.terweij@lxcenter.org> ]
# Version 0.1 Initial release [ Ángel Guzmán Maeso <angel.guzman@lxcenter.org> ]
#
KLOXO_PATH='/usr/local/lxlabs'
REPO="lxcenter"
BRANCH="7.0.x"

usage(){
    echo "Usage: $0 [BRANCH] [REPOSITORY] [-h]"
    echo "-b : BRANCH (optional): git branch (like: $BRANCH)"
    echo "-r : REPOSITORY (optional): the repo you want to use  (like: $REPO)"
    echo 'h: shows this help.'
    exit 1
}

while getopts "h:r:b:" OPTION
do
     case $OPTION in
         h)
             usage
             exit 1
             ;;
         r)
             REPO="$OPTARG"
             ;;
         b)
             BRANCH="$OPTARG"
             ;;
         ?)
             usage
             exit
             ;;
     esac
done

echo "Using REPO: $REPO BRANCH: $BRANCH " 

install_GIT()
{
  # CentOS
  if [ -f /etc/centos-release ] ; then
  # Install git with curl and expat support to enable support on github cloning
  yum install -y gcc gettext-devel expat-devel curl-devel zlib-devel openssl-devel perl-ExtUtils-MakeMaker
  fi

  # @todo Try to get the lastest version from some site. LATEST file?
  ## GIT_VERSION='1.8.3.4'
  GIT_VERSION='2.9.5'

  echo "Downloading and compiling GIT ${GIT_VERSION}"
  wget https://mirrors.edge.kernel.org/pub/software/scm/git/git-${GIT_VERSION}.tar.gz
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

# Clone from GitHub the last version using git transport (no http or https)
echo "Cleaning up old installs"
rm -Rf /usr/local/lxlabs.bak
mv /usr/local/lxlabs /usr/local/lxlabs.bak

echo "Installing branch $BRANCH from $REPO repository"
git clone -b $BRANCH --single-branch git://github.com/$REPO/kloxo.git ${KLOXO_PATH}

if [ $? -ne 0 ]; then
  echo "Git checkout failed. Exiting."
  exit 1;
fi

cd ${KLOXO_PATH}/kloxo-install
sh ./make-distribution.sh
cd ${KLOXO_PATH}/kloxo
sh ./make-distribution.sh
printf "Done.\nInstall Kloxo:\ncd ${KLOXO_PATH}/kloxo-install/\nsh kloxo-installer.sh with args\n"
