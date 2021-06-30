#!/bin/bash

# Get Instance Location
region_id=`ec2metadata --availability-zone | sed 's/.$//'`
avail_id=`ec2metadata --availability-zone`
inst_id=`ec2metadata --instance-id`
host=`hostname`

# Mount New Recent Media
echo -en "Finding Media "
# Most recent snap
snap=`aws ec2 describe-snapshots --owner self --max-items 1 --filters Name=volume-id,Values=vol-0d90c04f2295dfa28 Name=status,Values=completed | head -n 1 | awk '{print $12}'`
started_vol=0
while [[ ! $volumeid ]]
do
    if [ $started_vol == 0 ]
    then
        volumeid=`aws ec2 create-volume --region $region_id --availability-zone $avail_id --snapshot-id $snap --volume-type gp2 --size 125 --tag-specification "ResourceType=volume,Tags=[{Key=Name,Value=$host Temp Magento media}]" | awk '{print $8}'`
        started_vol=1
    fi
    echo -en "."
    sleep 10
done
echo -e " [ \e[32mOK\e[0m ]"

echo -en "Mounting Media "
disktype=$(sudo fdisk -l | grep nvme)
if [[ $? != 0 ]]; then
    mediadevice="/dev/xvdg"
    mediadeviceinc="1"
elif [[ $disktype ]]; then
    mediadevice="/dev/nvme1n2"
    mediadeviceinc="p1"
else
    echo -e "\n Something is definitely wrong. \n"
    exit
fi
echo -en "."
# Don't ask me why aws is just plain being weird.
aws ec2 attach-volume --volume-id $volumeid --instance-id $inst_id --device /dev/xvdg > /dev/null 2>&1
mount_break=0
while [ ! -b $mediadevice$mediadeviceinc ]
do
    if [ $mount_break -gt 10 ]
    then
        echo -e "\n Something is definitely wrong. \n"
        exit
    fi
    ((mount_break++))
    echo -en "."
    sleep 10
done
sudo mount -t ext4 -o rw,discard,errors=remount-ro $mediadevice$mediadeviceinc /mnt/
echo -e "[ \e[32mOK\e[0m ]"
# Rsync between new & old
rsync --progress --info=progress2 -ahr /mnt/ /home/admin/html/media/ 2>/dev/null 
echo -e "[ \e[32mOK\e[0m ]"
# Unmount new and delete
echo -en "Destroying Tmp Media "
sudo umount /mnt
aws ec2 detach-volume --volume-id $volumeid > /dev/null 2>&1
echo -en "."
detachedid="in-use"
while [[ $detachedid != "available" ]]
do
    detachedid=`aws ec2 describe-volumes --volume-ids $volumeid --query 'Volumes[*].{Status:State}'`
    echo -en "."
    sleep 10
done
aws ec2 delete-volume --volume-id $volumeid
echo -e "[ \e[32mOK\e[0m ]"
