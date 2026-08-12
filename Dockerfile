# =========================================================
# STAGE 1: Builder (Збірка залежностей, асетів та кешу)
# =========================================================
FROM dunglas/frankenphp:1-php8.4 AS builder

WORKDIR /app

# Ставимо утиліти, необхідні для завантаження пакетів та роботи Composer
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl ca-certificates git unzip \
    && rm -rf /var/lib/apt/lists/*

# Встановлюємо Composer та базові розширення для компіляції Symfony
RUN install-php-extensions intl redis @composer

# 1. Завантажуємо залежності Composer (оптимізація кешу Docker)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

# 2. Копіюємо весь код проєкту
COPY . .

# 3. Генеруємо оптимізований автолоадер
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev

# 4. Збираємо фронтенд
# Фейкові змінні потрібні, щоб Symfony Kernel зміг запуститися для консольних команд
ENV APP_ENV=prod APP_DEBUG=0 APP_SECRET=build_time_dummy_secret
RUN php bin/console importmap:install -vvv && \
    php bin/console tailwind:build --minify -vvv && \
    php bin/console asset-map:compile -vvv

# 5. Прогріваємо кеш для продакшену
RUN php bin/console cache:clear && \
    php bin/console cache:warmup


# =========================================================
# STAGE 2: Clean Production Runtime (Хірургічне копіювання)
# =========================================================
FROM dunglas/frankenphp:1-php8.4 AS runtime

WORKDIR /app

# Встановлюємо ТІЛЬКИ ті розширення, що потрібні для роботи з базою та кешем
RUN install-php-extensions pdo_pgsql intl redis opcache apcu

# Налаштовуємо php.ini та агресивний OPcache під продакшн
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
    { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=256'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.jit=tracing'; \
        echo 'opcache.jit_buffer_size=100M'; \
        echo 'apc.enable_cli=1'; \
    } > $PHP_INI_DIR/conf.d/00-prod.ini

# Базові змінні середовища для FrankenPHP (секрети та доступи до БД передасиш на сервері)
ENV APP_ENV=prod \
    APP_DEBUG=0 \
    FRANKENPHP_CONFIG="worker /app/public/index.php"

# ЗАБИРАЄМО ЛИШЕ ПОТРІБНЕ (жодного сміття чи бінарників збірки):
# 1. Основний код та конфіги
COPY --from=builder /app/bin /app/bin
COPY --from=builder /app/config /app/config
COPY --from=builder /app/migrations /app/migrations
COPY --from=builder /app/public /app/public
COPY --from=builder /app/src /app/src
COPY --from=builder /app/templates /app/templates
# 3.1 ЗАБИРАЄМО ЛИШЕ КЕШ (без бінарника tailwind, який лежить у var/tailwind)
COPY --from=builder /app/var/cache/prod /app/var/cache/prod
COPY --from=builder /app/vendor /app/vendor
COPY --from=builder /app/composer.json /app/

# 3.2 Створюємо чисту пусту папку для логів, якщо вона потрібна
RUN mkdir -p /app/var/log

RUN touch /app/.env

# Права
RUN chown -R www-data:www-data /app/var /app/public

EXPOSE 80 443 443/udp

# ---------------------------------------------------------
# ENTRYPOINT (Міграції прямо з Dockerfile)
# ---------------------------------------------------------
RUN printf '#!/bin/sh\nset -e\necho "Running database migrations..."\nphp bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration\nexec "$@"\n' > /usr/local/bin/docker-entrypoint && \
    chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]


CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
