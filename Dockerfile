# 1. БАЗОВИЙ ОБРАЗ (завантажується 1 раз)
FROM dunglas/frankenphp:1-php8.4 AS runtime

WORKDIR /app

# 2. СИСТЕМНІ ЕКСТЕНШЕНИ (закешовані! Не перебудовуються при пуші коду)
RUN install-php-extensions \
    pdo_pgsql \
    pdo_mysql \
    intl \
    zip \
    opcache \
    apcu

# 3. КОНФІГУРАЦІЯ PHP ТА КОМПОЗЕР (закешовані!)
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV APP_ENV=prod \
    APP_DEBUG=0 \
    FRANKENPHP_CONFIG="web_path /app/public"

# 4. ВЕНДОРИ / COMPOSER (закешовані! Перебудовуються ТІЛЬКИ якщо змінився composer.lock)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# 5. ТВІЙ КОД ПРОЄКТУ (ось ця частина і все нижче виконується за 3 секунди при кожному пуші)
COPY . .

# 6. ФІНАЛЬНІ СКРИПТИ
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev && \
    php bin/console cache:clear && \
    php bin/console cache:warmup && \
    chown -R www-data:www-data /app/var

EXPOSE 80 443
