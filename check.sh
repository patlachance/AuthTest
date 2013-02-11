#!/usr/bin/env sh

GPGID=patlachance@gmail.com
FILEPLAIN=secured.tgz
FILECRYPT=secured.tgz.gpg

usage() {
  echo "$0 {in|out}"
  exit 1
}

[ $# != 1 ] && usage

case $1 in
  'in')
	echo "Pushing to remote origin"
	rm -f $FILEPLAIN $FILECRYPT
	tar zcf $FILEPLAIN secured
	gpg --encrypt --recipient $GPGID $FILEPLAIN
	rm -rf $FILEPLAIN secured
	;;
  'out')
	echo "Pulling from remote origin"
	rm -rf secured $FILEPLAIN
	gpg --output $FILEPLAIN --decrypt $FILECRYPT
	tar zxf $FILEPLAIN secured
	cp -a secured/cfg/* .
	;;
  *)
	echo "ERROR: Unknown option"
	;;
esac
