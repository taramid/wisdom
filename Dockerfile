# --------------------------------------------------------- stage 1 --- builder

FROM dunglas/frankenphp:1-php8.4 AS builder

WORKDIR /app

# utilities for Composer
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl ca-certificates git unzip \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions intl redis @composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

# copy it all
COPY . .

# optimized class autoload
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev

# .env + .env.prod = .env.local.php
RUN composer dump-env prod

# frontend & cache
RUN export APP_ENV=prod APP_DEBUG=0 APP_SECRET=dummy && \
    php bin/console importmap:install -vvv && \
    php bin/console tailwind:build --minify -vvv && \
    php bin/console asset-map:compile -vvv && \
    php bin/console cache:clear && \
    php bin/console cache:warmup


# --------------------------------------------------------- stage 2 --- runtime

FROM dunglas/frankenphp:1-php8.4 AS runtime

WORKDIR /app

RUN install-php-extensions pdo_pgsql intl redis opcache apcu

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
    { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=256'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.jit=tracing'; \
        echo 'opcache.jit_buffer_size=100M'; \
        echo 'apc.enable_cli=1'; \
    } > $PHP_INI_DIR/conf.d/00-prod.ini

ENV APP_ENV=prod \
    APP_DEBUG=0 \
    SERVER_NAME=:80 \
    FRANKENPHP_CONFIG="worker /app/public/index.php"

COPY --from=builder /app/bin /app/bin
COPY --from=builder /app/config /app/config
COPY --from=builder /app/migrations /app/migrations
COPY --from=builder /app/public /app/public
COPY --from=builder /app/src /app/src
COPY --from=builder /app/templates /app/templates
COPY --from=builder /app/var/cache/prod /app/var/cache/prod
COPY --from=builder /app/vendor /app/vendor
COPY --from=builder /app/.env.local.php /app/.env.local.php
COPY --from=builder /app/composer.json /app/

RUN mkdir -p /app/var/log

RUN touch /app/.env

RUN chown -R www-data:www-data /app/var /app/public

EXPOSE 80 443 443/udp

# migrations (inside, so it works with Watchtower)
RUN printf '#!/bin/sh\nset -e\necho "Running database migrations..."\nphp bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration\nexec "$@"\n' > /usr/local/bin/docker-entrypoint && \
    chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]


CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
