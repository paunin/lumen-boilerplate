FROM nginx:1.11

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
        libxml2-dev

ENV DOCKERIZE_VERSION v0.2.0

RUN wget https://github.com/jwilder/dockerize/releases/download/$DOCKERIZE_VERSION/dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && tar -C /usr/local/bin -xzvf dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz

# CONFD
RUN curl -L https://github.com/kelseyhightower/confd/releases/download/v0.11.0/confd-0.11.0-linux-amd64 > /usr/local/bin/confd && \
chmod +x /usr/local/bin/confd

# NPM
RUN curl -sL https://deb.nodesource.com/setup_7.x | bash - && apt-get install -y nodejs

COPY . /var/www/application
COPY ./docker/WebserverUiApplication/default.conf /etc/nginx/conf.d/default.conf
COPY ./docker/WebserverUiApplication/confd /etc/confd
COPY ./docker/WebserverUiApplication/bin /usr/local/bin/app

WORKDIR /var/www/application
RUN chmod -R +x /usr/local/bin/app/*

CMD /usr/local/bin/app/run.sh
