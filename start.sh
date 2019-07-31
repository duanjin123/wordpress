#/usr/bin/bash
sed 's/;opcache.enable=1/opcache.enable=1/g' /etc/php/7.1/fpm/php.ini
sed '8a zend_extension=opcache.so' /etc/php/7.1/fpm/php.ini
