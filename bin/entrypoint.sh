#!/bin/bash
echo $NAMESPACE
sed -i -e 's/"REPLACE_WITH_REAL_KEY"/"fea75357072fdff9844d839de5d86bf4ebca4a6a"/' \
    -e "s/PHP Application/$NAMESPACE-Magento/" \
    -e 's/;newrelic.daemon.app_connect_timeout =.*/newrelic.daemon.app_connect_timeout=15s/' \
    -e 's/;newrelic.daemon.start_timeout =.*/newrelic.daemon.start_timeout=5s/' \
    /usr/local/etc/php/conf.d/newrelic.ini
sudo service ssh start
php-fpm -F --fpm-config /usr/local/etc/php/php-fpm.pool.conf
