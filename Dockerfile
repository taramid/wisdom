# =========================================================
# STAGE 1: Збірка залежностей Composer
# =========================================================
FROM composer:2 AS composer_builder

WORKDIR /app

# Копіюємо тільки composer-файли для ефективного кешування шарів Docker
COPY composer.json composer.lock ./

# Встановлюємо залежності без dev-пакетів та без запуску скриптів
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

# Копіюємо весь проєкт та збираємо оптимізований автолоадер
COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev


# =========================================================
# STAGE 2: Production Runtime (FrankenPHP + PHP 8.4)
# =========================================================
FROM dunglas/frankenphp:1-php8.4 AS runtime

WORKDIR /app

# У FrankenPHP вбудовано скрипт install-php-extensions
# Додавай потрібні тобі розширення через пробіл (наприклад: pdo_pgsql, pdo_mysql, redis, gd)
RUN install-php-extensions \
    pdo_pgsql \
    intl \
    redis \
    opcache \
    apcu

# Використовуємо стандартний продакшн-конфіг PHP
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Додаємо тюнінг OPcache, JIT та APCu під Prod
RUN echo "opcache.enable=1" >> $PHP_INI_DIR/conf.d/00-prod.ini && \
    echo "opcache.memory_consumption=256" >> $PHP_INI_DIR/conf.d/00-prod.ini && \
    echo "opcache.max_accelerated_files=20000" >> $PHP_INI_DIR/conf.d/00-prod.ini && \
    echo "opcache.jit=tracing" >> $PHP_INI_DIR/conf.d/00-prod.ini && \
    echo "opcache.jit_buffer_size=100M" >> $PHP_INI_DIR/conf.d/00-prod.ini && \
    echo "apc.enable_cli=1" >> $PHP_INI_DIR/conf.d/00-prod.ini

# Передаємо змінні середовища для Prod та вказуємо FrankenPHP публічну папку
ENV APP_ENV=prod \
    APP_DEBUG=0 \
    FRANKENPHP_CONFIG="web_path /app/public"

# Копіюємо зібрані залежності та код із Stage 1
COPY --from=composer_builder /app /app

# 2. Збірка фронтенду (Tailwind CSS + AssetMapper)
RUN php bin/console tailwind:build --minify && \
    php bin/console asset-map:compile

# Прогріваємо кеш Symfony
RUN php bin/console cache:clear && \
    php bin/console cache:warmup

# Налаштовуємо права на папку кешу, логи та згенеровані асети
RUN chown -R www-data:www-data /app/var /app/public

EXPOSE 80 443 443/udp
