FROM debian:11.2
LABEL maintainer="current_user@mail.ru"
RUN apt-get update && apt-get update && apt-get install nginx=1.18.0-6.1 nano procps nmap tcpdump curl -y && \
mkdir /var/lib/nginx/cache && rm -rf etc/nginx/sites-available/default /etc/nginx/sites-enabled/default
COPY ./src /src
COPY ./nginx/conf/nginx.conf /etc/nginx/nginx.conf
COPY ./nginx/conf/site.conf /etc/nginx/conf.d/
COPY ./nginx/conf/cache.conf /etc/nginx/conf.d/
COPY ./nginx/conf/upstream.conf /etc/nginx/conf.d/
RUN chown -R www-data:www-data /etc/nginx/ /src /var/log/nginx/ /var/lib/nginx/cache/ && chmod 660 /src/* && chmod 760 /src/*.php 
EXPOSE 80
ENTRYPOINT ["nginx", "-g", "daemon off;"]