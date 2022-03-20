FROM debian:11.2
LABEL maintainer="current_user@mail.ru"
RUN apt-get update && apt-get update && apt-get install php7.4-fpm -y \
&& mkdir /run/php /var/log/php-fpm
COPY ./php-fpm/conf/php-fpm.conf /etc/php/7.4/fpm/php-fpm.conf
COPY ./php-fpm/conf/www.conf /etc/php/7.4/fpm/pool.d/www.conf
COPY ./src /src
RUN chown -R www-data:www-data /etc/php/ /src /run/php/ /var/log/php-fpm && chmod 760 /src/*.php 
EXPOSE 9000
ENTRYPOINT ["php-fpm7.4", "-F"]