#!/bin/bash
envsubst "`for v in $(compgen -v);do printf '${%s} ' $v;done`'" < /etc/nginx/sites-available/magento2.conf.template > /etc/nginx/sites-available/magento2.conf
envsubst "`for v in $(compgen -v);do printf '${%s} ' $v;done`'" < /etc/nginx/sites-available/magento2-api.conf.template > /etc/nginx/sites-available/magento2-api.conf
envsubst "`for v in $(compgen -v);do printf '${%s} ' $v;done`'" < /etc/nginx/sites-default.development.conf.template > /etc/nginx/sites-default.development.conf
envsubst "`for v in $(compgen -v);do printf '${%s} ' $v;done`'" < /etc/nginx/sites-default.production.conf.template > /etc/nginx/sites-default.production.conf
envsubst "`for v in $(compgen -v);do printf '${%s} ' $v;done`'" < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf
sudo nginx -g 'daemon off;' & 

sudo varnishd -j unix,user=vcache -F -a :81 -T localhost:6082 -f /etc/varnish/default.vcl -S /etc/varnish/secret -p http_resp_hdr_len=10k -p http_resp_size=142k -p workspace_backend=5M -s malloc,3G &

if php -d memory_limit=-1 bin/magento cron:install
then
  echo "Cron Installed"
else
  echo "Cron Already Installed"
fi
crontab -l | sed 's/bin\/php/bin\/php -d memory_limit=-1/g' | crontab -
sudo cron -f &
sudo service ssh start
php-fpm -F --fpm-config /usr/local/etc/php/php-fpm.pool.conf
