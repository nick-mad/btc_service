# Використовуємо офіційний образ PHP 7
FROM php:8

# Встановлюємо залежності
RUN apt-get update && apt-get install -y zip unzip

# Встановлюємо Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Копіюємо ваш додаток в робочу директорію контейнера
COPY . /var/www/html

# Встановлюємо значення змінних середовища для налаштування поштового сервера
ENV MAIL_SERVER your_mail_server
ENV MAIL_PORT your_mail_password
ENV MAIL_USERNAME your_mail_username
ENV MAIL_PASSWORD your_mail_password

# Встановлюємо значення змінної оточення для дозволу виконання Composer як root/superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

# Встановлюємо робочу директорію
WORKDIR /var/www/html

# Виконуємо composer install для встановлення залежностей
RUN composer install --no-dev

# Запускаємо веб-сервер PHP
CMD ["php", "-S", "0.0.0.0:80", "-t", "/var/www/html/public"]