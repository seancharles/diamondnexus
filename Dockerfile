FROM php:7.4.13-fpm
LABEL maintainer="Forever Companies"

RUN groupadd -g 1000 admin
RUN useradd -u 1000 -g 1000 -d /var/www/ admin -s /bin/bash
RUN usermod -g www-data admin && usermod -a -G www-data,root root

ARG BUILD
ENV BUILD $BUILD

ARG xdebug
ENV XDEBUG $xdebug

ARG DN_BASE_URL
ENV DN_BASE_URL $DN_BASE_URL

ARG FA_BASE_URL
ENV FA_BASE_URL $FA_BASE_URL

ARG TF_BASE_URL
ENV TF_BASE_URL $TF_BASE_URL

ARG host_name
ENV HOST $host_name

ARG REDIS_HOST
ENV REDIS_HOST $REDIS_HOST

ARG DB_HOST
ENV DB_HOST $DB_HOST

ARG DB_NAME
ENV DB_NAME $DB_NAME

ARG DB_USER
ENV DB_USER $DB_USER

ARG DB_ROOT_PASSWORD
ENV DB_ROOT_PASSWORD $DB_ROOT_PASSWORD

ARG VARNISH_HOST
ENV VARNISH_HOST $VARNISH_HOST

ARG RABBIT_HOST
ENV RABBIT_HOST $RABBIT_HOST

ARG RABBIT_VHOST
ENV RABBIT_VHOST $RABBIT_VHOST

ARG RABBIT_USER
ENV RABBIT_USER $RABBIT_USER

ARG RABBIT_PASSWORD
ENV RABBIT_PASSWORD $RABBIT_PASSWORD

ARG MAG_NAME
ENV MAG_NAME $MAG_NAME

ARG ELASTICSEARCH7_SERVER_HOSTNAME
ENV ELASTICSEARCH7_SERVER_HOSTNAME $ELASTICSEARCH7_SERVER_HOSTNAME

ARG ELASTICSEARCH7_ENGINE
ENV ELASTICSEARCH7_ENGINE $ELASTICSEARCH7_ENGINE

ARG ELASTICSEARCH7_SERVER_PORT
ENV ELASTICSEARCH7_SERVER_PORT $ELASTICSEARCH7_SERVER_PORT

ARG CRYPT
ENV CRYPT $CRYPT

ARG MAG_ENV
ENV MAG_ENV $MAG_ENV

ARG MAGENTO
ENV MAGENTO $MAGENTO

ARG NGINX_HOST
ENV NGINX_HOST $NGINX_HOST

ARG VARNISH_BACKEND_HOST
ENV VARNISH_BACKEND_HOST $VARNISH_BACKEND_HOST

ARG VARNISH_ACCESS_LIST
ENV VARNISH_ACCESS_LIST $VARNISH_ACCESS_LIST

ARG ADMIN_URL
ENV ADMIN_URL $ADMIN_URL

ARG BRAINTREE_ENV
ENV BRAINTREE_ENV $BRAINTREE_ENV

ARG BRAINTREE_PUBLIC_KEY
ENV BRAINTREE_PUBLIC_KEY $BRAINTREE_PUBLIC_KEY

ARG BRAINTREE_PRIVATE_KEY
ENV BRAINTREE_PRIVATE_KEY $BRAINTREE_PRIVATE_KEY

ARG BRAINTREE_SANDBOX_PUBLIC_KEY
ENV BRAINTREE_SANDBOX_PUBLIC_KEY $BRAINTREE_SANDBOX_PUBLIC_KEY

ARG BRAINTREE_SANDBOX_PRIVATE_KEY
ENV BRAINTREE_SANDBOX_PRIVATE_KEY $BRAINTREE_SANDBOX_PRIVATE_KEY

ARG BRAINTREE_MERCHANT_ID
ENV BRAINTREE_MERCHANT_ID $BRAINTREE_MERCHANT_ID

ARG BRAINTREE_MERCHANT_ACCOUNT_ID
ENV BRAINTREE_MERCHANT_ACCOUNT_ID $BRAINTREE_MERCHANT_ACCOUNT_ID

ARG AFFIRM_GATEWAY_MODE
ENV AFFIRM_GATEWAY_MODE $AFFIRM_GATEWAY_MODE

ARG AFFIRM_GATEWAY_PUBLIC_API_KEY_SANDBOX
ENV AFFIRM_GATEWAY_PUBLIC_API_KEY_SANDBOX $AFFIRM_GATEWAY_PUBLIC_API_KEY_SANDBOX

ARG AFFIRM_GATEWAY_PRIVATE_API_KEY_SANDBOX
ENV AFFIRM_GATEWAY_PRIVATE_API_KEY_SANDBOX $AFFIRM_GATEWAY_PRIVATE_API_KEY_SANDBOX

ARG PROGRESSIVE_GATEWAY_MODE
ENV PROGRESSIVE_GATEWAY_MODE $PROGRESSIVE_GATEWAY_MODE

ARG PROGRESSIVE_GATEWAY_PUBLIC_API_KEY_DEMO
ENV PROGRESSIVE_GATEWAY_PUBLIC_API_KEY_DEMO $PROGRESSIVE_GATEWAY_PUBLIC_API_KEY_DEMO

ARG PROGRESSIVE_GATEWAY_PRIVATE_API_KEY_DEMO
ENV PROGRESSIVE_GATEWAY_PRIVATE_API_KEY_DEMO $PROGRESSIVE_GATEWAY_PRIVATE_API_KEY_DEMO

ARG PROGRESSIVE_GATEWAY_MERCHANT_ID
ENV PROGRESSIVE_GATEWAY_MERCHANT_ID $PROGRESSIVE_GATEWAY_MERCHANT_ID

ARG PROGRESSIVE_GATEWAY_STORE_ID
ENV PROGRESSIVE_GATEWAY_STORE_ID $PROGRESSIVE_GATEWAY_STORE_ID

ARG PROGRESSIVE_GATEWAY_DEMO_API_URL
ENV PROGRESSIVE_GATEWAY_DEMO_API_URL $PROGRESSIVE_GATEWAY_DEMO_API_URL

ARG SHIP_LOG
ENV SHIP_LOG $SHIP_LOG

ARG SMTP_PRODUCT_KEY
ENV SMTP_PRODUCT_KEY $SMTP_PRODUCT_KEY

ARG SMTP_PASSWORD
ENV SMTP_PASSWORD $SMTP_PASSWORD

ARG BASE_SMTP_PASSWORD
ENV BASE_SMTP_PASSWORD $BASE_SMTP_PASSWORD

ARG WWW_FA_SMTP_PASSWORD
ENV WWW_FA_SMTP_PASSWORD $WWW_FA_SMTP_PASSWORD

ARG WWW_1215_SMTP_PASSWORD
ENV WWW_1215_SMTP_PASSWORD $WWW_1215_SMTP_PASSWORD

ARG WORDPRESS
ENV WORDPRESS $WORDPRESS

ARG SF_HOST
ENV SF_HOST $SF_HOST

ARG SF_CLIENT_ID
ENV SF_CLIENT_ID $SF_CLIENT_ID

ARG SF_CLIENT_SECRET
ENV SF_CLIENT_SECRET $SF_CLIENT_SECRET

ARG SF_EMAIL
ENV SF_EMAIL $SF_EMAIL

ARG SF_PASSWD
ENV SF_PASSWD $SF_PASSWD

ENV PHP_LOG_ERRORS On
ENV PHP_MAX_EXECUTION_TIME 30
ENV PHP_MAX_INPUT_TIME 60
ENV PHP_DISPLAY_ERRORS Off
ENV PHP_POST_MAX_SIZE 2G
ENV PHP_UPLOAD_MAX_FILESIZE 2G
ENV PHP_MAX_FILE_UPLOADS 20
ENV PHP_MYSQL_CACHE_SIZE 2000
RUN cat /etc/debian_version
RUN echo "Env Set Installing Dependacies"
RUN echo "deb http://ftp.ua.debian.org/debian/ stretch main" >> /etc/apt/sources.list \
   && apt-get update && apt-get install locales -y \
   && echo "en_US.UTF-8 UTF-8" >> /etc/locale.gen && locale-gen \
   && apt-get install --allow-remove-essential -yf software-properties-common gnupg gnupg-agent wget \
   libzip-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev xml-core unzip libssl-dev libonig-dev \
   libicu-dev libxml2 libxml2-dev git jq libxslt-dev ssmtp mailutils vim cron ssh-client openssh-server nano sudo \
   && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
   && docker-php-ext-install -j$(nproc) bcmath exif gettext gd zip pdo_mysql iconv opcache mysqli intl soap mbstring dom shmop sockets sysvmsg sysvsem sysvshm xsl \
   && pecl install igbinary \
   && docker-php-ext-enable exif gettext shmop sockets sysvmsg sysvsem sysvshm igbinary xsl zip \
   && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false gnupg gnupg-agent \
   && rm -rf /var/lib/apt/lists/* /usr/local/etc/php-fpm.d/* \
   && cd /tmp && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && php /tmp/composer-setup.php --version=1.10.16 --install-dir=/usr/bin && php -r "unlink('composer-setup.php');" \
   && mv /usr/bin/composer.phar /usr/bin/composer

RUN if [ "$XDEBUG" = "on" ] ; then pecl install xdebug \
&& docker-php-ext-enable xdebug \
&& touch /var/log/xdebug_remote.log \
&& chmod 777 /var/log/xdebug_remote.log \
&& echo "zend_extension=/usr/local/lib/php/extensions/`ls /usr/local/lib/php/extensions/`/xdebug.so" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
&& echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
&& echo "xdebug.remote_handler=dbgp" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
&& echo "xdebug.start_with_request=trigger" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
&& echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
&& echo "xdebug.log=/var/log/xdebug_remote.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
&& echo "xdebug.client_host=$HOST" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
&& echo "xdebug.discover_client_host=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ; fi

RUN echo "admin	ALL=(ALL:ALL)	NOPASSWD: ALL" >> /etc/sudoers
COPY bin/php.ini /usr/local/etc/php/php.ini
COPY bin/php-fpm.pool.conf /usr/local/etc/php/php-fpm.pool.conf

COPY bin/entrypoint.sh /
RUN chmod 755 /entrypoint.sh
RUN chown 1000:1000 /entrypoint.sh

USER admin
WORKDIR /var/www/magento

RUN sudo chown admin:admin -R /usr/local/etc/php/php.ini
RUN sudo chown admin:admin -R /usr/local/etc/php/php-fpm.pool.conf
RUN sudo chown -R admin: /var/www
RUN sudo mkdir /var/www/.ssh/
RUN sudo chown admin:admin -R /var/www/.ssh/
COPY bin/authorized_keys.$BUILD /var/www/.ssh/authorized_keys
RUN sudo chown admin:admin -R /var/www/.ssh/
RUN sudo chmod 600 /var/www/.ssh/*
RUN sudo mkdir /var/www/.composer
run sudo chown admin:admin /var/www/.composer
COPY bin/auth.json /var/www/.composer/auth.json
run sudo chown admin:admin -R /var/www/.composer
COPY . /var/www/magento
RUN sudo chown admin:admin -R /var/www/magento

RUN echo "Composer Install"
RUN cp app/etc/env.php.bak app/etc/env.php
RUN php -d memory_limit=-1 `which composer` install
RUN php -d memory_limit=-1 bin/magento setup:upgrade
RUN php -d memory_limit=-1 bin/magento setup:di:compile
RUN php -d memory_limit=-1 bin/magento cron:install
RUN php -d memory_limit=-1 bin/magento indexer:reindex
RUN php -d memory_limit=-1 bin/magento setup:static-content:deploy -f
ENTRYPOINT [ "/entrypoint.sh" ]
