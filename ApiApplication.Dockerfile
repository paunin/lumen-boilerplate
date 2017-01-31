FROM php:7.1-fpm

# don't use httpredir.debian.org mirror as it's very unreliable
RUN echo deb http://ftp.us.debian.org/debian jessie main > /etc/apt/sources.list
RUN apt-get update --fix-missing && \
#INSTALL TOOLS
    apt-get install -y \
        apt-utils \
        git \
        wget \
        curl \
        libpq-dev \
        libicu-dev \
        libcurl4-gnutls-dev \
        libmcrypt-dev \
        libmemcached-dev \
        libxml2-dev \
    && \
    docker-php-ext-install -j$(nproc) \
        pgsql \
        pdo \
        pdo_pgsql \
        opcache \
        zip \
        mbstring \
        bcmath \
        pcntl \
        mcrypt \
        soap

#Possible values for ext-name:
#bcmath bz2 calendar ctype curl dba dom enchant exif fileinfo filter ftp gd gettext gmp hash iconv imap interbase intl
#json ldap mbstring mcrypt mysqli oci8 odbc opcache pcntl pdo pdo_dblib pdo_firebird pdo_mysql pdo_oci pdo_odbc pdo_pgsql
#pdo_sqlite pgsql phar posix pspell readline recode reflection session shmop simplexml snmp soap sockets spl standard
#sysvmsg sysvsem sysvshm tidy tokenizer wddx xml xmlreader xmlrpc xmlwriter xsl zip



#COMPOSER
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer
# ConfD
RUN wget https://github.com/kelseyhightower/confd/releases/download/v0.10.0/confd-0.10.0-linux-amd64 -O /usr/local/bin/confd && \
    chmod +x /usr/local/bin/confd


# NEW RELIC
RUN echo 'deb http://apt.newrelic.com/debian/ newrelic non-free' | tee /etc/apt/sources.list.d/newrelic.list && \
    wget -O- https://download.newrelic.com/548C16BF.gpg | apt-key add - && \
    apt-get update && \
    apt-get install -y newrelic-php5 && \
    mv /usr/local/php /usr/local/non_php && \
    export NR_INSTALL_KEY=0000000000000000000000000000000000000000 && \
    export NR_INSTALL_PHPLIST=/usr/local/bin:/usr/bin && \
    export NR_INSTALL_SILENT=true && \
    newrelic-install install && \
    mv /usr/local/non_php /usr/local/php

# XDEBUG
RUN mkdir /tmp/xdebug_install && \
    wget -O /tmp/xdebug_install/xdebug-2.5.0.tgz http://xdebug.org/files/xdebug-2.5.0.tgz && \
    cd /tmp/xdebug_install/ && \
    tar -xvzf xdebug-2.5.0.tgz && \
    cd xdebug-2.5.0 && \
    phpize && \
    ./configure && \
    make && \
    cp modules/xdebug.so /usr/local/lib/php/extensions/no-debug-non-zts-20160303/ && \
    cd / && \
    rm -rf /tmp/xdebug_install

ENV DOCKERIZE_VERSION v0.2.0

RUN wget https://github.com/jwilder/dockerize/releases/download/$DOCKERIZE_VERSION/dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && tar -C /usr/local/bin -xzvf dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz

ENV TERM dump

# NPM
RUN apt-get install -y npm
RUN curl -sL https://deb.nodesource.com/setup_7.x | bash - && apt-get install -y nodejs

# Swagger Checker
RUN npm install -g swagger-cli

# Fix for php-fpm
RUN sed -i "s/www-data/root/g" /usr/local/etc/php-fpm.d/www.conf

COPY ./docker/ApiApplication/bin /usr/local/bin/app
RUN chmod +x /usr/local/bin/app/*

COPY docker/ApiApplication/confd /etc/confd
COPY . /var/www/application

WORKDIR /var/www/application

#RUN php -d memory_limit=-1 /bin/composer install \
#        --no-ansi \
#        --no-dev \
#        --prefer-dist \
#        --no-interaction \
#        --no-progress \
#        --no-scripts \
#        --optimize-autoloader

HEALTHCHECK --interval=1m --timeout=10s --retries=5 \
  CMD echo true

CMD /usr/local/bin/app/run.sh