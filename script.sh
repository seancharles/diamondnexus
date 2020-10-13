#!/bin/bash
curl https://$username:$pass@api.github.com/repos/ForeverCompanies/tf/pulls\?head\="ForeverCompanies:$BRANCH" | grep labels/develop
rtn=$?
    if [ $rtn = 0 ]; then
        ls
        pwd
        pip install dirsync
        cp -p -r --backup=numbered `find ~/ -name dirsync -a -type d` ./
        ls
        mkdir ../temp 
        mv *.yml script.sh ../temp
        zip -qr source.zip ./ -x '.git/*'
        aws s3 cp source.zip s3://mag2-dev-codebuild/source.zip
        aws lambda update-function-code --function-name $FUNCTION_NAME --s3-bucket mag2-dev-codebuild --s3-key source.zip
        sleep 3
    else
    echo "no pull request found with label develop"
    fi
echo "completed"
