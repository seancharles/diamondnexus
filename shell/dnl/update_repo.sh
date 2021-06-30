#!/bin/bash

function commandcheck() {
if [ $? -eq 0 ]; then
  echo -e "[ \e[32mOK\e[0m ]"
else
  echo -e "[ \e[91mFAIL\e[0m ]"
fi
}

function update() {

echo "Master: "
echo -en "git fetch --all "
git fetch --all &> /dev/null
commandcheck
echo -en "git stash -u "
git stash -u &> /dev/null
commandcheck
echo -en "git checkout origin/master "
git checkout origin/master &> /dev/null
commandcheck
echo -en "git checkout -b master "
git checkout -b master &> /dev/null
commandcheck
echo -en "git checkout master "
git checkout master &> /dev/null
commandcheck
echo -en "git pull origin master "
git pull origin master &> /dev/null
commandcheck
echo -en "git push $1 master "
git push $1 master &> /dev/null
echo " "
echo "Develop: "
commandcheck
echo -en "git checkout origin/develop "
git checkout origin/develop &> /dev/null
commandcheck
echo -en "git checkout -b develop "
git checkout -b develop &> /dev/null
commandcheck
echo -en "git checkout develop "
git checkout develop &> /dev/null
commandcheck
echo -en "git pull origin develop "
git pull origin develop &> /dev/null
commandcheck
echo -en "git push $1 develop "
git push $1 develop &> /dev/null
commandcheck

}

if [ -z "$1" ]
then
	echo "Need remote name"
	exit
fi

# Update magento core
echo "Updating $1 Repo"
update $1
echo " "

for GIT_DIR in "dn" "fa" "1215"
do
	echo "Updating $1's $GIT_DIR Repo"
	# Update submodules
	cd ~/html/skin/frontend/rwd_custom/$GIT_DIR
	update $1
	echo " "
done
