FROM php:8.4-fpm-alpine

# Install nginx and composer
RUN apk --no-cache add nginx
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Workdir and project files copy
WORKDIR /var/www/html
COPY . /var/www/html

# Nginx config and entrypoint script files
COPY .docker/nginx.conf /etc/nginx/nginx.conf
COPY .docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

# Run the startup script
CMD ["/entrypoint.sh"]
