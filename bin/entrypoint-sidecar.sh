#!/bin/bash
echo $NAMESPACE
sed -i -e 's/"REPLACE_WITH_REAL_KEY"/"fea75357072fdff9844d839de5d86bf4ebca4a6a"/' \
    -e "s/PHP Application/$NAMESPACE-Magento-Cron/" \
    -e 's/;newrelic.daemon.app_connect_timeout =.*/newrelic.daemon.app_connect_timeout=15s/' \
    -e 's/;newrelic.daemon.start_timeout =.*/newrelic.daemon.start_timeout=5s/' \
    /usr/local/etc/php/conf.d/newrelic.ini
cd /var/www/magento/
for x in `php bin/magento indexer:status | grep "Processing" | awk '{print $2}'`
do 
  php bin/magento indexer:reset $x
done
if php -d memory_limit=-1 bin/magento cron:install
then
  echo "Cron Installed"
else
  echo "Cron Already Installed"
fi
crontab -l | sed 's/bin\/php/bin\/php -d memory_limit=-1/g' | crontab -
sudo cron -f &
php ./bin/magento queue:consumers:start async.operations.all & 
sudo service ssh start
php-fpm -F --fpm-config /usr/local/etc/php/php-fpm.pool.conf
