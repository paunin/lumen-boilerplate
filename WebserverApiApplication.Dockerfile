FROM nginx:1.11
COPY ./docker/WebserverApiApplication/default.conf /etc/nginx/conf.d/default.conf
WORKDIR /var/www/application