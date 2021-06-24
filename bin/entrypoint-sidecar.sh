#!/bin/bash
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
