# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# 1. Front-end : compile Vue + Vite vers web/dist
# ---------------------------------------------------------------------------
FROM node:24-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js ./
COPY src ./src
RUN npm run build

# ---------------------------------------------------------------------------
# 2. Application : PHP 8.4 + FrankenPHP (serveur web Caddy intégré)
# ---------------------------------------------------------------------------
FROM dunglas/frankenphp:1-php8.4 AS app

# Extensions requises ou recommandées par Craft 5
RUN install-php-extensions pdo_mysql intl gd zip bcmath opcache

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-craft.ini"
COPY docker/Caddyfile /etc/caddy/Caddyfile

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app

# Dépendances PHP d'abord, pour profiter du cache Docker
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress

COPY . .
COPY --from=assets /app/web/dist ./web/dist

RUN composer dump-autoload --no-dev --optimize --no-interaction \
 && mkdir -p storage/runtime storage/logs web/cpresources web/uploads

# Railway fournit $PORT ; 8080 par défaut en local
ENV PORT=8080
EXPOSE 8080

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
