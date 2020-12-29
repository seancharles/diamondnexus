#!/bin/bash
taskexecution=$(aws datasync start-task-execution --task-arn arn:aws:datasync:us-east-1:$2:task/$1 --override-options Gid=NONE,Uid=NONE,PreserveDeletedFiles=REMOVE --region us-east-1 | awk 'NF{ print $NF }' | sed 's/{//g' | sed 's/"//g' | sed 's/}//g')
rtn=$?
if [ $rtn = 0 ]; then  
  execute=$(aws datasync describe-task-execution --task-execution-arn $taskexecution --region us-east-1 | grep  "TransferStatus")
  while [ -z "$execute" ] 
  do
    sleep 20
    echo "inprogress"
    execute=$(aws datasync describe-task-execution --task-execution-arn $taskexecution --region us-east-1 | grep  "TransferStatus")
    sleep 10
  done
  echo "transfer completed"
else
  echo "something wrong"
fi
