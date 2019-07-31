FROM gcorreaalves/web_nginx_php7:latest
COPY . /var/www/wordpress
WORKDIR /var/www/wordpress
RUN chown -R www-data:www-data /var/www/wordpress && chmod -R 755 /var/www/wordpress/ssl/
RUN cp -rf /var/www/wordpress/vhost.conf /etc/nginx/conf.d/ && cp -rf /var/www/wordpress/ssl/ /etc/nginx/conf.d/
RUN bash /var/www/wordpress/start.sh
RUN service nginx restart && service php7.1-fpm restart
RUN ls /var/www/wordpress

