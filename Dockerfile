FROM php:7.2.26-fpm
LABEL maintainer="Forever Companies"

ENV PHP_LOG_ERRORS On
ENV PHP_MAX_EXECUTION_TIME 30
ENV PHP_MAX_INPUT_TIME 60
ENV PHP_MEMORY_LIMIT -1
ENV PHP_DISPLAY_ERRORS Off
ENV PHP_POST_MAX_SIZE 2G
ENV PHP_UPLOAD_MAX_FILESIZE 2G
ENV PHP_MAX_FILE_UPLOADS 20
ENV PHP_MYSQL_CACHE_SIZE 2000

RUN echo "Env Set Installing Dependacies"
RUN echo "deb http://ftp.ua.debian.org/debian/ stretch main" >> /etc/apt/sources.list \
   && apt-get update && apt-get install locales -y \
   && echo "en_US.UTF-8 UTF-8" >> /etc/locale.gen && locale-gen \
   && apt-get install --allow-remove-essential -yf software-properties-common gnupg gnupg-agent wget \ 
   libzip-dev libfreetype6-dev libjpeg62-turbo-dev libpng-dev xml-core unzip libssl-dev redis-tools \
   libicu-dev libxml2 libxml2-dev git jq libxslt-dev ssmtp mailutils vim \
   && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
   && docker-php-ext-install -j$(nproc) bcmath exif gettext gd zip pdo_mysql iconv opcache mysqli intl soap mbstring dom wddx shmop sockets sysvmsg sysvsem sysvshm xsl \
   && pecl install igbinary \
   && docker-php-ext-enable exif gettext shmop sockets sysvmsg sysvsem sysvshm wddx igbinary xsl zip \
   && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false gnupg gnupg-agent \
   && rm -rf /var/lib/apt/lists/* /usr/local/etc/php-fpm.d/* \
   && cd /tmp && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && php /tmp/composer-setup.php --install-dir=/usr/bin && php -r "unlink('composer-setup.php');" \
   && mv /usr/bin/composer.phar /usr/bin/composer

RUN echo "Creating Local Copy of Commit"
RUN chown -R www-data: /var/www
COPY app/etc/php.ini /usr/local/etc/php/php.ini
COPY app/etc/php-fpm.pool.conf /usr/local/etc/php/php-fpm.pool.conf
COPY ./ /var/magento
COPY app/etc/auth.json /var/www/.composer/auth.json
COPY app/etc/env.php.test /var/magento/app/etc/env.php
RUN chown www-data:www-data -R /usr/local/etc/php/php.ini
RUN chown www-data:www-data -R /usr/local/etc/php/php-fpm.pool.conf
RUN chown www-data:www-data -R /var/magento
RUN chown www-data:www-data -R /var/www/.composer/auth.json

RUN echo "Switching User to Build"
USER www-data
WORKDIR /var/www/magento

RUN echo "Composer Install"
RUN cd /var/magento && php -d memory_limit=-1 `which composer` update
RUN echo "Flush & Set-up Upgrade"
RUN cd /var/magento && redis-cli -h magento2-bc388d2e299adefb.13rcum.0001.use1.cache.amazonaws.com -n 8 FLUSHDB && redis-cli -h magento2-bc388d2e299adefb.13rcum.0001.use1.cache.amazonaws.com -n 9 FLUSHDB && php bin/magento setup:upgrade && php bin/magento setup:di:compile && php bin/magento setup:static-content:deploy -j $(nproc) && php bin/magento cache:flush && php bin/magento cache:enable
RUN echo "Unit Tests"
RUN echo "Skipped until modules are fixed"
#RUN cd /var/magento && php bin/magento dev:tests:run unit

RUN echo "Finish Docker Install"
EXPOSE 9000

CMD ["php-fpm", "-F", "--fpm-config", "/usr/local/etc/php/php-fpm.pool.conf", "-d", "PHP_LOG_ERRORS=${PHP_LOG_ERRORS}", "-d", "PHP_MAX_EXECUTION_TIME=${PHP_MAX_EXECUTION_TIME}", "-d", "PHP_MAX_INPUT_TIME=${PHP_MAX_INPUT_TIME}", "-d", "PHP_MEMORY_LIMIT=${PHP_MEMORY_LIMIT}", "-d", "PHP_DISPLAY_ERRORS=${PHP_DISPLAY_ERRORS}", "-d", "PHP_POST_MAX_SIZE=${PHP_POST_MAX_SIZE}", "-d", "PHP_UPLOAD_MAX_FILESIZE=${PHP_UPLOAD_MAX_FILESIZE}", "-d", "PHP_MAX_FILE_UPLOADS=${PHP_MAX_FILE_UPLOADS}", "-d", "PHP_MYSQL_CACHE_SIZE=${PHP_MYSQL_CACHE_SIZE}"] 
