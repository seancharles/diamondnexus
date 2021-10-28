#!/bin/bash

echo $NAMESPACE
sed -i -e 's/"REPLACE_WITH_REAL_KEY"/"fea75357072fdff9844d839de5d86bf4ebca4a6a"/' \
    -e "s/PHP Application/$NAMESPACE-Magento/" \
    -e 's/;newrelic.daemon.app_connect_timeout =.*/newrelic.daemon.app_connect_timeout=15s/' \
    -e 's/;newrelic.daemon.start_timeout =.*/newrelic.daemon.start_timeout=5s/' \
    /usr/local/etc/php/conf.d/newrelic.ini
sudo service ssh start
sudo cat /hoster.sh.template | sed "s/NGINX/$NGINX/g" | sed "s/MAG_NAME/$MAG_NAME/g" > /tmp/hoster.sh
sudo mv /tmp/hoster.sh /hoster.sh
crontab -l | echo "* * * * * sudo bash /hoster.sh" | crontab - 
sudo cron -f &
php-fpm -F --fpm-config /usr/local/etc/php/php-fpm.pool.conf
