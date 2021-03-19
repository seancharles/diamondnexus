#!/bin/bash
distributionId=$(aws cloudfront get-distribution-config --id E206DWJUE94NO9 | grep DomainName | awk 'NF{ print $NF }')
if [ $distributionId = '"mag2-prod-cluster-LoadBalancer-88293620.us-east-1.elb.amazonaws.com",' ]; then
  s3location=arn:aws:datasync:us-east-1:$2:location/loc-06ba97a9cdf96a6c3
  aws s3 sync . s3://mag2-prod-standby-codebuild/magento --exclude ".git/*"  --delete
  efslocation=$(aws datasync create-location-efs --subdirectory /magento/ --efs-filesystem-arn arn:aws:elasticfilesystem:us-east-1:$2:file-system/$3 --ec2-config SecurityGroupArns=arn:aws:ec2:us-east-1:$2:security-group/$4,SubnetArn=arn:aws:ec2:us-east-1:$2:subnet/$5 --region us-east-1 | awk 'NF{ print $NF }' | sed 's/{//g' | sed 's/"//g' | sed 's/}//g')
  task=$(aws datasync create-task --source-location-arn $s3location --destination-location-arn $efslocation --cloud-watch-log-group-arn arn:aws:logs:us-east-1:$2:log-group:/aws/lambda/mag2-prod-magento-Lambda --name mag2-prod-magento-standby --excludes FilterType=SIMPLE_PATTERN,Value='*/log|/env.php' --options PreserveDeletedFiles=REMOVE,Uid=NONE,Gid=NONE,LogLevel=BASIC --region us-east-1 | awk 'NF{ print $NF }' | sed 's/{//g' | sed 's/"//g' | sed 's/}//g')
  taskexecution=$(aws datasync start-task-execution --task-arn $task --override-options Gid=NONE,Uid=NONE,PreserveDeletedFiles=REMOVE --region us-east-1 | awk 'NF{ print $NF }' | sed 's/{//g' | sed 's/"//g' | sed 's/}//g')
  rtn=$?
  if [ $rtn = 0 ]; then  
    execute=$(aws datasync describe-task-execution --task-execution-arn $taskexecution --region us-east-1 | grep  "TransferStatus" | awk 'NF{ print $NF }' | sed 's/,$//' | sed 's/"//g')
    while [ -z "$execute" ] || [ "$execute" = "PENDING" ]
    do
      sleep 20
      echo "inprogress"
      execute=$(aws datasync describe-task-execution --task-execution-arn $taskexecution --region us-east-1 | grep  "TransferStatus" | awk 'NF{ print $NF }' | sed 's/,$//' | sed 's/"//g')
      sleep 10
    done
    echo "transfer completed"
    aws datasync delete-task --task-arn $task
    aws datasync delete-location --location-arn $efslocation
  else
    echo "something wrong"
  fi
  aws lambda update-function-configuration --function-name mag2-prod-magento-Lambda --environment '{"Variables":{"cluster":"mag2-prod-cluster-standby", "service":"magento", "destination":"/mnt/magento"}}' --region us-east-1
else
  aws s3 sync . s3://mag2-prod-codebuild/magento --exclude ".git/*"  --delete
  taskexecution=$(aws datasync start-task-execution --task-arn arn:aws:datasync:us-east-1:$2:task/$1 --override-options Gid=NONE,Uid=NONE,PreserveDeletedFiles=REMOVE --region us-east-1 | awk 'NF{ print $NF }' | sed 's/{//g' | sed 's/"//g' | sed 's/}//g')
  rtn=$?
  if [ $rtn = 0 ]; then  
    execute=$(aws datasync describe-task-execution --task-execution-arn $taskexecution --region us-east-1 | grep  "TransferStatus" | awk 'NF{ print $NF }' | sed 's/,$//' | sed 's/"//g')
    while [ -z "$execute" ] || [ "$execute" = "PENDING" ]
    do
      sleep 20
      echo "inprogress"
      execute=$(aws datasync describe-task-execution --task-execution-arn $taskexecution --region us-east-1 | grep  "TransferStatus" | awk 'NF{ print $NF }' | sed 's/,$//' | sed 's/"//g')
      sleep 10
    done
    echo "transfer completed"
  else
    echo "something wrong"
  fi
  aws lambda update-function-configuration --function-name mag2-prod-magento-Lambda --environment '{"Variables":{"cluster":"mag2-prod-cluster", "service":"magento", "destination":"/mnt/magento"}}' --region us-east-1
fi
